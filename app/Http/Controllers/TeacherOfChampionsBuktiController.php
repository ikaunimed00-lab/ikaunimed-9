<?php

namespace App\Http\Controllers;

use App\Models\PendaftarTeacherOfChampions;
use App\Services\SiteManagementService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class TeacherOfChampionsBuktiController extends Controller
{
    public function __construct(protected SiteManagementService $siteService) {}

    public function __invoke(Request $request, PendaftarTeacherOfChampions $pendaftar): View|Response
    {
        if (! $request->hasValidSignature()) {
            $footer = collect($this->siteService->getHomepageSections())->firstWhere('slug', 'footer');

            return Inertia::render('TeacherOfChampions/TautanTidakValid', [
                'cekStatusUrl' => route('teacher-of-champions.status'),
                'settings' => $this->siteService->getSettings(),
                'footerContent' => $footer?->content,
                'guidebookUrl' => config('homepage.teacher_of_champions.guidebook_url'),
            ]);
        }

        return view('toc.bukti-pendaftaran', [
            'pendaftar' => $pendaftar,
            'autoprint' => $request->boolean('autoprint'),
        ]);
    }
}
