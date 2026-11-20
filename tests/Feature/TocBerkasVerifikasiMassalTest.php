<?php

use App\Filament\Resources\TocBerkasFinalis\Pages\ManageTocBerkasFinalis;
use App\Filament\Resources\TocBerkasFinalis\TocBerkasFinalisResource;
use App\Jobs\GenerateTocBerkasKeputusanZipJob;
use App\Jobs\SendTocBerkasRejectionMailJob;
use App\Mail\TocBerkasDitolakMail;
use App\Models\Notification;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use App\Models\TocBerkasAuditLog;
use App\Models\TocBerkasExportArtifact;
use App\Models\TocBerkasRejectionMailLog;
use App\Models\User;
use App\Services\TocBerkasKeputusanExportService;
use App\Services\TocBerkasKeputusanImportService;
use App\Services\TocBerkasVerificationService;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('local');
    config([
        'toc.pengumuman_dibuka' => true,
        'toc.batas_unggah' => '2026-11-18 23:59:59',
        'toc.batas_unggah_ulang' => '2026-11-18 23:59:59',
    ]);
    Carbon::setTestNow(Carbon::parse('2026-11-12 10:00', 'Asia/Jakarta')->utc());
});

afterEach(function () {
    Carbon::setTestNow();
});

function verifMassalAdmin(string $role = 'admin'): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function verifMassalPeserta(array $attributes = []): PendaftarTeacherOfChampions
{
    return PendaftarTeacherOfChampions::factory()->lulus()->create($attributes);
}

function verifMassalBerkas(
    PendaftarTeacherOfChampions $peserta,
    string $jenis,
    string $status = TocBerkas::STATUS_MENUNGGU,
    string $extension = 'jpg',
): TocBerkas {
    $path = 'toc/berkas/'.$peserta->nomor_pendaftaran.'/'.$jenis.'-'.uniqid().'.'.$extension;
    Storage::disk('local')->put($path, 'bytes-'.$jenis);

    return TocBerkas::query()->create([
        'pendaftar_id' => $peserta->id,
        'jenis' => $jenis,
        'path' => $path,
        'nama_asli' => 'Dokumen Asli.'.strtoupper($extension),
        'mime' => 'image/jpeg',
        'ukuran' => 10,
        'status' => $status,
        'catatan' => $status === TocBerkas::STATUS_DITOLAK ? 'Alasan lama' : null,
        'uploaded_at' => now(),
    ]);
}

function verifMassalCompleteSet(PendaftarTeacherOfChampions $peserta, string $status = TocBerkas::STATUS_MENUNGGU): array
{
    return [
        TocBerkas::JENIS_FOTO => verifMassalBerkas($peserta, TocBerkas::JENIS_FOTO, $status),
        TocBerkas::JENIS_PAKTA => verifMassalBerkas($peserta, TocBerkas::JENIS_PAKTA, $status, 'pdf'),
        TocBerkas::JENIS_SURAT => verifMassalBerkas($peserta, TocBerkas::JENIS_SURAT, $status, 'png'),
    ];
}

/**
 * @param  list<array<string, mixed>>  $rows
 */
function verifMassalXlsx(array $rows): string
{
    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('toc-keputusan-', true).'.xlsx';
    $headers = TocBerkasKeputusanExportService::headers();

    $writer = new Writer;
    $writer->openToFile($path);
    $writer->addRow(Row::fromValues($headers));

    foreach ($rows as $row) {
        $writer->addRow(Row::fromValues(array_map(fn (string $header) => $row[$header] ?? '', $headers)));
    }

    $writer->close();

    return $path;
}

/**
 * @return array<string, list<array<int, mixed>>>
 */
function verifMassalReadXlsx(string $path): array
{
    $reader = new Reader;
    $reader->open($path);
    $sheets = [];

    foreach ($reader->getSheetIterator() as $sheet) {
        $sheets[$sheet->getName()] = [];

        foreach ($sheet->getRowIterator() as $row) {
            $sheets[$sheet->getName()][] = $row->toArray();
        }
    }

    $reader->close();

    return $sheets;
}

function verifMassalFotoRow(PendaftarTeacherOfChampions $peserta, TocBerkas $foto, string $keputusan, string $alasan = ''): array
{
    return [
        'nomor_pendaftaran' => $peserta->nomor_pendaftaran,
        'nama' => $peserta->nama,
        'foto_keputusan' => $keputusan,
        'foto_alasan' => $alasan,
        '_foto_id' => $foto->id,
        '_foto_ver' => $foto->updated_at->toIso8601String(),
    ];
}

function verifMassalBerkasSession(PendaftarTeacherOfChampions $peserta): array
{
    return [
        config('toc.berkas.session_key') => $peserta->id,
        config('toc.berkas.session_key').'_expires_at' => now()->addHour()->timestamp,
    ];
}

