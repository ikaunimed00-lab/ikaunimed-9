import MainLayout from '@/components/MainLayout';
import NewsCard from '@/components/NewsCard';
import NewsLayout from '@/components/NewsLayout';
import AdInline from '@/components/AdInline';
import AdSidebar from '@/components/AdSidebar';
import { Head, Link } from '@inertiajs/react';
import { formatNumber } from '@/lib/utils';
import { useEffect, useState } from 'react';

interface AdsConfig {
  inline_article_slot?: string;
  infeed_slot?: string;
}

interface NewsItem {
  id: number;
  title: string;
  excerpt: string;
  content: string;
  slug: string;
  image?: string | null;
  view_count: number;
  created_at?: string;
  updated_at?: string;
  published_at?: string;
  author?: {
    id: number;
    name?: string | null;
  } | null;
  categories?: Array<{
    name: string;
    slug: string;
  }>;
  video_urls?: any;
  reading_time?: string;
}

interface RelatedNews extends NewsItem {}

interface NewsShowProps {
  news: NewsItem;
  relatedNews?: RelatedNews[];
  ads?: AdsConfig;
}

export default function NewsShow({ news, relatedNews = [], ads }: NewsShowProps) {
  const [shareUrl, setShareUrl] = useState('');
  const [showToast, setShowToast] = useState(false);

  useEffect(() => {
    setShareUrl(window.location.href);
  }, []);

  const imageUrl = news.image ?? undefined;
  const rawVideoUrls = (news as any).video_urls ?? [];
  const videoUrls = Array.isArray(rawVideoUrls)
    ? rawVideoUrls
    : rawVideoUrls
    ? [rawVideoUrls]
    : [];

  const origin = typeof window !== 'undefined' ? window.location.origin : 'https://ikaunimed.or.id';

  const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'NewsArticle',
    headline: news.title,
    description: news.excerpt,
    image: imageUrl ? [imageUrl] : undefined,
    datePublished: news.published_at || news.created_at,
    dateModified: news.updated_at || news.created_at,
    author: {
      '@type': 'Person',
      name: news.author?.name || 'Admin',
    },
    publisher: {
      '@type': 'Organization',
      name: 'IKA UNIMED',
      logo: {
        '@type': 'ImageObject',
        url: `${origin}/images/logo.png`,
      },
    },
    mainEntityOfPage: {
      '@type': 'WebPage',
      '@id': shareUrl,
    },
  };

  // Share URLs
  const shareLinks = {
    whatsapp: `https://wa.me/?text=${encodeURIComponent(news.title + ' ' + shareUrl)}`,
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`,
    twitter: `https://twitter.com/intent/tweet?text=${encodeURIComponent(news.title)}&url=${encodeURIComponent(shareUrl)}`,
  };

  const publishDate = new Date(news.published_at || news.created_at || '').toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });

  return (
    <>
      <Head title={`${news.title} - IKA UNIMED`}>
        <meta name="description" content={news.excerpt} />
        <meta property="og:title" content={news.title} />
        <meta property="og:description" content={news.excerpt} />
        {imageUrl && <meta property="og:image" content={imageUrl} />}
        <meta property="og:url" content={shareUrl} />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content={news.title} />
        <meta name="twitter:description" content={news.excerpt} />
        {imageUrl && <meta name="twitter:image" content={imageUrl} />}
        <link rel="canonical" href={shareUrl} />
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{
            __html: JSON.stringify(structuredData),
          }}
        />
      </Head>

      <MainLayout variant="full">

        {/* Article dengan NewsLayout 3 kolom */}
        <NewsLayout>
          <article className="p-4 sm:p-6 lg:p-8">
            {/* Breadcrumb */}
            <nav className="flex items-center gap-2 text-sm text-[#6B7280] mb-6 overflow-x-auto">
              <Link href={route('news.index')} className="hover:text-[#0F766E] whitespace-nowrap">
                Berita
              </Link>
              <span>/</span>
              {news.categories?.[0] && (
                <>
                  <Link
                    href={route('categories.show', news.categories[0].slug)}
                    className="hover:text-[#0F766E] whitespace-nowrap"
                  >
                    {news.categories[0].name}
                  </Link>
                  <span>/</span>
                </>
              )}
              <span className="text-[#0F172A] font-medium truncate">{news.title}</span>
            </nav>

            {/* Categories */}
            {news.categories && news.categories.length > 0 && (
              <div className="flex flex-wrap gap-2 mb-4">
                {news.categories.map(cat => (
                  <Link
                    key={cat.slug}
                    href={route('categories.show', cat.slug)}
                    className="bg-[#0F766E]/10 text-[#0F766E] px-3 py-1 rounded-full text-xs font-semibold hover:bg-[#0F766E]/20"
                  >
                    {cat.name}
                  </Link>
                ))}
              </div>
            )}

            {/* Title */}
            <h1 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-[#0F172A] mb-6 leading-tight">
              {news.title}
            </h1>

            {/* Meta Info */}
            <div className="flex flex-wrap items-center gap-6 pb-6 border-b border-[#E6EAE8] mb-6 text-sm text-[#6B7280]">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 bg-[#0F766E] rounded-full flex items-center justify-center text-white font-bold text-lg shrink-0">
                  {news.author?.name?.charAt(0).toUpperCase() || 'A'}
                </div>
                <div>
                  <p className="font-bold text-[#0F172A]">{news.author?.name || 'Admin'}</p>
                  <div className="flex items-center gap-2 text-xs sm:text-sm">
                    <time dateTime={news.published_at}>{publishDate}</time>
                    {news.reading_time && (
                      <>
                        <span className="text-gray-300">•</span>
                        <span>{news.reading_time}</span>
                      </>
                    )}
                  </div>
                </div>
              </div>
              
              <div className="flex items-center gap-1 ml-auto sm:ml-0 text-xs sm:text-sm bg-gray-100 px-3 py-1 rounded-full">
                <span>👁️</span>
                <span>{formatNumber(news.view_count)} pembaca</span>
              </div>
            </div>

            {/* Featured Image */}
            {imageUrl && (
              <figure className="mb-8">
                <img
                  src={imageUrl}
                  alt={news.title}
                  loading="eager"
                  decoding="async"
                  className="w-full h-auto rounded-lg shadow-lg"
                />
              </figure>
            )}

            {/* Excerpt */}
            {news.excerpt && (
              <div className="mb-8 text-lg text-[#374151] font-medium italic border-l-4 border-[#0F766E] pl-4">
                "{news.excerpt}"
              </div>
            )}

            {/* Content dengan Ad Inline */}
            <div className="prose prose-lg prose-slate max-w-none mb-12 prose-img:rounded-lg prose-img:shadow-lg prose-a:text-[#0F766E] prose-a:no-underline hover:prose-a:underline prose-headings:text-slate-900 prose-p:text-slate-700 prose-p:leading-relaxed">
              {/* Parse content dan inject ad di tengah-tengah */}
              {(() => {
                // Split content by paragraph tags untuk smart ad placement
                const sections = news.content.split(/(<p>|<\/p>|<h[2-6]>|<\/h[2-6]>|<figure>|<\/figure>|<blockquote>|<\/blockquote>)/i);
                const paragraphCount = sections.filter(s => s.match(/^<p>/i)).length;
                let currentParagraph = 0;
                const adPosition = Math.ceil(paragraphCount / 2); // Sisipkan di tengah

                return sections.map((section, idx) => {
                  // Count paragraphs
                  if (section.match(/^<p>/i)) {
                    currentParagraph++;
                    // Cek apakah harus tampilin ad setelah section ini
                    if (currentParagraph === adPosition) {
                      return (
                        <div key={idx}>
                          <div dangerouslySetInnerHTML={{ __html: section }} />
                          <AdInline position={`middle-after-paragraph-${currentParagraph}`} slot={ads?.inline_article_slot} />
                        </div>
                      );
                    }
                  }

                  return <div key={idx} dangerouslySetInnerHTML={{ __html: section }} />;
                });
              })()}
            </div>

            {/* Author Box */}
            <div className="bg-slate-50 rounded-xl p-6 border border-slate-100 mb-12 flex items-start gap-4 sm:gap-6">
              <div className="shrink-0">
                <div className="w-16 h-16 bg-[#0F766E] rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-sm">
                  {news.author?.name?.charAt(0).toUpperCase() || 'A'}
                </div>
              </div>
              <div className="flex-1">
                <h3 className="text-lg font-bold text-slate-900 mb-2">
                  Tentang {news.author?.name || 'Admin'}
                </h3>
                <p className="text-slate-600 text-sm leading-relaxed mb-3">
                  Penulis aktif di Portal Berita IKA UNIMED. Menyajikan informasi terkini dan terpercaya seputar alumni, universitas, dan pendidikan.
                </p>
                <div className="flex gap-3">
                   {/* Social Links Placeholder - Bisa dikembangkan nanti */}
                   <span className="text-xs font-medium text-slate-400 bg-slate-200 px-2 py-1 rounded">Penulis Terverifikasi</span>
                </div>
              </div>
            </div>

            {/* Video Gallery Section */}
            {videoUrls.length > 0 && (
              <div className="mb-12">
                <h3 className="text-2xl font-bold text-[#0F172A] mb-6 flex items-center gap-2">
                  <span className="text-3xl">🎥</span> Galeri Video
                </h3>
                <div className={`grid grid-cols-1 ${videoUrls.length > 1 ? 'md:grid-cols-2' : ''} gap-6`}>
              {videoUrls.map((rawUrl, index) => {
                const url = typeof rawUrl === 'string' ? rawUrl : String(rawUrl ?? '');
                let embedUrl = null;
                let isTikTok = false;
                let isShorts = false;

                // Helper to extract YouTube ID
                const getYoutubeId = (url: string) => {
                    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=|shorts\/)([^#&?]*).*/;
                    const match = url.match(regExp);
                    return (match && match[2].length === 11) ? match[2] : null;
                };

                const youtubeId = getYoutubeId(url);
                if (youtubeId) {
                    embedUrl = `https://www.youtube.com/embed/${youtubeId}`;
                    if (url.includes('shorts/')) {
                        isShorts = true;
                    }
                }
                // TikTok
                else if (url.includes('tiktok.com')) {
                    isTikTok = true;
                    // TikTok embed usually requires the video ID
                    const match = url.match(/video\/(\d+)/);
                    if (match && match[1]) {
                        embedUrl = `https://www.tiktok.com/embed/v2/${match[1]}`;
                    }
                }

                const isVertical = isTikTok || isShorts;

                if (!embedUrl) {
                    // Fallback for unknown video types or parsing failures
                    return (
                         <div key={index} className="aspect-video bg-gray-100 rounded-xl overflow-hidden shadow-lg flex items-center justify-center border border-gray-200">
                            <a href={url} target="_blank" rel="noopener noreferrer" className="flex flex-col items-center gap-2 text-gray-500 hover:text-[#0F766E] transition-colors">
                                <span className="text-3xl">🔗</span>
                                <span className="font-medium">Tonton Video</span>
                            </a>
                         </div>
                    );
                }

                return (
                  <div key={index} className={`bg-black rounded-xl overflow-hidden shadow-lg ${isVertical ? 'aspect-[9/16] max-w-[350px] mx-auto w-full' : 'aspect-video w-full'}`}>
                    <iframe
                      src={embedUrl}
                      title={`Video ${index + 1}`}
                      className="w-full h-full"
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                      allowFullScreen
                    />
                  </div>
                );
              })}
            </div>
              </div>
            )}

            {/* Author Box */}
            <div className="bg-white border border-[#E6EAE8] rounded-xl p-6 mb-8 flex flex-col sm:flex-row items-center sm:items-start gap-4 text-center sm:text-left shadow-sm">
              <div className="w-16 h-16 bg-[#F0FDF4] rounded-full flex items-center justify-center text-2xl font-bold text-[#166534] shrink-0 border border-[#DCFCE7]">
                {news.author?.name?.charAt(0).toUpperCase() || 'A'}
              </div>
              <div className="flex-1">
                <div className="text-xs text-[#6B7280] font-bold mb-1 uppercase tracking-wider">Ditulis Oleh</div>
                <h3 className="text-lg font-bold text-[#0F172A] mb-2">
                  {news.author?.name || 'Admin IKA UNIMED'}
                </h3>
                <p className="text-[#374151] text-sm leading-relaxed">
                  Kontributor aktif di portal berita IKA UNIMED. Menyajikan informasi terkini seputar alumni dan kampus Universitas Negeri Medan.
                </p>
              </div>
            </div>

            {/* Share Section */}
            <div className="bg-[#F8FAF9] border border-[#E6EAE8] rounded-lg p-5 mb-8">
              <h3 className="font-bold text-[#0F172A] mb-4 text-sm flex items-center gap-2">
                <span className="text-xl">📢</span> Bagikan Berita Ini:
              </h3>

              <div className="flex flex-wrap gap-2">
                {/* WhatsApp */}
                <a
                  href={shareLinks.whatsapp}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium
                             bg-green-50 text-green-700 hover:bg-green-100 transition"
                >
                  <span className="text-base">📱</span>
                  <span>WhatsApp</span>
                </a>

                {/* Facebook */}
                <a
                  href={shareLinks.facebook}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium
                             bg-blue-50 text-blue-700 hover:bg-blue-100 transition"
                >
                  <span className="text-base">👍</span>
                  <span>Facebook</span>
                </a>

                {/* Twitter / X */}
                <a
                  href={shareLinks.twitter}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium
                             bg-gray-100 text-gray-800 hover:bg-gray-200 transition"
                >
                  <span className="text-base">𝕏</span>
                  <span>Twitter</span>
                </a>

                {/* Copy Link */}
                <button
                  onClick={() => {
                    navigator.clipboard.writeText(shareUrl);
                    setShowToast(true);
                    setTimeout(() => setShowToast(false), 3000);
                  }}
                  className="flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium
                             bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
                >
                  <span className="text-base">🔗</span>
                  <span>Salin Link</span>
                </button>
              </div>
            </div>

            {/* Toast Notification */}
            {showToast && (
              <div className="fixed bottom-4 right-4 bg-[#0F172A] text-white px-4 py-3 rounded-lg shadow-lg z-50 flex items-center gap-3 animate-bounce">
                <span className="text-green-400 font-bold">✓</span>
                <span className="font-medium text-sm">Link berhasil disalin!</span>
              </div>
            )}


            {/* Back to news */}
            <Link
              href={route('news.index')}
              className="inline-flex items-center gap-2 text-[#0F766E] font-medium hover:text-[#115E59] transition-colors"
            >
              <span>←</span> Kembali ke Berita Lainnya
            </Link>
          </article>
        </NewsLayout>

        {/* Related News - Full width section */}
        {relatedNews && relatedNews.length > 0 && (
          <section className="bg-white border-t border-[#E6EAE8] py-12 sm:py-16">
            <div className="w-full">
              <div className="mx-auto px-4 sm:px-6 lg:px-0 max-w-[1440px]">
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
                  <div className="hidden lg:block lg:col-span-1"></div>
                  <div className="col-span-1 lg:col-span-7 xl:col-span-6">
                    <h2 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                      Berita Terkait
                    </h2>
                    <p className="text-gray-600 mb-8">
                      Artikel lain yang mungkin kamu tertarik
                    </p>

                    <div className="space-y-4 sm:space-y-6">
                      {relatedNews.slice(0, 3).map(item => (
                        <NewsCard key={item.id} {...item} />
                      ))}
                    </div>
                  </div>
                  <div className="col-span-1 lg:col-span-4 xl:col-span-5"></div>
                </div>
              </div>
            </div>
          </section>
        )}
      </MainLayout>
    </>
  );
}
