<?php

namespace App\Services;

use App\Models\PendaftarTeacherOfChampions;
use OpenSpout\Common\Entity\Cell\NumericCell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TocShareableReport
{
    public function __construct(private TocDashboardStats $stats) {}

    /**
     * @param  array<string, mixed>|null  $snapshot
     */
    public function whatsappSummary(?array $snapshot = null): string
    {
        $snapshot ??= $this->stats->snapshot();
        $now = $snapshot['generated_at'];
        $closesAt = $snapshot['closes_at'];
        $statuses = $snapshot['statuses'];

        $jenjang = collect($snapshot['jenjang'])
            ->map(fn (array $row): string => $row['label'].' '.TocDashboardStats::formatNumber($row['total']))
            ->implode(' · ');

        $lines = [
            '*Update Pendaftar Teacher of Champions*',
            'Per '.$now->translatedFormat('l').', '.$now->translatedFormat('j M Y').', '.$now->format('H.i').' WIB',
            '',
            'Total pendaftar: '.TocDashboardStats::formatNumber($snapshot['total']),
            'Hari ini: '.TocDashboardStats::formatNumber($snapshot['today']),
            'Alumni UNIMED: '.TocDashboardStats::formatNumber($snapshot['alumni'])
                .' ('.TocDashboardStats::formatPercentage($snapshot['alumni_percentage']).'%)',
            '',
            '*Status*',
            'Baru: '.TocDashboardStats::formatNumber($statuses['baru'] ?? 0)
                .' · Terverifikasi: '.TocDashboardStats::formatNumber($statuses['terverifikasi'] ?? 0)
                .' · Lolos: '.TocDashboardStats::formatNumber($statuses['lolos'] ?? 0)
                .' · Ditolak: '.TocDashboardStats::formatNumber($statuses['ditolak'] ?? 0),
            '',
            '*Top Provinsi*',
            ...$this->rankedLines($snapshot['provinces'], 3),
            '',
            '*Jenjang*',
            $jenjang !== '' ? $jenjang : '-',
            '',
            '*Bidang Studi Teratas*',
            ...$this->rankedLines($snapshot['bidang_studi'], 3),
            '',
            'Sisa waktu: '.TocDashboardStats::formatNumber($snapshot['remaining_days'])
                .' hari '.TocDashboardStats::formatNumber($snapshot['remaining_hours'])
                .' jam menuju '.$closesAt->translatedFormat('j M Y').' '.$closesAt->format('H.i').' WIB',
            'Daftar: ikaunimed.or.id/toc',
        ];

        return implode("\n", $lines);
    }

    /**
     * Payload for the client-side 1080×1350 canvas (no PII).
     *
     * @param  array<string, mixed>|null  $snapshot
     * @return array<string, mixed>
     */
    public function imagePayload(?array $snapshot = null): array
    {
        $snapshot ??= $this->stats->snapshot();
        $now = $snapshot['generated_at'];
        $closesAt = $snapshot['closes_at'];

        return [
            'generated_at_label' => $now->translatedFormat('l').', '.$now->translatedFormat('j M Y').', '.$now->format('H.i').' WIB',
            'closes_label' => 'Sisa '.$snapshot['remaining_days'].' hari '.$snapshot['remaining_hours'].' jam · tutup '.$closesAt->translatedFormat('j M Y').' '.$closesAt->format('H.i').' WIB',
            'filename' => 'toc-update-'.$now->format('Ymd-Hi').'.png',
            'total' => $snapshot['total'],
            'today' => $snapshot['today'],
            'alumni' => $snapshot['alumni'],
            'alumni_percentage_label' => TocDashboardStats::formatPercentage($snapshot['alumni_percentage']).'%',
            'statuses' => $snapshot['statuses'],
            'daily' => $snapshot['daily'],
            'provinces' => array_slice($snapshot['provinces'], 0, 5),
            'jenjang' => $snapshot['jenjang'],
            'bidang_studi' => array_slice($snapshot['bidang_studi'], 0, 5),
            'link' => 'ikaunimed.or.id/toc',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $snapshot
     */
    public function printHeader(?array $snapshot = null): string
    {
        $snapshot ??= $this->stats->snapshot();
        $now = $snapshot['generated_at'];

        return 'Dicetak '.$now->translatedFormat('l').', '.$now->translatedFormat('j M Y').', '.$now->format('H.i').' WIB';
    }

    /**
     * Dedicated print-page payload (same snapshot as WhatsApp / XLSX / PNG).
     *
     * @param  array<string, mixed>|null  $snapshot
     * @return array<string, mixed>
     */
    public function printPayload(?array $snapshot = null): array
    {
        $snapshot ??= $this->stats->snapshot();
        $now = $snapshot['generated_at'];
        $closesAt = $snapshot['closes_at'];
        $daily = $snapshot['daily'];
        $maxDaily = max(1, ...array_map('intval', array_values($daily) ?: [1]));

        return [
            'title' => 'Teacher of Champions — Update Pendaftar',
            'generated_at_label' => $now->translatedFormat('l').', '.$now->translatedFormat('j M Y').', '.$now->format('H.i').' WIB',
            'closes_at_label' => $closesAt->translatedFormat('j M Y').' '.$closesAt->format('H.i').' WIB',
            'remaining_label' => TocDashboardStats::formatNumber($snapshot['remaining_days'])
                .' hari '.TocDashboardStats::formatNumber($snapshot['remaining_hours']).' jam',
            'total' => $snapshot['total'],
            'today' => $snapshot['today'],
            'alumni' => $snapshot['alumni'],
            'alumni_percentage_label' => TocDashboardStats::formatPercentage($snapshot['alumni_percentage']).'%',
            'statuses' => $snapshot['statuses'],
            'daily' => $daily,
            'daily_max' => $maxDaily,
            'provinces' => array_slice($snapshot['provinces'], 0, 5),
            'jenjang' => $snapshot['jenjang'],
            'bidang_studi' => array_slice($snapshot['bidang_studi'], 0, 5),
            'link' => 'ikaunimed.or.id/toc',
            'total_label' => TocDashboardStats::formatNumber($snapshot['total']),
            'today_label' => TocDashboardStats::formatNumber($snapshot['today']),
            'alumni_label' => TocDashboardStats::formatNumber($snapshot['alumni']),
        ];
    }

    /**
     * Renders a real 1080×1350 PNG (no registrant PII).
     *
     * @param  array<string, mixed>|null  $snapshot
     */
    public function renderPngBinary(?array $snapshot = null): string
    {
        $p = $this->imagePayload($snapshot);
        $W = 1080;
        $H = 1350;
        $im = imagecreatetruecolor($W, $H);
        imagealphablending($im, true);

        $bg = imagecolorallocate($im, 248, 250, 249);
        $green = imagecolorallocate($im, 11, 61, 46);
        $teal = imagecolorallocate($im, 0, 104, 55);
        $gold = imagecolorallocate($im, 255, 215, 0);
        $ink = imagecolorallocate($im, 15, 23, 42);
        $muted = imagecolorallocate($im, 71, 85, 105);
        $white = imagecolorallocate($im, 255, 255, 255);
        $border = imagecolorallocate($im, 226, 232, 240);

        imagefilledrectangle($im, 0, 0, $W, $H, $bg);
        imagefilledrectangle($im, 0, 0, $W, 200, $green);
        imagefilledrectangle($im, 0, 200, $W, 210, $gold);

        $this->pngText($im, 28, 56, 90, $white, 'Teacher of Champions');
        $this->pngText($im, 16, 56, 135, $white, 'Update pendaftar IKA UNIMED');
        $this->pngText($im, 13, 56, 175, $gold, $p['generated_at_label']);

        imagefilledrectangle($im, 56, 240, 516, 370, $white);
        imagerectangle($im, 56, 240, 516, 370, $border);
        $this->pngText($im, 12, 80, 280, $muted, 'Total pendaftar');
        $this->pngText($im, 32, 80, 340, $teal, (string) $p['total']);

        imagefilledrectangle($im, 560, 240, 1020, 370, $white);
        imagerectangle($im, 560, 240, 1020, 370, $border);
        $this->pngText($im, 12, 584, 280, $muted, 'Hari ini (WIB)');
        $this->pngText($im, 32, 584, 340, $teal, (string) $p['today']);

        $this->pngText($im, 16, 56, 420, $ink, 'Alumni UNIMED: '.$p['alumni'].' ('.$p['alumni_percentage_label'].')');
        $st = $p['statuses'];
        $this->pngText(
            $im,
            12,
            56,
            455,
            $muted,
            'Baru '.($st['baru'] ?? 0).' · Terverifikasi '.($st['terverifikasi'] ?? 0).' · Lolos '.($st['lolos'] ?? 0).' · Ditolak '.($st['ditolak'] ?? 0)
        );

        $this->pngText($im, 16, 56, 510, $ink, 'Tren harian (WIB)');
        $daily = $p['daily'];
        $chartX = 56;
        $chartY = 530;
        $chartW = $W - 112;
        $chartH = 150;
        $max = max(1, ...array_map('intval', array_values($daily) ?: [1]));
        imageline($im, $chartX, $chartY + $chartH, $chartX + $chartW, $chartY + $chartH, $border);

        if ($daily !== []) {
            $points = array_values($daily);
            $count = count($points);
            $step = $count > 1 ? $chartW / ($count - 1) : 0;
            $prevX = null;
            $prevY = null;
            foreach ($points as $i => $n) {
                $x = (int) round($chartX + $i * $step);
                $y = (int) round($chartY + $chartH - ((int) $n / $max) * $chartH);
                if ($prevX !== null) {
                    imageline($im, $prevX, $prevY, $x, $y, $teal);
                }
                $prevX = $x;
                $prevY = $y;
            }
        }

        // Two columns for province + jenjang, then bidang below — avoids overlap on 1080×1350.
        $listTop = 710;
        $line = 26;
        $this->pngText($im, 15, 56, $listTop, $ink, 'Top Provinsi');
        foreach (array_slice($p['provinces'], 0, 5) as $i => $row) {
            $this->pngText($im, 12, 56, $listTop + 30 + ($i * $line), $muted, ($i + 1).'. '.$row['label'].' ('.$row['total'].')');
        }

        $this->pngText($im, 15, 560, $listTop, $ink, 'Jenjang');
        foreach (array_values($p['jenjang']) as $i => $row) {
            $this->pngText($im, 12, 560, $listTop + 30 + ($i * $line), $muted, ($i + 1).'. '.$row['label'].' ('.$row['total'].')');
        }

        $leftRows = min(5, count($p['provinces']));
        $rightRows = count($p['jenjang']);
        $y = $listTop + 30 + (max($leftRows, $rightRows) * $line) + 36;
        $this->pngText($im, 15, 56, $y, $ink, 'Bidang Studi Teratas');
        foreach (array_slice($p['bidang_studi'], 0, 5) as $i => $row) {
            $this->pngText($im, 12, 56, $y + 30 + ($i * $line), $muted, ($i + 1).'. '.$row['label'].' ('.$row['total'].')');
        }

        imagefilledrectangle($im, 0, $H - 70, $W, $H, $green);
        $this->pngText($im, 11, 56, $H - 28, $gold, $p['closes_label']);
        $this->pngText($im, 11, $W - 320, $H - 28, $gold, $p['link']);

        ob_start();
        imagepng($im);
        $binary = (string) ob_get_clean();
        imagedestroy($im);

        return $binary;
    }

    /**
     * @param  array<string, mixed>|null  $snapshot
     */
    public function downloadPng(?array $snapshot = null): BinaryFileResponse
    {
        $snapshot ??= $this->stats->snapshot();
        $payload = $this->imagePayload($snapshot);
        $path = tempnam(sys_get_temp_dir(), 'toc-png').'.png';
        file_put_contents($path, $this->renderPngBinary($snapshot));

        return response()->download($path, $payload['filename'], [
            'Content-Type' => 'image/png',
        ])->deleteFileAfterSend(true);
    }

    private function pngText(\GdImage $im, int $size, int $x, int $y, int $color, string $text): void
    {
        $font = 5;
        imagestring($im, $font, $x, $y - 12, $this->latin1($text), $color);
        unset($size);
    }

    private function latin1(string $text): string
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text) ?: $text;
    }

    /**
     * Single-sheet flat XLSX of aggregate stats only (no personal data).
     *
     * @param  array<string, mixed>|null  $snapshot
     */
    public function downloadXlsx(?array $snapshot = null): BinaryFileResponse
    {
        $snapshot ??= $this->stats->snapshot();
        $now = $snapshot['generated_at'];
        $filename = 'toc-statistik-'.$now->format('Ymd-Hi').'.xlsx';
        $path = tempnam(sys_get_temp_dir(), 'toc-stat').'.xlsx';

        $this->writeStatistikXlsx($path, $snapshot);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    public function writeStatistikXlsx(string $path, array $snapshot): void
    {
        $headerStyle = (new Style)->setFontBold();
        $writer = new XlsxWriter;
        $writer->openToFile($path);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Statistik ToC');
        $sheet->setColumnWidth(16, 1);
        $sheet->setColumnWidth(40, 2);
        $sheet->setColumnWidth(14, 3);
        $sheet->setColumnWidth(14, 4);
        $sheet->setSheetView((new SheetView)->setFreezeRow(2));

        $writer->addRow(Row::fromValues(['Bagian', 'Item', 'Jumlah', 'Persen (%)'], $headerStyle));

        foreach ($this->statistikFlatRows($snapshot) as $row) {
            $writer->addRow($this->statistikExcelRow($row));
        }

        $lastRow = max(1, $sheet->getWrittenRowCount());
        $sheet->setAutoFilter(new AutoFilter(0, 1, 3, $lastRow));

        $writer->close();
    }

    /**
     * Flat rows for the Statistik ToC sheet (testable without OpenSpout I/O).
     *
     * @param  array<string, mixed>  $snapshot
     * @return list<array{bagian: string, item: string, jumlah: int|float|string|null, persen: float|null}>
     */
    public function statistikFlatRows(array $snapshot): array
    {
        $total = (int) $snapshot['total'];
        $now = $snapshot['generated_at'];
        $closesAt = $snapshot['closes_at'];
        $rows = [];

        $rows[] = ['bagian' => 'Ringkasan', 'item' => 'Total pendaftar', 'jumlah' => $total, 'persen' => null];
        $rows[] = ['bagian' => 'Ringkasan', 'item' => 'Hari ini', 'jumlah' => (int) $snapshot['today'], 'persen' => null];
        $rows[] = [
            'bagian' => 'Ringkasan',
            'item' => 'Alumni UNIMED',
            'jumlah' => (int) $snapshot['alumni'],
            'persen' => (float) $snapshot['alumni_percentage'],
        ];
        $rows[] = [
            'bagian' => 'Ringkasan',
            'item' => 'Dicetak pada',
            'jumlah' => $now->format('Y-m-d H:i').' WIB',
            'persen' => null,
        ];
        $rows[] = [
            'bagian' => 'Ringkasan',
            'item' => 'Pendaftaran ditutup',
            'jumlah' => $closesAt->format('Y-m-d H:i').' WIB',
            'persen' => null,
        ];

        foreach (PendaftarTeacherOfChampions::STATUSES as $status => $label) {
            $count = (int) ($snapshot['statuses'][$status] ?? 0);
            $rows[] = [
                'bagian' => 'Status',
                'item' => $label,
                'jumlah' => $count,
                'persen' => $this->percentShare($count, $total),
            ];
        }

        foreach ($snapshot['daily'] as $date => $count) {
            $rows[] = [
                'bagian' => 'Tren Harian',
                'item' => (string) $date,
                'jumlah' => (int) $count,
                'persen' => null,
            ];
        }

        foreach (array_slice($snapshot['provinces'], 0, 10) as $row) {
            $count = (int) $row['total'];
            $rows[] = [
                'bagian' => 'Provinsi',
                'item' => $row['label'],
                'jumlah' => $count,
                'persen' => $this->percentShare($count, $total),
            ];
        }

        foreach ($snapshot['jenjang'] as $row) {
            $count = (int) $row['total'];
            $rows[] = [
                'bagian' => 'Jenjang',
                'item' => $row['label'],
                'jumlah' => $count,
                'persen' => $this->percentShare($count, $total),
            ];
        }

        foreach (array_slice($snapshot['bidang_studi'], 0, 10) as $row) {
            $count = (int) $row['total'];
            $rows[] = [
                'bagian' => 'Bidang Studi',
                'item' => $row['label'],
                'jumlah' => $count,
                'persen' => $this->percentShare($count, $total),
            ];
        }

        return $rows;
    }

    /**
     * @param  list<array{label: string, total: int}>  $rows
     * @return list<string>
     */
    private function rankedLines(array $rows, int $limit): array
    {
        $slice = array_slice($rows, 0, $limit);

        if ($slice === []) {
            return ['-'];
        }

        return collect($slice)
            ->values()
            ->map(fn (array $row, int $index): string => ($index + 1).'. '.$row['label'].' ('.TocDashboardStats::formatNumber($row['total']).')')
            ->all();
    }

    private function percentShare(int $count, int $total): ?float
    {
        if ($total <= 0) {
            return null;
        }

        return round($count / $total * 100, 1);
    }

    /**
     * @param  array{bagian: string, item: string, jumlah: int|float|string|null, persen: float|null}  $row
     */
    private function statistikExcelRow(array $row): Row
    {
        $jumlah = $row['jumlah'];
        $jumlahCell = match (true) {
            is_int($jumlah), is_float($jumlah) => new NumericCell((float) $jumlah, null),
            is_string($jumlah) => new StringCell($jumlah, null),
            default => new StringCell('', null),
        };

        $persenCell = $row['persen'] === null
            ? new StringCell('', null)
            : new NumericCell((float) $row['persen'], (new Style)->setFormat('0.0'));

        return new Row([
            new StringCell($row['bagian'], null),
            new StringCell($row['item'], null),
            $jumlahCell,
            $persenCell,
        ]);
    }
}
