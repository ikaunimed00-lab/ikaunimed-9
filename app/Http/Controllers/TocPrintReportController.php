<?php

namespace App\Http\Controllers;

use App\Filament\Pages\TocDashboard;
use App\Services\AuditLogger;
use App\Services\TocDashboardStats;
use App\Services\TocShareableReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TocPrintReportController extends Controller
{
    public function __invoke(Request $request, TocDashboardStats $stats, TocShareableReport $report): View
    {
        abort_unless(TocDashboard::userCanView(), 403);

        TocDashboardStats::forget();
        $snapshot = $stats->snapshot();

        if ($request->boolean('autoprint')) {
            app(AuditLogger::class)->record('laporan.dibagikan', null, ['jenis' => 'print']);
        }

        return view('toc.print-report', [
            'report' => $report->printPayload($snapshot),
            'autoprint' => $request->boolean('autoprint'),
        ]);
    }
}
