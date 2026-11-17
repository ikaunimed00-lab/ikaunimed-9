<?php

use App\Filament\Resources\TocBerkasFinalis\Pages\ManageTocBerkasFinalis;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Mail\TocBerkasDitolakMail;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use App\Models\TocBerkasAuditLog;
use App\Models\User;
use App\Services\TocBerkasKeputusanExportService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TocRolesSeeder;
use Filament\Facades\Filament;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, TocRolesSeeder::class]);
    Storage::fake('local');
    Filament::setCurrentPanel('admin');
});

it('seeds panitia_berkas idempotently with only its own permissions', function () {
    $this->seed(TocRolesSeeder::class);
    TocRolesSeeder::seedPanitiaBerkas();

    $role = Role::findByName(User::ROLE_PANITIA_BERKAS, 'web');
    $names = $role->permissions()->pluck('name')->sort()->values()->all();

    expect(Role::query()->where('name', User::ROLE_PANITIA_BERKAS)->count())->toBe(1)
        ->and($names)->toBe(['toc.berkas.verify', 'toc.berkas.view'])
        ->and($role->permissions()->count())->toBe(2)
        ->and(Permission::query()->where('name', 'toc.berkas.view')->count())->toBe(1);

    expect(Role::findByName('admin')->hasPermissionTo('toc.berkas.view'))->toBeFalse()
        ->and(Role::findByName(User::ROLE_PANITIA_TOC)->permissions()->pluck('name')->sort()->values()->all())
        ->toBe(['toc.dashboard.view', 'toc.pendaftar.view']);
});

it('shows only the berkas menu and rejects other admin urls', function () {
    $panitia = panitiaActor(User::ROLE_PANITIA_BERKAS);
    $email = 'kontak-rahasia@example.test';
    $whatsapp = '6281112223334';

    PendaftarTeacherOfChampions::factory()->lulus()->create([
        'nama' => 'Finalis Terlihat',
        'nomor_pendaftaran' => 'TOC-2026-9001',
        'email' => $email,
        'whatsapp' => $whatsapp,
    ]);

    $this->actingAs($panitia)
        ->get('/admin/berkas-finalis-toc')
        ->assertOk()
        ->assertSee('Berkas Finalis ToC')
        ->assertSee('Finalis Terlihat')
        ->assertDontSee($email)
        ->assertDontSee($whatsapp);

    $labels = collect(Filament::getNavigation())
        ->flatMap(fn ($group) => $group->getItems())
        ->map(fn ($item): string => (string) $item->getLabel())
        ->values()
        ->all();

    expect($labels)->toBe(['Berkas Finalis ToC']);

    foreach ([
        '/admin',
        '/admin/users',
        '/admin/roles',
        '/admin/shop/orders',
        '/admin/shop/payments',
        '/admin/pendaftar-teacher-of-champions',
        '/admin/kegiatan',
        '/admin/news',
        '/admin/toc-dashboard',
    ] as $path) {
        $this->actingAs($panitia)->get($path)->assertForbidden();
    }
});

it('blocks zip import mass accept and csv for panitia including direct urls', function () {
    $panitia = panitiaActor(User::ROLE_PANITIA_BERKAS);
    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();
    $berkas = panitiaBerkasRow($peserta, TocBerkas::JENIS_FOTO);

    $this->actingAs($panitia);
    Livewire::test(ManageTocBerkasFinalis::class)
        ->assertActionHidden('unduhZipKeputusan')
        ->assertActionHidden('imporKeputusan')
        ->assertActionHidden('eksporSemua')
        ->assertTableBulkActionHidden('terimaSemuaMenunggu')
        ->assertTableBulkActionHidden('eksporCsv')
        ->assertTableBulkActionHidden('unduhZipFoto')
        ->assertTableBulkActionHidden('unduhZipKeputusan')
        ->assertTableActionVisible('kelolaBerkas', $peserta);

    $this->actingAs($panitia)
        ->get(route('admin.toc.berkas.exports.langsung', [
            'token' => '00000000-0000-4000-8000-000000000001',
            'bagian' => 1,
        ]))
        ->assertForbidden();

    $lihat = $this->actingAs($panitia)->get(route('admin.toc.berkas.lihat', $berkas));
    $lihat->assertOk();
    expect($lihat->headers->get('content-disposition'))->toContain('inline');

    $this->actingAs(panitiaActor('admin'))
        ->get('/admin/berkas-finalis-toc')
        ->assertOk();

    Livewire::test(ManageTocBerkasFinalis::class)
        ->assertActionVisible('unduhZipKeputusan')
        ->assertActionVisible('imporKeputusan');
});

