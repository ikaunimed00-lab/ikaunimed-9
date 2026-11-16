<?php

namespace App\Http\Controllers;

use App\Http\Requests\TocLoginRequest;
use App\Http\Requests\TocUnggahBerkasRequest;
use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use App\Services\SiteManagementService;
use App\Services\TocBerkasVerificationService;
use App\Services\TocPesertaAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TocBerkasController extends Controller
{
    public function __construct(
        protected SiteManagementService $siteService,
        protected TocPesertaAuthService $authService,
        protected TocBerkasVerificationService $verification,
    ) {}

    public function showLogin(): Response|RedirectResponse
    {
        if (! config('toc.pengumuman_dibuka')) {
            return $this->render('TeacherOfChampions/BerkasLogin', [
                'pengumumanDibuka' => false,
                'helpText' => (string) config('toc.login.help_text'),
            ]);
        }

        if ($this->currentPeserta()) {
            return redirect()->route('toc.berkas.formulir');
        }

        return $this->render('TeacherOfChampions/BerkasLogin', [
            'pengumumanDibuka' => true,
            'helpText' => (string) config('toc.login.help_text'),
        ]);
    }

    public function login(TocLoginRequest $request): RedirectResponse|SymfonyResponse
    {
        if (! config('toc.pengumuman_dibuka')) {
            return back()->withErrors([
                TocPesertaAuthService::GENERIC_ERROR_KEY => config('toc.login.generic_error'),
            ]);
        }

        $attempt = $this->authService->attempt(
            $request,
            $request->string('nomor_pendaftaran')->toString(),
            requireLulus: true,
            sumber: 'berkas',
        );

        if (! $attempt['ok']) {
            if (! empty($attempt['blocked'])) {
                return response($attempt['error'], 429);
            }

            return back()->withErrors([
                TocPesertaAuthService::GENERIC_ERROR_KEY => $attempt['error'],
            ])->withInput($request->only('nomor_pendaftaran'));
        }

        $request->session()->regenerate();
        $request->session()->put(config('toc.berkas.session_key'), $attempt['pendaftar']->id);
        $request->session()->put(
            config('toc.berkas.session_key').'_expires_at',
            now()->addMinutes((int) config('toc.berkas.session_ttl_minutes'))->timestamp,
        );

        // Full reload so CSRF meta/cookie stay in sync after session regenerate (avoids 419 on upload).
        return Inertia::location(route('toc.berkas.formulir'));
    }

    public function formulir(Request $request): Response|RedirectResponse
    {
        if (! config('toc.pengumuman_dibuka')) {
            return redirect()->route('toc.berkas');
        }

        $peserta = $this->currentPeserta();

        if (! $peserta) {
            return redirect()->route('toc.berkas');
        }

        $berkas = TocBerkas::query()
            ->where('pendaftar_id', $peserta->id)
            ->get()
            ->keyBy('jenis');

        $batas = $this->verification->effectiveBatas($peserta, $berkas);
        $terkunci = now($batas->timezone)->greaterThan($batas);

        return $this->render('TeacherOfChampions/BerkasFormulir', [
            'peserta' => [
                'nama' => $peserta->nama,
                'nomorPendaftaran' => $peserta->nomor_pendaftaran,
                'setujuPublikasi' => $peserta->setuju_publikasi_at !== null,
            ],
            'batasUnggah' => $batas->toIso8601String(),
            'batasUnggahLabel' => $batas->locale('id')->translatedFormat('l, j F Y \p\u\k\u\l H.i').' WIB',
            'terkunci' => $terkunci,
            'berkas' => collect(config('toc.berkas.jenis'))->map(function (array $meta, string $jenis) use ($berkas, $terkunci) {
                $item = $berkas->get($jenis);
                $diterima = $item?->status === TocBerkas::STATUS_DITERIMA;

                return [
                    'jenis' => $jenis,
                    'label' => $meta['label'],
                    'maxKb' => $meta['max_kb'],
                    'extensions' => $meta['extensions'],
                    'minWidth' => $meta['min_width'] ?? null,
                    'minHeight' => $meta['min_height'] ?? null,
                    'status' => $item?->status,
                    'statusLabel' => $item ? (TocBerkas::STATUS_LABELS[$item->status] ?? $item->status) : 'Belum diunggah',
                    'catatan' => $item?->catatan,
                    'namaAsli' => $item?->nama_asli,
                    'uploadedAt' => $item?->uploaded_at?->timezone(config('toc.timezone'))->format('d M Y H:i'),
                    'bolehUnggah' => ! $terkunci && ! $diterima,
                    'butuhPersetujuan' => $jenis === TocBerkas::JENIS_FOTO,
                ];
            })->values(),
            'templates' => collect(config('toc.berkas.templates'))->map(fn (array $tpl, string $key) => [
                'key' => $key,
                'label' => $tpl['download_as'],
                'url' => route('toc.berkas.template', $key),
            ])->values(),
        ]);
    }

    public function unggah(TocUnggahBerkasRequest $request, string $jenis): RedirectResponse
    {
        if (! config('toc.pengumuman_dibuka')) {
            abort(403);
        }

        $peserta = $this->currentPeserta();

        if (! $peserta) {
            return redirect()->route('toc.berkas');
        }

        if (now(config('toc.timezone'))->greaterThan($this->verification->effectiveBatas($peserta))) {
            return back()->withErrors([
                'file' => 'Batas waktu unggah berkas telah berakhir.',
            ]);
        }

        $meta = config("toc.berkas.jenis.{$jenis}");

        if (! is_array($meta)) {
            abort(404);
        }

        $existing = TocBerkas::query()
            ->where('pendaftar_id', $peserta->id)
            ->where('jenis', $jenis)
            ->first();

        if ($existing && $existing->status === TocBerkas::STATUS_DITERIMA) {
            return back()->withErrors([
                'file' => 'Berkas yang sudah diterima tidak dapat diganti. Hubungi admin bila perlu revisi.',
            ]);
        }

        $file = $request->file('file');
        $disk = Storage::disk(config('toc.berkas.disk'));
        $directory = trim((string) config('toc.berkas.path_prefix'), '/').'/'.$peserta->nomor_pendaftaran;
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs($directory, $filename, config('toc.berkas.disk'));

        if ($existing) {
            if ($disk->exists($existing->path)) {
                $disk->delete($existing->path);
            }
            $existing->delete();
        }

        TocBerkas::query()->create([
            'pendaftar_id' => $peserta->id,
            'jenis' => $jenis,
            'path' => $path,
            'nama_asli' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType() ?: 'application/octet-stream',
            'ukuran' => $file->getSize(),
            'status' => TocBerkas::STATUS_MENUNGGU,
            'catatan' => null,
            'uploaded_at' => now(),
            'uploaded_ip' => $request->ip(),
            'uploaded_ua' => $request->userAgent() ? Str::limit($request->userAgent(), 500, '') : null,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        if ($jenis === TocBerkas::JENIS_FOTO && $request->boolean('setuju_publikasi') && $peserta->setuju_publikasi_at === null) {
            $peserta->forceFill(['setuju_publikasi_at' => now()])->save();
        }

        return back()->with('success', $meta['label'].' berhasil diunggah.');
    }

    public function template(string $nama): BinaryFileResponse
    {
        $templates = config('toc.berkas.templates', []);

        if (! isset($templates[$nama])) {
            abort(404);
        }

        $path = public_path('downloads/toc/'.$templates[$nama]['file']);

        if (! is_file($path)) {
            abort(404);
        }

        return response()->download($path, $templates[$nama]['download_as']);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget([
            config('toc.berkas.session_key'),
            config('toc.berkas.session_key').'_expires_at',
        ]);

        return redirect()->route('toc.berkas');
    }

    private function currentPeserta(): ?PendaftarTeacherOfChampions
    {
        $key = config('toc.berkas.session_key');
        $expiresKey = $key.'_expires_at';
        $id = session($key);
        $expiresAt = session($expiresKey);

        if (! is_numeric($id) || ! is_numeric($expiresAt) || now()->timestamp > (int) $expiresAt) {
            session()->forget([$key, $expiresKey]);

            return null;
        }

        $peserta = PendaftarTeacherOfChampions::query()
            ->whereKey((int) $id)
            ->where('lulus_penyisihan', true)
            ->first([
                'id',
                'nomor_pendaftaran',
                'nama',
                'lulus_penyisihan',
                'setuju_publikasi_at',
            ]);

        if (! $peserta) {
            session()->forget([$key, $expiresKey]);
        }

        return $peserta;
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function render(string $component, array $props): Response
    {
        $footer = collect($this->siteService->getHomepageSections())->firstWhere('slug', 'footer');
        $title = 'Unggah Berkas Finalis - Teacher of Champions';
        $description = 'Unggah berkas finalis Teacher of Champions 2026.';

        return Inertia::render($component, [
            ...$props,
            'settings' => $this->siteService->getSettings(),
            'footerContent' => $footer?->content,
            'contacts' => config('homepage.reuni_contacts', []),
        ])->withViewData([
            'meta_description' => $description,
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => url(config('homepage.teacher_of_champions.og_image')),
            'robots' => 'noindex, nofollow',
        ]);
    }
}
