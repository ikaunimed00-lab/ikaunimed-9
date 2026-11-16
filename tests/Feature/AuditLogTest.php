<?php

use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Filament\Resources\AuditLogs\Pages\ManageAuditLogs;
use App\Filament\Resources\Kegiatans\Pages\ManageKegiatans;
use App\Filament\Resources\News\Pages\CreateNews;
use App\Filament\Resources\News\Pages\EditNews;
use App\Filament\Resources\PendaftarTeacherOfChampions\Pages\ManagePendaftarTeacherOfChampions;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Kegiatan;
use App\Models\News;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\User;
use App\Services\AuditLogger;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TocRolesSeeder;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, TocRolesSeeder::class]);
    Filament::setCurrentPanel('admin');
});

function auditActor(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

it('logs status and note changes on a registrant', function () {
    $user = auditActor(User::ROLE_ADMIN_TOC);
    $pendaftar = PendaftarTeacherOfChampions::factory()->create(['status' => 'baru']);

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->callTableAction('edit', $pendaftar, data: ['status' => 'terverifikasi', 'catatan_internal' => 'Cek ulang ijazah']);

    $log = AuditLog::query()->where('aksi', 'pendaftar.updated')->sole();

    expect($log->user_id)->toBe($user->id)
        ->and($log->subject_id)->toBe($pendaftar->id)
        ->and($log->perubahan['old'])->toBe(['status' => 'baru', 'catatan_internal' => null])
        ->and($log->perubahan['new'])->toBe(['status' => 'terverifikasi', 'catatan_internal' => 'Cek ulang ijazah']);
});

it('logs every registrant changed by a bulk action and marks it as bulk', function () {
    auditActor(User::ROLE_ADMIN_TOC);
    $baru = PendaftarTeacherOfChampions::factory()->count(2)->create(['status' => 'baru']);
    $lolos = PendaftarTeacherOfChampions::factory()->create(['status' => 'lolos']);

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->callTableBulkAction('tandaiTerverifikasi', [...$baru, $lolos]);

    $logs = AuditLog::query()->where('aksi', 'pendaftar.updated')->get();

    expect($logs)->toHaveCount(2)
        ->and($logs->pluck('subject_id')->sort()->values()->all())->toBe($baru->pluck('id')->sort()->values()->all())
        ->and($logs->every(fn (AuditLog $log): bool => $log->perubahan['massal'] === true))->toBeTrue();
});

it('does not log public registrations', function () {
    PendaftarTeacherOfChampions::register(PendaftarTeacherOfChampions::factory()->raw());

    expect(AuditLog::query()->count())->toBe(0);
});

it('logs exports with the active filter', function () {
    $user = auditActor(User::ROLE_ADMIN_TOC);
    PendaftarTeacherOfChampions::factory()->create(['status' => 'lolos']);
    PendaftarTeacherOfChampions::factory()->create(['status' => 'baru']);

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->filterTable('status', 'lolos')
        ->callAction('ekspor', data: ['format' => 'xlsx']);

    $log = AuditLog::query()->where('aksi', 'pendaftar.exported')->sole();

    expect($log->user_id)->toBe($user->id)
        ->and($log->perubahan['jumlah'])->toBe(1)
        ->and($log->perubahan['format'])->toBe('xlsx')
        ->and($log->perubahan['filter']['status']['value'])->toBe('lolos');
});

it('logs changes to the Teacher of Champions kegiatan only', function () {
    auditActor('admin');
    $toc = Kegiatan::factory()->create(['slug' => Kegiatan::TEACHER_OF_CHAMPIONS_SLUG, 'location' => 'Medan']);
    $other = Kegiatan::factory()->create(['slug' => 'turnamen-catur']);

    $toc->update(['location' => 'Auditorium UNIMED']);
    $other->update(['location' => 'Lapangan']);

    $log = AuditLog::query()->where('aksi', 'kegiatan.updated')->sole();

    expect($log->subject_id)->toBe($toc->id)
        ->and($log->perubahan)->toBe(['old' => ['location' => 'Medan'], 'new' => ['location' => 'Auditorium UNIMED']]);
});

it('logs admin-toc editing their kegiatan from the panel', function () {
    auditActor(User::ROLE_ADMIN_TOC);
    $toc = Kegiatan::factory()->create(['slug' => Kegiatan::TEACHER_OF_CHAMPIONS_SLUG]);

    Livewire::test(ManageKegiatans::class)
        ->callTableAction('edit', $toc, data: ['location' => 'Gedung Serbaguna']);

    expect(AuditLog::query()->where('aksi', 'kegiatan.updated')->sole()->perubahan['new']['location'])->toBe('Gedung Serbaguna')
        ->and($toc->refresh()->slug)->toBe(Kegiatan::TEACHER_OF_CHAMPIONS_SLUG);
});

it('logs news created, published and edited by admin-toc', function () {
    Storage::fake('public');
    $category = Category::query()->create(['name' => 'Kegiatan', 'slug' => 'kegiatan']);
    auditActor(User::ROLE_ADMIN_TOC);

    Livewire::test(CreateNews::class)
        ->fillForm([
            'title' => 'Pengumuman ToC',
            'slug' => 'pengumuman-toc',
            'categories' => [$category->id],
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'excerpt' => 'Ringkasan',
            'content' => '<p>Isi</p>',
            'image' => UploadedFile::fake()->image('poster.jpg'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $news = News::query()->where('slug', 'pengumuman-toc')->sole();

    Livewire::test(EditNews::class, ['record' => $news->getRouteKey()])
        ->fillForm(['title' => 'Pengumuman ToC (revisi)'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(AuditLog::query()->where('subject_id', $news->id)->pluck('aksi')->all())
        ->toBe(['news.created', 'news.published', 'news.updated'])
        ->and(AuditLog::query()->where('aksi', 'news.updated')->sole()->perubahan['new']['title'])->toBe('Pengumuman ToC (revisi)');
});

it('does not log news edits by regular editors', function () {
    $user = auditActor('admin');
    $news = News::query()->create([
        'title' => 'Berita umum', 'excerpt' => 'R', 'content' => 'I', 'slug' => 'berita-umum',
        'status' => 'draft', 'type' => 'public', 'user_id' => $user->id,
    ]);

    $news->update(['status' => 'published', 'published_at' => now()]);

    expect(AuditLog::query()->count())->toBe(0);
});

it('logs admin logins without credentials', function () {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->assignRole(User::ROLE_PANITIA_TOC);
    $subscriber = User::factory()->withoutTwoFactor()->create();
    $subscriber->assignRole('subscriber');

    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password']);
    $this->assertAuthenticatedAs($admin);
    auth()->logout();
    $this->post(route('login.store'), ['email' => $subscriber->email, 'password' => 'password']);

    $log = AuditLog::query()->where('aksi', 'auth.admin_login')->sole();

    expect($log->user_id)->toBe($admin->id)
        ->and($log->perubahan['roles'])->toBe([User::ROLE_PANITIA_TOC])
        ->and(json_encode($log->perubahan))->not->toContain('password');
});

it('strips passwords and tokens from audit payloads', function () {
    $log = app(AuditLogger::class)->record('pendaftar.exported', null, [
        'password' => 'rahasia',
        'nested' => ['remember_token' => 'abc', 'Token' => 'xyz', 'ok' => 1],
        'panjang' => str_repeat('a', 1500),
    ]);

    expect($log->perubahan)->not->toHaveKey('password')
        ->and($log->perubahan['nested'])->toBe(['ok' => 1])
        ->and(mb_strlen($log->perubahan['panjang']))->toBe(AuditLogger::MAX_STRING_LENGTH + 1);
});

it('never blocks the audited action when the audit log cannot be written', function () {
    auditActor(User::ROLE_ADMIN_TOC);
    $pendaftar = PendaftarTeacherOfChampions::factory()->create(['status' => 'baru']);
    Illuminate\Support\Facades\Schema::drop('audit_logs');

    $pendaftar->update(['status' => 'terverifikasi']);

    expect($pendaftar->refresh()->status)->toBe('terverifikasi');
});

it('shows the read-only audit log only to admins or toc.audit.view', function () {
    AuditLog::factory()->create(['aksi' => 'pendaftar.exported']);

    foreach ([User::ROLE_ADMIN_TOC, User::ROLE_PANITIA_TOC, 'editor'] as $role) {
        auditActor($role);
        $this->get(AuditLogResource::getUrl())->assertForbidden();
    }

    auditActor('super_admin');
    $this->get(AuditLogResource::getUrl())->assertOk()->assertSee('Ekspor pendaftar');

    $auditor = auditActor(User::ROLE_PANITIA_TOC);
    $auditor->givePermissionTo('toc.audit.view');
    $this->get(AuditLogResource::getUrl())->assertOk();

    $log = AuditLog::query()->first();

    Livewire::test(ManageAuditLogs::class)
        ->assertCanSeeTableRecords([$log])
        ->assertTableActionVisible('view', $log)
        ->assertTableActionDoesNotExist('edit')
        ->assertTableActionDoesNotExist('delete')
        ->assertActionDoesNotExist('create');
});
