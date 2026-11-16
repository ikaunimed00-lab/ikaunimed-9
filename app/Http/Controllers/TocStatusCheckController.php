<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckTocRegistrationStatusRequest;
use App\Models\PendaftarTeacherOfChampions;
use App\Services\SiteManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TocStatusCheckController extends Controller
{
    public function __construct(protected SiteManagementService $siteService) {}

    public function show(): Response
    {
        return $this->render([
            'result' => session('toc_status_result'),
            'genericError' => session('toc_status_error'),
        ]);
    }

    public function check(CheckTocRegistrationStatusRequest $request): RedirectResponse
    {
        $whatsapp = $request->validated('whatsapp');
        $email = $request->validated('email');

        $pendaftar = PendaftarTeacherOfChampions::query()
            ->where('whatsapp', $whatsapp)
            ->where('email', $email)
            ->first();

        if ($pendaftar === null) {
            Log::warning('toc.status_check.failed', [
                'ip_hash' => substr(hash('sha256', (string) $request->ip()), 0, 12),
                'wa_hash' => substr(hash('sha256', (string) $whatsapp), 0, 12),
                'email_hash' => substr(hash('sha256', (string) $email), 0, 12),
            ]);

            return redirect()
                ->route('teacher-of-champions.status')
                ->with('toc_status_error', 'Data tidak ditemukan atau tidak cocok');
        }

        return redirect()
            ->route('teacher-of-champions.status')
            ->with('toc_status_result', [
                'nomorPendaftaran' => $pendaftar->nomor_pendaftaran,
                'namaMasked' => PendaftarTeacherOfChampions::maskName($pendaftar->nama),
                'status' => $pendaftar->status,
                'statusLabel' => $pendaftar->statusLabel(),
                'tanggalDaftar' => $pendaftar->registeredAtWibLabel(),
                'buktiUrl' => $pendaftar->signedBuktiUrl(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function render(array $props): Response
    {
        $footer = collect($this->siteService->getHomepageSections())->firstWhere('slug', 'footer');
        $title = 'Cek Status Pendaftaran Teacher of Champions - IKA UNIMED';
        $description = 'Cek nomor pendaftaran Teacher of Champions dengan WhatsApp dan email yang digunakan saat mendaftar.';

        return Inertia::render('TeacherOfChampions/CekStatus', [
            ...$props,
            'settings' => $this->siteService->getSettings(),
            'footerContent' => $footer?->content,
            'contacts' => config('homepage.reuni_contacts', []),
            'guidebookUrl' => config('homepage.teacher_of_champions.guidebook_url'),
        ])->withViewData([
            'meta_description' => $description,
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => url(config('homepage.teacher_of_champions.og_image')),
        ]);
    }
}
