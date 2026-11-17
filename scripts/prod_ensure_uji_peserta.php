<?php

/**
 * Buat peserta uji berkas aman (TOC-UJI-xxxx). Tidak menyentuh finalis asli.
 * Cetak hanya nomor; hapus dengan --cleanup=TOC-UJI-0001
 */

use App\Models\PendaftarTeacherOfChampions;
use App\Support\TocBerkasUjiPeserta;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cleanup = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--cleanup=')) {
        $cleanup = substr($arg, strlen('--cleanup='));
    }
}

if (is_string($cleanup) && $cleanup !== '') {
    $peserta = PendaftarTeacherOfChampions::query()
        ->where('nomor_pendaftaran', $cleanup)
        ->first();

    if (! $peserta) {
        echo "missing={$cleanup}\n";
        exit(1);
    }

    TocBerkasUjiPeserta::destroy($peserta);
    echo "deleted={$cleanup}\n";
    exit(0);
}

$peserta = TocBerkasUjiPeserta::create();
echo 'nomor_pendaftaran='.$peserta->nomor_pendaftaran.PHP_EOL;
echo 'created=yes'.PHP_EOL;
