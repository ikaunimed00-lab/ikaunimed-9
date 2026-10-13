<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use App\Services\SiteManagementService;
use App\Services\UpcomingEventService;
use Inertia\Inertia;
use Inertia\Response;

class TeacherOfChampionsController extends Controller
{
    public function __construct(
        protected SiteManagementService $siteService,
        protected UpcomingEventService $eventService,
    ) {}

    public function show(): Response
    {
        $kegiatan = $this->eventService->findBySlugs([ReuniAkbarController::TEACHER_OF_CHAMPIONS_SLUG])->first();
        $kegiatanPayload = $kegiatan ? $this->eventService->toArray($kegiatan) : null;
        $closesAt = PendaftarTeacherOfChampions::registrationClosesAt()->setTimezone(Kegiatan::TIMEZONE)->locale('id');
        $settings = $this->siteService->getSettings();
        $footer = collect($this->siteService->getHomepageSections())->firstWhere('slug', 'footer');

        $openGraph = $this->eventService->openGraphPayload(
            'Teacher of Champions 2026 | IKA UNIMED',
            'Kompetisi online untuk guru se-Indonesia dari IKA UNIMED. Pendaftaran 12–29 Oktober 2026 untuk Hari Guru Nasional.',
            config('homepage.teacher_of_champions.og_image'),
        );

        return Inertia::render('TeacherOfChampions/Landing', [
            'settings' => $settings,
            'footerContent' => $footer?->content,
            'contacts' => config('homepage.reuni_contacts', []),
            'isOpen' => PendaftarTeacherOfChampions::isRegistrationOpen(),
            'closesAt' => $closesAt->toIso8601String(),
            'closesAtLabel' => $closesAt->translatedFormat('l, j F Y').' pukul '.$closesAt->format('H.i').' WIB',
            'periodLabel' => $kegiatanPayload['date_label'] ?? config('homepage.teacher_of_champions.period_label'),
            'jenjangOptions' => PendaftarTeacherOfChampions::JENJANG,
            'instagramUrl' => $this->instagramUrl($settings),
            'meta' => ['title' => $openGraph['title'], 'description' => $openGraph['description']],
        ])->withViewData([
            'page_title' => $openGraph['title'],
            'meta_description' => $openGraph['description'],
            'og_type' => 'website',
            'og_title' => $openGraph['title'],
            'og_description' => $openGraph['description'],
            'og_image' => url($openGraph['image']),
            'og_image_width' => $openGraph['image_width'],
            'og_image_height' => $openGraph['image_height'],
        ]);
    }

    /**
     * @param  iterable<string, mixed>  $settings
     */
    private function instagramUrl(iterable $settings): ?string
    {
        $url = collect($settings)->get('social')['social_instagram'] ?? null;

        return is_string($url) && preg_match('#^https?://#', $url) ? $url : null;
    }
}
