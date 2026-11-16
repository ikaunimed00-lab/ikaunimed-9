<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kegiatan>
 */
class KegiatanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);
        $startsAt = now()->addDays(fake()->numberBetween(1, 30))->startOfHour();

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(12),
            'description' => "08.00-09.00 | Registrasi peserta\n09.00-12.00 | Acara utama",
            'location' => 'Kampus UNIMED, Medan',
            'image' => null,
            'image_sm' => null,
            'route_map_image' => null,
            'hero_image' => null,
            'hero_image_mobile' => null,
            'theme' => 'hijau',
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHours(6),
            'cta_label' => 'Lihat detail',
            'cta_url' => '/news',
            'featured' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['featured' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->subDays(3),
            'ends_at' => now()->subDays(2),
        ]);
    }
}
