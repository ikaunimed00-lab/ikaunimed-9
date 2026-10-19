<?php

use App\Models\PendaftarTeacherOfChampions;
use Carbon\Carbon;
use Database\Seeders\KegiatanSeeder;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-10-20 10:00', 'Asia/Jakarta')->utc());
    $this->seed(KegiatanSeeder::class);
});

afterEach(function () {
    Carbon::setTestNow();
});

/**
 * @return array<string, mixed>
 */
function validRegistration(array $overrides = []): array
{
    return [
        'nama' => 'Siti Aminah, S.Pd.',
        'asal_instansi' => 'SMA Negeri 1 Medan',
        'whatsapp' => '0812-3456-7890',
        'email' => 'Siti.Aminah@example.com',
        'provinsi' => 'Sumatera Utara',
        'kabupaten_kota' => 'Kota Medan',
        'lama_mengabdi_tahun' => 8,
        'instagram' => 'https://instagram.com/Siti.Guru/',
        'bidang_studi' => 'Matematika',
        'asal_universitas' => 'Universitas Negeri Medan',
        'jenjang' => 'SMA',
        'status_kepegawaian' => 'ASN',
        'persetujuan_data' => true,
        'website' => '',
        ...$overrides,
    ];
}

function assertRedirectedToSignedSuccess($response): PendaftarTeacherOfChampions
{
    $pendaftar = PendaftarTeacherOfChampions::query()->latest('id')->first();

    expect($pendaftar)->not->toBeNull();
    $response->assertRedirect($pendaftar->signedSuccessUrl());

    return $pendaftar;
}

it('shows the open registration form with the regional dataset', function () {
    $this->get(route('teacher-of-champions.create'))
        ->assertOk()
        ->assertSee('content="'.url('/images/og-teacher-of-champions.jpg').'"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('TeacherOfChampions/Daftar')
            ->where('isOpen', true)
            ->has('wilayah', 38)
            ->where('kegiatan.slug', 'teacher-of-champions')
            ->where('universitasOptions', fn ($options) => collect($options)->contains(
                fn ($option) => is_array($option)
                    && ($option['name'] ?? null) === 'Universitas Malikussaleh'
                    && in_array('UNIMAL', $option['aliases'] ?? [], true)
            ))
            ->where('universitasOptions', fn ($options) => collect($options)->contains(
                fn ($option) => is_array($option) && ($option['name'] ?? null) === 'Universitas Negeri Medan'
            ))
        );
});

it('accepts free-text universities outside the suggestion list', function (string $asalUniversitas) {
    $response = $this->post(route('teacher-of-champions.store'), validRegistration([
        'asal_universitas' => $asalUniversitas,
        'email' => Str::slug($asalUniversitas).'@example.com',
        'whatsapp' => '0812'.fake()->unique()->numerify('########'),
    ]));

    $pendaftar = assertRedirectedToSignedSuccess($response);

    expect($pendaftar->asal_universitas)->toBe(trim(preg_replace('/\s+/', ' ', $asalUniversitas) ?? $asalUniversitas))
        ->and($pendaftar->alumni_unimed)->toBeFalse();
})->with([
    'Malikussaleh' => ['Universitas Malikussaleh'],
    'random campus' => ['  Institut Teknologi  Fiktif  Nusantara  '],
]);

it('detects UNIMED alumni from the free-text university name', function (string $asalUniversitas, bool $expected) {
    expect(PendaftarTeacherOfChampions::isAlumniUnimed($asalUniversitas))->toBe($expected);
})->with([
    'UNIMED' => ['UNIMED', true],
    'Unimed' => ['Unimed', true],
    'Universitas Negeri Medan' => ['Universitas Negeri Medan', true],
    'USU' => ['USU', false],
    'with punctuation' => ['Univ. Negeri Medan (U.N.I.M.E.D)', true],
    'english name' => ['State University of Medan', true],
    'other university' => ['Universitas Sumatera Utara', false],
]);

it('stores the alumni flag on registration and updates it when the university changes', function () {
    assertRedirectedToSignedSuccess(
        $this->post(route('teacher-of-champions.store'), validRegistration(['asal_universitas' => 'Unimed']))
    );

    $pendaftar = PendaftarTeacherOfChampions::query()->sole();

    expect($pendaftar->alumni_unimed)->toBeTrue();

    $pendaftar->update(['asal_universitas' => 'USU']);

    expect($pendaftar->fresh()->alumni_unimed)->toBeFalse();
});

it('registers a teacher with normalized contact data and a sequential number', function () {
    $response = $this->post(route('teacher-of-champions.store'), validRegistration())
        ->assertSessionHas('nomor_pendaftaran', 'TOC-2026-0001');

    $pendaftar = assertRedirectedToSignedSuccess($response);

    expect($pendaftar->whatsapp)->toBe('6281234567890')
        ->and($pendaftar->email)->toBe('siti.aminah@example.com')
        ->and($pendaftar->instagram)->toBe('@siti.guru')
        ->and($pendaftar->status)->toBe('baru')
        ->and($pendaftar->persetujuan_data)->toBeTrue();

    $this->get($pendaftar->signedSuccessUrl())
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TeacherOfChampions/Sukses')
            ->where('nomorPendaftaran', 'TOC-2026-0001')
            ->where('nama', 'Siti Aminah, S.Pd.')
            ->has('buktiUrl')
        );

    $this->withSession(['nomor_pendaftaran' => 'TOC-2026-0001'])
        ->get(route('teacher-of-champions.success'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TeacherOfChampions/Sukses')
            ->where('nomorPendaftaran', 'TOC-2026-0001')
        );
});

