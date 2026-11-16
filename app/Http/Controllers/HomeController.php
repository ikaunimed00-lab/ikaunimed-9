<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\News;
use App\Services\SiteManagementService;
use App\Services\UpcomingEventService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        protected SiteManagementService $siteService,
        protected UpcomingEventService $eventService,
    ) {}

    public function index(): Response
    {
        if (config('homepage.use_portal')) {
            return $this->renderPortal(isPreview: false);
        }

        return $this->legacy();
    }

    public function legacy(): Response
    {
        return Inertia::render('Index', [
            'settings' => $this->siteService->getSettings(),
            'sections' => $this->siteService->getHomepageSections(),
        ]);
    }

    public function preview(): Response
    {
        return $this->renderPortal(isPreview: true);
    }

    private function renderPortal(bool $isPreview): Response
    {
        $events = $this->eventService->active()
            ->map(fn (Kegiatan $kegiatan): array => $this->eventService->toArray($kegiatan))
            ->all();

        $latestNews = News::query()
            ->published()
            ->with('author:id,name')
            ->latest()
            ->take(4)
            ->get()
            ->map(fn (News $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'excerpt' => $item->excerpt,
                'slug' => $item->slug,
                'image' => News::buildImageUrl($item->image),
                'view_count' => $item->view_count,
                'author' => ['name' => $item->author?->name],
                'published_at' => $item->published_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        $getSection = function (string $slug) {
            $sections = $this->siteService->getHomepageSections();

            return collect($sections)->firstWhere('slug', $slug)?->content;
        };

        $openGraph = $this->eventService->openGraph();

        return Inertia::render('Home/Portal', [
            'isPreview' => $isPreview,
            'settings' => $this->siteService->getSettings(),
            'footerContent' => $getSection('footer'),
            'hero' => $this->eventService->hero(),
            'events' => $events,
            'latestNews' => $latestNews,
            'services' => config('homepage.services', []),
            'pimpinan' => $this->eventService->pimpinan(),
            'contacts' => config('homepage.reuni_contacts', []),
        ])->withViewData([
            'meta_description' => $openGraph['description'],
            'og_type' => 'website',
            'og_title' => $openGraph['title'],
            'og_description' => $openGraph['description'],
            'og_image' => url($openGraph['image']),
            'og_image_width' => $openGraph['image_width'],
            'og_image_height' => $openGraph['image_height'],
        ]);
    }
}
