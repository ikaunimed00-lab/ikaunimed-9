<?php

use App\Filament\Resources\Kegiatans\Pages\ManageKegiatans;
use App\Filament\Resources\News\Pages\CreateNews;
use App\Filament\Resources\News\Pages\ListNews;
use App\Filament\Resources\NewsResource;
use App\Filament\Resources\PendaftarTeacherOfChampions\Pages\ManagePendaftarTeacherOfChampions;
use App\Models\Category;
use App\Models\Kegiatan;
use App\Models\News;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TocRolesSeeder;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, TocRolesSeeder::class]);
    Filament::setCurrentPanel('admin');
});

function tocUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function tocNews(User $author, array $attributes = []): News
{
    return News::query()->create([
        'title' => 'Berita '.fake()->unique()->word(),
        'excerpt' => 'Ringkasan',
        'content' => 'Isi berita',
        'slug' => 'berita-'.fake()->unique()->slug(2),
        'status' => 'draft',
        'type' => 'public',
        'user_id' => $author->id,
        ...$attributes,
    ]);
}

it('seeds the ToC roles idempotently without touching existing roles', function () {
    $adminPermissions = Role::findByName('admin')->permissions()->pluck('name')->sort()->values()->all();

    $this->seed(TocRolesSeeder::class);

    expect(Role::query()->where('name', User::ROLE_ADMIN_TOC)->count())->toBe(1)
        ->and(Role::findByName(User::ROLE_ADMIN_TOC)->permissions()->pluck('name')->all())
        ->toContain('toc.pendaftar.export', 'toc.kegiatan.update', 'cms.news.publish')
        ->not->toContain('toc.audit.view')
        ->and(Role::findByName(User::ROLE_PANITIA_TOC)->permissions()->pluck('name')->sort()->values()->all())
        ->toBe(['toc.dashboard.view', 'toc.pendaftar.view'])
        ->and(Role::findByName('admin')->permissions()->pluck('name')->sort()->values()->all())
        ->toBe($adminPermissions);
});

it('lets ToC roles into the panel and the registrant list', function (string $role) {
    PendaftarTeacherOfChampions::factory()->create(['nama' => 'Guru Teladan']);

    $this->actingAs(tocUser($role))
        ->get('/admin/pendaftar-teacher-of-champions')
        ->assertOk()
        ->assertSee('Guru Teladan');
})->with([User::ROLE_ADMIN_TOC, User::ROLE_PANITIA_TOC]);

it('forbids ToC roles from other admin areas', function (string $role, string $path) {
    $this->actingAs(tocUser($role))
        ->get($path)
        ->assertForbidden();
})->with([User::ROLE_ADMIN_TOC, User::ROLE_PANITIA_TOC])->with([
    '/admin/users',
    '/admin/roles',
    '/admin/shop/orders',
    '/admin/shop/products',
    '/admin/shop/payments',
    '/admin/shop/coupons',
    '/admin/site-settings',
    '/admin/legalizations',
]);

it('keeps panitia-toc out of news and kegiatan', function (string $path) {
    $this->actingAs(tocUser(User::ROLE_PANITIA_TOC))
        ->get($path)
        ->assertForbidden();
})->with(['/admin/news', '/admin/kegiatan']);

it('masks registrant contacts for panitia-toc and hides export and status changes', function () {
    $pendaftar = PendaftarTeacherOfChampions::factory()->create([
        'whatsapp' => '6281234567890',
        'email' => 'guru.teladan@example.com',
    ]);

    $this->actingAs(tocUser(User::ROLE_PANITIA_TOC));

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->assertCanSeeTableRecords([$pendaftar])
        ->assertSee('6281*****7890')
        ->assertDontSee('6281234567890')
        ->assertSee('guru****************.com')
        ->assertDontSee('guru.teladan@example.com')
        ->assertActionHidden('ekspor')
        ->assertTableBulkActionHidden('ubahStatus')
        ->assertTableBulkActionHidden('eksporTerpilih')
        ->assertTableActionHidden('edit', $pendaftar);
});

it('shows full contacts and export to admin-toc but never delete', function () {
    $pendaftar = PendaftarTeacherOfChampions::factory()->create(['whatsapp' => '6281234567890']);
    $user = tocUser(User::ROLE_ADMIN_TOC);
    $this->actingAs($user);

    Livewire::test(ManagePendaftarTeacherOfChampions::class)
        ->assertSee('6281234567890')
        ->assertActionVisible('ekspor')
        ->assertTableBulkActionVisible('ubahStatus')
        ->assertTableBulkActionHidden('delete')
        ->assertTableActionVisible('edit', $pendaftar);

    expect($user->can('update', $pendaftar))->toBeTrue()
        ->and($user->can('delete', $pendaftar))->toBeFalse()
        ->and($user->can('deleteAny', PendaftarTeacherOfChampions::class))->toBeFalse()
        ->and(tocUser('admin')->can('delete', $pendaftar))->toBeFalse()
        ->and(tocUser('super_admin')->can('delete', $pendaftar))->toBeTrue();
});

