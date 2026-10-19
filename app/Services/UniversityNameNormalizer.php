<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Display-only canonical university names from universitas.json.
 * Does not change stored registrant data or alumni_unimed detection.
 */
class UniversityNameNormalizer
{
    /**
     * @var array<string, array{display: string, name: string, aliases: list<string>}>|null
     */
    private static ?array $catalogByKey = null;

    /**
     * @var list<array{display: string, name: string, aliases: list<string>}>|null
     */
    private static ?array $entries = null;

    public function canonicalize(?string $raw): string
    {
        if ($raw === null || trim($raw) === '') {
            return TocDashboardStats::EMPTY_LABEL;
        }

        $entry = $this->resolve($raw);

        if ($entry !== null) {
            return $entry['display'];
        }

        return TocDashboardStats::displayLabel($raw);
    }

    /**
     * Stable grouping key for rekap (case/space-insensitive, catalog-aware).
     */
    public function groupKey(?string $raw): string
    {
        return Str::of($this->canonicalize($raw))->lower()->squish()->toString();
    }

    /**
     * Raw name variants that should match a search term in admin filters.
     *
     * @return list<string>
     */
    public function searchVariants(string $search): array
    {
        $search = trim($search);

        if ($search === '') {
            return [];
        }

        $entry = $this->resolve($search);
        $variants = [$search];

        if ($entry !== null) {
            $variants[] = $entry['name'];
            $variants[] = $entry['display'];
            foreach ($entry['aliases'] as $alias) {
                $variants[] = $alias;
            }
        }

        return collect($variants)
            ->map(fn (string $value): string => trim($value))
            ->filter()
            ->unique(fn (string $value): string => Str::lower($value))
            ->values()
            ->all();
    }

    /**
     * @return array{display: string, name: string, aliases: list<string>}|null
     */
    private function resolve(string $raw): ?array
    {
        $this->boot();

        foreach ($this->candidateKeys($raw) as $key) {
            if (isset(self::$catalogByKey[$key])) {
                return self::$catalogByKey[$key];
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function candidateKeys(string $raw): array
    {
        $keys = [];
        $keys[] = $this->fingerprint($raw);

        $withoutParens = trim(preg_replace('/\([^)]*\)/u', ' ', $raw) ?? $raw);
        $keys[] = $this->fingerprint($withoutParens);

        $expanded = preg_replace('/\buniv\.?\b/iu', 'universitas', $withoutParens) ?? $withoutParens;
        $keys[] = $this->fingerprint($expanded);

        // Bare acronym leftover from parentheses, e.g. "UNIMED" from "... (UNIMED)"
        if (preg_match_all('/\(([^)]+)\)/u', $raw, $matches) > 0) {
            foreach ($matches[1] as $inside) {
                $keys[] = $this->fingerprint($inside);
            }
        }

        return collect($keys)
            ->filter(fn (string $key): bool => $key !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function fingerprint(string $value): string
    {
        $value = Str::of($value)
            ->lower()
            ->replaceMatches('/\buniv\.?\b/u', 'universitas')
            ->replaceMatches('/[^\p{L}\p{N}\s]+/u', ' ')
            ->squish()
            ->toString();

        return $value;
    }

    private function boot(): void
    {
        if (self::$catalogByKey !== null) {
            return;
        }

        self::$catalogByKey = [];
        self::$entries = [];

        $path = resource_path('data/universitas.json');
        $raw = File::exists($path) ? json_decode((string) File::get($path), true) : [];

        if (! is_array($raw)) {
            return;
        }

        foreach ($raw as $item) {
            if (! is_array($item) || ! is_string($item['name'] ?? null) || $item['name'] === '') {
                continue;
            }

            $name = $item['name'];
            $aliases = collect($item['aliases'] ?? [])
                ->filter(fn (mixed $alias): bool => is_string($alias) && $alias !== '')
                ->values()
                ->all();

            $display = $this->displayName($name, $aliases);
            $entry = [
                'display' => $display,
                'name' => $name,
                'aliases' => $aliases,
            ];
            self::$entries[] = $entry;

            $this->index($this->fingerprint($name), $entry);
            $this->index($this->fingerprint($display), $entry);

            foreach ($aliases as $alias) {
                $this->index($this->fingerprint($alias), $entry);
            }
        }
    }

    /**
     * @param  list<string>  $aliases
     */
    private function displayName(string $name, array $aliases): string
    {
        $acronym = collect($aliases)->first(
            fn (string $alias): bool => (bool) preg_match('/^[A-Za-z]{2,12}$/', $alias)
        );

        if ($acronym === null) {
            return $name;
        }

        return $name.' ('.Str::upper($acronym).')';
    }

    /**
     * @param  array{display: string, name: string, aliases: list<string>}  $entry
     */
    private function index(string $key, array $entry): void
    {
        if ($key === '' || isset(self::$catalogByKey[$key])) {
            return;
        }

        self::$catalogByKey[$key] = $entry;
    }

    /**
     * @internal for tests
     */
    public static function flush(): void
    {
        self::$catalogByKey = null;
        self::$entries = null;
    }
}
