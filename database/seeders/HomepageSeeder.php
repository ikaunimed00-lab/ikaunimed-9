<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\SiteSetting;
use App\Models\HomepageSection;

class HomepageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Site Settings
        $settings = [
            // Identity
            [
                'key' => 'site_name',
                'value' => 'IKA UNIMED',
                'type' => 'text',
                'group' => 'identity',
                'label' => 'Nama Situs',
            ],
            [
                'key' => 'site_tagline',
                'value' => 'Connect, Collaborate, Contribute',
                'type' => 'text',
                'group' => 'identity',
                'label' => 'Tagline',
            ],
            [
                'key' => 'site_logo',
                'value' => 'logo_ikaunimed.png',
                'type' => 'image',
                'group' => 'identity',
                'label' => 'Logo Utama',
            ],
            [
                'key' => 'site_favicon',
                'value' => 'favicon_ikaunimed.png',
                'type' => 'image',
                'group' => 'identity',
                'label' => 'Favicon',
            ],

            // Contact
            [
                'key' => 'contact_address',
                'value' => 'Medan, Indonesia',
                'type' => 'textarea',
                'group' => 'contact',
                'label' => 'Alamat Sekretariat',
            ],
            [
                'key' => 'contact_phone',
                'value' => '+62 815 3238 7608',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Nomor Telepon/WA',
            ],
            [
                'key' => 'contact_email',
                'value' => 'office@ikaunimed.or.id',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Email Resmi',
            ],

            // Social Media
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/ikaunimed',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Facebook URL',
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/ikaunimed',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Instagram URL',
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/ikaunimed',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Twitter/X URL',
            ],
            [
                'key' => 'social_youtube',
                'value' => 'https://youtube.com/ikaunimed',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Youtube URL',
            ],

            // Footer
            [
                'key' => 'footer_description',
                'value' => 'Wadah resmi kolaborasi alumni Universitas Negeri Medan untuk membangun jejaring profesional dan kontribusi bagi almamater.',
                'type' => 'textarea',
                'group' => 'footer',
                'label' => 'Deskripsi Footer',
            ],
            [
                'key' => 'footer_copyright',
                'value' => '© 2026',
                'type' => 'text',
                'group' => 'footer',
                'label' => 'Teks Copyright',
            ],
            [
                'key' => 'footer_location',
                'value' => 'Sumatera Utara, Indonesia',
                'type' => 'text',
                'group' => 'footer',
                'label' => 'Lokasi Footer',
            ],

            // Floating CTA
            [
                'key' => 'floating_cta_bubble_text',
                'value' => 'Butuh bantuan IKA?',
                'type' => 'text',
                'group' => 'floating_cta',
                'label' => 'Teks Bubble Floating CTA',
            ],
            [
                'key' => 'floating_cta_bubble_subtext',
                'value' => 'Tim kami siap membantu',
                'type' => 'text',
                'group' => 'floating_cta',
                'label' => 'Subteks Bubble Floating CTA',
            ],

            // SEO
            [
                'key' => 'seo_title',
                'value' => 'IKA UNIMED - Ikatan Alumni Universitas Negeri Medan',
                'type' => 'text',
                'group' => 'seo',
                'label' => 'SEO Title',
            ],
            [
                'key' => 'seo_description',
                'value' => 'Portal resmi Ikatan Alumni Universitas Negeri Medan (IKA UNIMED). Connect, Collaborate, Contribute.',
                'type' => 'textarea',
                'group' => 'seo',
                'label' => 'SEO Description',
            ],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // 2. Homepage Sections
        
        // Hero Section
        HomepageSection::updateOrCreate(
            ['slug' => 'hero'],
            [
                'type' => 'hero',
                'title' => 'Hero Slider Utama',
                'order' => 1,
                'content' => [
                    'slides' => [
                        [
                            'title' => "Layanan Alumni",
                            'subtitle' => "SISTEM LAYANAN",
                            'highlight' => "LEGALISIR & DATA\nALUMNI DIGITAL",
                            'stats' => "95%",
                            'statsLabel' => "Proses Cepat",
                            'serviceName' => "Legalisir Online",
                            'primaryFeature' => "Verifikasi Ijazah",
                            'features' => ["Sistem Terintegrasi", "Legalitas Terjamin", "Update Data Real-time"],
                            'bgDesktop' => "hero_slide_administrasi.png",
                            'bgMobile' => "hero_slide_administrasi_mobile.png",
                            'colors' => [
                                'textPrimary' => 'text-first-dark-green',
                                'textSecondary' => 'text-ika-yellow',
                                'bgAccent' => 'bg-second-dark-green',
                                'bgFeature' => 'bg-first-dark-green',
                                'textFeature' => 'text-white',
                                'overlay' => 'bg-white opacity-40',
                            ],
                        ],
                        [
                            'title' => "Informasi Terkini",
                            'subtitle' => "KABAR & AGENDA",
                            'highlight' => "JEJAK KISAH\nALUMNI INSPIRATIF",
                            'stats' => "100+",
                            'statsLabel' => "Agenda Aktif",
                            'serviceName' => "Berita Organisasi",
                            'primaryFeature' => "Jadwal Acara",
                            'features' => ["Liputan Kegiatan", "Galeri Foto", "Notifikasi Event"],
                            'bgDesktop' => "hero_slide_agenda.png",
                            'bgMobile' => "hero_slide_agenda_mobile.png",
                            'colors' => [
                                'textPrimary' => 'text-first-dark-green',
                                'textSecondary' => 'text-second-dark-green',
                                'bgAccent' => 'bg-ika-yellow',
                                'bgFeature' => 'bg-first-dark-green',
                                'textFeature' => 'text-white',
                                'overlay' => 'bg-white opacity-50',
                            ],
                        ],
                        [
                            'title' => "Kontribusi Sosial",
                            'subtitle' => "DONASI & BEASISWA",
                            'highlight' => "MEMBANGUN MASA\nDEPAN GENERASI",
                            'stats' => "Ratusan",
                            'statsLabel' => "Penerima Manfaat",
                            'serviceName' => "Program Beasiswa",
                            'primaryFeature' => "Open Donasi",
                            'features' => ["Transparan", "Tepat Sasaran", "Wakaf Pendidikan"],
                            'bgDesktop' => "hero_slide_mentoring.png",
                            'bgMobile' => "hero_slide_mentoring_mobile.png",
                            'colors' => [
                                'textPrimary' => 'text-white',
                                'textSecondary' => 'text-ika-yellow',
                                'bgAccent' => 'bg-ika-yellow',
                                'bgFeature' => 'bg-first-dark-green',
                                'textFeature' => 'text-white',
                                'overlay' => 'bg-black opacity-40',
                            ],
                        ],
                        [
                            'title' => "Dukungan Karir",
                            'subtitle' => "KARIR & JEJARING",
                            'highlight' => "KONEKSI KUAT\nUNTUK ALUMNI",
                            'stats' => "50+",
                            'statsLabel' => "Lowongan Kerja",
                            'serviceName' => "Job Portal",
                            'primaryFeature' => "Mentoring",
                            'features' => ["Mitra Industri", "Informasi Karir", "Worskhop Rutin"],
                            'bgDesktop' => "hero_slide_karir.png",
                            'bgMobile' => "hero_slide_karir_mobile.png",
                            'colors' => [
                                'textPrimary' => 'text-white',
                                'textSecondary' => 'text-ika-yellow',
                                'bgAccent' => 'bg-ika-yellow',
                                'bgFeature' => 'bg-second-dark-green',
                                'textFeature' => 'text-white',
                                'overlay' => 'bg-black opacity-30',
                            ],
                        ],
                        [
                            'title' => "Peningkatan Skill",
                            'subtitle' => "MICRO LEARNING",
                            'highlight' => "TINGKATKAN KOMPETENSI\nSECARA FLEKSIBEL",
                            'stats' => "Ribuan",
                            'statsLabel' => "Materi Belajar",
                            'serviceName' => "E-Learning",
                            'primaryFeature' => "Sertifikasi Online",
                            'features' => ["Modul Praktis", "Akses 24 Jam", "Dari Dosen UNIMED"],
                            'bgDesktop' => "hero_slide_micro_learning.png",
                            'bgMobile' => "hero_slide_micro_learning_mobile.png",
                            'colors' => [
                                'textPrimary' => 'text-white',
                                'textSecondary' => 'text-ika-yellow',
                                'bgAccent' => 'bg-ika-yellow',
                                'bgFeature' => 'bg-first-dark-green',
                                'textFeature' => 'text-white',
                                'overlay' => 'bg-black opacity-50',
                            ],
                        ],
                    ]
                ]
            ]
        );

        // CTA Cards Section
        HomepageSection::updateOrCreate(
            ['slug' => 'cta-cards'],
            [
                'type' => 'cta_cards',
                'title' => 'Kartu Akses Cepat (CTA Cards)',
                'order' => 2,
                'content' => [
                    'cards' => [
                        ['image' => 'card_administrasi.png', 'alt' => 'Administrasi Online', 'href' => '/legalisir'],
                        ['image' => 'card_berita.png', 'alt' => 'Info Terbaru', 'href' => '/berita'],
                        ['image' => 'card_donasi.png', 'alt' => 'Program Donasi', 'href' => '/donasi'],
                        ['image' => 'card_karir.png', 'alt' => 'Pusat Karir', 'href' => '/karir'],
                        ['image' => 'card_skill-upgrading.png', 'alt' => 'Skill Upgrading', 'href' => '/skill-upgrading'],
                    ]
                ]
            ]
        );

        // Video Section
        HomepageSection::updateOrCreate(
            ['slug' => 'video-intro'],
            [
                'type' => 'video',
                'title' => 'Video Profil / Sambutan',
                'order' => 3,
                'content' => [
                    'title' => 'Merajut Silaturahmi, Membangun Sinergi Alumni',
                    'highlight' => 'Membangun Sinergi Alumni',
                    'description' => 'Wadah resmi kolaborasi dan koneksi bagi seluruh alumni Universitas Negeri Medan. Bersama kita berkontribusi bagi almamater, nusa, dan bangsa melalui jaringan profesional yang kuat, unggul, dan berkelanjutan.',
                    'features' => ['Terintegrasi', 'Kolaboratif', 'Inovatif'],
                    'video_url' => '/media/video',
                    'video_label' => 'Lihat Galeri Video',
                ]
            ]
        );

        // Features Section
        HomepageSection::updateOrCreate(
            ['slug' => 'features'],
            [
                'type' => 'features',
                'title' => 'Keunggulan & Layanan',
                'order' => 4,
                'content' => [
                    'subtitle' => 'Keunggulan Kami',
                    'title' => 'Kenapa IKA UNIMED?',
                    'description' => 'Fokus Kepada Kemajuan Universitas, Alumni dan Keluarga Alumni',
                    'items' => [
                        ['icon' => 'Users', 'title' => 'Database Alumni', 'description' => 'Terhubung kembali dengan rekan sejawat', 'color' => 'text-primary', 'bg' => 'bg-primary/10', 'border' => 'bg-primary', 'href' => '/database'],
                        ['icon' => 'Briefcase', 'title' => 'Info Karir', 'description' => 'Loker & peluang bisnis alumni', 'color' => 'text-oxygen-teal', 'bg' => 'bg-oxygen-teal/10', 'border' => 'bg-oxygen-teal', 'href' => '/karir'],
                        ['icon' => 'Newspaper', 'title' => 'Berita Kampus', 'description' => 'Update terkini agenda UNIMED', 'color' => 'text-[#FFD700]', 'bg' => 'bg-[#FFD700]/10', 'border' => 'bg-[#FFD700]', 'href' => '/news'],
                        ['icon' => 'GraduationCap', 'title' => 'Program Beasiswa', 'description' => 'Bantuan pendidikan mahasiswa', 'color' => 'text-primary', 'bg' => 'bg-primary/10', 'border' => 'bg-primary', 'href' => '/beasiswa'],
                        ['icon' => 'CreditCard', 'title' => 'Kartu Alumni', 'description' => 'Akses identitas digital khusus', 'color' => 'text-oxygen-teal', 'bg' => 'bg-oxygen-teal/10', 'border' => 'bg-oxygen-teal', 'href' => '/kartu-alumni'],
                        ['icon' => 'HeartHandshake', 'title' => 'Ruang Pengabdian', 'description' => 'Kontribusi nyata bagi almamater', 'color' => 'text-[#FFD700]', 'bg' => 'bg-[#FFD700]/10', 'border' => 'bg-[#FFD700]', 'href' => '/pengabdian'],
                    ]
                ]
            ]
        );

        // Package CTA Section
        HomepageSection::updateOrCreate(
            ['slug' => 'package-section'],
            [
                'type' => 'package',
                'title' => 'Section Promosi Layanan (Alternating)',
                'order' => 5,
                'content' => [
                    'items' => [
                        [
                            'title' => "Micro Learning & Skill Upgrading",
                            'description' => "Tingkatkan kompetensi dengan materi ringkas dari seluruh program studi UNIMED. Pelajari keahlian baru kapan dan dimana saja. Daftar sekarang dan mulailah bertumbuh.",
                            'bgImage' => "/images/cta_ikaunimed-01.png",
                            'tagline' => "Keterampilan Siap Pakai",
                            'href' => "/categories/skill",
                            'position' => "right",
                            'buttonColor' => "#10b981",
                        ],
                        [
                            'title' => "Donasi & Beasiswa Alumni",
                            'description' => "Program Donasi & Beasiswa dari Ikatan Alumni kini kembali dibuka. Jangan biarkan kendala biaya menghalangi prestasimu. Segera cek persyaratan dan daftar melalui Portal Mahasiswa Bakat Unimed.",
                            'bgImage' => "/images/cta_ikaunimed-002.png",
                            'tagline' => "Wujudkan Mimpimu Sekarang!",
                            'href' => "/categories/beasiswa",
                            'position' => "left",
                            'buttonColor' => "#10b981",
                        ],
                        [
                            'title' => "Karir & Jejaring Profesional",
                            'description' => "Temukan lowongan kerja eksklusif dari mitra perusahaan terpercaya. Kami menjembatani lulusan UNIMED dengan industri untuk membangun karir yang gemilang. Akses portal karir sekarang juga.",
                            'bgImage' => "/images/cta_ikaunimed-03.png",
                            'tagline' => "Karir Masa Depan",
                            'href' => "/categories/karier",
                            'position' => "right",
                            'buttonColor' => "#10b981",
                        ],
                        [
                            'title' => "Legalisir Online & Data Alumni",
                            'description' => "Update data diri Anda sekarang dan nikmati kemudahan akses legalisir dokumen secara online hanya dalam beberapa klik.",
                            'bgImage' => "/images/cta_ikaunimed-04.png",
                            'tagline' => "Daftar & Ajukan Sekarang!",
                            'href' => "/legalization/create",
                            'position' => "left",
                            'buttonColor' => "#10b981",
                        ]
                    ]
                ]
            ]
        );

        // Footer Section
        HomepageSection::updateOrCreate(
            ['slug' => 'footer'],
            [
                'type' => 'footer',
                'title' => 'Footer Links',
                'order' => 6,
                'content' => [
                    'footer_links' => [
                        'layanan' => [
                            ['label' => "Direktori Alumni", 'href' => "/alumni"],
                            ['label' => "Karir & Profesional", 'href' => "/karir"],
                            ['label' => "Beasiswa", 'href' => "/beasiswa"],
                            ['label' => "Skill Upgrading", 'href' => "/skill-upgrading"],
                            ['label' => "Kemitraan", 'href' => "/kemitraan"],
                            ['label' => "Kartu Alumni", 'href' => "/kartu-alumni"],
                            ['label' => "E-Voting", 'href' => "/voting"],
                            ['label' => "Legalisir Ijazah", 'href' => "/legalisir"],
                        ],
                        'informasi' => [
                            ['label' => "Kabar Alumni", 'href' => "/kabar-alumni"],
                            ['label' => "Berita", 'href' => "/berita"],
                            ['label' => "Agenda", 'href' => "/agenda"],
                            ['label' => "Ruang Pengabdian", 'href' => "/pengabdian"],
                            ['label' => "Galeri Foto", 'href' => "/media/foto"],
                            ['label' => "Galeri Video", 'href' => "/media/video"],
                            ['label' => "FAQ", 'href' => "/faq"],
                        ],
                        'organisasi' => [
                            ['label' => "Tentang IKA UNIMED", 'href' => "/tentang-kami"],
                            ['label' => "Struktur Organisasi", 'href' => "/struktur-organisasi"],
                            ['label' => "Direktori Organisasi", 'href' => "/organisasi"],
                            ['label' => "Hubungi Kami & Sekretariat", 'href' => "/hubungi-kami"],
                            ['label' => "Donasi", 'href' => "/donasi"],
                            ['label' => "Syarat & Ketentuan", 'href' => "/syarat-ketentuan"],
                            ['label' => "Kebijakan Privasi", 'href' => "/kebijakan-privasi"],
                        ],
                    ]
                ]
            ]
        );
    }
}
