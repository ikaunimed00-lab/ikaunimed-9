import { Head, Link } from '@inertiajs/react';
import React from 'react';
import MainLayout from '@/components/MainLayout';
import NewsHeroSection from '@/components/NewsHeroSection'; // New Component
import BreakingNews from '@/components/BreakingNews'; // New Component
import AdLeaderboard from '@/components/AdLeaderboard'; // New Component
import NewsCard from '@/components/NewsCard';
import CategoryNavigation from '@/components/CategoryNavigation';
import Pagination from '@/components/Pagination';
import AdsenseUnit from '@/components/AdsenseUnit';
import { FlashContent, PollingSection, VideoPopular, EditorsPicks, KolumOpini, BeritaPopuler, TagPopuler, KomentarTerbanyak } from '@/components/editorial';

interface NewsItem {
  id: number;
  title: string;
  excerpt?: string;
  slug: string;
  image?: string | null;
  view_count: number;
  published_at: string;
  created_at?: string;
  author?: {
    name?: string;
  };
  categories?: Array<{
    name: string;
    slug: string;
  }>;
  reading_time?: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface VideoItem {
  id: number;
  title: string;
  slug: string;
  video_urls: string[];
  view_count?: number;
  published_at: string;
  image?: string | null;
}

interface OpinionItem {
  id: number;
  title: string;
  slug: string;
  author?: string | null;
  category?: string | null;
  published_at?: string | null;
}

interface PopularNewsItem {
  id: number;
  title: string;
  slug: string;
  image?: string | null;
  view_count?: number;
  published_at?: string | null;
}

interface EditorsPickItem {
  id: number;
  title: string;
  slug: string;
  image?: string | null;
  published_at?: string;
}

interface TagItem {
  id: number;
  name: string;
  slug: string;
  count: number;
}

interface AdsConfig {
  enabled?: boolean;
  provider?: string;
  leaderboard_slot?: string | null;
  sidebar_1_slot?: string | null;
  sidebar_2_slot?: string | null;
  inline_article_slot?: string | null;
  infeed_slot?: string | null;
}

interface BreakingNewsItem {
  id: number;
  title: string;
  slug: string;
}

interface NewsIndexProps {
  news: {
    data: NewsItem[];
    current_page: number;
    last_page: number;
    links: PaginationLink[];
  };
  breakingNews?: BreakingNewsItem[];
  heroNews?: NewsItem[];
  alumniNews?: NewsItem[];
  opinionNews?: NewsItem[];
  latestVideos?: VideoItem[];
  popularVideos?: VideoItem[];
  opinionColumns?: OpinionItem[];
  popularNews?: PopularNewsItem[];
  editorsPicks?: EditorsPickItem[];
  popularTags?: TagItem[];
  ads?: AdsConfig;
}

const NewsIndex = ({
  news,
  breakingNews = [],
  heroNews = [],
  alumniNews = [],
  opinionNews = [],
  latestVideos = [],
  popularVideos = [],
  opinionColumns = [],
  popularNews = [],
  editorsPicks = [],
  popularTags = [],
  ads,
}: NewsIndexProps) => {
  const restNews = news.data; // Use all paginated data for "Berita Lainnya"

  const hasAnyNews =
    (heroNews?.length ?? 0) > 0 ||
    (alumniNews?.length ?? 0) > 0 ||
    (opinionNews?.length ?? 0) > 0 ||
    restNews.length > 0;

  return (
    <>
      <Head>
        <title>Portal Berita Alumni - IKA UNIMED</title>
        <meta
          name="description"
          content="Portal berita resmi Ikatan Alumni Universitas Negeri Medan (IKA UNIMED). Informasi terkini program alumni, kisah sukses, opini, dan kabar UNIMED."
        />
        <meta name="keywords" content="IKA UNIMED, alumni UNIMED, Universitas Negeri Medan, berita alumni, opini alumni, program alumni, kabar kampus" />
        <meta property="og:title" content="Portal Berita Alumni - IKA UNIMED" />
        <meta
          property="og:description"
          content="Berita, opini, dan informasi terkini dari Ikatan Alumni Universitas Negeri Medan."
        />
        <meta property="og:image" content="/images/cta_ikaunimed-002.png" />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Portal Berita Alumni - IKA UNIMED" />
        <meta name="twitter:image" content="/images/cta_ikaunimed-002.png" />
      </Head>

      <MainLayout variant="full">
        {/* 1. Ad Leaderboard (Full Width) — hanya render jika ads.enabled */}
        <AdLeaderboard
          enabled={ads?.enabled}
          provider={ads?.provider}
          slot={ads?.leaderboard_slot}
        />

        {/* 2. Breaking News (Marquee) */}
        <BreakingNews items={breakingNews} />

        {/* 3. New Hero Section (1 Main + 2 Sub) */}
        {heroNews && heroNews.length > 0 ? (
           <NewsHeroSection items={heroNews} />
        ) : hasAnyNews ? (
            <section className="bg-white border-b border-[#E6EAE8] py-10">
                <div className="mx-auto px-4 max-w-[1440px] text-center text-[#6B7280]">
                    Belum ada berita unggulan untuk saat ini. Jelajahi daftar berita lainnya di bawah.
                </div>
            </section>
        ) : (
            <section className="bg-white border-b border-[#E6EAE8] py-16 sm:py-20">
                <div className="mx-auto px-4 max-w-2xl text-center">
                    <div className="w-20 h-20 mx-auto mb-6 rounded-full bg-[#F0FDFA] flex items-center justify-center text-4xl">
                        📰
                    </div>
                    <h1 className="text-2xl sm:text-3xl font-bold text-[#0F172A] mb-3">
                        Portal Berita IKA UNIMED Segera Tayang
                    </h1>
                    <p className="text-[#6B7280] leading-relaxed mb-6">
                        Saat ini belum ada berita yang dipublikasikan. Redaksi sedang menyiapkan
                        artikel pertama. Silakan kembali beberapa saat lagi untuk membaca kabar
                        terbaru seputar alumni dan Universitas Negeri Medan.
                    </p>
                    <Link
                        href="/"
                        className="inline-flex items-center gap-2 bg-[#0F766E] text-white font-semibold px-5 py-2.5 rounded-lg hover:bg-[#115E59] transition-colors"
                    >
                        <span>←</span>
                        <span>Kembali ke Beranda</span>
                    </Link>
                </div>
            </section>
        )}

        {/* Category Navigation */}
        <section className="bg-white border-b border-[#E6EAE8]">
          <div className="w-full">
            <div className="mx-auto px-4 sm:px-6 lg:px-8 max-w-[1440px] py-4">
               <CategoryNavigation />
            </div>
          </div>
        </section>

        {/* Editorial Layout: 4-Area Grid */}
        <section className="bg-[#F8FAF9]">
          <div className="w-full">
            <div className="mx-auto px-4 sm:px-6 lg:px-8 max-w-[1440px] py-8 lg:py-12">
              <div className="grid grid-cols-1 md:grid-cols-9 lg:grid-cols-9 xl:grid-cols-12 gap-6 lg:gap-8">
                
                {/* AREA 1: SIDEBAR KIRI - Editorial Navigation (Hidden < 1280px) */}
                <div className="hidden xl:block xl:col-span-1">
                  <div className="sticky top-24 space-y-6">
                    {/* Editorial Nav Section */}
                    <div className="bg-white rounded-lg border border-[#E6EAE8] p-4">
                      <h3 className="text-xs font-bold text-[#0F172A] mb-4 truncate">EDITORIAL</h3>
                      <nav className="space-y-2">
                        <a href="#" className="text-xs text-[#0F766E] hover:text-[#115E59] block transition-colors py-1">
                          Berita Utama
                        </a>
                        <a href="#" className="text-xs text-[#0F766E] hover:text-[#115E59] block transition-colors py-1">
                          Program Alumni
                        </a>
                        <a href="#" className="text-xs text-[#0F766E] hover:text-[#115E59] block transition-colors py-1">
                          Video Alumni
                        </a>
                        <a href="#" className="text-xs text-[#0F766E] hover:text-[#115E59] block transition-colors py-1">
                          Opini Alumni
                        </a>
                        <a href="#" className="text-xs text-[#0F766E] hover:text-[#115E59] block transition-colors py-1">
                          Kisah Sukses
                        </a>
                      </nav>
                    </div>

                    {/* TUGAS 1: PROGRAM ALUMNI Section */}
                    <div className="bg-white rounded-lg border border-[#E6EAE8] p-4">
                      <h3 className="text-xs font-bold text-[#0F172A] mb-4 truncate">PROGRAM ALUMNI</h3>
                      <nav className="space-y-2">
                        <a href="#" className="text-xs text-[#0F766E] hover:text-[#115E59] block transition-colors py-1 truncate">
                          Program Alumni
                        </a>
                        <a href="#" className="text-xs text-[#0F766E] hover:text-[#115E59] block transition-colors py-1 truncate">
                          Agenda Mingguan
                        </a>
                        <a href="#" className="text-xs text-[#0F766E] hover:text-[#115E59] block transition-colors py-1 truncate">
                          Info Beasiswa
                        </a>
                        <a href="#" className="text-xs text-[#0F766E] hover:text-[#115E59] block transition-colors py-1 truncate">
                          Kontribusi Alumni
                        </a>
                      </nav>
                    </div>
                  </div>
                </div>

                {/* AREA 2: KONTEN UTAMA - News Feed (md:col-9, lg:col-6) */}
                <div className="col-span-1 md:col-span-9 lg:col-span-6">
                  <div className="space-y-8">
                    {/* 1. FlashContent */}
                    <div className="bg-white rounded-lg border border-[#E6EAE8] overflow-hidden">
                      <FlashContent videos={latestVideos} />
                    </div>

                    {/* 2. SECTION: KABAR ALUMNI (Grid 3 Col: 1 Big + 2 Small) */}
                    {alumniNews.length > 0 && (
                        <div className="space-y-4">
                            <div className="flex items-center justify-between border-b border-gray-200 pb-2">
                                <h2 className="text-xl font-bold text-[#0F172A]">Kabar Alumni</h2>
                                <Link href={route('categories.show', 'alumni')} className="text-xs text-[#0F766E] font-semibold hover:underline">Lihat Semua</Link>
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* First item big */}
                                <div className="md:col-span-2">
                                     <NewsCard {...alumniNews[0]} reading_time={alumniNews[0].reading_time} />
                                </div>
                                {/* Next 2 items small */}
                                {alumniNews.slice(1, 3).map(item => (
                                    <div key={item.id} className="col-span-1">
                                         <NewsCard {...item} reading_time={item.reading_time} compact size="sm" />
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* 3. SECTION: OPINI & ARTIKEL (List View) */}
                    {opinionNews.length > 0 && (
                        <div className="space-y-4">
                             <div className="flex items-center justify-between border-b border-gray-200 pb-2">
                                <h2 className="text-xl font-bold text-[#0F172A]">Opini & Artikel</h2>
                                <Link href={route('categories.show', 'opini')} className="text-xs text-[#0F766E] font-semibold hover:underline">Lihat Semua</Link>
                            </div>
                            <div className="flex flex-col gap-4">
                                {opinionNews.map(item => (
                                    <div key={item.id} className="flex gap-4 bg-white p-4 rounded-lg border border-[#E6EAE8] hover:shadow-sm transition-shadow">
                                        <div className="w-1/3 aspect-[4/3] rounded overflow-hidden flex-shrink-0">
                                            {item.image ? (
                                                <img src={item.image} alt={item.title} className="w-full h-full object-cover" />
                                            ) : (
                                                <div className="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400">No Image</div>
                                            )}
                                        </div>
                                        <div className="w-2/3 flex flex-col justify-between">
                                            <div>
                                                <div className="flex gap-2 mb-1">
                                                    {item.categories?.slice(0, 1).map(cat => (
                                                        <span key={cat.slug} className="text-[#0F766E] text-[10px] font-bold uppercase">{cat.name}</span>
                                                    ))}
                                                </div>
                                                <Link href={route('news.show', item.slug)} className="text-base sm:text-lg font-bold text-[#0F172A] hover:text-[#0F766E] line-clamp-2 leading-tight mb-2">
                                                    {item.title}
                                                </Link>
                                                <p className="text-xs text-gray-500 line-clamp-2 hidden sm:block">{item.excerpt}</p>
                                            </div>
                                            <div className="flex items-center text-[10px] text-gray-400 gap-2 mt-2">
                                                <span>{item.author?.name}</span>
                                                <span>•</span>
                                                <span>{new Date(item.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}</span>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* 4. PollingSection — placeholder hingga modul polling tersedia */}
                    <div className="bg-white rounded-lg border border-[#E6EAE8] p-6">
                      <PollingSection poll={null} />
                    </div>

                    {/* 5. VideoPopular */}
                    <div className="bg-white rounded-lg border border-[#E6EAE8] p-6">
                      <VideoPopular videos={popularVideos} />
                    </div>

                    {/* 6. KolumOpini */}
                    <div className="bg-white rounded-lg border border-[#E6EAE8] p-6">
                      <KolumOpini maxItems={4} items={opinionColumns} />
                    </div>

                    {/* 7. Berita Lainnya + Pagination */}
                    <div className="space-y-6">
                      <div>
                        <h2 className="text-lg sm:text-xl font-bold text-[#0F172A] mb-1 truncate">Berita Lainnya</h2>
                        <div className="h-1 w-16 bg-[#0F766E] rounded"></div>
                      </div>

                      {restNews.length > 0 ? (
                        <>
                          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {restNews.map((item, idx) => {
                              // KEBIJAKAN IN-FEED: maks. 1 iklan setiap 4 berita.
                              // Posisi: setelah item ke-4, ke-8, ke-12, ke-16.
                              // Render hanya jika ads.enabled & slot terkonfigurasi.
                              const showInfeedAd =
                                ads?.enabled &&
                                ads?.infeed_slot &&
                                (idx + 1) % 4 === 0;

                              return (
                                <React.Fragment key={item.id}>
                                  <NewsCard {...item} reading_time={item.reading_time} />
                                  {showInfeedAd && (
                                    <div className="col-span-1 md:col-span-2 bg-white rounded-lg border border-[#E6EAE8] p-4">
                                      <div className="text-[10px] uppercase tracking-wider text-[#6B7280] text-center mb-2">
                                        Advertisement
                                      </div>
                                      <div className="flex justify-center items-center min-h-32">
                                        <AdsenseUnit
                                          slot={ads.infeed_slot as string}
                                          format="auto"
                                          style={{ display: 'block', width: '100%' }}
                                        />
                                      </div>
                                    </div>
                                  )}
                                </React.Fragment>
                              );
                            })}
                          </div>

                          {/* Pagination */}
                          {news.last_page > 1 && (
                            <div className="mt-8 bg-white rounded-lg border border-[#E6EAE8] p-6">
                              <Pagination
                                links={news.links}
                                current_page={news.current_page}
                                last_page={news.last_page}
                              />
                            </div>
                          )}
                        </>
                      ) : (
                        <div className="bg-white rounded-lg border border-dashed border-[#CBD5E1] p-8 sm:p-10 text-center">
                          <div className="w-14 h-14 mx-auto mb-4 rounded-full bg-[#F0FDFA] flex items-center justify-center text-2xl">
                            📰
                          </div>
                          <p className="text-[#0F172A] font-semibold mb-1">
                            Belum ada berita untuk ditampilkan
                          </p>
                          <p className="text-sm text-[#6B7280] max-w-md mx-auto">
                            Redaksi sedang menyiapkan artikel terbaru. Silakan kembali beberapa
                            saat lagi atau telusuri kategori yang tersedia di atas.
                          </p>
                        </div>
                      )}
                    </div>
                  </div>
                </div>

                {/* AREA 3: SIDEBAR KONTEN - Editorial Components (Hidden < 768px) */}
                <div className="hidden md:block md:col-span-9 lg:col-span-3">
                  <div className="sticky top-24 space-y-6">
                    {/* EditorsPicks */}
                    <div className="bg-white rounded-lg border border-[#E6EAE8] p-4 md:p-5">
                      <EditorsPicks items={editorsPicks} />
                    </div>

                    {/* BeritaPopuler */}
                    <div className="bg-white rounded-lg border border-[#E6EAE8] p-4 md:p-5">
                      <BeritaPopuler variant="list" maxItems={6} items={popularNews} />
                    </div>

                    {/* TagPopuler */}
                    <div className="bg-white rounded-lg border border-[#E6EAE8] p-4 md:p-5">
                      <TagPopuler maxTags={12} tags={popularTags} />
                    </div>

                    {/* KomentarTerbanyak — placeholder hingga sistem komentar tersedia */}
                    <div className="bg-white rounded-lg border border-[#E6EAE8] p-4 md:p-5">
                      <KomentarTerbanyak maxItems={5} items={[]} />
                    </div>
                  </div>
                </div>

                {/* AREA 4: SIDEBAR IKLAN & CAMPAIGN (Hidden < 1280px) */}
                <div className="hidden xl:block xl:col-span-2">
                  <div className="sticky top-24 space-y-6">
                    {/* Sidebar Ad 1 - Medium Rectangle (300x250). Hanya render
                        jika ads.enabled & slot terkonfigurasi di Site Settings. */}
                    {ads?.enabled && ads?.sidebar_1_slot && (
                      <div className="bg-white rounded-lg border border-[#E6EAE8] p-3">
                        <div className="text-[10px] uppercase tracking-wider text-[#6B7280] text-center mb-2">
                          Advertisement
                        </div>
                        <div className="flex justify-center items-center min-h-[250px]">
                          <AdsenseUnit
                            slot={ads.sidebar_1_slot}
                            format="auto"
                            style={{ display: 'block', width: '100%' }}
                          />
                        </div>
                      </div>
                    )}

                    {/* Campaign Khusus IKA UNIMED */}
                    <div className="bg-gradient-to-br from-[#0F766E] to-[#115E59] rounded-lg p-6 text-white">
                      <h3 className="text-xs font-bold mb-2 truncate">CAMPAIGN KHUSUS</h3>
                      <p className="text-xs mb-4 opacity-90">
                        Ikuti program eksklusif dari IKA UNIMED
                      </p>
                      <button className="w-full bg-white text-[#0F766E] text-xs font-bold py-2 rounded hover:bg-[#F8FAF9] transition-colors">
                        Pelajari Lebih Lanjut
                      </button>
                    </div>

                    {/* Donasi Alumni */}
                    <div className="bg-white rounded-lg border border-[#E6EAE8] p-6 text-center">
                      <h3 className="text-xs font-bold text-[#0F172A] mb-2 truncate">Dukung IKA UNIMED</h3>
                      <p className="text-xs text-[#6B7280] mb-4">
                        Berkontribusi untuk kemajuan organisasi alumni
                      </p>
                      <button className="w-full bg-[#0F766E] text-white text-xs font-bold py-2 rounded hover:bg-[#115E59] transition-colors">
                        Berdonasi
                      </button>
                    </div>

                    {/* Sidebar Ad 2 - Half Page (300x600). Hanya render jika
                        ads.enabled & slot terkonfigurasi di Site Settings. */}
                    {ads?.enabled && ads?.sidebar_2_slot && (
                      <div className="bg-white rounded-lg border border-[#E6EAE8] p-3">
                        <div className="text-[10px] uppercase tracking-wider text-[#6B7280] text-center mb-2">
                          Advertisement
                        </div>
                        <div className="flex justify-center items-center min-h-[600px]">
                          <AdsenseUnit
                            slot={ads.sidebar_2_slot}
                            format="auto"
                            style={{ display: 'block', width: '100%' }}
                          />
                        </div>
                      </div>
                    )}

                    {/* Info Box */}
                    <div className="bg-[#F8FAF9] rounded-lg border border-[#E6EAE8] p-4">
                      <h3 className="text-xs font-bold text-[#0F172A] mb-2 truncate">Tentang Portal</h3>
                      <p className="text-xs text-[#6B7280]">
                        Portal berita resmi Ikatan Alumni UNIMED. Informasi terdepan dan terpercaya.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

      </MainLayout>
    </>
  );
};

export default NewsIndex;
