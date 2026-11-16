<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class WilayahService
{
    /**
     * Static province -> regency dataset stored in resources/data/wilayah.json.
     *
     * @return list<array{kode: string, nama: string, kabupaten_kota: list<string>}>
     */
    public function all(): array
    {
        return Cache::rememberForever('wilayah.indonesia', function (): array {
            $path = resource_path('data/wilayah.json');

            return json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        });
    }

    /**
     * @return list<string>
     */
    public function provinsi(): array
    {
        return array_column($this->all(), 'nama');
    }

    /**
     * @return list<string>
     */
    public function kabupatenKota(?string $provinsi): array
    {
        foreach ($this->all() as $item) {
            if ($item['nama'] === $provinsi) {
                return $item['kabupaten_kota'];
            }
        }

        return [];
    }
}
