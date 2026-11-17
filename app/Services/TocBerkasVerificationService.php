<?php

namespace App\Services;

use App\Jobs\SendTocBerkasRejectionMailJob;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use App\Models\TocBerkasAuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TocBerkasVerificationService
{
    /**
     * Menerima satu berkas. Tidak mengubah apa pun bila sudah diterima.
     */
    public function accept(TocBerkas $berkas, User $user): TocBerkas
    {
        if ($berkas->status === TocBerkas::STATUS_DITERIMA) {
            return $berkas;
        }

        $berkas->forceFill([
            'status' => TocBerkas::STATUS_DITERIMA,
            'catatan' => null,
            'verified_by' => $user->getKey(),
            'verified_at' => now(),
        ])->save();

        $this->recordKeputusan($berkas, $user, TocBerkas::STATUS_DITERIMA);

        return $berkas;
    }

    /**
     * Menolak satu berkas dan (opsional) mengantrekan email penolakan untuk peserta.
     * Gunakan $queueMail = false pada proses massal lalu panggil dispatchRejectionMails().
     */
    public function reject(TocBerkas $berkas, User $user, string $catatan, bool $queueMail = true): TocBerkas
    {
        $catatan = trim($catatan);

        if ($catatan === '') {
            throw ValidationException::withMessages([
                'catatan' => 'Alasan penolakan wajib diisi.',
            ]);
        }

        if ($berkas->status === TocBerkas::STATUS_DITOLAK && $berkas->catatan === $catatan) {
            return $berkas;
        }

        $berkas->forceFill([
            'status' => TocBerkas::STATUS_DITOLAK,
            'catatan' => $catatan,
            'verified_by' => $user->getKey(),
            'verified_at' => now(),
        ])->save();

        if ($queueMail) {
            $this->dispatchRejectionMails([$berkas]);
        }

        $this->recordKeputusan($berkas, $user, TocBerkas::STATUS_DITOLAK);

        return $berkas;
    }

    /**
     * Catat siapa, kapan, nomor peserta, dan keputusan. Tanpa isi berkas.
     */
    private function recordKeputusan(TocBerkas $berkas, User $user, string $keputusan): void
    {
        $berkas->loadMissing('pendaftar');

        TocBerkasAuditLog::record(
            $keputusan === TocBerkas::STATUS_DITERIMA ? TocBerkasAuditLog::TYPE_TERIMA : TocBerkasAuditLog::TYPE_TOLAK,
            (int) $user->getKey(),
            [
                'peserta' => 1,
                'diterima' => $keputusan === TocBerkas::STATUS_DITERIMA ? 1 : 0,
                'ditolak' => $keputusan === TocBerkas::STATUS_DITOLAK ? 1 : 0,
            ],
            [
                'nomor_pendaftaran' => $berkas->pendaftar?->nomor_pendaftaran,
                'jenis' => $berkas->jenis,
                'keputusan' => $keputusan,
            ],
        );
    }

    /**
     * Mengantrekan SATU job email per peserta untuk kumpulan berkas yang baru ditolak.
     *
     * @param  iterable<int, TocBerkas>  $rejected
     */
    public function dispatchRejectionMails(iterable $rejected): void
    {
        collect($rejected)
            ->groupBy('pendaftar_id')
            ->each(function (Collection $items, int|string $pendaftarId): void {
                SendTocBerkasRejectionMailJob::dispatch(
                    (int) $pendaftarId,
                    $items->pluck('id')->map(fn ($id): int => (int) $id)->values()->all(),
                )->afterCommit();
            });
    }

    /**
     * Menerima semua berkas berstatus menunggu milik satu peserta.
     *
     * @return int jumlah berkas yang diterima
     */
    public function acceptWaitingForPeserta(PendaftarTeacherOfChampions $peserta, User $user): int
    {
        $count = 0;

        TocBerkas::query()
            ->where('pendaftar_id', $peserta->getKey())
            ->where('status', TocBerkas::STATUS_MENUNGGU)
            ->get()
            ->each(function (TocBerkas $berkas) use ($user, &$count): void {
                $this->accept($berkas, $user);
                $count++;
            });

        return $count;
    }

    /**
     * Menerima semua berkas berstatus menunggu milik banyak peserta, lengkap dengan audit.
     *
     * @param  Collection<int, PendaftarTeacherOfChampions>  $pendaftar
     * @return array{peserta: int, berkas: int}
     */
    public function acceptWaitingForMany(Collection $pendaftar, User $user): array
    {
        $ids = $pendaftar->pluck('id')->all();

        return DB::transaction(function () use ($ids, $user): array {
            $waiting = TocBerkas::query()
                ->whereIn('pendaftar_id', $ids)
                ->where('status', TocBerkas::STATUS_MENUNGGU)
                ->get();

            foreach ($waiting as $berkas) {
                $this->accept($berkas, $user);
            }

            $result = [
                'peserta' => $waiting->pluck('pendaftar_id')->unique()->count(),
                'berkas' => $waiting->count(),
            ];

            TocBerkasAuditLog::record(TocBerkasAuditLog::TYPE_ACCEPT_WAITING, $user->getKey(), [
                'peserta' => $result['peserta'],
                'diterima' => $result['berkas'],
            ], ['dipilih' => count($ids)]);

            return $result;
        });
    }

    /**
     * Ringkasan berkas menunggu untuk modal konfirmasi.
     *
     * @param  Collection<int, PendaftarTeacherOfChampions>  $pendaftar
     * @return array{peserta: int, berkas: int}
     */
    public function waitingSummary(Collection $pendaftar): array
    {
        $pendingPerPeserta = TocBerkas::query()
            ->whereIn('pendaftar_id', $pendaftar->pluck('id')->all())
            ->where('status', TocBerkas::STATUS_MENUNGGU)
            ->selectRaw('pendaftar_id, count(*) as total')
            ->groupBy('pendaftar_id')
            ->pluck('total', 'pendaftar_id');

        return [
            'peserta' => $pendingPerPeserta->count(),
            'berkas' => (int) $pendingPerPeserta->sum(),
        ];
    }

    /**
     * Batas unggah yang berlaku bagi peserta.
     *
     * @param  Collection<int, TocBerkas>|null  $berkas  berkas milik peserta (dimuat bila null)
     */
    public function effectiveBatas(PendaftarTeacherOfChampions $peserta, ?Collection $berkas = null, ?Carbon $now = null): Carbon
    {
        $timezone = config('toc.timezone');
        $batas = Carbon::parse(config('toc.batas_unggah'), $timezone);
        $now ??= now($timezone);

        if ($now->lessThanOrEqualTo($batas)) {
            return $batas;
        }

        $berkas ??= TocBerkas::query()->where('pendaftar_id', $peserta->getKey())->get();

        $hasDitolak = $berkas->contains(fn (TocBerkas $item): bool => $item->status === TocBerkas::STATUS_DITOLAK);
        $isIncomplete = $berkas->pluck('jenis')->unique()->count() < count(config('toc.berkas.jenis'));

        if ($hasDitolak || $isIncomplete) {
            return Carbon::parse(config('toc.batas_unggah_ulang'), $timezone);
        }

        return $batas;
    }
}
