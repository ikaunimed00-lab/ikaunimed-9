<?php

namespace App\Services;

use App\Models\TocBerkas;
use App\Models\TocBerkasAuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader;
use Throwable;

class TocBerkasKeputusanImportService
{
    public const KEPUTUSAN_TERIMA = 'terima';

    public const KEPUTUSAN_TOLAK = 'tolak';

    public function __construct(protected TocBerkasVerificationService $verification) {}

    /**
     * Pratinjau tanpa mengubah data.
     *
     * @return array{rows: int, terima: int, tolak: int, dilewati: int, sudah_sesuai: int, errors: list<array{row: int, message: string}>, fatal: string|null}
     */
    public function preview(UploadedFile|string $file): array
    {
        return $this->analyze($this->pathOf($file))['summary'];
    }

    /**
     * Menerapkan keputusan dalam satu transaksi. Baris bermasalah dilewati, baris kosong tidak mengubah apa pun.
     *
     * @return array{rows: int, terima: int, tolak: int, dilewati: int, sudah_sesuai: int, errors: list<array{row: int, message: string}>, fatal: string|null}
     */
    public function apply(UploadedFile|string $file, User $user): array
    {
        $path = $this->pathOf($file);
        $rejected = new Collection;

        $summary = DB::transaction(function () use ($path, $user, $rejected): array {
            $analysis = $this->analyze($path, lock: true);
            $summary = $analysis['summary'];

            if ($summary['fatal'] !== null) {
                return $summary;
            }

            $pesertaIds = [];

            foreach ($analysis['entries'] as $entry) {
                /** @var TocBerkas $berkas */
                $berkas = $entry['berkas'];

                if ($entry['keputusan'] === self::KEPUTUSAN_TERIMA) {
                    $this->verification->accept($berkas, $user);
                } else {
                    $rejected->push($this->verification->reject($berkas, $user, $entry['alasan'], queueMail: false));
                }

                $pesertaIds[$berkas->pendaftar_id] = true;
            }

            TocBerkasAuditLog::record(TocBerkasAuditLog::TYPE_IMPORT_KEPUTUSAN, $user->getKey(), [
                'peserta' => count($pesertaIds),
                'diterima' => $summary['terima'],
                'ditolak' => $summary['tolak'],
                'dilewati' => $summary['dilewati'] + $summary['sudah_sesuai'] + count($summary['errors']),
            ], ['baris' => $summary['rows'], 'kesalahan' => count($summary['errors'])]);

            return $summary;
        });

        if ($rejected->isNotEmpty()) {
            $this->verification->dispatchRejectionMails($rejected);
        }

        return $summary;
    }

