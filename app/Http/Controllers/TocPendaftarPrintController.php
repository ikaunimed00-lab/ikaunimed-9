<?php

namespace App\Http\Controllers;

use App\Models\PendaftarTeacherOfChampions;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TocPendaftarPrintController extends Controller
{
    public function __invoke(Request $request, PendaftarTeacherOfChampions $pendaftar, AuditLogger $auditLogger): View
    {
        abort_unless($request->user() !== null, 403);

        $this->authorize('view', $pendaftar);

        $canViewContact = $request->user()?->can('viewContact', PendaftarTeacherOfChampions::class) ?? false;

        $auditLogger->record('pendaftar.dicetak', $pendaftar, [
            'pendaftar_id' => $pendaftar->id,
        ]);

        return view('toc.pendaftar-print', [
            'pendaftar' => $pendaftar,
            'canViewContact' => $canViewContact,
            'autoprint' => $request->boolean('autoprint'),
        ]);
    }
}
