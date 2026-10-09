<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PendaftarTeacherOfChampions>
 */
class PendaftarTeacherOfChampionsFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nomor_pendaftaran' => sprintf('TOC-2026-%04d', fake()->unique()->numberBetween(1000, 9999)),
            'nama' => fake()->name(),
            'asal_instansi' => 'SMA Negeri '.fake()->numberBetween(1, 20).' Medan',
            'whatsapp' => '628'.fake()->unique()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'provinsi' => 'Sumatera Utara',
            'kabupaten_kota' => 'Kota Medan',
            'lama_mengabdi_tahun' => fake()->numberBetween(1, 30),
            'instagram' => '@'.fake()->unique()->userName(),
            'bidang_studi' => 'Matematika',
            'asal_universitas' => 'Universitas Negeri Medan',
            'jenjang' => 'SMA',
            'status_kepegawaian' => 'ASN',
            'persetujuan_data' => true,
            'status' => 'baru',
            'catatan' => null,
        ];
    }
}
