<?php

use App\Models\Category;
use App\Models\News;
use App\Models\User;

function legacyNews(string $slug, string $status = 'published'): News
{
    return News::query()->create([
        'title' => 'Berita '.$slug,
        'excerpt' => 'Ringkasan',
        'content' => 'Isi berita',
        'slug' => $slug,
        'status' => $status,
        'type' => 'public',
        'user_id' => User::factory()->create()->id,
        'published_at' => $status === 'published' ? now()->subHour() : null,
    ]);
}

test('berita redirects permanently to the news index', function () {
    $this->get('/berita')->assertStatus(301)->assertRedirect('/news');
});

test('berita keeps the query string when redirecting', function () {
    $this->get('/berita?page=2')->assertStatus(301)->assertRedirect('/news?page=2');
});

test('berita slug redirects to the published news with the same slug', function () {
    legacyNews('reuni-akbar');

    $this->get('/berita/reuni-akbar')->assertStatus(301)->assertRedirect('/news/reuni-akbar');
});

test('berita slug redirects to the category with the same slug', function () {
    Category::query()->create(['name' => 'Info Karir', 'slug' => 'info-karir']);

    $this->get('/berita/info-karir')->assertStatus(301)->assertRedirect('/kategori/info-karir');
});

test('berita slug without a match or with a draft redirects to the news index', function (string $slug) {
    legacyNews('masih-draft', 'draft');

    $this->get('/berita/'.$slug)->assertStatus(301)->assertRedirect('/news');
})->with(['tidak-ada', 'masih-draft']);
