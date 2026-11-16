<?php

namespace App\Console\Commands;

use App\Models\PendaftarTeacherOfChampions;
use App\Support\TocNomorPendaftaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TocImportLulusCommand extends Command
{
    protected $signature = 'toc:import-lulus {file} {--dry-run : Hitung saja, jangan tulis ke database}';

    protected $description = 'Import status lulus penyisihan ToC dari CSV (nomor_pendaftaran,status)';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (! File::isFile($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $expectedLulus = (int) config('toc.expected_import.lulus');
        $expectedTidakLulus = (int) config('toc.expected_import.tidak_lulus');
        $expectedTotal = (int) config('toc.expected_import.total');

        $totalPendaftar = PendaftarTeacherOfChampions::query()->count();
        $csvLulus = $this->readLulusNomor($path);

        if ($csvLulus === null) {
            return self::FAILURE;
        }

        $missingInDb = [];
        $foundIds = [];

        foreach ($csvLulus as $nomor) {
            $id = PendaftarTeacherOfChampions::query()
                ->where('nomor_pendaftaran', $nomor)
                ->value('id');

            if ($id === null) {
                $missingInDb[] = $nomor;

                continue;
            }

            $foundIds[] = (int) $id;
        }

        $lulusCount = count($foundIds);
        $tidakLulusCount = max(0, $totalPendaftar - $lulusCount);

        $this->table(
            ['Metrik', 'Hitungan', 'Diharapkan'],
            [
                ['Lulus (di CSV & DB)', number_format($lulusCount), number_format($expectedLulus)],
                ['Tidak lulus', number_format($tidakLulusCount), number_format($expectedTidakLulus)],
                ['Total pendaftar DB', number_format($totalPendaftar), number_format($expectedTotal)],
                ['Nomor CSV tidak ada di DB', number_format(count($missingInDb)), '0'],
                ['Baris lulus di CSV', number_format(count($csvLulus)), number_format($expectedLulus)],
            ]
        );

        if ($missingInDb !== []) {
            $this->warn('Nomor di CSV yang tidak ditemukan di database:');
            foreach (array_slice($missingInDb, 0, 50) as $nomor) {
                $this->line(" - {$nomor}");
            }
            if (count($missingInDb) > 50) {
                $this->line(' - ... dan '.(count($missingInDb) - 50).' lainnya');
            }
        }

        $countsMatch = $lulusCount === $expectedLulus
            && $tidakLulusCount === $expectedTidakLulus
            && $totalPendaftar === $expectedTotal
            && $missingInDb === []
            && count($csvLulus) === $expectedLulus;

        if (! $countsMatch) {
            $this->error('Hitungan tidak cocok dengan yang diharapkan. Berhenti sebelum menulis ke database.');
            $this->line(sprintf(
                'Selisih: lulus %+d, tidak lulus %+d, total %+d, missing CSV %d',
                $lulusCount - $expectedLulus,
                $tidakLulusCount - $expectedTidakLulus,
                $totalPendaftar - $expectedTotal,
                count($missingInDb),
            ));

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry-run OK. Tidak ada perubahan database.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($foundIds): void {
            PendaftarTeacherOfChampions::query()->update(['lulus_penyisihan' => false]);

            foreach (array_chunk($foundIds, 500) as $chunk) {
                PendaftarTeacherOfChampions::query()
                    ->whereIn('id', $chunk)
                    ->update(['lulus_penyisihan' => true]);
            }
        });

        $actualLulus = PendaftarTeacherOfChampions::query()->where('lulus_penyisihan', true)->count();
        $actualTidak = PendaftarTeacherOfChampions::query()->where('lulus_penyisihan', false)->count();

        $this->info(sprintf(
            'Import selesai. Lulus: %d · Tidak lulus: %d · Total: %d',
            $actualLulus,
            $actualTidak,
            $actualLulus + $actualTidak,
        ));

        return self::SUCCESS;
    }

    /**
     * @return list<string>|null
     */
    private function readLulusNomor(string $path): ?array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            $this->error("Gagal membuka file: {$path}");

            return null;
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);
            $this->error('CSV kosong.');

            return null;
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        $nomorIdx = array_search('nomor_pendaftaran', $header, true);
        $statusIdx = array_search('status', $header, true);

        if ($nomorIdx === false || $statusIdx === false) {
            fclose($handle);
            $this->error('CSV harus punya kolom nomor_pendaftaran dan status.');

            return null;
        }

        $nomors = [];
        $duplicates = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;

            if ($row === [null] || $row === false) {
                continue;
            }

            $rawNomor = trim((string) ($row[$nomorIdx] ?? ''));
            $status = strtolower(trim((string) ($row[$statusIdx] ?? '')));

            if ($rawNomor === '') {
                continue;
            }

            if ($status !== 'lulus') {
                $this->warn("Baris {$line}: status '{$status}' diabaikan (hanya 'lulus' yang diimpor).");

                continue;
            }

            $nomor = TocNomorPendaftaran::normalize($rawNomor);

            if ($nomor === null) {
                $this->error("Baris {$line}: nomor pendaftaran tidak valid: {$rawNomor}");
                fclose($handle);

                return null;
            }

            if (isset($nomors[$nomor])) {
                $duplicates[] = $nomor;

                continue;
            }

            $nomors[$nomor] = true;
        }

        fclose($handle);

        if ($duplicates !== []) {
            $this->warn('Duplikat di CSV (diambil sekali): '.implode(', ', array_unique($duplicates)));
        }

        return array_keys($nomors);
    }
}
