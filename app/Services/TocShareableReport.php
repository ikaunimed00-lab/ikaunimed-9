<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use Carbon\CarbonImmutable;
use Illuminate\Support\Number;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TocShareableReport
{
    public function __construct(private TocDashboardStats $stats) {}

    /**
     * WhatsApp-ready summary without registrant PII.
     */
    public function whatsappSummary(?CarbonImmutable $now = null): string
    {
        $summary = $this->stats->summary();
        $now ??= CarbonImmutable::now(Kegiatan::TIMEZONE)->locale('id');
        $closesAt = PendaftarTeacherOfChampions::registrationClosesAt()
            ->copy()
            ->setTimezone(Kegiatan::TIMEZONE)
            ->locale('id');

        $remainingDays = $now->greaterThan($closesAt)
            ? 0
            : (int) $now->startOfDay()->diffInDays($closesAt->copy()->startOfDay());

        $statuses = $summary['statuses'];
        $jenjang = collect($summary['jenjang'])
            ->map(fn (array $row): string => $this->shortJenjang($row['label']).' '.$this->formatNumber($row['total']))
            ->implode(' · ');

        $lines = [
            '*Update Pendaftar Teacher of Champions*',
            'Per '.$now->translatedFormat('l').', '.$now->translatedFormat('j F Y').', '.$now->format('H.i').' WIB',
            'Total: '.$this->formatNumber($summary['total']).' | Hari ini: '.$this->formatNumber($summary['today']),
            'Status: baru '.$this->formatNumber($statuses['baru'] ?? 0)
                .' · terverifikasi '.$this->formatNumber($statuses['terverifikasi'] ?? 0)
                .' · lolos '.$this->formatNumber($statuses['lolos'] ?? 0)
                .' · ditolak '.$this->formatNumber($statuses['ditolak'] ?? 0),
            'Alumni UNIMED: '.$this->formatNumber($summary['alumni'])
                .' ('.$this->formatPercentage($summary['alumni_percentage']).'%)',
            'Top provinsi: '.$this->rankedList($summary['provinces'], 3),
            'Jenjang: '.($jenjang !== '' ? $jenjang : '-'),
            'Bidang studi teratas: '.$this->rankedList($summary['bidang_studi'], 3),
            'Sisa waktu: '.$this->formatNumber($remainingDays).' hari menuju '.$closesAt->translatedFormat('j M Y').' '.$closesAt->format('H.i').' WIB',
            'Info: ikaunimed.or.id/toc',
        ];

        return implode("\n", $lines);
    }

    /**
     * Aggregate-only CSV (no registrant PII).
     */
    public function downloadCsv(): StreamedResponse
    {
        $summary = $this->stats->summary();
        $filename = 'toc-statistik-'.CarbonImmutable::now(Kegiatan::TIMEZONE)->format('Ymd-Hi').'.csv';

        return response()->streamDownload(function () use ($summary): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['metrik', 'nilai']);
            fputcsv($out, ['total', $summary['total']]);
            fputcsv($out, ['hari_ini', $summary['today']]);
            fputcsv($out, ['alumni_unimed', $summary['alumni']]);
            fputcsv($out, ['alumni_unimed_persen', $summary['alumni_percentage']]);

            foreach ($summary['statuses'] as $status => $total) {
                fputcsv($out, ['status_'.$status, $total]);
            }

            fputcsv($out, []);
            fputcsv($out, ['tanggal_wib', 'pendaftar']);
            foreach ($summary['daily'] as $date => $total) {
                fputcsv($out, [$date, $total]);
            }

            fputcsv($out, []);
            fputcsv($out, ['peringkat', 'provinsi', 'total']);
            foreach (array_values($summary['provinces']) as $index => $row) {
                fputcsv($out, [$index + 1, $row['label'], $row['total']]);
            }

            fputcsv($out, []);
            fputcsv($out, ['jenjang', 'total']);
            foreach ($summary['jenjang'] as $row) {
                fputcsv($out, [$row['label'], $row['total']]);
            }

            fputcsv($out, []);
            fputcsv($out, ['peringkat', 'bidang_studi', 'total']);
            foreach (array_values($summary['bidang_studi']) as $index => $row) {
                fputcsv($out, [$index + 1, $row['label'], $row['total']]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  list<array{label: string, total: int}>  $rows
     */
    private function rankedList(array $rows, int $limit): string
    {
        $slice = array_slice($rows, 0, $limit);

        if ($slice === []) {
            return '-';
        }

        return collect($slice)
            ->values()
            ->map(fn (array $row, int $index): string => ($index + 1).') '.$row['label'].' '.$this->formatNumber($row['total']))
            ->implode(' ');
    }

    private function shortJenjang(string $label): string
    {
        return match (true) {
            str_starts_with($label, 'TK') => 'TK',
            str_starts_with($label, 'SD') => 'SD',
            str_starts_with($label, 'SMP') => 'SMP',
            str_starts_with($label, 'SMA') => 'SMA',
            str_starts_with($label, 'SMK') => 'SMK',
            str_starts_with($label, 'SLB') => 'SLB',
            default => $label,
        };
    }

    private function formatNumber(int|float $value, int $precision = 0): string
    {
        return Number::format($value, precision: $precision, locale: 'id');
    }

    private function formatPercentage(float $value): string
    {
        if (fmod($value, 1.0) === 0.0) {
            return Number::format((int) $value, locale: 'id');
        }

        return Number::format($value, precision: 1, locale: 'id');
    }
}
