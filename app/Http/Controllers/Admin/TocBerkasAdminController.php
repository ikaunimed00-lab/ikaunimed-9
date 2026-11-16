<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TocBerkas;
use App\Models\TocBerkasExportArtifact;
use App\Services\TocBerkasVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TocBerkasAdminController extends Controller
{
    public function __construct(protected TocBerkasVerificationService $verification) {}

    public function download(TocBerkas $berkas): StreamedResponse
    {
        $this->authorizeAdmin();

        $disk = Storage::disk(config('toc.berkas.disk'));

        abort_unless($disk->exists($berkas->path), 404);

        return $disk->download($berkas->path, $berkas->nama_asli);
    }

    public function terima(Request $request, TocBerkas $berkas): RedirectResponse
    {
        $this->authorizeAdmin();

        $this->verification->accept($berkas, Auth::user());

        return back();
    }

    public function tolak(Request $request, TocBerkas $berkas): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'catatan' => ['required', 'string', 'max:1000'],
        ]);

        $this->verification->reject($berkas, Auth::user(), $data['catatan']);

        return back();
    }

    public function downloadExport(TocBerkasExportArtifact $artifact): StreamedResponse
    {
        $this->authorizeAdmin();

        $disk = Storage::disk(config('toc.berkas.disk'));

        if ($artifact->isExpired()) {
            $this->deleteArtifact($artifact);

            abort(410, 'Tautan unduhan sudah kedaluwarsa.');
        }

        abort_unless($artifact->user_id === Auth::id(), 403);
        abort_unless($disk->exists($artifact->path), 404);

        return $disk->download(
            $artifact->path,
            'berkas-toc-'.$artifact->created_at->timezone(config('toc.timezone'))->format('Ymd-His').'.zip',
            ['Content-Type' => 'application/zip'],
        );
    }

    private function deleteArtifact(TocBerkasExportArtifact $artifact): void
    {
        $disk = Storage::disk(config('toc.berkas.disk'));

        if ($disk->exists($artifact->path)) {
            $disk->delete($artifact->path);
        }

        $artifact->delete();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->hasSystemRole(['super_admin', 'admin']) ?? false, 403);
    }
}
