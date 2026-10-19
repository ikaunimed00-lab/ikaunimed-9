<?php

use App\Models\PendaftarTeacherOfChampions;
use App\Services\TocDashboardStats;
use App\Services\TocShareableReport;
use App\Services\UniversityNameNormalizer;
use Carbon\CarbonImmutable;

beforeEach(function () {
    UniversityNameNormalizer::flush();
    config(['homepage.teacher_of_champions.registration_closes_at' => '2026-10-29 23:59']);
});

it('canonicalizes UNIMED name variants to one official display label', function (string $raw) {
    expect(app(UniversityNameNormalizer::class)->canonicalize($raw))
        ->toBe('Universitas Negeri Medan (UNIMED)');
})->with([
    'full' => ['Universitas Negeri Medan'],
    'with paren' => ['Universitas Negeri Medan (UNIMED)'],
    'acronym' => ['UNIMED'],
    'lower' => ['unimed'],
    'univ dot' => [' Univ. Negeri Medan '],
    'spaced' => ['Universitas  Negeri   Medan'],
]);

it('groups UNIMED variants together in kampus rekap without merging other campuses', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta')->utc());

    foreach ([
        'Universitas Negeri Medan',
        'Universitas Negeri Medan (UNIMED)',
        'UNIMED',
        'unimed',
        ' Univ. Negeri Medan ',
        'Universitas Sumatera Utara',
        'USU',
    ] as $i => $kampus) {
        PendaftarTeacherOfChampions::factory()->create([
            'asal_universitas' => $kampus,
            'created_at' => CarbonImmutable::parse('2026-10-17 10:00', 'Asia/Jakarta')->utc()->addMinutes($i),
        ]);
    }

    TocDashboardStats::forget();
    $kampus = app(TocDashboardStats::class)->snapshot()['kampus'];
    $byLabel = collect($kampus)->keyBy('label');

    expect($byLabel->get('Universitas Negeri Medan (UNIMED)')['total'])->toBe(5)
        ->and($byLabel->get('Universitas Sumatera Utara (USU)')['total'])->toBe(2)
        ->and($byLabel->has('Universitas Negeri Medan'))->toBeFalse();
});

it('does not change alumni_unimed detection results for known samples', function () {
    $samples = [
        'UNIMED',
        'Unimed',
        'Universitas Negeri Medan',
        'Universitas Negeri Medan (UNIMED)',
        'Univ. Negeri Medan (U.N.I.M.E.D)',
        'State University of Medan',
        'USU',
        'Universitas Sumatera Utara',
        'Universitas Malikussaleh',
        'random campus xyz',
        '',
        null,
    ];

    $before = [];
    foreach ($samples as $sample) {
        $before[(string) $sample] = PendaftarTeacherOfChampions::isAlumniUnimed($sample);
    }

    // Canonicalization must not alter the detector.
    $normalizer = app(UniversityNameNormalizer::class);
    foreach ($samples as $sample) {
        if (is_string($sample) && $sample !== '') {
            $normalizer->canonicalize($sample);
        }
    }

    $after = [];
    foreach ($samples as $sample) {
        $after[(string) $sample] = PendaftarTeacherOfChampions::isAlumniUnimed($sample);
    }

    expect($after)->toBe($before);
});

it('keeps print Top 5 kampus labels canonical and PII-free', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta')->utc());

    PendaftarTeacherOfChampions::factory()->create([
        'asal_universitas' => 'Universitas Negeri Medan (UNIMED)',
        'nama' => 'Rahasia',
        'email' => 'rahasia@example.com',
        'created_at' => CarbonImmutable::parse('2026-10-17 10:00', 'Asia/Jakarta')->utc(),
    ]);
    PendaftarTeacherOfChampions::factory()->create([
        'asal_universitas' => 'UNIMED',
        'created_at' => CarbonImmutable::parse('2026-10-17 11:00', 'Asia/Jakarta')->utc(),
    ]);

    TocDashboardStats::forget();
    $payload = app(TocShareableReport::class)->printPayload(
        app(TocDashboardStats::class)->snapshot(CarbonImmutable::parse('2026-10-17 16:23', 'Asia/Jakarta'))
    );

    expect($payload['kampus'][0]['label'])->toBe('Universitas Negeri Medan (UNIMED)')
        ->and($payload['kampus'][0]['total'])->toBe(2)
        ->and(collect($payload['kampus'])->pluck('label')->join(' '))->not->toContain('Rahasia');
});
