<?php

use App\Models\PendaftarTeacherOfChampions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-10-20 10:00', 'Asia/Jakarta')->utc());
});

afterEach(function () {
    Carbon::setTestNow();
});

it('shows the public status check page', function () {
    $this->get(route('teacher-of-champions.status'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TeacherOfChampions/CekStatus')
            ->where('result', null)
            ->where('genericError', null)
        );
});

it('returns masked registration details when whatsapp and email both match', function () {
    $pendaftar = PendaftarTeacherOfChampions::factory()->create([
        'nama' => 'Eli Astuti',
        'whatsapp' => '6281234567890',
        'email' => 'eli.astuti@example.com',
        'status' => 'terverifikasi',
        'nomor_pendaftaran' => 'TOC-2026-0017',
    ]);

    $this->from(route('teacher-of-champions.status'))
        ->post(route('teacher-of-champions.status.check'), [
            'whatsapp' => '0812-3456-7890',
            'email' => 'Eli.Astuti@Example.com',
            'website' => '',
        ])
        ->assertRedirect(route('teacher-of-champions.status'));

    $this->get(route('teacher-of-champions.status'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TeacherOfChampions/CekStatus')
            ->where('result.nomorPendaftaran', 'TOC-2026-0017')
            ->where('result.namaMasked', 'Eli A*****')
            ->where('result.statusLabel', 'Terverifikasi')
            ->where('result.buktiUrl', $pendaftar->signedBuktiUrl())
            ->missing('result.email')
            ->missing('result.whatsapp')
            ->where('genericError', null)
        );

    $this->get($pendaftar->signedBuktiUrl())
        ->assertOk()
        ->assertSee('TOC-2026-0017')
        ->assertSee('Eli Astuti')
        ->assertDontSee('eli.astuti@example.com')
        ->assertDontSee('6281234567890');
});

it('returns the same generic message when only one contact matches', function () {
    PendaftarTeacherOfChampions::factory()->create([
        'whatsapp' => '6281234567890',
        'email' => 'eli.astuti@example.com',
    ]);

    Log::spy();

    $this->from(route('teacher-of-champions.status'))
        ->post(route('teacher-of-champions.status.check'), [
            'whatsapp' => '0812-3456-7890',
            'email' => 'orang.lain@example.com',
            'website' => '',
        ])
        ->assertRedirect(route('teacher-of-champions.status'));

    $this->get(route('teacher-of-champions.status'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('result', null)
            ->where('genericError', 'Data tidak ditemukan atau tidak cocok')
        );

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context): bool => $message === 'toc.status_check.failed'
            && isset($context['ip_hash'], $context['wa_hash'], $context['email_hash'])
            && ! str_contains(json_encode($context), 'eli.astuti')
            && ! str_contains(json_encode($context), '6281234567890'));
});

it('rejects honeypot submissions on status check', function () {
    $this->post(route('teacher-of-champions.status.check'), [
        'whatsapp' => '081234567890',
        'email' => 'eli@example.com',
        'website' => 'http://spam.test',
    ])->assertSessionHasErrors('website');
});

it('throttles status checks after five attempts per minute', function () {
    PendaftarTeacherOfChampions::factory()->create([
        'whatsapp' => '6281111111111',
        'email' => 'throttle@example.com',
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('teacher-of-champions.status.check'), [
            'whatsapp' => '081111111111',
            'email' => 'salah'.$attempt.'@example.com',
            'website' => '',
        ])->assertRedirect();
    }

    $this->post(route('teacher-of-champions.status.check'), [
        'whatsapp' => '081111111111',
        'email' => 'salah6@example.com',
        'website' => '',
    ])->assertTooManyRequests();
});

it('masks names for partial display', function () {
    expect(PendaftarTeacherOfChampions::maskName('Eli Astuti'))->toBe('Eli A*****')
        ->and(PendaftarTeacherOfChampions::maskName('Budi'))->toBe('B***');
});