it('builds a zip with one folder per peserta, empty folders for missing berkas and the keputusan workbook', function () {
    $lengkap = verifMassalPeserta(['nomor_pendaftaran' => 'TOC-2026-0001', 'nama' => 'Budi: "Guru" <Hebat>/Medan']);
    $kosong = verifMassalPeserta(['nomor_pendaftaran' => 'TOC-2026-0002', 'nama' => 'Siti Kosong']);
    $set = verifMassalCompleteSet($lengkap);
    $set[TocBerkas::JENIS_SURAT]->update(['status' => TocBerkas::STATUS_DITOLAK, 'catatan' => 'Buram']);

    $zipPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('toc-test-', true).'.zip';
    $count = app(TocBerkasKeputusanExportService::class)->buildZip(
        PendaftarTeacherOfChampions::query()->whereIn('id', [$lengkap->id, $kosong->id])->orderBy('nomor_pendaftaran')->get(),
        $zipPath,
    );

    expect($count)->toBe(2);

    $zip = new ZipArchive;
    $zip->open($zipPath);
    $names = collect(range(0, $zip->numFiles - 1))->map(fn (int $i) => $zip->getNameIndex($i))->all();

    expect($names)->toContain('TOC-2026-0001 - Budi Guru HebatMedan/foto.jpg')
        ->toContain('TOC-2026-0001 - Budi Guru HebatMedan/pakta-integritas.pdf')
        ->toContain('TOC-2026-0001 - Budi Guru HebatMedan/surat-keterangan.png')
        ->toContain('TOC-2026-0002 - Siti Kosong/')
        ->toContain('keputusan-berkas.xlsx');
    expect(collect($names)->filter(fn (string $n) => str_starts_with($n, 'TOC-2026-0002 - Siti Kosong/') && $n !== 'TOC-2026-0002 - Siti Kosong/'))->toBeEmpty();
    expect($zip->getFromName('TOC-2026-0001 - Budi Guru HebatMedan/foto.jpg'))->toBe('bytes-foto');

    $xlsxPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('toc-test-', true).'.xlsx';
    file_put_contents($xlsxPath, $zip->getFromName('keputusan-berkas.xlsx'));
    $zip->close();

    $sheets = verifMassalReadXlsx($xlsxPath);

    expect(array_keys($sheets))->toBe(['Keputusan', 'Petunjuk'])
        ->and($sheets['Keputusan'][0])->toBe(TocBerkasKeputusanExportService::headers())
        ->and($sheets['Keputusan'])->toHaveCount(3)
        ->and($sheets['Keputusan'][1][0])->toBe('TOC-2026-0001')
        ->and($sheets['Keputusan'][1][3])->toBe('menunggu')
        ->and($sheets['Keputusan'][1][9])->toBe('ditolak')
        ->and((int) $sheets['Keputusan'][1][12])->toBe($set[TocBerkas::JENIS_FOTO]->id)
        ->and($sheets['Keputusan'][1][13])->toBe($set[TocBerkas::JENIS_FOTO]->updated_at->toIso8601String())
        ->and($sheets['Keputusan'][2][3])->toBe('belum')
        ->and($sheets['Keputusan'][2][12])->toBe('')
        ->and($sheets['Keputusan'][2][13])->toBe('')
        ->and(collect($sheets['Petunjuk'])->flatten()->implode(' '))->toContain('Terima')->toContain('Tolak');

    @unlink($zipPath);
    @unlink($xlsxPath);
});

it('sanitizes folder names and limits them to 80 characters', function () {
    $peserta = verifMassalPeserta(['nomor_pendaftaran' => 'TOC-2026-0003', 'nama' => str_repeat('Nama Panjang ', 20).'?*|']);
    $folder = app(TocBerkasKeputusanExportService::class)->folderName($peserta);

    expect(mb_strlen($folder))->toBeLessThanOrEqual(80)
        ->and($folder)->toStartWith('TOC-2026-0003 - Nama Panjang')
        ->and($folder)->not->toMatch('/[\\\\\/:*?"<>|]/');
});

it('refuses to export more than the configured maximum of peserta', function () {
    config(['toc.berkas.export_max_peserta' => 2]);

    $service = app(TocBerkasKeputusanExportService::class);

    $service->assertWithinLimit(2);
    expect(fn () => $service->assertWithinLimit(3))->toThrow(ValidationException::class);
});

it('generates the export artifact, notifies the requester and audits the export', function () {
    $admin = verifMassalAdmin();
    $peserta = verifMassalPeserta();
    verifMassalCompleteSet($peserta);

    GenerateTocBerkasKeputusanZipJob::dispatchSync($admin->id, [$peserta->id]);

    $artifact = TocBerkasExportArtifact::query()->firstOrFail();

    expect($artifact->user_id)->toBe($admin->id)
        ->and($artifact->path)->toBe('toc/exports/'.$artifact->token.'-bagian-1.zip')
        ->and($artifact->peserta_count)->toBe(1)
        ->and($artifact->part_number)->toBe(1)
        ->and($artifact->part_total)->toBe(1)
        ->and($artifact->expires_at->toIso8601String())->toBe(now()->addMinutes(60)->toIso8601String());
    Storage::disk('local')->assertExists($artifact->path);

    $notification = Notification::query()->where('user_id', $admin->id)->firstOrFail();
    expect($notification->action_url)->toContain('/admin/toc/berkas/exports/'.$artifact->token)
        ->and($notification->action_url)->toContain('signature=');

    $audit = TocBerkasAuditLog::query()->where('type', TocBerkasAuditLog::TYPE_EXPORT_ZIP)->firstOrFail();
    expect($audit->user_id)->toBe($admin->id)->and($audit->peserta_count)->toBe(1);

    $this->actingAs($admin)->get($notification->action_url)->assertOk();
});

