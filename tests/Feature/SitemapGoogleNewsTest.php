<?php

use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::forget('sitemap.google-news');
});

function parseSitemapXml(string $xml): SimpleXMLElement
{
    $previous = libxml_use_internal_errors(true);
    $document = simplexml_load_string($xml);
    $errors = libxml_get_errors();
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    expect($errors)->toBe([])
        ->and($document)->toBeInstanceOf(SimpleXMLElement::class);

    return $document;
}

test('google news sitemap renders valid xml with recent published news', function () {
    $author = User::factory()->create();

    News::query()->create([
        'title' => 'Reuni & Fun Walk <IKA UNIMED>',
        'excerpt' => 'Ringkasan',
        'content' => 'Isi berita',
        'slug' => 'reuni-fun-walk',
        'status' => 'published',
        'type' => 'public',
        'user_id' => $author->id,
        'published_at' => now()->subHour(),
    ]);

    News::query()->create([
        'title' => 'Berita lama',
        'excerpt' => 'Ringkasan',
        'content' => 'Isi berita',
        'slug' => 'berita-lama',
        'status' => 'published',
        'type' => 'public',
        'user_id' => $author->id,
        'published_at' => now()->subDays(5),
    ]);

    $response = $this->get('/sitemap/google-news.xml');

    $response->assertOk()->assertHeader('Content-Type', 'application/xml');

    $document = parseSitemapXml($response->getContent());
    $urls = $document->children('http://www.sitemaps.org/schemas/sitemap/0.9')->url;

    expect($urls)->toHaveCount(1)
        ->and((string) $urls[0]->loc)->toBe(url('/news/reuni-fun-walk'));

    $title = $urls[0]->children('http://www.google.com/schemas/sitemap-news/0.9')->news->title;

    expect((string) $title)->toBe('Reuni & Fun Walk <IKA UNIMED>');
});

test('google news sitemap renders valid xml when there is no recent news', function () {
    $response = $this->get('/sitemap/google-news.xml');

    $response->assertOk();

    parseSitemapXml($response->getContent());
});
