<?php

namespace App\Filament\Widgets\Toc;

use App\Filament\Pages\TocDashboard;
use App\Services\TocDashboardStats;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class TocDailyRegistrationsChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Tren pendaftar harian (WIB)';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    public static function canView(): bool
    {
        return TocDashboard::userCanView();
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $daily = app(TocDashboardStats::class)->summary()['daily'];

        return [
            'datasets' => [
                [
                    'label' => 'Pendaftar',
                    'data' => array_values($daily),
                    'fill' => true,
                ],
            ],
            'labels' => collect(array_keys($daily))
                ->map(fn (string $date): string => CarbonImmutable::parse($date)->translatedFormat('d M'))
                ->all(),
        ];
    }
}