it('partitions peserta into download parts of at most the configured size', function () {
    config(['toc.berkas.export_chunk_size' => 50]);
    $service = app(TocBerkasKeputusanExportService::class);

    expect($service->partitionIds(range(1, 50)))->toHaveCount(1)
        ->and($service->partitionIds(range(1, 51)))->toHaveCount(2)
        ->and($service->partitionIds(range(1, 514)))->toHaveCount(11)
        ->and(collect($service->partitionIds(range(1, 514)))->map(fn (array $chunk): int => count($chunk))->all())
        ->toBe([50, 50, 50, 50, 50, 50, 50, 50, 50, 50, 14]);
});

it('streams a direct zip part with folders, berkas and keputusan workbook', function () {
    $admin = verifMassalAdmin();
    $lengkap = verifMassalPeserta(['nomor_pendaftaran' => 'TOC-2026-0101', 'nama' => 'Langsung Satu']);
    $kosong = verifMassalPeserta(['nomor_pendaftaran' => 'TOC-2026-0102', 'nama' => 'Langsung Kosong']);
    verifMassalCompleteSet($lengkap);

    config(['toc.berkas.export_chunk_size' => 50]);
    $manifest = app(TocBerkasKeputusanExportService::class)
        ->rememberSelection($admin->id, [$lengkap->id, $kosong->id]);

    expect($manifest['total_bagian'])->toBe(1)
        ->and($manifest['parts'][0]['label'])->toBe('Bagian 1 (peserta 1-2)');

    $response = $this->actingAs($admin)->get($manifest['parts'][0]['url']);
    $response->assertOk();
    expect($response->headers->get('content-disposition'))->toContain('berkas-finalis-bagian-1-dari-1-');

    $zipPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('toc-direct-', true).'.zip';
    file_put_contents($zipPath, $response->streamedContent());

    $zip = new ZipArchive;
    expect($zip->open($zipPath))->toBeTrue();
    $names = collect(range(0, $zip->numFiles - 1))->map(fn (int $i) => $zip->getNameIndex($i))->all();
    expect($names)->toContain('TOC-2026-0101 - Langsung Satu/foto.jpg')
        ->toContain('keputusan-berkas.xlsx');
    expect(collect($names)->contains(fn (string $n) => str_starts_with($n, 'TOC-2026-0102 - Langsung Kosong')))->toBeTrue();

    $xlsxPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('toc-direct-x-', true).'.xlsx';
    file_put_contents($xlsxPath, $zip->getFromName('keputusan-berkas.xlsx'));
    $zip->close();

    $sheets = verifMassalReadXlsx($xlsxPath);
    expect($sheets['Keputusan'])->toHaveCount(3)
        ->and($sheets['Keputusan'][0])->toBe(TocBerkasKeputusanExportService::headers());

    expect(TocBerkasAuditLog::query()->where('type', TocBerkasAuditLog::TYPE_EXPORT_ZIP)->count())->toBe(1)
        ->and(TocBerkasAuditLog::query()->value('meta')['mode'] ?? null)->toBe('langsung');

    @unlink($zipPath);
    @unlink($xlsxPath);
});

it('limits direct zip downloads to the selecting admin and rejects editors', function () {
    $admin = verifMassalAdmin();
    $other = verifMassalAdmin();
    $editor = verifMassalAdmin('editor');
    $peserta = verifMassalPeserta();
    verifMassalCompleteSet($peserta);

    $manifest = app(TocBerkasKeputusanExportService::class)
        ->rememberSelection($admin->id, [$peserta->id]);
    $url = $manifest['parts'][0]['url'];

    $this->get($url)->assertRedirect();
    $this->actingAs($editor)->get($url)->assertForbidden();
    $this->actingAs($other)->get($url)->assertForbidden();
    $this->actingAs($admin)->get($url)->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.toc.berkas.exports.langsung', ['token' => $manifest['token'], 'bagian' => 9]))
        ->assertNotFound();
});

it('only lets the requesting admin open a valid signed export link and removes expired ones', function () {
    $admin = verifMassalAdmin();
    $other = verifMassalAdmin();
    $editor = verifMassalAdmin('editor');
    $peserta = verifMassalPeserta();

    GenerateTocBerkasKeputusanZipJob::dispatchSync($admin->id, [$peserta->id]);
    $artifact = TocBerkasExportArtifact::query()->firstOrFail();
    $url = GenerateTocBerkasKeputusanZipJob::downloadUrl($artifact);

    $this->get($url)->assertRedirect();
    $this->actingAs($editor)->get($url)->assertForbidden();
    $this->actingAs($other)->get($url)->assertForbidden();
    $this->actingAs($admin)->get(route('admin.toc.berkas.exports.download', $artifact->token))->assertForbidden();
    $this->actingAs($admin)->get($url)->assertOk();

    $artifact->update(['expires_at' => now()->subMinute()]);

    $freshUrl = URL::temporarySignedRoute('admin.toc.berkas.exports.download', now()->addMinutes(5), ['artifact' => $artifact->token]);
    $this->actingAs($admin)->get($freshUrl)->assertStatus(410);

    expect(TocBerkasExportArtifact::query()->count())->toBe(0);
    Storage::disk('local')->assertMissing($artifact->path);
});

