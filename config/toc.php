<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pengumuman hasil penyisihan
    |--------------------------------------------------------------------------
    |
    | Saat false, halaman pengumuman menampilkan "belum dibuka" tanpa membocorkan
    | hasil. Buka dengan TOC_PENGUMUMAN_DIBUKA=true di .env setelah siap.
    |
    */

    'pengumuman_dibuka' => (bool) env('TOC_PENGUMUMAN_DIBUKA', false),

    'link_grup_wa_finalis' => env('TOC_FINALIS_WA_LINK', ''),

    'batas_unggah' => env('TOC_BATAS_UNGGAH', '2026-11-18 23:59:59'),

    'batas_unggah_ulang' => env('TOC_BATAS_UNGGAH_ULANG', env('TOC_BATAS_UNGGAH', '2026-11-18 23:59:59')),

    'panitia_whatsapp' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TOC_PANITIA_WHATSAPP', '089679018457,081397718747')),
    ))),

    'timezone' => 'Asia/Jakarta',

    'tm_final' => [
        'label' => 'Technical Meeting Final',
        'tanggal' => 'Jumat, 13 November 2026',
        'info' => env('TOC_TM_FINAL_INFO', ''),
        'fallback' => 'akan diinformasikan di grup WhatsApp',
    ],

    'final' => [
        'label' => 'Final',
        'tanggal' => 'Minggu, 22 November 2026',
        'info' => env('TOC_FINAL_INFO', ''),
        'fallback' => 'akan diinformasikan di grup WhatsApp',
    ],

    'berkas' => [
        'session_key' => 'toc_berkas_peserta_id',
        'session_ttl_minutes' => 60,
        'disk' => 'local',
        'path_prefix' => 'toc/berkas',
        'export_path' => 'toc/exports',
        'export_max_peserta' => 600,
        // Maks peserta per ZIP unduhan langsung (modal bagian).
        'export_chunk_size' => (int) env('TOC_BERKAS_EXPORT_CHUNK_SIZE', 50),
        'export_selection_ttl_minutes' => (int) env('TOC_BERKAS_EXPORT_SELECTION_TTL', 120),
        'export_download_time_limit' => (int) env('TOC_BERKAS_EXPORT_TIME_LIMIT', 300),
        'export_ttl_minutes' => 60,
        'import_max_kb' => 5120,
        'rejection_mail_per_minute' => 30,
        'jenis' => [
            'foto' => [
                'label' => 'Foto terbaik guru',
                'mimes' => ['image/jpeg', 'image/png'],
                'extensions' => ['jpg', 'jpeg', 'png'],
                'max_kb' => 5120,
                'min_width' => 1080,
                'min_height' => 1350,
            ],
            'pakta_integritas' => [
                'label' => 'Pakta Integritas',
                'mimes' => ['application/pdf', 'image/jpeg', 'image/png'],
                'extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_kb' => 3072,
            ],
            'surat_keterangan' => [
                'label' => 'Surat Keterangan Sebagai Guru',
                'mimes' => ['application/pdf', 'image/jpeg', 'image/png'],
                'extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_kb' => 3072,
            ],
        ],
        'templates' => [
            'pakta-integritas-docx' => [
                'file' => 'Pakta-Integritas-ToC-2026.docx',
                'download_as' => 'Pakta-Integritas-ToC-2026.docx',
            ],
            'pakta-integritas-pdf' => [
                'file' => 'Pakta-Integritas-ToC-2026.pdf',
                'download_as' => 'Pakta-Integritas-ToC-2026.pdf',
            ],
            'surat-keterangan-docx' => [
                'file' => 'Surat-Keterangan-Guru-ToC-2026.docx',
                'download_as' => 'Surat-Keterangan-Guru-ToC-2026.docx',
            ],
            'surat-keterangan-pdf' => [
                'file' => 'Surat-Keterangan-Guru-ToC-2026.pdf',
                'download_as' => 'Surat-Keterangan-Guru-ToC-2026.pdf',
            ],
        ],
    ],

    'login' => [
        'throttle_per_minute' => 20,
        'throttle_per_hour' => 120,
        'scan_distinct_threshold' => 30,
        'scan_window_minutes' => 10,
        'scan_block_minutes' => 60,
        'generic_error' => 'Nomor pendaftaran tidak ditemukan. Periksa kembali atau hubungi admin.',
        'scan_block_message' => 'Terlalu banyak percobaan dari jaringan ini. Silakan coba lagi dalam satu jam atau hubungi admin.',
        'help_text' => 'Masukkan nomor pendaftaran Anda, contoh: TOC-2026-0071 (nomor ada di email akun CBT atau bukti pendaftaran).',
    ],

    'expected_import' => [
        'lulus' => 514,
        'tidak_lulus' => 3604,
        'total' => 4118,
    ],

];