it('limits admin-toc to the Teacher of Champions kegiatan', function () {
    $toc = Kegiatan::factory()->create(['slug' => Kegiatan::TEACHER_OF_CHAMPIONS_SLUG, 'title' => 'Teacher of Champions']);
    $other = Kegiatan::factory()->create(['slug' => 'turnamen-catur', 'title' => 'Turnamen Catur']);
    $user = tocUser(User::ROLE_ADMIN_TOC);
    $this->actingAs($user);

    Livewire::test(ManageKegiatans::class)
        ->assertCanSeeTableRecords([$toc])
        ->assertCanNotSeeTableRecords([$other])
        ->assertActionHidden('create')
        ->assertTableActionVisible('edit', $toc)
        ->assertTableActionHidden('delete', $toc);

    expect($user->can('update', $toc))->toBeTrue()
        ->and($user->can('update', $other))->toBeFalse()
        ->and($user->can('delete', $toc))->toBeFalse()
        ->and($user->can('create', Kegiatan::class))->toBeFalse();
});

it('keeps kegiatan fully manageable for admins', function () {
    $toc = Kegiatan::factory()->create(['slug' => Kegiatan::TEACHER_OF_CHAMPIONS_SLUG]);
    $other = Kegiatan::factory()->create(['slug' => 'turnamen-catur']);
    $this->actingAs(tocUser('admin'));

    Livewire::test(ManageKegiatans::class)
        ->assertCanSeeTableRecords([$toc, $other])
        ->assertActionVisible('create')
        ->assertTableActionVisible('delete', $other);
});

it('scopes admin-toc news to their own records', function () {
    $user = tocUser(User::ROLE_ADMIN_TOC);
    $own = tocNews($user);
    $others = tocNews(User::factory()->create());
    $this->actingAs($user);

    Livewire::test(ListNews::class)
        ->assertCanSeeTableRecords([$own])
        ->assertCanNotSeeTableRecords([$others]);

    $this->get(NewsResource::getUrl('edit', ['record' => $own]))->assertOk();
    $this->get(NewsResource::getUrl('edit', ['record' => $others]))->assertNotFound();

    expect($user->can('update', $own))->toBeTrue()
        ->and($user->can('update', $others))->toBeFalse()
        ->and($user->can('delete', $others))->toBeFalse()
        ->and($user->can('forceDelete', $own))->toBeFalse()
        ->and($user->can('deleteAny', News::class))->toBeFalse();
});

it('tags news created by admin-toc as Teacher of Champions and lets them publish', function () {
    Storage::fake('public');
    $category = Category::query()->create(['name' => 'Kegiatan', 'slug' => 'kegiatan']);
    $user = tocUser(User::ROLE_ADMIN_TOC);
    $this->actingAs($user);

    Livewire::test(CreateNews::class)
        ->fillForm([
            'title' => 'Seleksi Teacher of Champions dibuka',
            'slug' => 'seleksi-teacher-of-champions-dibuka',
            'categories' => [$category->id],
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'excerpt' => 'Ringkasan',
            'content' => '<p>Isi</p>',
            'image' => UploadedFile::fake()->image('poster.jpg'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $news = News::query()->where('slug', 'seleksi-teacher-of-champions-dibuka')->sole();

    expect($news->user_id)->toBe($user->id)
        ->and($news->status)->toBe('published')
        ->and($news->tags()->pluck('slug')->all())->toBe([Tag::TEACHER_OF_CHAMPIONS_SLUG]);
});

it('does not tag news created by regular admins', function () {
    Storage::fake('public');
    $category = Category::query()->create(['name' => 'Kegiatan', 'slug' => 'kegiatan']);
    $this->actingAs(tocUser('admin'));

    Livewire::test(CreateNews::class)
        ->fillForm([
            'title' => 'Berita umum',
            'slug' => 'berita-umum',
            'categories' => [$category->id],
            'status' => 'draft',
            'excerpt' => 'Ringkasan',
            'content' => '<p>Isi</p>',
            'image' => UploadedFile::fake()->image('poster.jpg'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(News::query()->where('slug', 'berita-umum')->sole()->tags()->count())->toBe(0);
});
