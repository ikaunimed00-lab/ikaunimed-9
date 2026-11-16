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

    public int $timeout = 900;

    public int $tries = 1;

    /**
     * @param  list<int>  $pendaftarIds
     */
    public function __construct(
        public int $userId,
        public array $pendaftarIds,
    ) {}

    public function handle(TocBerkasKeputusanExportService $exports): void
    {
        $user = User::query()->find($this->userId);

        if (! $user) {
            return;
        }

        $disk = Storage::disk(config('toc.berkas.disk'));
        $token = Str::uuid()->toString();
        $directory = trim((string) config('toc.berkas.export_path', 'toc/exports'), '/');
        $relativePath = $directory.'/'.$token.'.zip';

        try {
            $exports->assertWithinLimit(count($this->pendaftarIds));

            $pendaftar = PendaftarTeacherOfChampions::query()
                ->where('lulus_penyisihan', true)
                ->whereIn('id', $this->pendaftarIds)
                ->with('berkas')
                ->orderBy('nomor_pendaftaran')
                ->get();

            $disk->makeDirectory($directory);

            $count = $exports->buildZip($pendaftar, $disk->path($relativePath));
        } catch (Throwable $exception) {
            if ($disk->exists($relativePath)) {
                $disk->delete($relativePath);
            }

            $this->notify($user, 'Ekspor berkas ToC gagal', 'ZIP berkas + Excel keputusan tidak dapat dibuat. Coba lagi atau persempit filter.', 'error');

            throw $exception;
        }

        $artifact = TocBerkasExportArtifact::query()->create([
            'token' => $token,
            'user_id' => $user->getKey(),
            'path' => $relativePath,
            'peserta_count' => $count,
            'expires_at' => now()->addMinutes((int) config('toc.berkas.export_ttl_minutes', 60)),
        ]);

        TocBerkasAuditLog::record(TocBerkasAuditLog::TYPE_EXPORT_ZIP, $user->getKey(), [
            'peserta' => $count,
        ], ['artifact' => $artifact->token]);

        $this->notify(
            $user,
            'ZIP berkas + Excel keputusan siap diunduh',
            "{$count} peserta. Tautan berlaku 1 jam dan hanya dapat dibuka oleh Anda.",
            'success',
            self::downloadUrl($artifact),
            $artifact->id,
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

    private function notify(User $user, string $title, string $message, string $type, ?string $url = null, ?int $relatedId = null): void
    {
        Notification::query()->create([
            'user_id' => $user->getKey(),
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $url,
            'action_label' => $url ? 'Unduh ZIP' : null,
            'related_id' => $relatedId,
            'related_type' => $relatedId ? TocBerkasExportArtifact::class : null,
        ]);
    }
}
