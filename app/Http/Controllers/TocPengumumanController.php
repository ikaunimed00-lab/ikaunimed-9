<?php

namespace App\Http\Controllers;

use App\Http\Requests\TocLoginRequest;
use App\Services\SiteManagementService;
use App\Services\TocPesertaAuthService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TocPengumumanController extends Controller
{
    public function __construct(
        protected SiteManagementService $siteService,
        protected TocPesertaAuthService $authService,
    ) {}

    public function show(): Response
    {
        return $this->render('TeacherOfChampions/Pengumuman', [
            'pengumumanDibuka' => (bool) config('toc.pengumuman_dibuka'),
            'helpText' => (string) config('toc.login.help_text'),
            'result' => null,
        ]);
    }

    public function check(TocLoginRequest $request): Response|RedirectResponse|SymfonyResponse
    {
        if (! config('toc.pengumuman_dibuka')) {
            return $this->render('TeacherOfChampions/Pengumuman', [
                'pengumumanDibuka' => false,
                'helpText' => (string) config('toc.login.help_text'),
                'result' => null,
            ]);
        }

        $attempt = $this->authService->attempt(
            $request,
            $request->string('nomor_pendaftaran')->toString(),
            requireLulus: false,
            sumber: 'pengumuman',
        );

        if (! $attempt['ok']) {
            if (! empty($attempt['blocked'])) {
                return response($attempt['error'], 429);
            }

            return back()->withErrors([
                TocPesertaAuthService::GENERIC_ERROR_KEY => $attempt['error'],
            ])->withInput($request->only('nomor_pendaftaran'));
        }

        $pendaftar = $attempt['pendaftar'];
        $lulus = $pendaftar->lulus_penyisihan;

        $result = [
            'nama' => $pendaftar->nama,
            'status' => $lulus === null ? 'belum_tersedia' : ($lulus ? 'lulus' : 'tidak_lulus'),
        ];

        if ($lulus === true) {
            $result['linkGrupWa'] = (string) config('toc.link_grup_wa_finalis');
            $result['jadwal'] = $this->jadwalPayload();
            $result['batasUnggahLabel'] = $this->batasUnggahLabel();
            $result['berkasUrl'] = route('toc.berkas');
        }

        return $this->render('TeacherOfChampions/Pengumuman', [
            'pengumumanDibuka' => true,
            'helpText' => (string) config('toc.login.help_text'),
            'result' => $result,
        ]);
    }

    /**
     * @return array{tmFinal: array{label: string, tanggal: string, info: string}, final: array{label: string, tanggal: string, info: string}}
     */
    private function jadwalPayload(): array
    {
        return [
            'tmFinal' => [
                'label' => (string) config('toc.tm_final.label'),
                'tanggal' => (string) config('toc.tm_final.tanggal'),
                'info' => $this->infoOrFallback('tm_final'),
            ],
            'final' => [
                'label' => (string) config('toc.final.label'),
                'tanggal' => (string) config('toc.final.tanggal'),
                'info' => $this->infoOrFallback('final'),
            ],
        ];
    }

    private function infoOrFallback(string $key): string
    {
        $info = trim((string) config("toc.{$key}.info"));

        return $info !== '' ? $info : (string) config("toc.{$key}.fallback");
    }

    private function batasUnggahLabel(): string
    {
        return \Carbon\Carbon::parse(config('toc.batas_unggah'), config('toc.timezone'))
            ->locale('id')
            ->translatedFormat('l, j F Y').' pukul 23.59 WIB';
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function render(string $component, array $props): Response
    {
        $footer = collect($this->siteService->getHomepageSections())->firstWhere('slug', 'footer');
        $title = 'Pengumuman Penyisihan - Teacher of Champions';
        $description = 'Cek hasil Babak Penyisihan Teacher of Champions 2026 IKA UNIMED.';

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
