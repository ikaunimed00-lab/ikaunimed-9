<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkasAuditLog;
use App\Models\TocBerkasExportArtifact;
use App\Models\User;
use App\Services\TocBerkasKeputusanExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class GenerateTocBerkasKeputusanZipJob implements ShouldQueue
{
    use Queueable;

    /**
     * Chunk ZIP can take several minutes on shared hosting; keep under queue retry_after.
     */
    public int $timeout = 240;

    public int $tries = 1;

    public int $userId;

    /** @var list<int> */
    public array $pendaftarIds;

    /**
     * Defaults must live on the property so legacy queued payloads (pre-chunking) unserialize safely.
     */
    public ?string $batchId = null;

    public int $partNumber = 1;

    public int $partTotal = 1;

    /**
     * @param  list<int>  $pendaftarIds
     */
    public function __construct(
        int $userId,
        array $pendaftarIds,
        ?string $batchId = null,
        int $partNumber = 1,
        int $partTotal = 1,
    ) {
        $this->userId = $userId;
        $this->pendaftarIds = $pendaftarIds;
        $this->batchId = $batchId;
        $this->partNumber = $partNumber;
        $this->partTotal = $partTotal;
    }

    public function handle(TocBerkasKeputusanExportService $exports): void
    {
        $user = User::query()->find($this->userId);

        if (! $user) {
            return;
        }

        $ids = array_values(array_unique(array_map('intval', $this->pendaftarIds)));
        $chunkSize = max(1, (int) config('toc.berkas.export_chunk_size', 100));

        // Legacy / full-selection jobs: split into parts then exit (each part is its own job).
        if (($this->batchId ?? null) === null && count($ids) > $chunkSize) {
            $this->dispatchChunkJobs($user, $ids, $chunkSize);

            return;
        }

        $batchId = $this->batchId ?: (string) Str::uuid();
        $partNumber = max(1, $this->partNumber ?: 1);
        $partTotal = max(1, $this->partTotal ?: 1);

        $disk = Storage::disk(config('toc.berkas.disk'));
        $token = (string) Str::uuid();
        $directory = trim((string) config('toc.berkas.export_path', 'toc/exports'), '/');
        $relativePath = $directory.'/'.$token.'-bagian-'.$partNumber.'.zip';

        try {
            $exports->assertWithinLimit(count($ids));

            $pendaftar = PendaftarTeacherOfChampions::query()
                ->where('lulus_penyisihan', true)
                ->whereIn('id', $ids)
                ->with('berkas')
                ->orderBy('nomor_pendaftaran')
                ->get();

            $disk->makeDirectory($directory);

            $count = $exports->buildZip($pendaftar, $disk->path($relativePath));
        } catch (Throwable $exception) {
            if ($disk->exists($relativePath)) {
                $disk->delete($relativePath);
            }

            // User-facing error is sent from failed(); rethrow so the job is marked failed.
            throw $exception;
        }

        $artifact = TocBerkasExportArtifact::query()->create([
            'token' => $token,
            'batch_id' => $batchId,
            'user_id' => $user->getKey(),
            'path' => $relativePath,
            'peserta_count' => $count,
            'part_number' => $partNumber,
            'part_total' => $partTotal,
            'expires_at' => now()->addMinutes((int) config('toc.berkas.export_ttl_minutes', 60)),
        ]);

        TocBerkasAuditLog::record(TocBerkasAuditLog::TYPE_EXPORT_ZIP, $user->getKey(), [
            'peserta' => $count,
            'bagian' => $partNumber,
            'total_bagian' => $partTotal,
        ], ['artifact' => $artifact->token, 'batch' => $batchId]);

        $label = $partTotal > 1 ? "Bagian {$partNumber}/{$partTotal}" : 'ZIP berkas + Excel keputusan';

        $this->notify(
            $user,
            "{$label} siap diunduh",
            "{$count} peserta. Tautan berlaku 1 jam dan hanya dapat dibuka oleh Anda. Lihat juga menu \"Unduhan saya\".",
            'success',
            self::downloadUrl($artifact),
            $artifact->id,
            $partTotal > 1 ? "Unduh bagian {$partNumber}" : 'Unduh ZIP',
        );
    }

    public function failed(?Throwable $exception): void
    {
        $user = User::query()->find($this->userId);

        if (! $user) {
            return;
        }

        $this->notify(
            $user,
            'Ekspor berkas ToC gagal',
            $this->failureMessage(max(1, $this->partNumber ?: 1), max(1, $this->partTotal ?: 1), $exception),
            'error',
        );
    }

    /**
     * @param  list<int>  $ids
     */
    private function dispatchChunkJobs(User $user, array $ids, int $chunkSize): void
    {
        $chunks = array_values(array_chunk($ids, $chunkSize));
        $batchId = (string) Str::uuid();
        $total = count($chunks);

        foreach ($chunks as $index => $chunk) {
            self::dispatch(
                $this->userId,
                array_values($chunk),
                $batchId,
                $index + 1,
                $total,
            );
        }

        $this->notify(
            $user,
            'Ekspor dibagi menjadi beberapa ZIP',
            count($ids)." peserta dibagi menjadi {$total} bagian (maks. {$chunkSize} peserta per ZIP). Tautan tiap bagian akan muncul di notifikasi dan \"Unduhan saya\" saat selesai.",
            'info',
        );
    }

    public static function downloadUrl(TocBerkasExportArtifact $artifact): string
    {
        return URL::temporarySignedRoute(
            'admin.toc.berkas.exports.download',
            $artifact->expires_at,
            ['artifact' => $artifact->token],
        );
    }

    private function failureMessage(int $partNumber, int $partTotal, ?Throwable $exception): string
    {
        $part = $partTotal > 1 ? "Bagian {$partNumber}/{$partTotal}: " : '';
        $hint = 'Coba lagi atau persempit filter.';

        if ($exception !== null) {
            $raw = $exception->getMessage();

            if (str_contains($raw, 'Allowed memory') || str_contains($raw, 'memory size')) {
                $hint = 'Memori server tidak cukup. Ekspor sudah dipecah per bagian; coba unduh ulang bagian yang gagal.';
            } elseif (str_contains($raw, 'Maximum execution') || str_contains($raw, 'timed out') || str_contains($raw, 'Timeout')) {
                $hint = 'Waktu proses habis. Coba unduh ulang bagian yang gagal atau persempit filter.';
            } elseif (str_contains($raw, 'Permission denied') || str_contains($raw, 'Disk') || str_contains($raw, 'No space')) {
                $hint = 'Penyimpanan server bermasalah. Hubungi admin hosting bila berulang.';
            } elseif (str_contains($raw, 'must not be accessed before initialization')) {
                $hint = 'Job lama dari sebelum perbaikan tidak bisa dilanjutkan. Jalankan ulang ekspor dari panel admin.';
            }
        }

        return $part.'ZIP berkas + Excel keputusan tidak dapat dibuat. '.$hint;
    }

    private function notify(
        User $user,
        string $title,
        string $message,
        string $type,
        ?string $url = null,
        ?int $relatedId = null,
        ?string $actionLabel = null,
    ): void {
        Notification::query()->create([
            'user_id' => $user->getKey(),
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $url,
            'action_label' => $url ? ($actionLabel ?? 'Unduh ZIP') : null,
            'related_id' => $relatedId,
            'related_type' => $relatedId ? TocBerkasExportArtifact::class : null,
        ]);
    }
}