it('cleans up expired export files with the artisan command', function () {
    $admin = verifMassalAdmin();
    Storage::disk('local')->put('toc/exports/old.zip', 'zip');
    Storage::disk('local')->put('toc/exports/new.zip', 'zip');

    TocBerkasExportArtifact::query()->create([
        'token' => (string) Str::uuid(), 'user_id' => $admin->id, 'path' => 'toc/exports/old.zip',
        'peserta_count' => 1, 'expires_at' => now()->subMinute(),
    ]);
    TocBerkasExportArtifact::query()->create([
        'token' => (string) Str::uuid(), 'user_id' => $admin->id, 'path' => 'toc/exports/new.zip',
        'peserta_count' => 1, 'expires_at' => now()->addMinutes(30),
    ]);

    $this->artisan('toc:berkas-bersihkan-ekspor')->assertSuccessful();

    Storage::disk('local')->assertMissing('toc/exports/old.zip');
    Storage::disk('local')->assertExists('toc/exports/new.zip');
    expect(TocBerkasExportArtifact::query()->count())->toBe(1);
});

it('previews without changing data and then applies the decisions', function () {
    Mail::fake();
    $admin = verifMassalAdmin();
    $peserta = verifMassalPeserta();
    $set = verifMassalCompleteSet($peserta);

    $path = verifMassalXlsx([[
        'nomor_pendaftaran' => $peserta->nomor_pendaftaran,
        'foto_keputusan' => 'Terima',
        '_foto_id' => $set[TocBerkas::JENIS_FOTO]->id,
        '_foto_ver' => $set[TocBerkas::JENIS_FOTO]->updated_at->toIso8601String(),
        'pakta_keputusan' => 'tolak',
        'pakta_alasan' => 'Tanda tangan belum ada',
        '_pakta_id' => $set[TocBerkas::JENIS_PAKTA]->id,
        '_pakta_ver' => $set[TocBerkas::JENIS_PAKTA]->updated_at->toIso8601String(),
        '_surat_id' => $set[TocBerkas::JENIS_SURAT]->id,
        '_surat_ver' => $set[TocBerkas::JENIS_SURAT]->updated_at->toIso8601String(),
    ]]);

    $service = app(TocBerkasKeputusanImportService::class);
    $preview = $service->preview($path);

    expect($preview)->toMatchArray(['terima' => 1, 'tolak' => 1, 'dilewati' => 1, 'sudah_sesuai' => 0, 'fatal' => null])
        ->and($preview['errors'])->toBe([]);
    expect($set[TocBerkas::JENIS_FOTO]->fresh()->status)->toBe(TocBerkas::STATUS_MENUNGGU)
        ->and($set[TocBerkas::JENIS_PAKTA]->fresh()->status)->toBe(TocBerkas::STATUS_MENUNGGU);
    Mail::assertNothingSent();
    expect(TocBerkasAuditLog::query()->count())->toBe(0);

    $service->apply($path, $admin);

    $foto = $set[TocBerkas::JENIS_FOTO]->fresh();
    $pakta = $set[TocBerkas::JENIS_PAKTA]->fresh();

    expect($foto->status)->toBe(TocBerkas::STATUS_DITERIMA)
        ->and($foto->verified_by)->toBe($admin->id)
        ->and($foto->verified_at)->not->toBeNull()
        ->and($pakta->status)->toBe(TocBerkas::STATUS_DITOLAK)
        ->and($pakta->catatan)->toBe('Tanda tangan belum ada')
        ->and($pakta->verified_by)->toBe($admin->id)
        ->and($set[TocBerkas::JENIS_SURAT]->fresh()->status)->toBe(TocBerkas::STATUS_MENUNGGU);

    $audit = TocBerkasAuditLog::query()->where('type', TocBerkasAuditLog::TYPE_IMPORT_KEPUTUSAN)->firstOrFail();
    expect($audit->user_id)->toBe($admin->id)
        ->and($audit->diterima_count)->toBe(1)
        ->and($audit->ditolak_count)->toBe(1);

    @unlink($path);
});

it('treats an untouched export as a full skip', function () {
    $admin = verifMassalAdmin();
    $peserta = verifMassalPeserta();
    verifMassalCompleteSet($peserta);

    $zipPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('toc-test-', true).'.zip';
    app(TocBerkasKeputusanExportService::class)->buildZip(PendaftarTeacherOfChampions::query()->get(), $zipPath);

    $zip = new ZipArchive;
    $zip->open($zipPath);
    $xlsxPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('toc-test-', true).'.xlsx';
    file_put_contents($xlsxPath, $zip->getFromName('keputusan-berkas.xlsx'));
    $zip->close();

    $summary = app(TocBerkasKeputusanImportService::class)->apply($xlsxPath, $admin);

    expect($summary)->toMatchArray(['terima' => 0, 'tolak' => 0, 'dilewati' => 3, 'fatal' => null])
        ->and($summary['errors'])->toBe([]);
    expect(TocBerkas::query()->where('status', TocBerkas::STATUS_MENUNGGU)->count())->toBe(3);

    @unlink($zipPath);
    @unlink($xlsxPath);
});

