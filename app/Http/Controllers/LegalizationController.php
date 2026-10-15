<?php

namespace App\Http\Controllers;

use App\Models\Legalization;
use App\Models\LegalizationFile;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LegalizationController extends Controller
{
    /**
     * LIST PENGAJUAN ALUMNI
     */
    public function index()
    {
        $user = auth()->user();

        // Get notifications
        try {
            $notifications = Notification::unreadForUser($user->id);
        } catch (\Exception $e) {
            $notifications = [];
        }

        $legalizations = Legalization::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'jenjang' => $item->jenjang,
                'tahun_lulus' => $item->tahun_lulus,
                'jumlah' => $item->jumlah_lembar,
                'tujuan' => $item->tujuan,
                'status' => $item->status,
                'created_at' => $item->created_at,
            ]);

        return Inertia::render('Dashboard/Subscriber/Legalization/Index', [
            'legalizations' => $legalizations,
            'user' => $user,
            'notifications' => $notifications,
            'stats' => [],
        ]);
    }

    /**
     * FORM AJUKAN LEGALISIR
     */
    public function create()
    {
        $user = auth()->user();

        // Get notifications
        try {
            $notifications = Notification::unreadForUser($user->id);
        } catch (\Exception $e) {
            $notifications = [];
        }

        return Inertia::render('Dashboard/Subscriber/Legalization/Create', [
            'user' => $user,
            'notifications' => $notifications,
            'stats' => [],
        ]);
    }

    /**
     * SIMPAN PENGAJUAN BARU
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenjang' => 'required|string|max:10',
            'tahun_lulus' => 'required|digits:4',
            'jumlah_lembar' => 'required|integer|min:1',
            'tujuan' => 'nullable|string|max:255',
        ]);

        Legalization::create([
            'user_id' => auth()->id(),
            'jenjang' => $validated['jenjang'],
            'tahun_lulus' => $validated['tahun_lulus'],
            'jumlah_lembar' => $validated['jumlah_lembar'],
            'tujuan' => $validated['tujuan'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('legalization.index')
            ->with('success', 'Pengajuan legalisir berhasil dibuat.');
    }

    /**
     * DETAIL LEGALISASI + FILE
     */
    public function show(Legalization $legalization)
    {
        abort_if($legalization->user_id !== auth()->id(), 403);

        $user = auth()->user();

        // Get notifications
        try {
            $notifications = Notification::unreadForUser($user->id);
        } catch (\Exception $e) {
            $notifications = [];
        }

        $legalization->load('files');

        return Inertia::render('Dashboard/Subscriber/Legalization/Show', [
            'legalization' => $legalization,
            'user' => $user,
            'notifications' => $notifications,
            'stats' => [],
        ]);
    }

    /**
     * UPLOAD FILE IJAZAH
     */
    public function upload(Request $request, Legalization $legalization)
    {
        abort_if($legalization->user_id !== auth()->id(), 403);

        // Check status - upload only allowed when submitted
        if ($legalization->status !== 'submitted') {
            return back()->with('error', 'Upload hanya diperbolehkan saat status "Menunggu Verifikasi".');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $file = $request->file('file');
        $path = $file->store(LegalizationFile::DIRECTORY, LegalizationFile::DISK);

        if ($path === false) {
            return back()->with('error', 'File gagal disimpan. Silakan coba lagi.');
        }

        try {
            LegalizationFile::create([
                'legalization_id' => $legalization->id,
                'type' => LegalizationFile::TYPE_IJAZAH,
                'original_name' => $file->getClientOriginalName(),
                'filename' => basename($path),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        } catch (\Throwable $exception) {
            Storage::disk(LegalizationFile::DISK)->delete($path);

            throw $exception;
        }

        return back()->with('success', 'File berhasil diupload. Pengajuan Anda sedang diproses.');
    }

    /**
     * UNDUH FILE IJAZAH (pemilik atau admin legalisasi)
     */
    public function download(LegalizationFile $legalizationFile): StreamedResponse
    {
        Gate::authorize('view', $legalizationFile);

        $disk = Storage::disk(LegalizationFile::DISK);
        $path = $legalizationFile->storagePath();

        abort_unless($disk->exists($path), 404);

        return $disk->download($path, $legalizationFile->original_name, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
