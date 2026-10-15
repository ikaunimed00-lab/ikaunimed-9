<?php

namespace App\Filament\Widgets\Toc;

class TocBidangStudiWidget extends TocBreakdownTableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected function breakdownHeading(): string
    {
        return 'Bidang studi teratas';
    }

    protected function columnLabel(): string
    {
        return 'Bidang studi';
    }

    protected function summaryKey(): string
    {
        return 'bidang_studi';
    }
}