it('skips rows whose berkas version changed after the export', function () {
    $admin = verifMassalAdmin();
    $peserta = verifMassalPeserta();
    $foto = verifMassalBerkas($peserta, TocBerkas::JENIS_FOTO);

    $path = verifMassalXlsx([verifMassalFotoRow($peserta, $foto, 'Terima')]);

    DB::table('toc_berkas')->where('id', $foto->id)->update(['updated_at' => now()->addMinutes(5)]);

    $service = app(TocBerkasKeputusanImportService::class);
    $preview = $service->preview($path);

    expect($preview['terima'])->toBe(0)
        ->and($preview['errors'])->toHaveCount(1)
        ->and($preview['errors'][0]['row'])->toBe(2)
        ->and($preview['errors'][0]['message'])->toContain('versi');

    $service->apply($path, $admin);

    expect($foto->fresh()->status)->toBe(TocBerkas::STATUS_MENUNGGU);
});

it('reports row level errors for every invalid decision', function () {
    $peserta = verifMassalPeserta();
    $foto = verifMassalBerkas($peserta, TocBerkas::JENIS_FOTO);

    $path = verifMassalXlsx([
        verifMassalFotoRow($peserta, $foto, 'Tolak', ''),
        verifMassalFotoRow($peserta, $foto, 'Mungkin'),
        [...verifMassalFotoRow($peserta, $foto, 'Terima'), '_foto_id' => 99999],
        [...verifMassalFotoRow($peserta, $foto, 'Terima'), '_foto_id' => '', '_foto_ver' => ''],
    ]);

    $preview = app(TocBerkasKeputusanImportService::class)->preview($path);

    expect($preview['terima'])->toBe(0)->and($preview['tolak'])->toBe(0)
        ->and($preview['errors'])->toHaveCount(4);

    $messages = collect($preview['errors'])->keyBy('row')->map(fn (array $e) => $e['message']);

    expect($messages[2])->toContain('alasan')
        ->and($messages[3])->toContain('tidak valid')
        ->and($messages[4])->toContain('tidak dikenal')
        ->and($messages[5])->toContain('belum diunggah');
});

it('rejects a workbook without the required columns', function () {
    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('toc-bad-', true).'.xlsx';
    $writer = new Writer;
    $writer->openToFile($path);
    $writer->addRow(Row::fromValues(['kolom_lain']));
    $writer->close();

    $preview = app(TocBerkasKeputusanImportService::class)->preview($path);

    expect($preview['fatal'])->toContain('Kolom wajib');
});

it('is idempotent when the same workbook is imported twice and sends a single rejection email per peserta', function () {
    Mail::fake();
    $admin = verifMassalAdmin();
    $peserta = verifMassalPeserta();
    $foto = verifMassalBerkas($peserta, TocBerkas::JENIS_FOTO);
    $pakta = verifMassalBerkas($peserta, TocBerkas::JENIS_PAKTA, extension: 'pdf');
    $surat = verifMassalBerkas($peserta, TocBerkas::JENIS_SURAT, extension: 'pdf');

    $path = verifMassalXlsx([[
        ...verifMassalFotoRow($peserta, $foto, 'Tolak', 'Foto buram'),
        'pakta_keputusan' => 'Tolak',
        'pakta_alasan' => 'Tidak bertanda tangan',
        '_pakta_id' => $pakta->id,
        '_pakta_ver' => $pakta->updated_at->toIso8601String(),
        'surat_keputusan' => 'Terima',
        '_surat_id' => $surat->id,
        '_surat_ver' => $surat->updated_at->toIso8601String(),
    ]]);

    $service = app(TocBerkasKeputusanImportService::class);

    $first = $service->apply($path, $admin);
    $second = $service->apply($path, $admin);

    expect($first)->toMatchArray(['terima' => 1, 'tolak' => 2])
        ->and($second)->toMatchArray(['terima' => 0, 'tolak' => 0, 'sudah_sesuai' => 3])
        ->and($second['errors'])->toBe([]);
    expect($surat->fresh()->status)->toBe(TocBerkas::STATUS_DITERIMA);

    Mail::assertSent(TocBerkasDitolakMail::class, 1);
    Mail::assertSent(TocBerkasDitolakMail::class, fn (TocBerkasDitolakMail $mail) => $mail->hasTo($peserta->email)
        && count($mail->items) === 2);
    expect(TocBerkasRejectionMailLog::query()->where('status', TocBerkasRejectionMailLog::STATUS_SENT)->count())->toBe(2);
});

it('accepts all waiting berkas for many peserta and leaves other statuses alone', function () {
    $admin = verifMassalAdmin();
    $one = verifMassalPeserta();
    $two = verifMassalPeserta();
    $setOne = verifMassalCompleteSet($one);
    $setOne[TocBerkas::JENIS_SURAT]->update(['status' => TocBerkas::STATUS_DITOLAK, 'catatan' => 'Buram']);
    $setTwo = verifMassalCompleteSet($two);
    $setTwo[TocBerkas::JENIS_FOTO]->update(['status' => TocBerkas::STATUS_DITERIMA]);

    $service = app(TocBerkasVerificationService::class);
    $pendaftar = PendaftarTeacherOfChampions::query()->get();

    expect($service->waitingSummary($pendaftar))->toBe(['peserta' => 2, 'berkas' => 4]);

    $result = $service->acceptWaitingForMany($pendaftar, $admin);

    expect($result)->toBe(['peserta' => 2, 'berkas' => 4])
        ->and($setOne[TocBerkas::JENIS_SURAT]->fresh()->status)->toBe(TocBerkas::STATUS_DITOLAK)
        ->and($setOne[TocBerkas::JENIS_FOTO]->fresh()->status)->toBe(TocBerkas::STATUS_DITERIMA)
        ->and($setTwo[TocBerkas::JENIS_PAKTA]->fresh()->verified_by)->toBe($admin->id);

    expect($service->acceptWaitingForMany($pendaftar, $admin))->toBe(['peserta' => 0, 'berkas' => 0]);
    expect(TocBerkasAuditLog::query()->where('type', TocBerkasAuditLog::TYPE_ACCEPT_WAITING)->count())->toBe(2);

    $three = verifMassalPeserta();
    verifMassalCompleteSet($three);
    expect($service->acceptWaitingForPeserta($three, $admin))->toBe(3);
});

