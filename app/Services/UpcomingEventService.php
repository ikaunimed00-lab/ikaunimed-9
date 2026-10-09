<?php

namespace App\Services;

use App\Models\Kegiatan;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class UpcomingEventService
{
    public const CACHE_KEY = 'kegiatan.active_rows';

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Active kegiatan that have not ended yet, ordered by start time.
     *
     * @return Collection<int, Kegiatan>
     */
    public function active(?CarbonInterface $at = null): Collection
    {
        $at ??= now();

        return $this->activeRows()
            ->filter(fn (Kegiatan $kegiatan): bool => $kegiatan->ends_at->greaterThanOrEqualTo($at))
            ->values();
    }

    public function featured(?CarbonInterface $at = null): ?Kegiatan
    {
        $active = $this->active($at);

        return $active->firstWhere('featured', true) ?? $active->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function hero(?CarbonInterface $at = null): array
    {
        $featured = $this->featured($at);

        if ($featured !== null) {
            return $this->toArray($featured, $at);
        }

        $fallback = config('homepage.hero_fallback');

        return [
            'id' => 0,
            'slug' => 'hero-fallback',
            'title' => $fallback['title'],
            'summary' => $fallback['summary'],
            'location' => null,
            'image' => $fallback['image'] ?? null,
            'image_sm' => null,
            'route_map_image' => null,
            'hero_image' => null,
            'hero_image_mobile' => null,
            'theme' => Kegiatan::THEME_HIJAU,
            'cta_label' => $fallback['cta_label'],
            'cta_url' => $fallback['cta_url'],
            'secondary_cta_label' => $fallback['secondary_cta_label'] ?? null,
            'secondary_cta_url' => $fallback['secondary_cta_url'] ?? null,
            'featured' => false,
            'is_fallback' => true,
            'starts_at' => null,
            'ends_at' => null,
            'date_label' => null,
            'time_label' => null,
            'time_range_label' => null,
            'group_key' => 'fallback',
            'is_multi_day' => false,
            'has_started' => true,
            'schedule' => [],
            'spotlight' => null,
        ];
    }

    /**
     * @param  list<string>  $slugs
     * @return Collection<string, Kegiatan>
     */
    public function findBySlugs(array $slugs): Collection
    {
        return Kegiatan::query()
            ->active()
            ->whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug');
    }

    /**
     * @param  list<string>|null  $keys
     * @return list<array{key: string, name: string, position: string, image: string, image_sm: string|null, image_zoom: float}>
     */
    public function pimpinan(?array $keys = null): array
    {
        $all = config('homepage.pimpinan', []);
        $keys ??= array_keys($all);

        return collect($keys)
            ->filter(fn (string $key): bool => isset($all[$key]))
            ->map(fn (string $key): array => [
                'key' => $key,
                'name' => $all[$key]['name'],
                'position' => $all[$key]['position'],
                'image' => $all[$key]['image'],
                'image_sm' => $all[$key]['image_sm'] ?? null,
                'image_zoom' => (float) ($all[$key]['image_zoom'] ?? 1),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Kegiatan $kegiatan, ?CarbonInterface $at = null): array
    {
        $at ??= now();
        $startsAt = $kegiatan->starts_at->copy()->setTimezone(Kegiatan::TIMEZONE)->locale('id');
        $endsAt = $kegiatan->ends_at->copy()->setTimezone(Kegiatan::TIMEZONE)->locale('id');
        $isMultiDay = ! $startsAt->isSameDay($endsAt);

        return [
            'id' => $kegiatan->id,
            'slug' => $kegiatan->slug,
            'title' => $kegiatan->title,
            'summary' => $kegiatan->summary,
            'location' => $kegiatan->location,
            'image' => Kegiatan::buildImageUrl($kegiatan->image),
            'image_sm' => Kegiatan::buildImageUrl($kegiatan->image_sm),
            'route_map_image' => Kegiatan::buildImageUrl($kegiatan->route_map_image),
            'hero_image' => Kegiatan::buildImageUrl($kegiatan->hero_image),
            'hero_image_mobile' => Kegiatan::buildImageUrl($kegiatan->hero_image_mobile),
            'theme' => $kegiatan->theme ?: Kegiatan::THEME_HIJAU,
            'cta_label' => $kegiatan->cta_label ?: 'Lihat detail',
            'cta_url' => $kegiatan->cta_url ?: '/news',
            'secondary_cta_label' => null,
            'secondary_cta_url' => null,
            'featured' => $kegiatan->featured,
            'is_fallback' => false,
            'starts_at' => $startsAt->toIso8601String(),
            'ends_at' => $endsAt->toIso8601String(),
            'date_label' => $isMultiDay ? $this->rangeLabel($startsAt, $endsAt) : $startsAt->translatedFormat('l, j F Y'),
            'time_label' => $isMultiDay ? null : $startsAt->format('H.i').' WIB',
            'time_range_label' => $isMultiDay ? null : $this->timeRangeLabel($startsAt, $endsAt),
            'group_key' => $isMultiDay ? 'range-'.$kegiatan->slug : $startsAt->toDateString(),
            'is_multi_day' => $isMultiDay,
            'has_started' => $kegiatan->starts_at->lessThanOrEqualTo($at),
            'schedule' => $kegiatan->scheduleItems(),
            'spotlight' => $this->spotlight($kegiatan->slug),
        ];
    }

    /**
     * @return array{eyebrow: string|null, headline: string|null, pimpinan: list<array<string, string|null>>}|null
     */
    private function spotlight(string $slug): ?array
    {
        $spotlight = config("homepage.hero_spotlights.{$slug}");

        if (! is_array($spotlight)) {
            return null;
        }

        return [
            'eyebrow' => $spotlight['eyebrow'] ?? null,
            'headline' => $spotlight['headline'] ?? null,
            'pimpinan' => $this->pimpinan($spotlight['pimpinan'] ?? []),
        ];
    }

    /**
     * @return Collection<int, Kegiatan>
     */
    private function activeRows(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => Kegiatan::query()
            ->active()
            ->orderBy('starts_at')
            ->orderBy('sort_order')
            ->get());
    }

    private function rangeLabel(CarbonInterface $startsAt, CarbonInterface $endsAt): string
    {
        $startFormat = $startsAt->year === $endsAt->year ? 'j F' : 'j F Y';

        return $startsAt->translatedFormat($startFormat).' – '.$endsAt->translatedFormat('j F Y');
    }

    private function timeRangeLabel(CarbonInterface $startsAt, CarbonInterface $endsAt): string
    {
        $end = $endsAt->format('H:i') === '23:59' ? 'selesai' : $endsAt->format('H.i').' WIB';

        return $startsAt->format('H.i').' WIB – '.$end;
    }
}
