<?php

namespace App\Filament\Widgets\Toc;

use App\Filament\Pages\TocDashboard;
use App\Services\TocDashboardStats;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Number;

abstract class TocBreakdownTableWidget extends TableWidget
{
    protected static bool $isDiscovered = false;

    abstract protected function breakdownHeading(): string;

    abstract protected function columnLabel(): string;

    /**
     * Key of the breakdown in TocDashboardStats::summary().
     */
    abstract protected function summaryKey(): string;

    public static function canView(): bool
    {
        return TocDashboard::userCanView();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading($this->breakdownHeading())
            ->records(fn (): array => $this->rows())
            ->paginated(false)
            ->columns([
                TextColumn::make('label')->label($this->columnLabel()),
                TextColumn::make('total')->label('Jumlah')->numeric()->alignEnd(),
                TextColumn::make('percentage')->label('%')->alignEnd(),
            ]);
    }

    /**
     * @return array<string, array{label: string, total: int, percentage: string}>
     */
    private function rows(): array
    {
        $summary = app(TocDashboardStats::class)->summary();
        $total = max($summary['total'], 1);

        return collect($summary[$this->summaryKey()])
            ->mapWithKeys(fn (array $row, int $index): array => [
                (string) $index => [
                    ...$row,
                    'percentage' => Number::format($row['total'] / $total * 100, maxPrecision: 1).'%',
                ],
            ])
            ->all();
    }
}