it('lets admins open the direct zip parts modal and run mass accept from the filament table', function () {
    $admin = verifMassalAdmin();
    $peserta = verifMassalPeserta();
    verifMassalCompleteSet($peserta);

    $this->actingAs($admin);
    Filament::setCurrentPanel('admin');

    $modalHtml = TocBerkasFinalisResource::keputusanExportModal(collect([$peserta]))->render();
    expect($modalHtml)->toContain('Bagian 1 (peserta 1-1)')
        ->and($modalHtml)->toContain('/admin/toc/berkas/exports/langsung/')
        ->and($modalHtml)->toContain('tanpa antrean');

    Livewire::test(ManageTocBerkasFinalis::class)
        ->assertActionExists('unduhZipKeputusan')
        ->assertActionExists('imporKeputusan')
        ->assertActionDoesNotExist('unduhanSaya');

    Livewire::test(ManageTocBerkasFinalis::class)
        ->callTableBulkAction('terimaSemuaMenunggu', [$peserta])
        ->assertNotified('3 berkas diterima');

    expect(TocBerkas::query()->where('status', TocBerkas::STATUS_DITERIMA)->count())->toBe(3);

    Livewire::test(ManageTocBerkasFinalis::class)
        ->callAction('terimaSemuaMenunggu')
        ->assertNotified('0 berkas diterima');
});

it('builds multi-part labels for selections larger than the part size', function () {
    config(['toc.berkas.export_chunk_size' => 2]);
    $admin = verifMassalAdmin();
    $ids = collect(range(1, 5))->map(fn () => verifMassalPeserta()->id)->all();

    $manifest = app(TocBerkasKeputusanExportService::class)->rememberSelection($admin->id, $ids);

    expect($manifest['total_bagian'])->toBe(3)
        ->and($manifest['parts'][0]['label'])->toBe('Bagian 1 (peserta 1-2)')
        ->and($manifest['parts'][1]['label'])->toBe('Bagian 2 (peserta 3-4)')
        ->and($manifest['parts'][2]['label'])->toBe('Bagian 3 (peserta 5-5)');
});

it('queues one rejection email with all rejected files and renders the reupload instructions', function () {
    Mail::fake();
    $admin = verifMassalAdmin();
    $peserta = verifMassalPeserta(['nama' => 'Ibu Guru Teladan']);
    $set = verifMassalCompleteSet($peserta);
    config(['toc.panitia_whatsapp' => ['089679018457', '081397718747']]);

    $service = app(TocBerkasVerificationService::class);
    $service->reject($set[TocBerkas::JENIS_FOTO], $admin, 'Resolusi kurang tajam', queueMail: false);
    $service->reject($set[TocBerkas::JENIS_PAKTA], $admin, 'Belum ditandatangani', queueMail: false);
    $service->dispatchRejectionMails([$set[TocBerkas::JENIS_FOTO]->fresh(), $set[TocBerkas::JENIS_PAKTA]->fresh()]);

    Mail::assertSent(TocBerkasDitolakMail::class, 1);

    $mail = new TocBerkasDitolakMail(
        $peserta,
        [['label' => 'Foto terbaik guru', 'catatan' => 'Resolusi kurang tajam']],
        Carbon::parse('2026-11-20 23:59', 'Asia/Jakarta'),
    );
    $html = $mail->render();

    expect($html)->toContain('Ibu Guru Teladan')
        ->toContain('Resolusi kurang tajam')
        ->toContain('https://www.ikaunimed.or.id/toc/berkas')
        ->toContain('089679018457')
        ->toContain('081397718747')
        ->toContain('23.59 WIB')
        ->toContain('JPG, JPEG, PNG')
        ->toContain('5 MB');
});

it('does not send a rejection email twice for the same rejection and skips empty emails', function () {
    Mail::fake();
    $admin = verifMassalAdmin();
    $peserta = verifMassalPeserta();
    $foto = verifMassalBerkas($peserta, TocBerkas::JENIS_FOTO);

    app(TocBerkasVerificationService::class)->reject($foto, $admin, 'Buram');
    dispatch_sync(new SendTocBerkasRejectionMailJob($peserta->id, [$foto->id]));
    dispatch_sync(new SendTocBerkasRejectionMailJob($peserta->id, [$foto->id]));

    Mail::assertSent(TocBerkasDitolakMail::class, 1);

    $tanpaEmail = verifMassalPeserta(['email' => '']);
    $surat = verifMassalBerkas($tanpaEmail, TocBerkas::JENIS_SURAT);
    app(TocBerkasVerificationService::class)->reject($surat, $admin, 'Salah dokumen');

    Mail::assertSent(TocBerkasDitolakMail::class, 1);
    expect(TocBerkasRejectionMailLog::query()->where('berkas_id', $surat->id)->value('status'))
        ->toBe(TocBerkasRejectionMailLog::STATUS_SKIPPED);

    $newRejection = TocBerkasRejectionMailLog::query()->where('berkas_id', $foto->id)->first();
    expect($newRejection->status)->toBe(TocBerkasRejectionMailLog::STATUS_SENT);

    Carbon::setTestNow(now()->addMinutes(5));
    app(TocBerkasVerificationService::class)->reject($foto->fresh(), $admin, 'Masih buram');
    Mail::assertSent(TocBerkasDitolakMail::class, 2);
});

