<?php

namespace App\Http\Controllers;

use App\Services\SiteManagementService;
use App\Services\UpcomingEventService;
use Inertia\Inertia;
use Inertia\Response;

class ReuniAkbarController extends Controller
{
    public const FUN_WALK_SLUG = 'fun-walk-fun-aerobic-jumpa-party';

    public const CATUR_SLUG = 'turnamen-catur-nasional-2026';

    public const TEACHER_OF_CHAMPIONS_SLUG = 'teacher-of-champions';

    public const BANNER_IMAGE = '/images/grand-reunion-banner-2026-koreksi.jpg';

    public function __construct(
        protected SiteManagementService $siteService,
        protected UpcomingEventService $eventService,
    ) {}

    public function show(): Response
    {
        $kegiatans = $this->eventService->findBySlugs([
            self::FUN_WALK_SLUG,
            self::CATUR_SLUG,
            self::TEACHER_OF_CHAMPIONS_SLUG,
        ]);

        $toArray = fn (string $slug): ?array => $kegiatans->has($slug)
            ? $this->eventService->toArray($kegiatans->get($slug))
            : null;

        $funWalk = $toArray(self::FUN_WALK_SLUG);

        if ($funWalk !== null && $funWalk['route_map_image'] === null) {
            $funWalk['route_map_image'] = '/images/fun-walk-rute-2026.png';
        }

        $footer = collect($this->siteService->getHomepageSections())->firstWhere('slug', 'footer');

        $title = 'Grand Reunion IKA UNIMED 2026';
        $description = 'Jadwal Fun Walk, Fun Aerobic & Jumpa Party dan Turnamen Catur Nasional, Sabtu 10 Oktober 2026 di UNIMED, serta pendaftaran Teacher of Champions.';

        return Inertia::render('ReuniAkbar/Show', [
            'settings' => $this->siteService->getSettings(),
            'footerContent' => $footer?->content,
            'funWalk' => $funWalk,
            'catur' => $toArray(self::CATUR_SLUG),
            'teacherOfChampions' => $toArray(self::TEACHER_OF_CHAMPIONS_SLUG),
            'contacts' => config('homepage.reuni_contacts', []),
            'bannerImage' => self::BANNER_IMAGE,
            'meta' => ['title' => $title, 'description' => $description],
        ])->withViewData([
            'meta_description' => $description,
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => url(self::BANNER_IMAGE),
        ]);
    }
}
