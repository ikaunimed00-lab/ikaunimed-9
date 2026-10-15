<?php

namespace App\Models;

use App\Services\UpcomingEventService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    /** @use HasFactory<\Database\Factories\KegiatanFactory> */
    use HasFactory;

    public const TIMEZONE = 'Asia/Jakarta';

    public const TEACHER_OF_CHAMPIONS_SLUG = 'teacher-of-champions';

    public const THEME_HIJAU = 'hijau';

    public const THEME_NAVY_EMAS = 'navy-emas';

    /**
     * @var array<string, string>
     */
    public const THEMES = [
        self::THEME_HIJAU => 'Hijau (IKA UNIMED)',
        self::THEME_NAVY_EMAS => 'Sorotan (hijau tua & kuning)',
    ];

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'location',
        'image',
        'image_sm',
        'route_map_image',
        'hero_image',
        'hero_image_mobile',
        'theme',
        'starts_at',
        'ends_at',
        'cta_label',
        'cta_url',
        'featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => UpcomingEventService::clearCache());
        static::deleted(fn () => UpcomingEventService::clearCache());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isTeacherOfChampions(): bool
    {
        return $this->getOriginal('slug', $this->slug) === self::TEACHER_OF_CHAMPIONS_SLUG;
    }

    /**
     * @param  Builder<Kegiatan>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public static function buildImageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http') || str_starts_with($path, '/')) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }

    /**
     * Rundown lines are written as "06.00-07.00 | Registrasi peserta", one per line.
     *
     * @return list<array{time: string|null, label: string}>
     */
    public function scheduleItems(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->description))
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->map(function (string $line): array {
                if (! str_contains($line, '|')) {
                    return ['time' => null, 'label' => $line];
                }

                [$time, $label] = array_map('trim', explode('|', $line, 2));

                return ['time' => $time !== '' ? $time : null, 'label' => $label];
            })
            ->values()
            ->all();
    }
}