it('sends rejection emails when berkas are rejected from the admin popup route', function () {
    Mail::fake();
    $admin = verifMassalAdmin();
    $peserta = verifMassalPeserta();
    $foto = verifMassalBerkas($peserta, TocBerkas::JENIS_FOTO);

    $this->actingAs($admin)
        ->post(route('admin.toc.berkas.tolak', $foto), ['catatan' => 'Wajah tidak terlihat'])
        ->assertRedirect();

    Mail::assertSent(TocBerkasDitolakMail::class, fn (TocBerkasDitolakMail $mail) => $mail->hasTo($peserta->email)
        && $mail->items[0]['catatan'] === 'Wajah tidak terlihat');
    expect($foto->fresh()->verified_by)->toBe($admin->id);

    $this->actingAs($admin)->post(route('admin.toc.berkas.terima', $foto))->assertRedirect();
    expect($foto->fresh()->status)->toBe(TocBerkas::STATUS_DITERIMA)->and($foto->fresh()->catatan)->toBeNull();
});

it('uses the general deadline before it passes', function () {
    config(['toc.batas_unggah_ulang' => '2026-11-22 23:59:59']);
    $peserta = verifMassalPeserta();
    verifMassalBerkas($peserta, TocBerkas::JENIS_FOTO, TocBerkas::STATUS_DITOLAK);

    $batas = app(TocBerkasVerificationService::class)->effectiveBatas($peserta);

    expect($batas->toDateTimeString())->toBe('2026-11-18 23:59:59');
});

