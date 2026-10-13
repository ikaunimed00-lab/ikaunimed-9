<?php

use App\Models\Kegiatan;
use App\Models\SiteSetting;
use App\Services\SiteManagementService;
use Carbon\Carbon;
use Database\Seeders\KegiatanSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-10-20 10:00', 'Asia/Jakarta')->utc());
    $this->seed(KegiatanSeeder::class);
});

afterEach(function () {
    Carbon::setTestNow();
});

it('renders the Teacher of Champions landing page for guests', function () {
    $this->get(route('teacher-of-champions.landing'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TeacherOfChampions/Landing')
            ->where('isOpen', true)
            ->where('periodLabel', '12 Oktober – 22 November 2026')
            ->where('jenjangOptions', [
                ['value' => 'TK', 'label' => 'TK/RA/Sederajat'],
                ['value' => 'SD', 'label' => 'SD/MI/Sederajat'],
                ['value' => 'SMP', 'label' => 'SMP/MTs/Sederajat'],
                ['value' => 'SMA', 'label' => 'SMA/MA/Sederajat'],
                ['value' => 'SMK', 'label' => 'SMK/MAK/Sederajat'],
                ['value' => 'SLB', 'label' => 'SLB/Pendidikan Khusus'],
            ])
            ->where('closesAt', '2026-10-29T23:59:59+07:00')
            ->has('contacts', 2)
            ->where('meta.title', 'Teacher of Champions 2026 | IKA UNIMED')
        );
});

it('serves the landing page at /toc', function () {
    expect(route('teacher-of-champions.landing', absolute: false))->toBe('/toc');
});

it('permanently redirects /teacher-of-champions to /toc', function () {
    $this->get('/teacher-of-champions')
        ->assertStatus(301)
        ->assertRedirect('/toc');
});

it('keeps the registration and success routes', function () {
    $this->get(route('teacher-of-champions.create'))->assertOk();
    $this->get(route('teacher-of-champions.success'))->assertRedirect(route('teacher-of-champions.create'));
});

it('closes the register buttons after 29 October 23:59 WIB', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-29 23:59:30', 'Asia/Jakarta')->utc());

    $this->get(route('teacher-of-champions.landing'))
        ->assertInertia(fn (Assert $page) => $page->where('isOpen', true));

    Carbon::setTestNow(Carbon::parse('2026-10-30 00:00:01', 'Asia/Jakarta')->utc());

    $this->get(route('teacher-of-champions.landing'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('isOpen', false));
});

it('falls back to the configured period when the kegiatan is not active', function () {
    Kegiatan::query()->where('slug', 'teacher-of-champions')->update(['is_active' => false]);

    $this->get(route('teacher-of-champions.landing'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('periodLabel', config('homepage.teacher_of_champions.period_label'))
        );
});

it('prints the link preview tags for /toc', function () {
    $this->get(route('teacher-of-champions.landing'))
        ->assertSee('<title inertia>Teacher of Champions 2026 | IKA UNIMED</title>', false)
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertSee('<meta property="og:title" content="Teacher of Champions 2026 | IKA UNIMED">', false)
        ->assertSee('<meta property="og:url" content="'.url('/toc').'">', false)
        ->assertSee('content="'.url('/images/og-teacher-of-champions.jpg').'"', false)
        ->assertSee('<meta property="og:image:width" content="1200">', false)
        ->assertSee('<meta property="og:image:height" content="630">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
        ->assertSee('Kompetisi online untuk guru se-Indonesia dari IKA UNIMED. Pendaftaran 12–29 Oktober 2026 untuk Hari Guru Nasional.', false);
});

it('links Instagram only when the site setting holds a URL', function () {
    SiteSetting::query()->updateOrCreate(
        ['key' => 'social_instagram'],
        ['value' => 'https://instagram.com/ikaunimed', 'type' => 'text', 'group' => 'social', 'label' => 'Instagram URL'],
    );
    SiteManagementService::clearStaticCache();

    $this->get(route('teacher-of-champions.landing'))
        ->assertInertia(fn (Assert $page) => $page->where('instagramUrl', 'https://instagram.com/ikaunimed'));

    SiteSetting::query()->where('key', 'social_instagram')->delete();
    SiteManagementService::clearStaticCache();

    $this->get(route('teacher-of-champions.landing'))
        ->assertInertia(fn (Assert $page) => $page->where('instagramUrl', null));
});

it('links the guidebook on every Teacher of Champions page', function () {
    $guidebookUrl = 'https://drive.google.com/drive/folders/1kV0jONNVApXlSDTy8MIPAO_Zs4Wjm4Wt';

    expect(config('homepage.teacher_of_champions.guidebook_url'))->toBe($guidebookUrl);

    $this->get(route('teacher-of-champions.landing'))
        ->assertInertia(fn (Assert $page) => $page->where('guidebookUrl', $guidebookUrl));

    $this->get(route('teacher-of-champions.create'))
        ->assertInertia(fn (Assert $page) => $page->where('guidebookUrl', $guidebookUrl));

    $this->withSession(['nomor_pendaftaran' => 'TOC-2026-0001'])
        ->get(route('teacher-of-champions.success'))
        ->assertInertia(fn (Assert $page) => $page->where('guidebookUrl', $guidebookUrl));

    $this->get(route('reuni-akbar.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('teacherOfChampions.guide_url', $guidebookUrl)
            ->where('funWalk.guide_url', null)
            ->where('catur.guide_url', null)
        );
});

it('lists /toc in the pages sitemap and the sitemap index', function () {
    $this->get('/sitemap/pages.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('<loc>'.url('/toc').'</loc>', false)
        ->assertSee('<loc>'.url('/reuni-akbar-2026').'</loc>', false);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(url('/sitemap/pages.xml'), false);
});

it('keeps the landing page sources free of navy styling', function () {
    $files = [
        resource_path('js/Pages/TeacherOfChampions/Landing.tsx'),
        ...glob(resource_path('js/components/toc/*.tsx')),
    ];

    expect($files)->toHaveCount(5);

    foreach ($files as $file) {
        expect(file_get_contents($file))
            ->not->toMatch('/navy/i')
            ->not->toMatch('/\b(bg|text|border|from|via|to|ring)-(blue|indigo|slate)-(7|8|9)\d{2}\b/')
            ->not->toMatch('/#(000080|001F3F|0A1F44|1E3A8A|172554|0B1F3A)\b/i');
    }
});
