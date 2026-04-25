import React from 'react';
import { Link } from '@inertiajs/react';

interface PopularNewsItem {
  id: number;
  title: string;
  slug: string;
  image?: string | null;
  view_count?: number;
  published_at?: string | null;
}

interface BeritaPopulerProps {
  variant?: 'list' | 'grid';
  maxItems?: number;
  items?: PopularNewsItem[];
}

const formatViewCount = (n: number): string => {
  if (n >= 1000) return `${(n / 1000).toFixed(1)}K`;
  return n.toString();
};

/**
 * BeritaPopuler - daftar berita terpopuler berdasarkan view_count.
 *
 * Kontrak data (lihat NewsController::index → $popularNews):
 *   items = News[] hasil scope `published()->trending()` (top 10 by view_count).
 *   Bentuk per item: { id, title, slug, image?, view_count?, published_at? }.
 *   - Thumbnail dirender dari `image` (URL absolut hasil News::buildImageUrl).
 *     Jika null, fallback ke ikon placeholder — BUKAN kotak abu-abu kosong.
 */
export const BeritaPopuler: React.FC<BeritaPopulerProps> = ({
  variant = 'list',
  maxItems = 5,
  items = [],
}) => {
  const list = items.slice(0, maxItems);

  if (!list.length) {
    return null;
  }

  return (
    <div className="space-y-3">
      {/* Header */}
      <div className="flex items-center justify-between px-1">
        <h3 className="text-lg font-bold text-[#0F172A]">🔥 Berita Alumni Terpopuler</h3>
        <span className="text-sm text-[#6B7280]">
          Berdasarkan jumlah pembaca di portal IKA UNIMED
        </span>
      </div>

      {variant === 'list' ? (
        <div className="space-y-3">
          {list.map((item, index) => (
            <Link
              key={item.id}
              href={route('news.show', item.slug)}
              className="flex gap-3 p-3 bg-white border border-[#E6EAE8] rounded-lg hover:border-[#0F766E] transition-colors cursor-pointer group"
            >
              <div className="flex-shrink-0 w-20 h-20 rounded overflow-hidden bg-[#F0FDFA] flex items-center justify-center">
                {item.image ? (
                  <img
                    src={item.image}
                    alt={item.title}
                    loading="lazy"
                    decoding="async"
                    className="w-full h-full object-cover"
                  />
                ) : (
                  <span className="text-2xl opacity-30" aria-hidden="true">📰</span>
                )}
              </div>

              <div className="flex-1 min-w-0">
                <h4 className="font-semibold text-[#0F172A] line-clamp-2 text-sm group-hover:text-[#0F766E] transition-colors">
                  {item.title}
                </h4>
                <p className="text-xs text-[#6B7280] mt-2">
                  👁️ {formatViewCount(item.view_count ?? 0)} views
                </p>
              </div>

              <div className="flex-shrink-0 w-8 h-8 bg-[#0F766E] text-white rounded-full flex items-center justify-center font-bold text-sm">
                {index + 1}
              </div>
            </Link>
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
          {list.map((item, index) => (
            <Link
              key={item.id}
              href={route('news.show', item.slug)}
              className="bg-white border border-[#E6EAE8] rounded-lg overflow-hidden hover:border-[#0F766E] transition-colors cursor-pointer group"
            >
              <div className="w-full aspect-square bg-[#F0FDFA] flex items-center justify-center overflow-hidden">
                {item.image ? (
                  <img
                    src={item.image}
                    alt={item.title}
                    loading="lazy"
                    decoding="async"
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                ) : (
                  <span className="text-3xl opacity-30" aria-hidden="true">📰</span>
                )}
              </div>

              <div className="p-3">
                <h4 className="font-semibold text-[#0F172A] line-clamp-2 text-sm group-hover:text-[#0F766E] transition-colors">
                  {item.title}
                </h4>
                <p className="text-xs text-[#6B7280] mt-1">
                  👁️ {formatViewCount(item.view_count ?? 0)}
                </p>
                <span className="inline-flex mt-2 w-8 h-8 bg-[#0F766E] text-white rounded-full items-center justify-center font-bold text-xs">
                  {index + 1}
                </span>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
};

export default BeritaPopuler;
