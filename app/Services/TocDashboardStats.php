<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TocDashboardStats
{
    public const CACHE_KEY = 'toc.dashboard.stats';

    public const CACHE_SECONDS = 60;

    public const TREND_MAX_DAYS = 60;

    public const TREND_MIN_DAYS = 14;

    /**
     * @return array{
     *     total: int,
     *     today: int,
     *     statuses: array<string, int>,
     *     alumni: int,
     *     alumni_percentage: float,
     *     daily: array<string, int>,
     *     provinces: list<array{label: string, total: int}>,
     *     jenjang: list<array{label: string, total: int}>,
     *     bidang_studi: list<array{label: string, total: int}>,
     * }
     */
    public function summary(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, fn (): array => $this->build());
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    private function build(): array
    {
        $total = PendaftarTeacherOfChampions::query()->count();
        $alumni = PendaftarTeacherOfChampions::query()->where('alumni_unimed', true)->count();
        $startOfTodayUtc = CarbonImmutable::now(Kegiatan::TIMEZONE)->startOfDay()->utc();

        $statusCounts = PendaftarTeacherOfChampions::query()
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => $total,
            'today' => PendaftarTeacherOfChampions::query()->where('created_at', '>=', $startOfTodayUtc)->count(),
            'statuses' => collect(PendaftarTeacherOfChampions::STATUSES)
                ->map(fn (string $label, string $status): int => (int) ($statusCounts[$status] ?? 0))
                ->all(),
            'alumni' => $alumni,
            'alumni_percentage' => $total === 0 ? 0.0 : round($alumni / $total * 100, 1),
            'daily' => $this->dailyTrend(),
            'provinces' => $this->breakdown('provinsi', limit: 10),
            'jenjang' => $this->breakdown('jenjang', labels: PendaftarTeacherOfChampions::JENJANG),
            'bidang_studi' => $this->breakdown('bidang_studi', limit: 10),
        ];
    }

    /**
     * Registrations per WIB calendar day, oldest first.
     *
     * @return array<string, int>
     */
    private function dailyTrend(): array
    {
        $today = CarbonImmutable::now(Kegiatan::TIMEZONE)->startOfDay();
        $firstRegistration = PendaftarTeacherOfChampions::query()->min('created_at');

        $start = $today->subDays(self::TREND_MIN_DAYS - 1);

        if ($firstRegistration !== null) {
            $firstDay = CarbonImmutable::parse($firstRegistration, 'UTC')->setTimezone(Kegiatan::TIMEZONE)->startOfDay();
            $start = $firstDay->min($start)->max($today->subDays(self::TREND_MAX_DAYS - 1));
        }

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
     * @param  array<string, string>  $labels
     * @return list<array{label: string, total: int}>
     */
    private function breakdown(string $column, ?int $limit = null, array $labels = []): array
    {
        /** @var Collection<int, object{value: ?string, total: int}> $rows */
        $rows = PendaftarTeacherOfChampions::query()
            ->toBase()
            ->selectRaw("{$column} as value, count(*) as total")
            ->groupBy($column)
            ->orderByDesc('total')
            ->orderBy($column)
            ->when($limit !== null, fn ($query) => $query->limit($limit))
            ->get();

        return $rows
            ->map(fn (object $row): array => [
                'label' => $row->value === null || $row->value === ''
                    ? 'Tidak diisi'
                    : ($labels[$row->value] ?? (string) $row->value),
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();
    }
}
