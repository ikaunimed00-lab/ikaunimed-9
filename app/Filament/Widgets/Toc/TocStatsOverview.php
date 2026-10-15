<?php

namespace App\Filament\Widgets\Toc;

use App\Filament\Pages\TocDashboard;
use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use App\Services\TocDashboardStats;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class TocStatsOverview extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected int|array|null $columns = 4;

    public static function canView(): bool
    {
        return TocDashboard::userCanView();
    }

    protected function getStats(): array
    {
        $summary = app(TocDashboardStats::class)->summary();

        $statusStats = collect(PendaftarTeacherOfChampions::STATUSES)
            ->map(fn (string $label, string $status): Stat => Stat::make("Status: {$label}", Number::format($summary['statuses'][$status] ?? 0)))
            ->values()
            ->all();

        return [
            Stat::make('Total pendaftar', Number::format($summary['total'])),
            Stat::make('Pendaftar hari ini (WIB)', Number::format($summary['today'])),
            Stat::make('Alumni UNIMED', Number::format($summary['alumni_percentage'], maxPrecision: 1).'%')
                ->description(Number::format($summary['alumni']).' dari '.Number::format($summary['total']).' pendaftar'),
            $this->countdownStat(),
            ...$statusStats,
        ];
    }

    private function countdownStat(): Stat
    {
        $closesAt = PendaftarTeacherOfChampions::registrationClosesAt();
        $description = 'Ditutup '.$closesAt->copy()->setTimezone(Kegiatan::TIMEZONE)->translatedFormat('d M Y, H:i').' WIB';

        if (! PendaftarTeacherOfChampions::isRegistrationOpen()) {
            return Stat::make('Pendaftaran', 'Ditutup')->description($description)->color('danger');
        }

        $remaining = now()->diff($closesAt);
        $value = $remaining->days > 0
            ? "{$remaining->days} hari {$remaining->h} jam lagi"
            : "{$remaining->h} jam {$remaining->i} menit lagi";

        return Stat::make('Sisa waktu pendaftaran', $value)->description($description)->color('success');
    }
}
