<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Toc\TocBidangStudiWidget;
use App\Filament\Widgets\Toc\TocDailyRegistrationsChart;
use App\Filament\Widgets\Toc\TocJenjangWidget;
use App\Filament\Widgets\Toc\TocStatsOverview;
use App\Filament\Widgets\Toc\TocTopProvincesWidget;
use BackedEnum;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TocDashboard extends Dashboard
{
    protected static string $routePath = 'toc-dashboard';

    protected static ?string $title = 'Dashboard Teacher of Champions';

    protected static ?string $navigationLabel = 'Dashboard ToC';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'ToC';

    protected static ?int $navigationSort = 0;

    public static function canAccess(): bool
    {
        return self::userCanView();
    }

    public static function userCanView(): bool
    {
        return Auth::user()?->hasTocPermission('toc.dashboard.view') ?? false;
    }

    public function getWidgets(): array
    {
        return [
            TocStatsOverview::class,
            TocDailyRegistrationsChart::class,
            TocTopProvincesWidget::class,
            TocJenjangWidget::class,
            TocBidangStudiWidget::class,
        ];
    }
}