    /**
     * @return array{summary: array{rows: int, terima: int, tolak: int, dilewati: int, sudah_sesuai: int, errors: list<array{row: int, message: string}>, fatal: string|null}, entries: list<array{berkas: TocBerkas, keputusan: string, alasan: string}>}
     */
    private function analyze(string $path, bool $lock = false): array
    {
        $summary = [
            'rows' => 0,
            'terima' => 0,
            'tolak' => 0,
            'dilewati' => 0,
            'sudah_sesuai' => 0,
            'errors' => [],
            'fatal' => null,
        ];

        try {
            $rows = $this->readRows($path);
        } catch (Throwable) {
            $summary['fatal'] = 'File Excel tidak dapat dibaca. Gunakan file keputusan-berkas.xlsx hasil unduhan.';

            return ['summary' => $summary, 'entries' => []];
        }

        $header = $rows['header'];
        $required = ['nomor_pendaftaran'];

        foreach (TocBerkasKeputusanExportService::COLUMN_PREFIX as $prefix) {
            array_push($required, "{$prefix}_keputusan", "{$prefix}_alasan", "_{$prefix}_id", "_{$prefix}_ver");
        }

        $missing = array_values(array_diff($required, array_keys($header)));

        if ($missing !== []) {
            $summary['fatal'] = 'Kolom wajib tidak ditemukan: '.implode(', ', $missing).'.';

            return ['summary' => $summary, 'entries' => []];
        }

        $candidates = [];
        $ids = [];

        foreach ($rows['data'] as $rowNumber => $cells) {
            $summary['rows']++;
            $nomor = $this->cell($cells, $header, 'nomor_pendaftaran');

            foreach (TocBerkasKeputusanExportService::COLUMN_PREFIX as $jenis => $prefix) {
                $keputusanRaw = $this->cell($cells, $header, "{$prefix}_keputusan");

                if ($keputusanRaw === '') {
                    $summary['dilewati']++;

                    continue;
                }

                $label = TocBerkas::JENIS_LABELS[$jenis];
                $keputusan = mb_strtolower($keputusanRaw);

                if (! in_array($keputusan, [self::KEPUTUSAN_TERIMA, self::KEPUTUSAN_TOLAK], true)) {
                    $summary['errors'][] = $this->error($rowNumber, "{$label}: keputusan \"{$keputusanRaw}\" tidak valid (gunakan Terima, Tolak, atau kosong).");

                    continue;
                }

                $idRaw = $this->cell($cells, $header, "_{$prefix}_id");

                if ($idRaw === '') {
                    $summary['errors'][] = $this->error($rowNumber, "{$label}: berkas belum diunggah, tidak ada yang bisa diputuskan.");

                    continue;
                }

                if (! ctype_digit($idRaw)) {
                    $summary['errors'][] = $this->error($rowNumber, "{$label}: id berkas \"{$idRaw}\" tidak dikenal.");

                    continue;
                }

                $ids[] = (int) $idRaw;
                $candidates[] = [
                    'row' => $rowNumber,
                    'nomor' => $nomor,
                    'jenis' => $jenis,
                    'label' => $label,
                    'id' => (int) $idRaw,
                    'keputusan' => $keputusan,
                    'alasan' => $this->cell($cells, $header, "{$prefix}_alasan"),
                    'ver' => $this->cell($cells, $header, "_{$prefix}_ver"),
                ];
            }
        }

        $query = TocBerkas::query()->with('pendaftar:id,nomor_pendaftaran')->whereIn('id', array_unique($ids));

        if ($lock) {
            $query->lockForUpdate();
        }

        $berkasById = $query->get()->keyBy('id');
        $entries = [];
        $seen = [];

        foreach ($candidates as $candidate) {
            $berkas = $berkasById->get($candidate['id']);
            $label = $candidate['label'];

            if (! $berkas
                || $berkas->jenis !== $candidate['jenis']
                || $berkas->pendaftar?->nomor_pendaftaran !== $candidate['nomor']) {
                $summary['errors'][] = $this->error($candidate['row'], "{$label}: id berkas {$candidate['id']} tidak dikenal.");

                continue;
            }

            if (isset($seen[$berkas->id])) {
                $summary['errors'][] = $this->error($candidate['row'], "{$label}: berkas muncul lebih dari sekali di file.");

                continue;
            }

            if ($candidate['keputusan'] === self::KEPUTUSAN_TOLAK && $candidate['alasan'] === '') {
                $summary['errors'][] = $this->error($candidate['row'], "{$label}: Tolak wajib disertai alasan.");

                continue;
            }

            if ($this->isAlreadyApplied($berkas, $candidate['keputusan'], $candidate['alasan'])) {
                $seen[$berkas->id] = true;
                $summary['sudah_sesuai']++;

                continue;
            }

            if (! $this->versionMatches($berkas, $candidate['ver'])) {
                $summary['errors'][] = $this->error($candidate['row'], "{$label}: versi berkas sudah berubah sejak ekspor (diunggah ulang atau diverifikasi lagi), baris dilewati.");

                continue;
            }

            $seen[$berkas->id] = true;
            $summary[$candidate['keputusan']]++;
            $entries[] = [
                'berkas' => $berkas,
                'keputusan' => $candidate['keputusan'],
                'alasan' => $candidate['alasan'],
            ];
        }

        return ['summary' => $summary, 'entries' => $entries];
    }

    private function isAlreadyApplied(TocBerkas $berkas, string $keputusan, string $alasan): bool
    {
        if ($keputusan === self::KEPUTUSAN_TERIMA) {
            return $berkas->status === TocBerkas::STATUS_DITERIMA;
        }

        return $berkas->status === TocBerkas::STATUS_DITOLAK && $berkas->catatan === $alasan;
    }

    private function versionMatches(TocBerkas $berkas, string $version): bool
    {
        if ($version === '' || ! $berkas->updated_at) {
            return false;
        }

        try {
            return Carbon::parse($version)->equalTo($berkas->updated_at);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array{row: int, message: string}
     */
    private function error(int $row, string $message): array
    {
        return ['row' => $row, 'message' => $message];
    }

    /**
     * @param  array<int, mixed>  $cells
     * @param  array<string, int>  $header
     */
    private function cell(array $cells, array $header, string $column): string
    {
        $value = $cells[$header[$column]] ?? '';

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toIso8601String();
        }

        if (is_float($value) && floor($value) === $value) {
            $value = (int) $value;
        }

        return trim((string) $value);
    }

    /**
     * @return array{header: array<string, int>, data: array<int, array<int, mixed>>}
     */
    private function readRows(string $path): array
    {
        $reader = new Reader;
        $reader->open($path);

        $header = [];
        $data = [];

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                    $cells = $row->toArray();

                    if ($header === []) {
                        foreach ($cells as $index => $name) {
                            $key = mb_strtolower(trim((string) $name));

                            if ($key !== '' && ! isset($header[$key])) {
                                $header[$key] = $index;
                            }
                        }

                        continue;
                    }

                    if (collect($cells)->every(fn ($value): bool => trim((string) $value) === '')) {
                        continue;
                    }

                    $data[$rowNumber] = $cells;
                }

                break;
            }
        } finally {
            $reader->close();
        }

        return ['header' => $header, 'data' => $data];
    }

    private function pathOf(UploadedFile|string $file): string
    {
        return $file instanceof UploadedFile ? (string) $file->getRealPath() : $file;
    }
}
