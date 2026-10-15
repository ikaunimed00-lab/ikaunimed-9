<?php

namespace App\Filament\Widgets\Toc;

class TocTopProvincesWidget extends TocBreakdownTableWidget
{
    protected static ?int $sort = 3;

    protected function breakdownHeading(): string
    {
        return 'Provinsi teratas';
    }

    protected function columnLabel(): string
    {
        return 'Provinsi';
    }

    protected function summaryKey(): string
    {
        return 'provinces';
    }
}