it('requires a rejection reason and emails the peserta once', function () {
    Mail::fake();
    $panitia = panitiaActor(User::ROLE_PANITIA_BERKAS);
    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create([
        'email' => 'peserta-uji@example.test',
    ]);
    $berkas = panitiaBerkasRow($peserta, TocBerkas::JENIS_FOTO);

    $this->actingAs($panitia)
        ->post(route('admin.toc.berkas.tolak', $berkas), ['catatan' => '   '])
        ->assertSessionHasErrors('catatan');

    expect($berkas->fresh()->status)->toBe(TocBerkas::STATUS_MENUNGGU);
    Mail::assertNothingSent();

    $this->actingAs($panitia)
        ->post(route('admin.toc.berkas.tolak', $berkas), ['catatan' => 'Foto buram'])
        ->assertRedirect();

    $fresh = $berkas->fresh();
    expect($fresh->status)->toBe(TocBerkas::STATUS_DITOLAK)
        ->and($fresh->catatan)->toBe('Foto buram')
        ->and($fresh->verified_by)->toBe($panitia->id)
        ->and($fresh->verified_at)->not->toBeNull();

    Mail::assertSent(TocBerkasDitolakMail::class, 1);
    Mail::assertSent(TocBerkasDitolakMail::class, fn (TocBerkasDitolakMail $mail): bool => $mail->hasTo('peserta-uji@example.test'));
});

it('locks an accepted file for panitia but lets an admin change it and writes an audit row', function () {
    Mail::fake();
    $panitia = panitiaActor(User::ROLE_PANITIA_BERKAS);
    $admin = panitiaActor('admin');
    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create([
        'nomor_pendaftaran' => 'TOC-2026-4242',
    ]);
    $berkas = panitiaBerkasRow($peserta, TocBerkas::JENIS_PAKTA, 'pdf');

    $this->actingAs($panitia)
        ->post(route('admin.toc.berkas.terima', $berkas))
        ->assertRedirect();

    $accepted = $berkas->fresh();
    expect($accepted->status)->toBe(TocBerkas::STATUS_DITERIMA)
        ->and($accepted->verified_by)->toBe($panitia->id);

    $audit = TocBerkasAuditLog::query()->where('type', TocBerkasAuditLog::TYPE_TERIMA)->firstOrFail();
    expect($audit->user_id)->toBe($panitia->id)
        ->and($audit->peserta_count)->toBe(1)
        ->and($audit->diterima_count)->toBe(1)
        ->and($audit->meta)->toMatchArray([
            'nomor_pendaftaran' => 'TOC-2026-4242',
            'jenis' => TocBerkas::JENIS_PAKTA,
            'keputusan' => TocBerkas::STATUS_DITERIMA,
        ])
        ->and($audit->meta)->not->toHaveKey('path')
        ->and(json_encode($audit->meta))->not->toContain('pakta.pdf');

    $html = view('filament.toc.berkas-detail', [
        'record' => $peserta->fresh()->load('berkas.verifier'),
    ])->render();
    expect($html)->toContain('disabled');

    $this->actingAs($panitia)
        ->post(route('admin.toc.berkas.tolak', $berkas), ['catatan' => 'Berubah pikiran'])
        ->assertForbidden();

    expect($berkas->fresh()->status)->toBe(TocBerkas::STATUS_DITERIMA);

    $this->actingAs($admin)
        ->post(route('admin.toc.berkas.tolak', $berkas), ['catatan' => 'Tanda tangan kurang'])
        ->assertRedirect();

    expect($berkas->fresh()->status)->toBe(TocBerkas::STATUS_DITOLAK)
        ->and($berkas->fresh()->verified_by)->toBe($admin->id);

    $tolak = TocBerkasAuditLog::query()->where('type', TocBerkasAuditLog::TYPE_TOLAK)->firstOrFail();
    expect($tolak->user_id)->toBe($admin->id)
        ->and($tolak->meta['nomor_pendaftaran'])->toBe('TOC-2026-4242')
        ->and($tolak->meta['keputusan'])->toBe(TocBerkas::STATUS_DITOLAK);
});

