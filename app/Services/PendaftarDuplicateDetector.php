<?php

namespace App\Services;

use App\Models\PendaftarTeacherOfChampions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Flags registrants that may be the same person. Only a marker for the committee; it never blocks.
 */
class PendaftarDuplicateDetector
{
    public const CACHE_SECONDS = 60;

    public const NAME_SIMILARITY_PERCENT = 90;

    public const SCHOOL_SIMILARITY_PERCENT = 85;

    /**
     * @return array<int, list<string>> registrant id => reasons
     */
    public function duplicates(): array
    {
        $fingerprint = PendaftarTeacherOfChampions::query()
            ->toBase()
            ->selectRaw('count(*) as total, max(updated_at) as last_update, max(id) as last_id')
            ->first();

        $key = 'toc.pendaftar.duplicates.'.md5(json_encode($fingerprint));

        return Cache::remember($key, self::CACHE_SECONDS, fn (): array => $this->detect(
            PendaftarTeacherOfChampions::query()->get(['id', 'nomor_pendaftaran', 'nama', 'whatsapp', 'email', 'asal_instansi']),
        ));
    }

    /**
     * @param  Collection<int, PendaftarTeacherOfChampions>  $pendaftar
     * @return array<int, list<string>>
     */
    public function detect(Collection $pendaftar): array
    {
        $reasons = [];

        $flag = function (Collection $group, string $label) use (&$reasons): void {
            foreach ($group as $item) {
                $others = $group->where('id', '!=', $item->id)->pluck('nomor_pendaftaran')->implode(', ');
                $reasons[$item->id][] = "{$label} sama dengan {$others}";
            }
        };

        $pendaftar
            ->groupBy(fn (PendaftarTeacherOfChampions $item): string => (string) static::normalizePhone($item->whatsapp))
            ->filter(fn (Collection $group, string $phone): bool => $phone !== '' && $group->count() > 1)
            ->each(fn (Collection $group) => $flag($group, 'WhatsApp'));

        $pendaftar
            ->groupBy(fn (PendaftarTeacherOfChampions $item): string => static::normalizeEmail($item->email))
            ->filter(fn (Collection $group, string $email): bool => $email !== '' && $group->count() > 1)
            ->each(fn (Collection $group) => $flag($group, 'Email'));

        $this->similarNameAndSchoolPairs($pendaftar)->each(function (array $pair) use (&$reasons): void {
            [$first, $second] = $pair;
            $reasons[$first->id][] = "Nama & sekolah mirip dengan {$second->nomor_pendaftaran}";
            $reasons[$second->id][] = "Nama & sekolah mirip dengan {$first->nomor_pendaftaran}";
        });

        return collect($reasons)->map(fn (array $list): array => array_values(array_unique($list)))->all();
    }

    public static function normalizePhone(?string $value): ?string
    {
        $digits = PendaftarTeacherOfChampions::normalizeWhatsapp($value);

        return $digits === null ? null : (string) preg_replace('/^(62)+/', '62', $digits);
    }

    public static function normalizeEmail(?string $value): string
    {
        return Str::of((string) $value)->lower()->replace(' ', '')->toString();
    }

    public static function normalizeName(?string $value): string
    {
        return Str::of((string) $value)
            ->before(',')
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    public static function normalizeSchool(?string $value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->ascii()
            ->replaceMatches('/\bnegeri\b/', 'n')
            ->replaceMatches('/\bswasta\b/', 's')
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    /**
     * @param  Collection<int, PendaftarTeacherOfChampions>  $pendaftar
     * @return Collection<int, array{0: PendaftarTeacherOfChampions, 1: PendaftarTeacherOfChampions}>
     */
    private function similarNameAndSchoolPairs(Collection $pendaftar): Collection
    {
        $pairs = collect();
        $seen = [];

        $compareWithin = function (Collection $bucket, callable $isSimilar) use (&$pairs, &$seen): void {
            $items = $bucket->values();

            for ($i = 0; $i < $items->count(); $i++) {
                for ($j = $i + 1; $j < $items->count(); $j++) {
                    [$first, $second] = [$items[$i], $items[$j]];
                    $pairKey = min($first->id, $second->id).'-'.max($first->id, $second->id);

                    if (! isset($seen[$pairKey]) && $isSimilar($first, $second)) {
                        $seen[$pairKey] = true;
                        $pairs->push([$first, $second]);
                    }
                }
            }
        };

        $pendaftar
            ->groupBy(fn (PendaftarTeacherOfChampions $item): string => static::normalizeSchool($item->asal_instansi))
            ->filter(fn (Collection $bucket, string $school): bool => $school !== '' && $bucket->count() > 1)
            ->each(fn (Collection $bucket) => $compareWithin($bucket, fn ($first, $second): bool => static::isSimilar(
                static::normalizeName($first->nama),
                static::normalizeName($second->nama),
                self::NAME_SIMILARITY_PERCENT,
            )));

        $pendaftar
            ->groupBy(fn (PendaftarTeacherOfChampions $item): string => static::normalizeName($item->nama))
            ->filter(fn (Collection $bucket, string $name): bool => $name !== '' && $bucket->count() > 1)
            ->each(fn (Collection $bucket) => $compareWithin($bucket, fn ($first, $second): bool => static::isSimilar(
                static::normalizeSchool($first->asal_instansi),
                static::normalizeSchool($second->asal_instansi),
                self::SCHOOL_SIMILARITY_PERCENT,
            )));

        return $pairs;
    }

    private static function isSimilar(string $first, string $second, int $minimumPercent): bool
    {
        if ($first === '' || $second === '') {
            return false;
        }

        if ($first === $second) {
            return true;
        }

        similar_text($first, $second, $percent);

        return $percent >= $minimumPercent;
    }
}
