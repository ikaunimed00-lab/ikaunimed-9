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
  created_at: string;
  author?: {
    name?: string;
  };
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string;
  icon?: string;
}

interface CategoryShowProps {
  category: Category;
  news: {
    data: NewsItem[];
    current_page: number;
    last_page: number;
    links: PaginationLink[];
  };
}

const CategoryShow = ({ category, news }: CategoryShowProps) => {
  return (
    <>
      <Head>
        <title>{`${category.name} - IKA UNIMED Portal Berita`}</title>
        <meta name="description" content={`Berita tentang ${category.name} dari Ikatan Alumni UNIMED`} />
        <meta property="og:title" content={`${category.name} - IKA UNIMED`} />
        <meta property="og:description" content={category.description} />
        <meta property="og:type" content="website" />
      </Head>

      <MainLayout variant="full">
          {/* Category Header */}
          <section className="bg-[#0F766E] text-white py-12 sm:py-16">
            <div className="container mx-auto px-4 max-w-7xl">
              <div className="flex items-center gap-4 mb-4">
                <span className="text-5xl sm:text-6xl">{category.icon || '📌'}</span>
                <div>
                  <h1 className="text-4xl sm:text-5xl font-bold">{category.name}</h1>
                </div>
              </div>

              {category.description && (
                <p className="text-white/90 text-lg max-w-3xl">
                  {category.description}
                </p>
              )}

              <div className="mt-6">
                <Link
                  href={route('news.index')}
                  className="inline-flex items-center gap-2 bg-white text-[#0F766E] px-4 py-2 rounded-lg font-medium hover:bg-[#F8FAF9] transition-colors"
                >
                  <span>←</span> Kembali ke Semua Berita
                </Link>
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
                Semua Berita {category.name}
              </h2>
              {news.data.length > 0 ? (
                <p className="text-[#6B7280] mb-8">
                  {news.data.length} artikel ditemukan dalam kategori ini
                </p>
              ) : (
                <p className="text-[#6B7280] mb-8">
                  Saat ini belum ada artikel yang dipublikasikan dalam kategori ini.
                </p>
              )}

              {news.data.length > 0 ? (
                <>
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    {news.data.map(item => (
                      <NewsCard key={item.id} {...item} />
                    ))}
                  </div>

                  {/* Pagination */}
                  {news.last_page > 1 && (
                    <Pagination
                      links={news.links}
                      current_page={news.current_page}
                      last_page={news.last_page}
                    />
                  )}
                </>
              ) : (
                <div className="bg-white border border-dashed border-[#CBD5E1] rounded-xl py-16 px-6 text-center">
                  <div className="w-16 h-16 mx-auto mb-5 rounded-full bg-[#F0FDFA] flex items-center justify-center">
                    <svg className="w-8 h-8 text-[#0F766E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.8} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  <h3 className="text-lg font-bold text-[#0F172A] mb-2">
                    Belum ada berita untuk kategori {category.name}
                  </h3>
                  <p className="text-[#6B7280] max-w-md mx-auto mb-6">
                    Redaksi belum mempublikasikan artikel pada kategori ini. Silakan telusuri
                    kategori lain atau kembali ke daftar berita utama.
                  </p>
                  <Link
                    href={route('news.index')}
                    className="inline-flex items-center gap-2 bg-[#0F766E] text-white font-semibold px-5 py-2.5 rounded-lg hover:bg-[#115E59] transition-colors"
                  >
                    <span>←</span>
                    <span>Kembali ke Semua Berita</span>
                  </Link>
                </div>
              )}
            </div>
          </section>
      </MainLayout>
    </>
  );
};

export default CategoryShow;
