<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TocDashboardStats
{
    public const CACHE_KEY = 'toc.dashboard.stats';

    public const CACHE_SECONDS = 60;

    public const TREND_MAX_DAYS = 60;

    /**
     * Compact jenjang labels shared by dashboard widgets and shareable reports.
     *
     * @var array<string, string>
     */
    public const JENJANG_LABELS = [
        'TK' => 'TK/RA',
        'SD' => 'SD/MI',
        'SMP' => 'SMP/MTs',
        'SMA' => 'SMA/MA',
        'SMK' => 'SMK/MAK',
        'SLB' => 'SLB',
    ];

    public const EMPTY_LABEL = 'Belum diisi';

    /**
     * Abbreviations kept uppercase when formatting free-text labels.
     *
     * @var array<string, string>
     */
    public const PRESERVED_TOKENS = [
        'ipa' => 'IPA',
        'ips' => 'IPS',
        'ppkn' => 'PPKn',
        'pkn' => 'PKN',
        'tik' => 'TIK',
        'pjok' => 'PJOK',
        'pai' => 'PAI',
        'unimed' => 'UNIMED',
    ];

    /**
     * Cached dashboard summary (may lag up to CACHE_SECONDS).
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, fn (): array => $this->build(CarbonImmutable::now(Kegiatan::TIMEZONE)));
    }

    /**
     * Fresh snapshot for shareable reports. All formats must use one call.
     *
     * @return array{
     *     total: int,
     *     today: int,
     *     statuses: array<string, int>,
     *     alumni: int,
     *     alumni_percentage: float,
     *     daily: array<string, int>,
     *     provinces: list<array{label: string, total: int}>,
     *     provinces_all: list<array{label: string, total: int}>,
     *     jenjang: list<array{label: string, total: int}>,
     *     bidang_studi: list<array{label: string, total: int}>,
     *     bidang_studi_all: list<array{label: string, total: int}>,
     *     generated_at: CarbonImmutable,
     *     closes_at: CarbonImmutable,
     *     remaining_days: int,
     *     remaining_hours: int,
     * }
     */
    public function snapshot(?CarbonImmutable $now = null): array
    {
        $now = ($now ?? CarbonImmutable::now(Kegiatan::TIMEZONE))->setTimezone(Kegiatan::TIMEZONE)->locale('id');
        $data = $this->build($now);
        $closesAt = PendaftarTeacherOfChampions::registrationClosesAt()
            ->copy()
            ->setTimezone(Kegiatan::TIMEZONE)
            ->locale('id');

        $remainingDays = 0;
        $remainingHours = 0;

        if ($now->lessThanOrEqualTo($closesAt)) {
            $diff = $now->diff($closesAt);
            $remainingDays = (int) $diff->days;
            $remainingHours = (int) $diff->h;
        }

        return [
            ...$data,
            'generated_at' => $now,
            'closes_at' => CarbonImmutable::instance($closesAt)->locale('id'),
            'remaining_days' => $remainingDays,
            'remaining_hours' => $remainingHours,
        ];
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function formatPercentage(float $value): string
    {
        if (abs($value - round($value)) < 0.05) {
            return number_format((int) round($value), 0, ',', '.');
        }

        return number_format(round($value, 1), 1, ',', '.');
    }

    public static function formatNumber(int|float $value): string
    {
        return number_format((int) $value, 0, ',', '.');
    }

    public static function displayLabel(string $raw): string
    {
        $normalized = Str::of($raw)->squish()->toString();

        if ($normalized === '') {
            return self::EMPTY_LABEL;
        }

        return collect(preg_split('/\s+/u', mb_strtolower($normalized, 'UTF-8')) ?: [])
            ->map(function (string $token): string {
                if (isset(self::PRESERVED_TOKENS[$token])) {
                    return self::PRESERVED_TOKENS[$token];
                }

                return mb_convert_case($token, MB_CASE_TITLE, 'UTF-8');
            })
            ->implode(' ');
    }

    public static function jenjangLabel(?string $code): string
    {
        if ($code === null || $code === '') {
            return self::EMPTY_LABEL;
        }

        return self::JENJANG_LABELS[$code] ?? self::displayLabel($code);
    }

    /**
     * @return array<string, mixed>
     */
    private function build(CarbonImmutable $now): array
    {
        $total = PendaftarTeacherOfChampions::query()->count();
        $alumni = PendaftarTeacherOfChampions::query()->where('alumni_unimed', true)->count();
        $startOfTodayUtc = $now->startOfDay()->utc();

        $statusCounts = PendaftarTeacherOfChampions::query()
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $provincesAll = $this->groupedBreakdown('provinsi');
        $bidangAll = $this->groupedBreakdown('bidang_studi');

        return [
            'total' => $total,
            'today' => PendaftarTeacherOfChampions::query()->where('created_at', '>=', $startOfTodayUtc)->count(),
            'statuses' => collect(PendaftarTeacherOfChampions::STATUSES)
                ->map(fn (string $label, string $status): int => (int) ($statusCounts[$status] ?? 0))
                ->all(),
            'alumni' => $alumni,
            'alumni_percentage' => $total === 0 ? 0.0 : round($alumni / $total * 100, 1),
            'daily' => $this->dailyTrend($now),
            'provinces' => array_slice($provincesAll, 0, 10),
            'provinces_all' => $provincesAll,
            'jenjang' => $this->jenjangBreakdown(),
            'bidang_studi' => array_slice($bidangAll, 0, 10),
            'bidang_studi_all' => $bidangAll,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function dailyTrend(CarbonImmutable $now): array
    {
        $today = $now->startOfDay();
        $firstRegistration = PendaftarTeacherOfChampions::query()->min('created_at');

        if ($firstRegistration === null) {
            return [];
        }

        $firstDay = CarbonImmutable::parse($firstRegistration, 'UTC')->setTimezone(Kegiatan::TIMEZONE)->startOfDay();
        $start = $firstDay->max($today->subDays(self::TREND_MAX_DAYS - 1));

        $counts = PendaftarTeacherOfChampions::query()
            ->where('created_at', '>=', $start->utc())
            ->pluck('created_at')
            ->countBy(fn (DateTimeInterface $createdAt): string => CarbonImmutable::instance($createdAt)->setTimezone(Kegiatan::TIMEZONE)->toDateString());

        $days = [];

        for ($day = $start; $day->lessThanOrEqualTo($today); $day = $day->addDay()) {
            $days[$day->toDateString()] = (int) ($counts[$day->toDateString()] ?? 0);
        }

        return $days;
    }

    /**
     * @return list<array{label: string, total: int}>
     */
    private function jenjangBreakdown(): array
    {
        /** @var Collection<int, object{value: ?string, total: int}> $rows */
        $rows = PendaftarTeacherOfChampions::query()
            ->toBase()
            ->selectRaw('jenjang as value, count(*) as total')
            ->groupBy('jenjang')
            ->orderByDesc('total')
            ->orderBy('jenjang')
            ->get();

        return $rows
            ->map(fn (object $row): array => [
                'label' => self::jenjangLabel($row->value),
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    /**
     * Groups free-text columns ignoring case and double spaces (display-only).
     *
     * @return list<array{label: string, total: int}>
     */
    private function groupedBreakdown(string $column): array
    {
        /** @var Collection<int, object{value: ?string, total: int}> $rows */
        $rows = PendaftarTeacherOfChampions::query()
            ->toBase()
            ->selectRaw("{$column} as value, count(*) as total")
            ->groupBy($column)
            ->get();

        /** @var array<string, array{label: string, total: int}> $grouped */
        $grouped = [];

        foreach ($rows as $row) {
            $raw = is_string($row->value) ? $row->value : '';
            $key = $raw === '' ? '' : Str::of($raw)->lower()->squish()->toString();
            $label = $key === '' ? self::EMPTY_LABEL : self::displayLabel($raw);
            $total = (int) $row->total;

            if (! isset($grouped[$key])) {
                $grouped[$key] = ['label' => $label, 'total' => $total];

                continue;
            }

            $grouped[$key]['total'] += $total;
        }

        return collect($grouped)
            ->sortBy([
                ['total', 'desc'],
                ['label', 'asc'],
            ])
            ->values()
            ->all();
    }
}
