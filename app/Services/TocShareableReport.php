<?php

namespace App\Services;

use App\Models\Kegiatan;
use Carbon\CarbonImmutable;
use OpenSpout\Common\Entity\Cell\DateTimeCell;
use OpenSpout\Common\Entity\Cell\NumericCell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
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

        $y = 720;
        $this->pngText($im, 15, 56, $y, $ink, 'Top Provinsi');
        foreach ($p['provinces'] as $i => $row) {
            $this->pngText($im, 12, 56, $y + 32 + ($i * 28), $muted, ($i + 1).'. '.$row['label'].' ('.$row['total'].')');
        }

        $y = 900;
        $this->pngText($im, 15, 56, $y, $ink, 'Jenjang');
        foreach ($p['jenjang'] as $i => $row) {
            $this->pngText($im, 12, 56, $y + 32 + ($i * 28), $muted, ($i + 1).'. '.$row['label'].' ('.$row['total'].')');
        }

        $y = 1080;
        $this->pngText($im, 15, 56, $y, $ink, 'Bidang Studi Teratas');
        foreach ($p['bidang_studi'] as $i => $row) {
            $this->pngText($im, 12, 56, $y + 32 + ($i * 28), $muted, ($i + 1).'. '.$row['label'].' ('.$row['total'].')');
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
     * Multi-sheet XLSX of aggregate stats only.
     *
     * @param  array<string, mixed>|null  $snapshot
     */
    public function downloadXlsx(?array $snapshot = null): BinaryFileResponse
    {
        $snapshot ??= $this->stats->snapshot();
        $now = $snapshot['generated_at'];
        $filename = 'toc-statistik-'.$now->format('Ymd-Hi').'.xlsx';
        $path = tempnam(sys_get_temp_dir(), 'toc-stat').'.xlsx';
        $headerStyle = (new Style)->setFontBold();

        $writer = new XlsxWriter;
        $writer->openToFile($path);

        $writer->getCurrentSheet()->setName('Ringkasan');
        $writer->addRow(Row::fromValues(['Dicetak (WIB)', $now->format('Y-m-d H:i')]));
        $writer->addRow(Row::fromValues(['Penutupan (WIB)', $snapshot['closes_at']->format('Y-m-d H:i')]));
        $writer->addRow(new Row([]));
        $writer->addRow(Row::fromValues(['Metrik', 'Nilai'], $headerStyle));

        $summaryRows = [
            ['Total pendaftar', $snapshot['total']],
            ['Hari ini', $snapshot['today']],
            ['Alumni UNIMED', $snapshot['alumni']],
            ['Alumni UNIMED (%)', $snapshot['alumni_percentage'] / 100],
            ['Status Baru', $snapshot['statuses']['baru'] ?? 0],
            ['Status Terverifikasi', $snapshot['statuses']['terverifikasi'] ?? 0],
            ['Status Lolos', $snapshot['statuses']['lolos'] ?? 0],
            ['Status Ditolak', $snapshot['statuses']['ditolak'] ?? 0],
            ['Sisa hari', $snapshot['remaining_days']],
            ['Sisa jam (sisa hari)', $snapshot['remaining_hours']],
        ];

        foreach ($summaryRows as [$label, $value]) {
            if (str_contains((string) $label, '(%)')) {
                $writer->addRow(new Row([
                    new StringCell((string) $label, null),
                    new NumericCell((float) $value, (new Style)->setFormat('0.0%')),
                ]));
            } else {
                $writer->addRow(new Row([
                    new StringCell((string) $label, null),
                    new NumericCell((float) $value, null),
                ]));
            }
        }

        $writer->addNewSheetAndMakeItCurrent()->setName('Tren Harian');
        $writer->addRow(Row::fromValues(['Tanggal', 'Pendaftar', 'Kumulatif'], $headerStyle));
        $cumulative = 0;
        foreach ($snapshot['daily'] as $date => $total) {
            $cumulative += $total;
            $writer->addRow(new Row([
                new DateTimeCell(CarbonImmutable::parse((string) $date, Kegiatan::TIMEZONE)->startOfDay()->toDateTimeImmutable(), (new Style)->setFormat('YYYY-MM-DD')),
                new NumericCell((float) $total, null),
                new NumericCell((float) $cumulative, null),
            ]));
        }

        $this->writeRankedSheet($writer, 'Provinsi', 'Provinsi', $snapshot['provinces_all'], $headerStyle);
        $this->writeRankedSheet($writer, 'Jenjang', 'Jenjang', $snapshot['jenjang'], $headerStyle);
        $this->writeRankedSheet($writer, 'Bidang Studi', 'Bidang Studi', $snapshot['bidang_studi_all'], $headerStyle);

        $writer->close();

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
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

    /**
     * @param  list<array{label: string, total: int}>  $rows
     */
    private function writeRankedSheet(XlsxWriter $writer, string $sheetName, string $labelHeader, array $rows, Style $headerStyle): void
    {
        $writer->addNewSheetAndMakeItCurrent()->setName($sheetName);
        $writer->addRow(Row::fromValues(['Peringkat', $labelHeader, 'Total'], $headerStyle));

        foreach (array_values($rows) as $index => $row) {
            $writer->addRow(new Row([
                new NumericCell((float) ($index + 1), null),
                new StringCell($row['label'], null),
                new NumericCell((float) $row['total'], null),
            ]));
        }
    }
}