it('splits finalists into parts of 50 with the remainder in bagian 11', function () {
    $now = now();
    $rows = [];

    for ($i = 1; $i <= 514; $i++) {
        $rows[] = [
            'nomor_pendaftaran' => sprintf('TOC-2026-%04d', $i),
            'nama' => 'Finalis '.$i,
            'asal_instansi' => 'SMA Negeri 1 Medan',
            'whatsapp' => '628'.str_pad((string) $i, 10, '0', STR_PAD_LEFT),
            'email' => "finalis{$i}@example.test",
            'provinsi' => 'Sumatera Utara',
            'kabupaten_kota' => 'Kota Medan',
            'lama_mengabdi_tahun' => 5,
            'instagram' => '@finalis'.$i,
            'bidang_studi' => 'Matematika',
            'asal_universitas' => 'Universitas Negeri Medan',
            'jenjang' => 'SMA',
            'status_kepegawaian' => 'ASN',
            'persetujuan_data' => true,
            'status' => 'baru',
            'lulus_penyisihan' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    foreach (array_chunk($rows, 100) as $chunk) {
        PendaftarTeacherOfChampions::query()->insert($chunk);
    }

    $service = app(TocBerkasKeputusanExportService::class);
    $ordered = $service->finalistIdsInNomorOrder();
    $chunks = $service->partitionIds($ordered);

    expect($service->bagianFilterOptions())->toHaveCount(11)
        ->and($chunks[0])->toHaveCount(50)
        ->and($chunks[9])->toHaveCount(50)
        ->and($service->idsForBagian(1))->toBe($chunks[0])
        ->and($service->idsForBagian(11))->toBe($chunks[10])
        ->and($service->idsForBagian(11))->toHaveCount(14);

    $this->actingAs(panitiaActor(User::ROLE_PANITIA_BERKAS));

    Livewire::test(ManageTocBerkasFinalis::class)
        ->filterTable('bagian', '1')
        ->assertCountTableRecords(50)
        ->filterTable('bagian', '11')
        ->assertCountTableRecords(14)
        ->filterTable('bagian', null);
});

it('filters examination status without hiding other finalists from an admin', function () {
    $menunggu = PendaftarTeacherOfChampions::factory()->lulus()->create(['nomor_pendaftaran' => 'TOC-2026-1001']);
    $diterima = PendaftarTeacherOfChampions::factory()->lulus()->create(['nomor_pendaftaran' => 'TOC-2026-1002']);
    $ditolak = PendaftarTeacherOfChampions::factory()->lulus()->create(['nomor_pendaftaran' => 'TOC-2026-1003']);

    foreach ([TocBerkas::JENIS_FOTO, TocBerkas::JENIS_PAKTA, TocBerkas::JENIS_SURAT] as $jenis) {
        panitiaBerkasRow($menunggu, $jenis);
        panitiaBerkasRow($diterima, $jenis, status: TocBerkas::STATUS_DITERIMA);
        panitiaBerkasRow($ditolak, $jenis, status: $jenis === TocBerkas::JENIS_FOTO ? TocBerkas::STATUS_DITOLAK : TocBerkas::STATUS_MENUNGGU);
    }

    $this->actingAs(panitiaActor(User::ROLE_PANITIA_BERKAS));

    Livewire::test(ManageTocBerkasFinalis::class)
        ->filterTable('status_pemeriksaan', 'belum_diperiksa')
        ->assertCanSeeTableRecords([$menunggu])
        ->assertCanNotSeeTableRecords([$diterima, $ditolak])
        ->filterTable('status_pemeriksaan', 'sudah_diterima')
        ->assertCanSeeTableRecords([$diterima])
        ->assertCanNotSeeTableRecords([$menunggu, $ditolak])
        ->filterTable('status_pemeriksaan', 'ada_ditolak')
        ->assertCanSeeTableRecords([$ditolak])
        ->assertCanNotSeeTableRecords([$menunggu, $diterima]);
});

it('lets an admin assign panitia_berkas from the user form', function () {
    $admin = panitiaActor('admin');
    $target = User::factory()->create([
        'name' => 'Calon Panitia',
        'email' => 'calon-panitia@example.test',
        'role' => 'subscriber',
    ]);
    $target->assignRole('subscriber');

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $target->getKey()])
        ->fillForm([
            'name' => 'Calon Panitia',
            'email' => 'calon-panitia@example.test',
            'role' => 'subscriber',
            'assign_panitia_berkas' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($target->fresh()->hasRole(User::ROLE_PANITIA_BERKAS))->toBeTrue()
        ->and($target->fresh()->hasRole('subscriber'))->toBeTrue();
});

it('rate limits accept and reject actions', function () {
    $panitia = panitiaActor(User::ROLE_PANITIA_BERKAS);
    $peserta = PendaftarTeacherOfChampions::factory()->lulus()->create();
    $berkas = panitiaBerkasRow($peserta, TocBerkas::JENIS_FOTO);

    RateLimiter::for('toc-berkas-keputusan', fn (Request $request): Limit => Limit::perMinute(1)->by('toc-berkas-keputusan:'.$request->user()?->getAuthIdentifier()));

    try {
        $this->actingAs($panitia)
            ->post(route('admin.toc.berkas.terima', $berkas))
            ->assertRedirect();

        $this->actingAs($panitia)
            ->post(route('admin.toc.berkas.tolak', $berkas), ['catatan' => 'Ubah'])
            ->assertStatus(429);
    } finally {
        RateLimiter::for('toc-berkas-keputusan', function (Request $request): Limit {
            $actor = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute((int) config('toc.berkas.keputusan_per_minute', 120))
                ->by('toc-berkas-keputusan:'.$actor);
        });
    }
});

function panitiaActor(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function panitiaBerkasRow(
    PendaftarTeacherOfChampions $peserta,
    string $jenis,
    string $extension = 'jpg',
    string $status = TocBerkas::STATUS_MENUNGGU,
): TocBerkas {
    $path = 'toc/berkas/'.$peserta->nomor_pendaftaran.'/'.$jenis.'.'.$extension;
    Storage::disk('local')->put($path, 'berkas-bytes');

    return TocBerkas::query()->create([
        'pendaftar_id' => $peserta->id,
        'jenis' => $jenis,
        'path' => $path,
        'nama_asli' => $jenis.'.'.$extension,
        'mime' => $extension === 'pdf' ? 'application/pdf' : 'image/jpeg',
        'ukuran' => 12,
        'status' => $status,
        'uploaded_at' => now(),
    ]);
}
