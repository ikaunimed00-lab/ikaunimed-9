<?php

namespace App\Filament\Resources\HomepageSections\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HomepageSectionForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Section Config')
                    ->description('Konfigurasi dasar section halaman utama.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Nama Section')
                            ->placeholder('Contoh: Hero Slider Utama')
                            ->required(),
                        Select::make('type')
                            ->label('Tipe Tampilan')
                            ->options([
                                'hero' => 'Hero Carousel',
                                'cta_cards' => 'CTA Cards',
                                'video' => 'Video Section',
                                'features' => 'Features Section',
                                'package' => 'Package Section',
                                'topbar' => 'Top Bar Navigation',
                                'footer' => 'Footer Section',
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('slug')
                            ->label('Slug Identifier')
                            ->helperText('Gunakan slug standar: hero, cta-cards, video-intro, package-section, features')
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])->columns(2),

                Section::make('Konten Section')
                    ->description('Sesuaikan isi konten berdasarkan tipe section yang dipilih.')
                    ->schema([
                        // Hero Content
                        Repeater::make('content.slides')
                            ->label('Daftar Slide')
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Slide Baru')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Gambar Desktop')
                                    ->image()
                                    ->directory('homepage/hero')
                                    ->required()
                                    ->helperText('Rekomendasi: 1920x800px. Gambar ini akan tampil di Desktop.'),
                                FileUpload::make('image_mobile')
                                    ->label('Gambar Mobile')
                                    ->image()
                                    ->directory('homepage/hero/mobile')
                                    ->helperText('Rekomendasi: 1080x1350px. Jika kosong, akan menggunakan gambar desktop.'),
                                TextInput::make('title')
                                    ->label('Label Admin (Internal)')
                                    ->placeholder('Contoh: Promo Alumni atau Info Karir')
                                    ->helperText('Hanya untuk memudahkan Anda mengenali slide ini di dashboard.'),
                                TextInput::make('buttonLink')
                                    ->label('Link Tujuan (URL)')
                                    ->placeholder('Contoh: /berita/info-karir atau https://google.com')
                                    ->helperText('Seluruh area gambar akan mengarah ke link ini saat diklik.'),
                            ])
                            ->visible(fn ($get) => $get('type') === 'hero')
                            ->collapsible()
                            ->columnSpanFull(),

                        // CTA Cards Content
                        Repeater::make('content.cards')
                            ->label('Daftar Kartu CTA')
                            ->itemLabel(fn (array $state): ?string => $state['altText'] ?? 'Kartu Baru')
                            ->schema([
                                FileUpload::make('imageName')
                                    ->label('Gambar Kartu')
                                    ->image()
                                    ->directory('homepage/cta')
                                    ->helperText('Unggah gambar kartu utuh (termasuk teks di dalam gambar). Rekomendasi: 400x500px.'),
                                TextInput::make('altText')
                                    ->label('Teks Alternatif (SEO)')
                                    ->placeholder('Contoh: Administrasi Online'),
                                TextInput::make('href')
                                    ->label('Link Tujuan')
                                    ->placeholder('Contoh: /layanan-alumni'),
                            ])
                            ->visible(fn ($get) => $get('type') === 'cta_cards')
                            ->collapsible()
                            ->collapsed()
                            ->columnSpanFull(),

                        // Video Content
                        Section::make('Video Content')
                            ->schema([
                                TextInput::make('content.videoUrl')
                                    ->label('Video URL (YouTube/Vimeo)')
                                    ->placeholder('https://www.youtube.com/watch?v=...')
                                    ->required(),
                                FileUpload::make('content.videoThumbnail')
                                    ->label('Thumbnail (Gambar Cover)')
                                    ->image()
                                    ->directory('homepage/video')
                                    ->helperText('Ukuran rekomendasi: 1280x720 (16:9). Maksimal 2MB.')
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('16:9'),
                                TextInput::make('content.videoTitle')
                                    ->label('Judul Atas (Warna Hitam)')
                                    ->placeholder('Contoh: Sambutan Ketua Umum IKA UNIMED')
                                    ->required(),
                                TextInput::make('content.videoSubTitle')
                                    ->label('Judul Bawah (Warna Kuning)')
                                    ->placeholder('Contoh: Merajut Silaturahmi, Membangun Sinergi Alumni')
                                    ->required(),
                                TextInput::make('content.badgeTitle')
                                    ->label('Judul Badge (Overlay)')
                                    ->placeholder('Contoh: IKA UNIMED Official')
                                    ->default('IKA UNIMED Official'),
                                TextInput::make('content.badgeSubtitle')
                                    ->label('Subtitle Badge (Overlay)')
                                    ->placeholder('Contoh: THE CHARACTER BUILDING UNIVERSITY')
                                    ->default('THE CHARACTER BUILDING UNIVERSITY'),
                                TagsInput::make('content.videoTags')
                                    ->label('Tag Video (Poin-poin di bawah deskripsi)')
                                    ->placeholder('Tambah tag...')
                                    ->default(['Terintegrasi', 'Kolaboratif', 'Inovatif']),
                                Textarea::make('content.videoDescription')
                                    ->label('Deskripsi Singkat Video'),
                            ])
                            ->visible(fn ($get) => $get('type') === 'video'),

                        // Package Section Content
                        Repeater::make('content.items')
                            ->label('Daftar Item Package')
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Item Package Baru')
                            ->schema([
                                FileUpload::make('bgImage')
                                    ->label('Gambar Background')
                                    ->image()
                                    ->directory('homepage/package')
                                    ->helperText('Ukuran rekomendasi: 1920x1080px. Pastikan gambar memiliki ruang kosong untuk teks.')
                                    ->required(),
                                TextInput::make('title')
                                    ->label('Judul Layanan')
                                    ->placeholder('Contoh: Micro Learning & Skill Upgrading')
                                    ->required(),
                                Textarea::make('description')
                                    ->label('Deskripsi Singkat')
                                    ->placeholder('Jelaskan layanan ini secara ringkas...')
                                    ->required(),
                                TextInput::make('tagline')
                                    ->label('Tagline (Samping Logo)')
                                    ->placeholder('Contoh: Keterampilan Siap Pakai')
                                    ->required(),
                                TextInput::make('href')
                                    ->label('Link Tujuan (HREF)')
                                    ->placeholder('Contoh: /categories/skill')
                                    ->required(),
                                Select::make('position')
                                    ->label('Posisi Konten')
                                    ->options([
                                        'left' => 'Kiri',
                                        'right' => 'Kanan',
                                    ])
                                    ->default('right')
                                    ->required(),
                                ColorPicker::make('buttonColor')
                                    ->label('Warna Tombol')
                                    ->default('#10b981') // emerald-500
                                    ->helperText('Pilih warna untuk tombol "Lihat Detail".'),
                            ])
                            ->visible(fn ($get) => $get('type') === 'package')
                            ->collapsible()
                            ->collapsed()
                            ->columnSpanFull(),

                        // TopBar Content
                        Section::make('Top Bar Content')
                            ->schema([
                                Repeater::make('content.links')
                                    ->label('Menu Navigasi Kiri')
                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Menu Baru')
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('Label Menu')
                                            ->placeholder('Contoh: Shop')
                                            ->required(),
                                        TextInput::make('href')
                                            ->label('Link Tujuan')
                                            ->placeholder('Contoh: /shop atau #')
                                            ->required(),
                                        Select::make('icon')
                                            ->label('Ikon')
                                            ->options([
                                                'ShoppingBag' => 'Shopping Bag (Orange)',
                                                'GraduationCap' => 'Graduation Cap (Sky)',
                                                'TrendingUp' => 'Trending Up (Emerald)',
                                                'Heart' => 'Heart (Red)',
                                            ])
                                            ->required(),
                                        Toggle::make('isNew')
                                            ->label('Tampilkan Badge New (*)')
                                            ->default(false),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
                                
                                Section::make('Tombol Kanan (Cek Area)')
                                    ->schema([
                                        TextInput::make('content.right_label')
                                            ->label('Label Tombol')
                                            ->default('Cek Area'),
                                        TextInput::make('content.right_href')
                                            ->label('Link Tujuan')
                                            ->default('#'),
                                    ]),
                            ])
                            ->visible(fn ($get) => $get('type') === 'topbar'),

                        // Features Section Content
                        Section::make('Features Content')
                            ->schema([
                                TextInput::make('content.subtitle')
                                    ->label('Sub Judul')
                                    ->placeholder('Contoh: Keunggulan Kami'),
                                TextInput::make('content.title')
                                    ->label('Judul Utama')
                                    ->placeholder('Contoh: Kenapa IKA UNIMED?'),
                                Textarea::make('content.description')
                                    ->label('Deskripsi'),
                                Repeater::make('content.items')
                                    ->label('Daftar Fitur')
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Fitur Baru')
                                    ->schema([
                                        Select::make('icon')
                                            ->label('Ikon')
                                            ->options([
                                                'Users' => 'Users (Database)',
                                                'Briefcase' => 'Briefcase (Karir)',
                                                'Newspaper' => 'Newspaper (Berita)',
                                                'GraduationCap' => 'GraduationCap (Beasiswa)',
                                                'CreditCard' => 'CreditCard (Kartu)',
                                                'HeartHandshake' => 'HeartHandshake (Pengabdian)',
                                            ])
                                            ->required(),
                                        TextInput::make('title')
                                            ->label('Judul')
                                            ->placeholder('Contoh: Database Alumni'),
                                        TextInput::make('description')
                                            ->label('Deskripsi Singkat')
                                            ->placeholder('Contoh: Terhubung kembali dengan rekan sejawat'),
                                        ColorPicker::make('color')
                                            ->label('Warna Aksen')
                                            ->default('#006837'),
                                        TextInput::make('href')
                                            ->label('Link Tujuan')
                                            ->placeholder('Contoh: /database')
                                            ->default('#'),
                                    ])
                                    ->columns(2)
                                    ->visible(fn ($get) => $get('type') === 'features')
                                    ->collapsible()
                                    ->collapsed(),
                            ])
                            ->visible(fn ($get) => $get('type') === 'features'),
                        
                        // Footer Content
                        Section::make('Footer Content')
                            ->schema([
                                Section::make('Informasi Umum')
                                    ->schema([
                                        Textarea::make('content.description')
                                            ->label('Deskripsi Singkat (Bawah Logo)')
                                            ->placeholder('Contoh: Wadah resmi kolaborasi alumni...')
                                            ->rows(3),
                                        TextInput::make('content.copyright_text')
                                            ->label('Teks Copyright')
                                            ->placeholder('Contoh: © 2026')
                                            ->default('© 2026'),
                                    ])->columns(2),

                                Section::make('Kontak & Lokasi')
                                    ->schema([
                                        Textarea::make('content.address')
                                            ->label('Alamat Lengkap')
                                            ->placeholder('Contoh: Jl. Willem Iskandar Pasar V...')
                                            ->rows(2),
                                        TextInput::make('content.phone')
                                            ->label('Nomor Telepon')
                                            ->placeholder('Contoh: +62 812 3456 7890'),
                                        TextInput::make('content.email')
                                            ->label('Email Resmi')
                                            ->placeholder('Contoh: sekretariat@ikaunimed.or.id')
                                            ->email(),
                                    ])->columns(3),

                                Section::make('Social Media')
                                    ->description('Kosongkan jika tidak ingin ditampilkan.')
                                    ->schema([
                                        TextInput::make('content.social_facebook')->label('Facebook URL')->prefix('fb.com/'),
                                        TextInput::make('content.social_instagram')->label('Instagram URL')->prefix('instagram.com/'),
                                        TextInput::make('content.social_twitter')->label('Twitter/X URL')->prefix('x.com/'),
                                        TextInput::make('content.social_youtube')->label('YouTube URL')->prefix('youtube.com/'),
                                    ])->columns(2),

                                Repeater::make('content.footer_links')
                                    ->label('Grup Link Footer')
                                    ->itemLabel(fn (array $state): ?string => $state['group_name'] ?? 'Grup Baru')
                                    ->schema([
                                        TextInput::make('group_name')
                                            ->label('Nama Grup (Contoh: Layanan)')
                                            ->required(),
                                        Repeater::make('links')
                                            ->label('Daftar Link')
                                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Link Baru')
                                            ->schema([
                                                TextInput::make('label')
                                                    ->label('Teks Menu')
                                                    ->placeholder('Contoh: Berita Terbaru')
                                                    ->required(),
                                                TextInput::make('href')
                                                    ->label('Link Tujuan (URL)')
                                                    ->placeholder('Contoh: /berita atau https://...')
                                                    ->required(),
                                            ])
                                            ->collapsible()
                                            ->collapsed(),
                                    ])
                                    ->collapsible()
                                    ->collapsed()
                                    ->columnSpanFull(),
                            ])
                            ->visible(fn ($get) => $get('type') === 'footer'),
                    ]),
            ]);
    }
}
