<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePendaftarTeacherOfChampionsRequest;
use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use App\Services\SiteManagementService;
use App\Services\UpcomingEventService;
use App\Services\WilayahService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeacherOfChampionsRegistrationController extends Controller
{
    public function __construct(
        protected SiteManagementService $siteService,
        protected UpcomingEventService $eventService,
        protected WilayahService $wilayahService,
    ) {}

    public function create(): Response
    {
        $kegiatan = $this->eventService->findBySlugs([ReuniAkbarController::TEACHER_OF_CHAMPIONS_SLUG])->first();
        $closesAt = PendaftarTeacherOfChampions::registrationClosesAt()->setTimezone(Kegiatan::TIMEZONE)->locale('id');

        return $this->render('TeacherOfChampions/Daftar', [
            'kegiatan' => $kegiatan ? $this->eventService->toArray($kegiatan) : null,
            'isOpen' => PendaftarTeacherOfChampions::isRegistrationOpen(),
            'closesAtLabel' => $closesAt->translatedFormat('l, j F Y').' pukul '.$closesAt->format('H.i').' WIB',
            'wilayah' => $this->wilayahService->all(),
            'jenjangOptions' => PendaftarTeacherOfChampions::jenjangOptions(),
            'statusKepegawaianOptions' => PendaftarTeacherOfChampions::STATUS_KEPEGAWAIAN,
            'universitasOptions' => $this->universitasOptions(),
        ]);
    }

    public function store(StorePendaftarTeacherOfChampionsRequest $request): RedirectResponse
    {
        $pendaftar = PendaftarTeacherOfChampions::register($request->safe()->except(['website']));

        return redirect()
            ->route('teacher-of-champions.success')
            ->with('nomor_pendaftaran', $pendaftar->nomor_pendaftaran);
    }

    public function success(): Response|RedirectResponse
    {
        $nomor = session('nomor_pendaftaran');

        if (! is_string($nomor)) {
            return redirect()->route('teacher-of-champions.create');
        }

        return $this->render('TeacherOfChampions/Sukses', [
            'nomorPendaftaran' => $nomor,
        ]);
    }

    /**
     * Suggestions for the free-text "asal universitas" field.
     *
     * @return list<string>
     */
    private function universitasOptions(): array
    {
        $path = resource_path('data/universitas.json');

        return is_file($path) ? json_decode((string) file_get_contents($path), true) ?? [] : [];
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function render(string $component, array $props): Response
    {
        $footer = collect($this->siteService->getHomepageSections())->firstWhere('slug', 'footer');
        $title = 'Pendaftaran Teacher of Champions - IKA UNIMED';
        $description = 'Daftar kompetisi online Teacher of Champions untuk guru se-Indonesia, program IKA UNIMED dalam rangka Hari Guru Nasional 2026.';

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
        ]);
    }
}