it('keeps the signed success page after refresh and rejects forged or expired signatures', function () {
    $pendaftar = PendaftarTeacherOfChampions::factory()->create([
        'nama' => 'Eli Astuti',
        'nomor_pendaftaran' => 'TOC-2026-0042',
    ]);

    $url = $pendaftar->signedSuccessUrl();

    $this->get($url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TeacherOfChampions/Sukses')
            ->where('nomorPendaftaran', 'TOC-2026-0042')
            ->where('nama', 'Eli Astuti')
        );

    $this->get($url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TeacherOfChampions/Sukses')
            ->where('nomorPendaftaran', 'TOC-2026-0042')
        );

    $this->get(route('teacher-of-champions.success.show', $pendaftar).'?signature=forged')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TeacherOfChampions/TautanTidakValid')
            ->where('cekStatusUrl', route('teacher-of-champions.status'))
        );

    Carbon::setTestNow(Carbon::parse('2026-11-25 10:00', 'Asia/Jakarta')->utc());

    $this->get($url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('TeacherOfChampions/TautanTidakValid'));
});

it('accepts optional jenjang and status kepegawaian', function () {
    assertRedirectedToSignedSuccess(
        $this->post(route('teacher-of-champions.store'), validRegistration(['jenjang' => '', 'status_kepegawaian' => '']))
    );

    expect(PendaftarTeacherOfChampions::query()->sole()->jenjang)->toBeNull();
});

it('offers inclusive jenjang labels while storing the short code', function () {
    $this->get(route('teacher-of-champions.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('jenjangOptions.1', ['value' => 'SD', 'label' => 'SD/MI/Sederajat'])
            ->where('jenjangOptions.3', ['value' => 'SMA', 'label' => 'SMA/MA/Sederajat'])
        );

    assertRedirectedToSignedSuccess(
        $this->post(route('teacher-of-champions.store'), validRegistration(['jenjang' => 'SMP']))
    );

    expect(PendaftarTeacherOfChampions::query()->sole()->jenjang)->toBe('SMP');
});

it('rejects a jenjang label in place of its code', function () {
    $this->post(route('teacher-of-champions.store'), validRegistration(['jenjang' => 'SD/MI/Sederajat']))
        ->assertSessionHasErrors(['jenjang' => 'Jenjang tidak valid.']);
});

it('redirects the success page back to the form without a fresh registration', function () {
    $this->get(route('teacher-of-champions.success'))
        ->assertRedirect(route('teacher-of-champions.create'));
});

it('rejects a duplicate whatsapp number written in another format', function () {
    PendaftarTeacherOfChampions::factory()->create(['whatsapp' => '6281234567890']);

    $this->post(route('teacher-of-champions.store'), validRegistration(['whatsapp' => '+62 812 3456 7890']))
        ->assertSessionHasErrors(['whatsapp' => 'Data Anda sudah terdaftar. Cek nomor pendaftaran Anda di Cek Status. Bila masih terkendala, hubungi panitia.']);

    expect(PendaftarTeacherOfChampions::query()->count())->toBe(1);
});

it('rejects a duplicate email regardless of letter case', function () {
    PendaftarTeacherOfChampions::factory()->create(['email' => 'siti.aminah@example.com']);

    $this->post(route('teacher-of-champions.store'), validRegistration())
        ->assertSessionHasErrors('email');

    expect(PendaftarTeacherOfChampions::query()->count())->toBe(1);
});

it('rejects submissions that fill the honeypot field', function () {
    $this->post(route('teacher-of-champions.store'), validRegistration(['website' => 'http://spam.example']))
        ->assertSessionHasErrors('website');

    expect(PendaftarTeacherOfChampions::query()->count())->toBe(0);
});

it('requires consent and a regency that belongs to the province', function () {
    $this->post(route('teacher-of-champions.store'), validRegistration([
        'persetujuan_data' => false,
        'kabupaten_kota' => 'Kota Bandung',
    ]))->assertSessionHasErrors(['persetujuan_data', 'kabupaten_kota']);

    expect(PendaftarTeacherOfChampions::query()->count())->toBe(0);
});

it('stays open until 29 October 23:59 WIB', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-29 23:59:30', 'Asia/Jakarta')->utc());

    assertRedirectedToSignedSuccess(
        $this->post(route('teacher-of-champions.store'), validRegistration())
    );
});

it('closes registration after 29 October 23:59 WIB', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-30 00:00:01', 'Asia/Jakarta')->utc());

    $this->get(route('teacher-of-champions.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('isOpen', false));

    $this->post(route('teacher-of-champions.store'), validRegistration())
        ->assertSessionHasErrors(['form' => 'Pendaftaran Teacher of Champions sudah ditutup.']);

    expect(PendaftarTeacherOfChampions::query()->count())->toBe(0);
});

it('throttles more than five attempts per minute', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('teacher-of-champions.store'), validRegistration(['website' => 'bot']));
    }

    $this->post(route('teacher-of-champions.store'), validRegistration())
        ->assertTooManyRequests();
});