it('gives peserta with rejected or incomplete berkas the reupload deadline after the general deadline', function () {
    config(['toc.batas_unggah_ulang' => '2026-11-22 23:59:59']);
    Carbon::setTestNow(Carbon::parse('2026-11-19 10:00', 'Asia/Jakarta')->utc());

    $ditolak = verifMassalPeserta();
    $set = verifMassalCompleteSet($ditolak);
    $set[TocBerkas::JENIS_FOTO]->update(['status' => TocBerkas::STATUS_DITOLAK, 'catatan' => 'Buram']);

    $takLengkap = verifMassalPeserta();
    verifMassalBerkas($takLengkap, TocBerkas::JENIS_FOTO);

    $lengkap = verifMassalPeserta();
    verifMassalCompleteSet($lengkap);

    $this->withSession(verifMassalBerkasSession($ditolak))
        ->get(route('toc.berkas.formulir'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('terkunci', false)
            ->where('batasUnggah', Carbon::parse('2026-11-22 23:59:59', 'Asia/Jakarta')->toIso8601String())
            ->where('berkas.0.bolehUnggah', true)
            ->where('berkas.0.catatan', 'Buram'));

    $this->withSession(verifMassalBerkasSession($takLengkap))
        ->get(route('toc.berkas.formulir'))
        ->assertInertia(fn (Assert $page) => $page->where('terkunci', false));

    $this->withSession(verifMassalBerkasSession($lengkap))
        ->get(route('toc.berkas.formulir'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('terkunci', true)
            ->where('batasUnggah', Carbon::parse('2026-11-18 23:59:59', 'Asia/Jakarta')->toIso8601String()));

    $this->withSession(verifMassalBerkasSession($lengkap))
        ->post(route('toc.berkas.unggah', TocBerkas::JENIS_FOTO), ['file' => UploadedFile::fake()->image('foto.jpg', 1080, 1350), 'setuju_publikasi' => true])
        ->assertSessionHasErrors('file');
});

it('locks everyone after the reupload deadline', function () {
    config(['toc.batas_unggah_ulang' => '2026-11-22 23:59:59']);
    Carbon::setTestNow(Carbon::parse('2026-11-23 00:30', 'Asia/Jakarta')->utc());

    $peserta = verifMassalPeserta();
    verifMassalBerkas($peserta, TocBerkas::JENIS_FOTO, TocBerkas::STATUS_DITOLAK);

    $this->withSession(verifMassalBerkasSession($peserta))
        ->get(route('toc.berkas.formulir'))
        ->assertInertia(fn (Assert $page) => $page->where('terkunci', true));
});

it('allows incomplete peserta to upload during the 21 Nov 2026 10.00 WIB susulan window', function () {
    config(['toc.batas_unggah_ulang' => '2026-11-21 10:00:00']);

    $takLengkap = verifMassalPeserta();
    verifMassalBerkas($takLengkap, TocBerkas::JENIS_FOTO);

    $lengkap = verifMassalPeserta();
    verifMassalCompleteSet($lengkap);

    $makePdf = function (string $name): UploadedFile {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('toc-susulan-', true).'.pdf';
        file_put_contents($path, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n");

        return new UploadedFile($path, $name, 'application/pdf', null, true);
    };

    foreach (['2026-11-20 12:00:00', '2026-11-21 09:59:00'] as $waktu) {
        Carbon::setTestNow(Carbon::parse($waktu, 'Asia/Jakarta')->utc());

        $this->withSession(verifMassalBerkasSession($takLengkap))
            ->get(route('toc.berkas.formulir'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('terkunci', false)
                ->where('batasUnggah', Carbon::parse('2026-11-21 10:00:00', 'Asia/Jakarta')->toIso8601String())
                ->where('batasUnggahLabel', 'Sabtu, 21 November 2026 pukul 10.00 WIB'));

        $this->withSession(verifMassalBerkasSession($takLengkap))
            ->post(route('toc.berkas.unggah', TocBerkas::JENIS_PAKTA), [
                'file' => $makePdf('pakta.pdf'),
            ])
            ->assertSessionHasNoErrors();
    }

    Carbon::setTestNow(Carbon::parse('2026-11-21 10:00:01', 'Asia/Jakarta')->utc());

    $this->withSession(verifMassalBerkasSession($takLengkap))
        ->post(route('toc.berkas.unggah', TocBerkas::JENIS_SURAT), [
            'file' => $makePdf('surat.pdf'),
        ])
        ->assertSessionHasErrors('file');

    Carbon::setTestNow(Carbon::parse('2026-11-19 00:00:01', 'Asia/Jakarta')->utc());

    $this->withSession(verifMassalBerkasSession($lengkap))
        ->get(route('toc.berkas.formulir'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('terkunci', true)
            ->where('batasUnggah', Carbon::parse('2026-11-18 23:59:59', 'Asia/Jakarta')->toIso8601String()));

    $this->withSession(verifMassalBerkasSession($lengkap))
        ->post(route('toc.berkas.unggah', TocBerkas::JENIS_FOTO), [
            'file' => UploadedFile::fake()->image('foto.jpg', 1080, 1350),
            'setuju_publikasi' => true,
        ])
        ->assertSessionHasErrors('file');
});

it('forbids editors from every verification admin endpoint', function () {
    $editor = verifMassalAdmin('editor');
    $admin = verifMassalAdmin();
    $peserta = verifMassalPeserta();
    $foto = verifMassalBerkas($peserta, TocBerkas::JENIS_FOTO);

    GenerateTocBerkasKeputusanZipJob::dispatchSync($admin->id, [$peserta->id]);
    $url = GenerateTocBerkasKeputusanZipJob::downloadUrl(TocBerkasExportArtifact::query()->firstOrFail());

    $this->actingAs($editor)->get($url)->assertForbidden();
    $this->actingAs($editor)->post(route('admin.toc.berkas.terima', $foto))->assertForbidden();
    $this->actingAs($editor)->post(route('admin.toc.berkas.tolak', $foto), ['catatan' => 'x'])->assertForbidden();
    $this->actingAs($editor)->get('/admin/berkas-finalis-toc')->assertForbidden();

    expect($foto->fresh()->status)->toBe(TocBerkas::STATUS_MENUNGGU);
});

it('walks through the filament import flow: upload, preview and confirm', function () {
    Mail::fake();
    $admin = verifMassalAdmin();
    $peserta = verifMassalPeserta();
    $foto = verifMassalBerkas($peserta, TocBerkas::JENIS_FOTO);

    $xlsx = verifMassalXlsx([verifMassalFotoRow($peserta, $foto, 'Terima')]);
    $upload = UploadedFile::fake()->createWithContent('keputusan-berkas.xlsx', file_get_contents($xlsx));

    $this->actingAs($admin);
    Filament::setCurrentPanel('admin');

    Livewire::test(ManageTocBerkasFinalis::class)
        ->callAction('imporKeputusan', ['file' => $upload])
        ->assertActionMounted('konfirmasiImporKeputusan');

    $html = view('filament.toc.impor-keputusan-pratinjau', [
        'summary' => app(TocBerkasKeputusanImportService::class)->preview($xlsx),
    ])->render();

    expect($html)->toContain('Akan diterima')->toContain('Akan ditolak');

    expect($foto->fresh()->status)->toBe(TocBerkas::STATUS_MENUNGGU);

    Livewire::test(ManageTocBerkasFinalis::class)
        ->callAction('imporKeputusan', ['file' => $upload])
        ->callMountedAction()
        ->assertNotified('Keputusan diterapkan');

    expect($foto->fresh()->status)->toBe(TocBerkas::STATUS_DITERIMA);
    @unlink($xlsx);
});

it('rejects oversized or non-xlsx files in the filament import action', function () {
    $admin = verifMassalAdmin();
    $this->actingAs($admin);
    Filament::setCurrentPanel('admin');

    Livewire::test(ManageTocBerkasFinalis::class)
        ->callAction('imporKeputusan', ['file' => UploadedFile::fake()->create('keputusan.csv', 10, 'text/csv')])
        ->assertHasActionErrors(['file']);

    Livewire::test(ManageTocBerkasFinalis::class)
        ->callAction('imporKeputusan', ['file' => UploadedFile::fake()->create('besar.xlsx', 6000, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')])
        ->assertHasActionErrors(['file']);
});
