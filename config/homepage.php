<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Portal homepage
    |--------------------------------------------------------------------------
    |
    | When true, "/" renders the new organization portal. Keep false until the
    | /preview/home draft is approved, then set HOMEPAGE_USE_PORTAL=true.
    | Kegiatan (agenda) data is managed from the admin menu CMS > Kegiatan.
    |
    */

    'use_portal' => (bool) env('HOMEPAGE_USE_PORTAL', false),

    'preview_token' => env('HOMEPAGE_PREVIEW_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Teacher of Champions registration
    |--------------------------------------------------------------------------
    |
    | Closing time is written in WIB (Asia/Jakarta). period_label is shown on
    | /toc when the "teacher-of-champions" kegiatan is not active.
    | guidebook_url (buku panduan) is linked wherever the competition appears.
    | instagram_url/instagram_handle is the account used for the Favorite
    | Teacher Award voting.
    |
    */

    'teacher_of_champions' => [
        'registration_closes_at' => '2026-10-29 23:59',
        'og_image' => '/images/og-teacher-of-champions.jpg',
        'period_label' => '12 Oktober – 22 November 2026',
        'guidebook_url' => 'https://drive.google.com/drive/folders/1kV0jONNVApXlSDTy8MIPAO_Zs4Wjm4Wt',
        'instagram_url' => 'https://www.instagram.com/penguruspusatikaunimed/',
        'instagram_handle' => '@penguruspusatikaunimed',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pimpinan
    |--------------------------------------------------------------------------
    |
    | Shown on the portal hero and the "Pimpinan IKA UNIMED" section.
    | image_sm is optional; it is used on small screens when available.
    | image_zoom (default 1) enlarges a photo from the top center so heads in
    | half-body tiles match the size of the closer portrait photos.
    |
    */

    'pimpinan' => [
        'rektor' => [
            'name' => 'Prof. Dr. Baharuddin, ST., M.Pd.',
            'position' => 'Rektor UNIMED',
            'image' => '/images/tokoh/rektor-baharuddin.jpg',
            'image_sm' => '/images/tokoh/rektor-baharuddin-sm.jpg',
        ],
        'ketua-senat' => [
            'name' => 'Prof. Dr. Martina Restuati, M.Si.',
            'position' => 'Ketua Senat UNIMED',
            'image' => '/images/tokoh/ketua-senat-martina-restuati.jpg',
            'image_sm' => '/images/tokoh/ketua-senat-martina-restuati-sm.jpg',
        ],
        'ketua-umum' => [
            'name' => 'Jafaruddin Harahap, S.Pd., M.Si.',
            'position' => 'Ketua Umum PP IKA UNIMED',
            'image' => '/images/tokoh/ketua-umum-jafaruddin-harahap.png',
            'image_sm' => null,
        ],
        'ketua-harian' => [
            'name' => 'Reza Faisal, S.Pd., M.PMat., C.Med.',
            'position' => 'Ketua Harian IKA UNIMED',
            'image' => '/images/tokoh/ketua-harian-reza-faisal.png',
            'image_sm' => null,
        ],
        'sekjen' => [
            'name' => 'Dr. Suriyadi, S.Pd., M.Pd.',
            'position' => 'Sekretaris Jenderal PP IKA UNIMED',
            'image' => '/images/tokoh/sekjen-suriyadi.png',
            'image_sm' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Hero copy per kegiatan
    |--------------------------------------------------------------------------
    |
    | Optional per-slug overrides for the code-built hero: eyebrow, headline,
    | and which pimpinan (keys of "pimpinan" above) appear beside the text.
    |
    */

    'hero_spotlights' => [
        'fun-walk-fun-aerobic-jumpa-party' => [
            'eyebrow' => 'Grand Reunion',
            'headline' => 'Semarak Temu Alumni Memperebutkan Piala Rektor UNIMED',
            'pimpinan' => ['rektor', 'ketua-senat', 'ketua-umum', 'sekjen'],
        ],
        'turnamen-catur-nasional-2026' => [
            'eyebrow' => 'Grand Reunion',
            'headline' => 'Turnamen Catur Nasional 2026',
            'pimpinan' => ['rektor', 'ketua-umum'],
        ],
    ],

    'hero_fallback' => [
        'title' => 'IKA UNIMED',
        'subtitle' => 'Connect, Collaborate, Contribute',
        'summary' => 'Portal resmi Ikatan Alumni Universitas Negeri Medan: kabar organisasi, kegiatan alumni, dan layanan keanggotaan.',
        'image' => null,
        'cta_label' => 'Lihat berita',
        'cta_url' => '/news',
        'secondary_cta_label' => 'Direktori alumni',
        'secondary_cta_url' => '/alumni',
    ],

    /*
    |--------------------------------------------------------------------------
    | Reuni Akbar 2026 committee contacts
    |--------------------------------------------------------------------------
    |
    | WhatsApp numbers use the 62... format and are rendered as wa.me buttons.
    |
    */

    'reuni_contacts' => [
        [
            'name' => 'Sekretariat IKA UNIMED',
            'role' => 'Informasi umum',
            'whatsapp' => '6282295203346',
        ],
        [
            'name' => 'Lila',
            'role' => 'Panitia',
            'whatsapp' => '6281218140142',
        ],
    ],

    'services' => [
        [
            'title' => 'Berita',
            'description' => 'Kabar organisasi',
            'href' => '/news',
            'icon' => 'Newspaper',
        ],
        [
            'title' => 'Kabar Alumni',
            'description' => 'Cerita dari alumni',
            'href' => '/kabar-alumni',
            'icon' => 'MessagesSquare',
        ],
        [
            'title' => 'Direktori',
            'description' => 'Jejaring alumni',
            'href' => '/alumni',
            'icon' => 'Users',
        ],
        [
            'title' => 'Organisasi',
            'description' => 'Pengurus & cabang',
            'href' => '/organisasi',
            'icon' => 'Building2',
        ],
        [
            'title' => 'Karir',
            'description' => 'Lowongan kerja',
            'href' => '/lowongan-kerja',
            'icon' => 'Briefcase',
        ],
        [
            'title' => 'Kemitraan',
            'description' => 'Kolaborasi mitra',
            'href' => '/kemitraan',
            'icon' => 'Handshake',
        ],
        [
            'title' => 'Legalisir',
            'description' => 'Layanan dokumen',
            'href' => '/legalization',
            'icon' => 'FileCheck',
        ],
        [
            'title' => 'Beasiswa',
            'description' => 'Program bantuan',
            'href' => '/beasiswa',
            'icon' => 'GraduationCap',
        ],
        [
            'title' => 'E-Learning',
            'description' => 'Kelas alumni',
            'href' => '/courses',
            'icon' => 'BookOpen',
        ],
        [
            'title' => 'Toko',
            'description' => 'Produk alumni',
            'href' => '/shop',
            'icon' => 'ShoppingBag',
        ],
    ],

];
