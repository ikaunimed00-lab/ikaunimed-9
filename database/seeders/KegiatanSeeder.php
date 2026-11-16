<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class KegiatanSeeder extends Seeder
{
    /**
     * Agenda resmi Oktober 2026. Idempotent: aman dijalankan ulang (updateOrCreate by slug).
     */
    public function run(): void
    {
        $kegiatans = [
            [
                'slug' => 'fun-walk-fun-aerobic-jumpa-party',
                'title' => 'Fun Walk, Fun Aerobic & Jumpa Party',
                'summary' => 'Rangkaian Silaturahim Grand Reunion, Semarak Temu Alumni Memperebutkan Piala Rektor UNIMED.',
                'location' => 'Lapangan Serbaguna UNIMED, Medan',
                'image' => '/images/event-fun-walk-card.jpg',
                'image_sm' => '/images/event-fun-walk-card-sm.jpg',
                'route_map_image' => '/images/fun-walk-rute-2026.png',
                'hero_image' => null,
                'hero_image_mobile' => null,
                'theme' => Kegiatan::THEME_HIJAU,
                'starts_at' => $this->wib('2026-10-10 06:00'),
                'ends_at' => $this->wib('2026-10-10 23:59'),
                'cta_label' => 'Lihat jadwal & denah rute',
                'cta_url' => '/reuni-akbar-2026',
                'featured' => true,
                'sort_order' => 1,
                'description' => implode("\n", [
                    '06.00-07.00 | Registrasi & pembagian kupon door prize (di lokasi)',
                    '07.00-07.15 | Persiapan pelepasan peserta oleh Rektor, Ketua Senat, unsur pimpinan UNIMED dan Ketua Umum PP IKA UNIMED',
                    '07.15-08.00 | Fun Walk: rute ±1,5 km mengelilingi lingkungan UNIMED, diiringi Marching Band MAN 1 Medan',
                    '08.00-08.05 | Sambutan Ketua PP IKA UNIMED',
                    '08.05-08.10 | Sambutan & pembukaan oleh Rektor',
                    '08.10-08.40 | Fun Aerobic (senam bersama)',
                    '08.40-selesai | Jumpa Party, door prize & penampilan band',
                ]),
            ],
            [
                'slug' => 'turnamen-catur-nasional-2026',
                'title' => 'Turnamen Catur Nasional 2026',
                'summary' => 'IKA UNIMED Turnamen Catur Nasional 2026, Memperebutkan Piala Rektor Universitas Negeri Medan.',
                'location' => 'VIP Room Serba Guna UNIMED, Medan',
                'image' => '/images/event-catur-card.jpg',
                'image_sm' => '/images/event-catur-card-sm.jpg',
                'route_map_image' => null,
                'hero_image' => '/images/event-catur-wide.jpg',
                'hero_image_mobile' => null,
                'theme' => Kegiatan::THEME_HIJAU,
                'starts_at' => $this->wib('2026-10-10 08:00'),
                'ends_at' => $this->wib('2026-10-10 23:59'),
                'cta_label' => 'Lihat jadwal',
                'cta_url' => '/reuni-akbar-2026',
                'featured' => false,
                'sort_order' => 2,
                'description' => implode("\n", [
                    '08.00-09.00 | Registrasi peserta (di lokasi)',
                    '09.00-09.30 | Persiapan',
                    '09.30-09.35 | Sambutan Ketua PP IKA UNIMED',
                    '09.35-09.40 | Sambutan & pembukaan oleh Rektor',
                    '09.40-09.50 | Foto bersama',
                    '09.50-17.00 | Pelaksanaan kompetisi',
                    '17.00-selesai | Penyerahan hadiah',
                ]),
            ],
            [
                'slug' => 'teacher-of-champions',
                'title' => 'Teacher of Champions',
                'summary' => 'Kompetisi online untuk guru se-Indonesia, program kerja IKA UNIMED dalam rangka Hari Guru Nasional 25 November. Menguji kompetensi pedagogik, profesional, studi kasus (SJT), penalaran dan pemecahan masalah.',
                'location' => 'Online (CBT, Zoom/Kahoot, Instagram resmi IKA UNIMED)',
                'image' => '/images/event-teacher-card-hijau.jpg',
                'image_sm' => '/images/event-teacher-card-hijau-sm.jpg',
                'route_map_image' => '/images/teacher-of-champions-timeline.jpg',
                'hero_image' => '/images/teacher-of-champions-poster.jpg',
                'hero_image_mobile' => null,
                'theme' => Kegiatan::THEME_NAVY_EMAS,
                'starts_at' => $this->wib('2026-10-12 00:00'),
                'ends_at' => $this->wib('2026-11-22 23:59'),
                'cta_label' => 'Daftar Teacher of Champions',
                'cta_url' => '/teacher-of-champions/daftar',
                'featured' => false,
                'sort_order' => 3,
                'description' => implode("\n", [
                    '12-29 Okt | Pendaftaran Peserta ToC 2026',
                    '1 Nov | Pengumuman peserta terpilih mengikuti ToC 2026',
                    '9 Nov | Pelaksanaan Tahap Penyisihan (The Qualifiers): 50 soal, 90 menit; 100 peserta terbaik akan lolos ke tahap final',
                    '12 Nov | Pengumuman 100 besar lolos ke Tahap Final (The Championship)',
                    '13 Nov | Technical Meeting Babak Final',
                    '14-22 Nov | Voting melalui Instagram Resmi IKA UNIMED (Penentuan Favorite Teacher Award)',
                    '22 Nov | Pelaksanaan dan Pengumuman Tahap Final (The Championship) Live Quiz',
                ]),
            ],
        ];

        foreach ($kegiatans as $kegiatan) {
            Kegiatan::query()->updateOrCreate(
                ['slug' => $kegiatan['slug']],
                [...$kegiatan, 'is_active' => true],
            );
        }
    }

    private function wib(string $dateTime): Carbon
    {
        return Carbon::parse($dateTime, Kegiatan::TIMEZONE)->utc();
    }
}
