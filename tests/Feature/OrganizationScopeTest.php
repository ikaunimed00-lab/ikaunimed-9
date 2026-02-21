<?php

use App\Models\News;
use App\Models\Organization;
use App\Models\User;
use App\Services\OrganizationScopeService;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('central admin via OrganizationScopeService dapat melihat semua data organisasi', function () {
    $centralAdmin = User::factory()->create();
    $centralAdmin->assignRole('admin');
    $centralAdmin->organization_id = null;
    $centralAdmin->save();

    $orgA = Organization::create([
        'name' => 'Org A',
        'slug' => 'org-a',
        'type' => 'dpw',
        'is_active' => true,
    ]);

    $orgB = Organization::create([
        'name' => 'Org B',
        'slug' => 'org-b',
        'type' => 'dpc',
        'parent_id' => $orgA->id,
        'is_active' => true,
    ]);

    $newsGlobal = News::create([
        'title' => 'Global News',
        'excerpt' => 'Global excerpt',
        'content' => 'Global content',
        'slug' => 'global-news',
        'status' => 'published',
        'type' => 'public',
        'organization_id' => null,
        'user_id' => $centralAdmin->id,
    ]);

    $newsOrgA = News::create([
        'title' => 'Org A News',
        'excerpt' => 'Org A excerpt',
        'content' => 'Org A content',
        'slug' => 'org-a-news',
        'status' => 'published',
        'type' => 'public',
        'organization_id' => $orgA->id,
        'user_id' => $centralAdmin->id,
    ]);

    $newsOrgB = News::create([
        'title' => 'Org B News',
        'excerpt' => 'Org B excerpt',
        'content' => 'Org B content',
        'slug' => 'org-b-news',
        'status' => 'published',
        'type' => 'public',
        'organization_id' => $orgB->id,
        'user_id' => $centralAdmin->id,
    ]);

    $service = new OrganizationScopeService();

    $scoped = $service->applyScope(News::query(), $centralAdmin)->pluck('id')->all();

    expect($scoped)->toContain($newsGlobal->id, $newsOrgA->id, $newsOrgB->id);
});

test('admin terikat organisasi hanya melihat data milik organisasinya', function () {
    $orgA = Organization::create([
        'name' => 'Org A',
        'slug' => 'org-a-scope',
        'type' => 'dpw',
        'is_active' => true,
    ]);

    $orgB = Organization::create([
        'name' => 'Org B',
        'slug' => 'org-b-scope',
        'type' => 'dpc',
        'parent_id' => $orgA->id,
        'is_active' => true,
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $admin->organization_id = $orgA->id;
    $admin->save();

    $newsOrgA1 = News::create([
        'title' => 'Org A News 1',
        'excerpt' => 'Org A news 1',
        'content' => 'Org A content 1',
        'slug' => 'org-a-news-1',
        'status' => 'published',
        'type' => 'public',
        'organization_id' => $orgA->id,
        'user_id' => $admin->id,
    ]);

    $newsOrgA2 = News::create([
        'title' => 'Org A News 2',
        'excerpt' => 'Org A news 2',
        'content' => 'Org A content 2',
        'slug' => 'org-a-news-2',
        'status' => 'published',
        'type' => 'public',
        'organization_id' => $orgA->id,
        'user_id' => $admin->id,
    ]);

    $newsOrgB = News::create([
        'title' => 'Org B News',
        'excerpt' => 'Org B news',
        'content' => 'Org B content',
        'slug' => 'org-b-news-scope',
        'status' => 'published',
        'type' => 'public',
        'organization_id' => $orgB->id,
        'user_id' => $admin->id,
    ]);

    $newsGlobal = News::create([
        'title' => 'Global News Scope',
        'excerpt' => 'Global excerpt',
        'content' => 'Global content',
        'slug' => 'global-news-scope',
        'status' => 'published',
        'type' => 'public',
        'organization_id' => null,
        'user_id' => $admin->id,
    ]);

    $service = new OrganizationScopeService();

    $scopedIds = $service->applyScope(News::query(), $admin)->pluck('id')->all();

    expect($scopedIds)->toContain($newsOrgA1->id, $newsOrgA2->id)
        ->not->toContain($newsOrgB->id, $newsGlobal->id);
});

test('user tanpa organization scope dan bukan admin pusat tidak melihat data sensitif', function () {
    $orgA = Organization::create([
        'name' => 'Org A',
        'slug' => 'org-a-editor',
        'type' => 'dpw',
        'is_active' => true,
    ]);

    $orgB = Organization::create([
        'name' => 'Org B',
        'slug' => 'org-b-editor',
        'type' => 'dpc',
        'parent_id' => $orgA->id,
        'is_active' => true,
    ]);

    $editor = User::factory()->create();
    $editor->assignRole('editor');
    $editor->organization_id = null;
    $editor->save();

    News::create([
        'title' => 'Org A News Editor',
        'excerpt' => 'Org A news editor',
        'content' => 'Org A content editor',
        'slug' => 'org-a-news-editor',
        'status' => 'published',
        'type' => 'public',
        'organization_id' => $orgA->id,
        'user_id' => $editor->id,
    ]);

    News::create([
        'title' => 'Org B News Editor',
        'excerpt' => 'Org B news editor',
        'content' => 'Org B content editor',
        'slug' => 'org-b-news-editor',
        'status' => 'published',
        'type' => 'public',
        'organization_id' => $orgB->id,
        'user_id' => $editor->id,
    ]);

    $service = new OrganizationScopeService();

    $scopedIds = $service->applyScope(News::query(), $editor)->pluck('id')->all();

    expect($scopedIds)->toBe([]);
});
