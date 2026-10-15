<?php

namespace App\Filament\Widgets\Toc;

class TocJenjangWidget extends TocBreakdownTableWidget
{
    protected static ?int $sort = 4;

    protected function breakdownHeading(): string
    {
        return 'Distribusi jenjang';
    }

    protected function columnLabel(): string
    {
        return 'Jenjang';
    }

    protected function summaryKey(): string
    {
        return 'jenjang';
    }
}
