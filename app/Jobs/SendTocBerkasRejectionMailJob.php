<?php

namespace App\Jobs;

use App\Mail\TocBerkasDitolakMail;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use App\Models\TocBerkasRejectionMailLog;
use App\Services\TocBerkasVerificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendTocBerkasRejectionMailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /**
     * @param  list<int>  $berkasIds
     */
    public function __construct(
        public int $pendaftarId,
        public array $berkasIds,
    ) {}

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('toc-rejection-mail')];
    }

    public function backoff(): int
    {
        return 60;
    }

    public function handle(TocBerkasVerificationService $verification): void
    {
        $peserta = PendaftarTeacherOfChampions::query()->find($this->pendaftarId);

        if (! $peserta) {
            return;
        }

        $rejected = TocBerkas::query()
            ->where('pendaftar_id', $peserta->id)
            ->whereIn('id', $this->berkasIds)
            ->where('status', TocBerkas::STATUS_DITOLAK)
            ->whereNotNull('verified_at')
            ->get();

        if ($rejected->isEmpty()) {
            return;
        }

        $logs = $rejected->map(fn (TocBerkas $berkas): TocBerkasRejectionMailLog => TocBerkasRejectionMailLog::query()->firstOrCreate(
            ['berkas_id' => $berkas->id, 'verified_at' => $berkas->verified_at],
            [
                'pendaftar_id' => $peserta->id,
                'status' => TocBerkasRejectionMailLog::STATUS_QUEUED,
            ],
        ));

        $claimed = $logs->filter(fn (TocBerkasRejectionMailLog $log): bool => TocBerkasRejectionMailLog::query()
            ->whereKey($log->id)
            ->whereIn('status', [TocBerkasRejectionMailLog::STATUS_QUEUED, TocBerkasRejectionMailLog::STATUS_FAILED])
            ->update(['status' => TocBerkasRejectionMailLog::STATUS_SENDING, 'keterangan' => null]) === 1);

        if ($claimed->isEmpty()) {
            return;
        }

        $claimedIds = $claimed->pluck('id')->all();

        if (blank($peserta->email)) {
            $this->markLogs($claimedIds, TocBerkasRejectionMailLog::STATUS_SKIPPED, 'Email peserta kosong');

            return;
        }

        $items = $rejected
            ->filter(fn (TocBerkas $berkas): bool => $claimed->contains(
                fn (TocBerkasRejectionMailLog $log): bool => $log->berkas_id === $berkas->id,
            ))
            ->sortBy(fn (TocBerkas $berkas): int => array_search($berkas->jenis, array_keys(TocBerkas::JENIS_LABELS), true) ?: 0)
            ->map(fn (TocBerkas $berkas): array => [
                'label' => TocBerkas::JENIS_LABELS[$berkas->jenis] ?? $berkas->jenis,
                'catatan' => (string) $berkas->catatan,
            ])
            ->values()
            ->all();

        $batas = $verification->effectiveBatas($peserta);

        try {
            Mail::to($peserta->email)->send(new TocBerkasDitolakMail($peserta, $items, $batas));
        } catch (Throwable $exception) {
            $this->markLogs($claimedIds, TocBerkasRejectionMailLog::STATUS_FAILED, 'Gagal mengirim email');

            throw $exception;
        }

        $this->markLogs($claimedIds, TocBerkasRejectionMailLog::STATUS_SENT, null, now());
    }

    /**
     * @param  list<int>  $logIds
     */
    private function markLogs(array $logIds, string $status, ?string $keterangan, mixed $sentAt = null): void
    {
        TocBerkasRejectionMailLog::query()
            ->whereIn('id', $logIds)
            ->update([
                'status' => $status,
                'keterangan' => $keterangan,
                'sent_at' => $sentAt,
                'updated_at' => now(),
            ]);
    }
}
