<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PendaftarTeacherOfChampions\PendaftarTeacherOfChampionsResource;
use App\Filament\Widgets\Toc\TocBidangStudiWidget;
use App\Filament\Widgets\Toc\TocDailyRegistrationsChart;
use App\Filament\Widgets\Toc\TocJenjangWidget;
use App\Filament\Widgets\Toc\TocStatsOverview;
use App\Filament\Widgets\Toc\TocTopProvincesWidget;
use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use App\Services\AuditLogger;
use App\Services\TocDashboardStats;
use App\Services\TocShareableReport;
use BackedEnum;
use Carbon\CarbonImmutable;
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
            ->body('Ringkasan siap dibagikan.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('salinRingkasanWhatsApp')
                ->label('Salin ringkasan WhatsApp')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->modalHeading('Ringkasan untuk WhatsApp')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(fn () => view('filament.toc.whatsapp-summary-modal', [
                    'text' => app(TocShareableReport::class)->whatsappSummary(),
                ])),

            Action::make('unduhGambarLaporan')
                ->label('Unduh gambar laporan')
                ->icon(Heroicon::OutlinedPhoto)
                ->modalHeading('Gambar laporan ToC')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(function () {
                    $now = CarbonImmutable::now(Kegiatan::TIMEZONE)->locale('id');
                    $closesAt = PendaftarTeacherOfChampions::registrationClosesAt()
                        ->copy()
                        ->setTimezone(Kegiatan::TIMEZONE)
                        ->locale('id');

                    return view('filament.toc.png-report-modal', [
                        'summary' => app(TocDashboardStats::class)->summary(),
                        'generatedAt' => $now->translatedFormat('l, j F Y · H.i').' WIB',
                        'closesLabel' => 'Ditutup '.$closesAt->translatedFormat('j M Y').' '.$closesAt->format('H.i').' WIB',
                        'filename' => 'toc-update-'.$now->format('Ymd-Hi').'.png',
                    ]);
                }),

            Action::make('cetakPdf')
                ->label('Cetak / simpan PDF')
                ->icon(Heroicon::OutlinedPrinter)
                ->action(function (): void {
                    $this->recordLaporanShare('print');
                })
                ->extraAttributes([
                    'onclick' => 'setTimeout(() => window.print(), 150)',
                ]),

            Action::make('eksporStatistikCsv')
                ->label('Ekspor statistik CSV')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->visible(fn (): bool => PendaftarTeacherOfChampionsResource::userCan('export'))
                ->action(function () {
                    abort_unless(PendaftarTeacherOfChampionsResource::userCan('export'), 403);
                    app(AuditLogger::class)->record('laporan.dibagikan', null, ['jenis' => 'csv']);

                    return app(TocShareableReport::class)->downloadCsv();
                }),
        ];
    }
}
