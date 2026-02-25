import { Head, Link } from '@inertiajs/react';
import React from 'react';
import MainLayout from '@/components/MainLayout';
import NewsCard from '@/components/NewsCard';
import CategoryNavigation from '@/components/CategoryNavigation';
import Pagination from '@/components/Pagination';

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
  organization?: {
    name?: string;
    slug?: string;
  } | null;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface NewsOrganizationProps {
  news: {
    data: NewsItem[];
    current_page: number;
    last_page: number;
    links: PaginationLink[];
  };
  scope: 'pp' | 'dpw' | 'dpc';
  orgSlug?: string | null;
}

const scopeLabelMap: Record<'pp' | 'dpw' | 'dpc', string> = {
  pp: 'Pimpinan Pusat',
  dpw: 'Dewan Pimpinan Wilayah',
  dpc: 'Dewan Pimpinan Cabang',
};

const NewsOrganization = ({ news, scope, orgSlug }: NewsOrganizationProps) => {
  const scopeLabel = scopeLabelMap[scope] ?? scope.toUpperCase();
  const hasNews = news.data.length > 0;

  const organizationName =
    news.data[0]?.organization?.name ||
    (orgSlug ? orgSlug.replace(/-/g, ' ').toUpperCase() : null);

  const pageTitle = organizationName
    ? `Berita Organisasi ${scope.toUpperCase()} - ${organizationName}`
    : `Berita Organisasi ${scope.toUpperCase()}`;

  const description = organizationName
    ? `Kumpulan berita resmi dari ${scopeLabel} ${organizationName} IKA UNIMED.`
    : `Kumpulan berita resmi dari ${scopeLabel} IKA UNIMED.`;

  return (
    <>
      <Head>
        <title>{pageTitle}</title>
        <meta name="description" content={description} />
        <meta property="og:title" content={pageTitle} />
        <meta property="og:description" content={description} />
        <meta property="og:type" content="website" />
      </Head>

      <MainLayout variant="full">
          {/* Header Organisasi */}
          <section className="bg-[#0F766E] text-white py-12 sm:py-16">
            <div className="container mx-auto px-4 max-w-7xl">
              <div className="flex flex-col gap-4">
                <nav className="flex items-center gap-2 text-sm text-white/80 mb-2 overflow-x-auto">
                  <Link
                    href={route('news.index')}
                    className="hover:text-white whitespace-nowrap"
                  >
                    Berita
                  </Link>
                  <span>/</span>
                  <span className="whitespace-nowrap">
                    Organisasi {scope.toUpperCase()}
                  </span>
                  {organizationName && (
                    <>
                      <span>/</span>
                      <span className="font-semibold whitespace-nowrap">
                        {organizationName}
                      </span>
                    </>
                  )}
                </nav>
                <div>
                  <p className="text-sm font-semibold uppercase tracking-wide text-white/80">
                    Portal Berita Organisasi
                  </p>
                  <h1 className="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight">
                    {organizationName || `Berita ${scopeLabel}`}
                  </h1>
                </div>

                <p className="text-white/90 text-base sm:text-lg max-w-3xl">
                  {description}
                </p>

                <div className="mt-4 flex flex-wrap gap-3">
                  <Link
                    href={route('news.index')}
                    className="inline-flex items-center gap-2 bg-white text-[#0F766E] px-4 py-2 rounded-lg font-medium hover:bg-[#F8FAF9] transition-colors"
                  >
                    <span>←</span>
                    <span>Ke Semua Berita</span>
                  </Link>
                  <Link
                    href={route('organizations.index')}
                    className="inline-flex items-center gap-2 bg-white/10 text-white px-4 py-2 rounded-lg font-medium hover:bg-white/20 transition-colors"
                  >
                    <span>🏛️</span>
                    <span>Daftar Organisasi</span>
                  </Link>
                </div>
              </div>
            </div>
          </section>

          {/* Category Navigation */}
          <section className="bg-white border-b border-[#E6EAE8]">
            <div className="container mx-auto px-4 py-8 max-w-7xl">
              <CategoryNavigation />
            </div>
          </section>

          {/* News Grid */}
          <section className="py-12 sm:py-16">
            <div className="container mx-auto px-4 max-w-7xl">
              <h2 className="text-2xl sm:text-3xl font-bold text-[#0F172A] mb-2">
                Berita Organisasi
              </h2>
              <p className="text-[#6B7280] mb-8">
                {hasNews
                  ? `${news.data.length} artikel ditemukan untuk organisasi ini`
                  : 'Belum ada artikel organisasi yang dipublikasikan.'}
              </p>

              {hasNews ? (
                <>
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    {news.data.map((item) => (
                      <NewsCard key={item.id} {...item} />
                    ))}
                  </div>

                  {/* Pagination */}
                  {news.last_page > 1 && (
                    <div className="mt-10">
                      <Pagination
                        links={news.links}
                        current_page={news.current_page}
                        last_page={news.last_page}
                      />
                    </div>
                  )}
                </>
              ) : (
                <div className="py-20 text-center">
                  <svg
                    className="w-20 h-20 text-[#E6EAE8] mx-auto mb-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={1.5}
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                  </svg>
                  <p className="text-[#6B7280] text-lg mb-2">
                    Belum ada berita organisasi yang dapat ditampilkan.
                  </p>
                  <p className="text-[#9CA3AF] text-sm">
                    Silakan kembali beberapa saat lagi ketika organisasi sudah
                    mulai mempublikasikan berita.
                  </p>
                </div>
              )}
            </div>
          </section>
      </MainLayout>
    </>
  );
};

export default NewsOrganization;

