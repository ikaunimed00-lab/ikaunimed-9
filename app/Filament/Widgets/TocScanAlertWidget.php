<?php

namespace App\Filament\Widgets;

use App\Services\TocAksesMonitorService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class TocScanAlertWidget extends Widget
{
    protected string $view = 'filament.widgets.toc-scan-alert';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Auth::user()?->hasSystemRole(['super_admin', 'admin']) ?? false;
    }

    /**
     * @return array{alerts: \Illuminate\Support\Collection}
     */
    protected function getViewData(): array
    {
        return [
            'alerts' => app(TocAksesMonitorService::class)->blockedIpAlerts(),
        ];
    }
}
