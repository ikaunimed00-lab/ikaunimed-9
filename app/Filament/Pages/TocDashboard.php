<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PendaftarTeacherOfChampions\PendaftarTeacherOfChampionsResource;
use App\Filament\Widgets\Toc\TocBidangStudiWidget;
use App\Filament\Widgets\Toc\TocDailyRegistrationsChart;
use App\Filament\Widgets\Toc\TocJenjangWidget;
use App\Filament\Widgets\Toc\TocStatsOverview;
use App\Filament\Widgets\Toc\TocTopProvincesWidget;
use App\Services\AuditLogger;
use App\Services\TocDashboardStats;
use App\Services\TocShareableReport;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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

    protected string $view = 'filament.pages.toc-dashboard';

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

    public function recordLaporanShare(string $jenis): void
    {
        abort_unless(self::userCanView(), 403);

        app(AuditLogger::class)->record('laporan.dibagikan', null, ['jenis' => $jenis]);

        Notification::make()
            ->title('Laporan dicatat')
            ->success()
            ->send();
    }

    public function downloadPngReport()
    {
        abort_unless(self::userCanView(), 403);
        TocDashboardStats::forget();
        $snapshot = app(TocDashboardStats::class)->snapshot();
        app(AuditLogger::class)->record('laporan.dibagikan', null, ['jenis' => 'png']);

        return app(TocShareableReport::class)->downloadPng($snapshot);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('salinRingkasanWhatsApp')
                ->label('Salin ringkasan WhatsApp')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->modalHeading('Ringkasan untuk WhatsApp')
                ->modalWidth('2xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(function () {
                    TocDashboardStats::forget();

                    return view('filament.toc.whatsapp-summary-modal', [
                        'text' => app(TocShareableReport::class)->whatsappSummary(),
                    ]);
                }),

            Action::make('unduhGambarLaporan')
                ->label('Unduh gambar laporan')
                ->icon(Heroicon::OutlinedPhoto)
                ->modalHeading('Gambar laporan ToC')
                ->modalWidth('2xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(function () {
                    TocDashboardStats::forget();
                    $snapshot = app(TocDashboardStats::class)->snapshot();
                    $report = app(TocShareableReport::class);
                    $payload = $report->imagePayload($snapshot);

                    return view('filament.toc.png-report-modal', [
                        'previewDataUri' => 'data:image/png;base64,'.base64_encode($report->renderPngBinary($snapshot)),
                        'filename' => $payload['filename'],
                    ]);
                }),

            Action::make('cetakPdf')
                ->label('Cetak / simpan PDF')
                ->icon(Heroicon::OutlinedPrinter)
                ->url(fn (): string => route('toc.print-report', ['autoprint' => 1]))
                ->openUrlInNewTab(),

            Action::make('eksporStatistikXlsx')
                ->label('Ekspor statistik XLSX')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->visible(fn (): bool => PendaftarTeacherOfChampionsResource::userCan('export'))
                ->action(function () {
                    abort_unless(PendaftarTeacherOfChampionsResource::userCan('export'), 403);
                    TocDashboardStats::forget();
                    $snapshot = app(TocDashboardStats::class)->snapshot();
                    app(AuditLogger::class)->record('laporan.dibagikan', null, ['jenis' => 'xlsx']);

                    return app(TocShareableReport::class)->downloadXlsx($snapshot);
                }),
        ];
    }
}
