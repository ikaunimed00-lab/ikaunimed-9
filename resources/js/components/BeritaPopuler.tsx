import React from 'react';
import { Link } from '@inertiajs/react';

interface PopularNewsItem {
  id: number;
  title: string;
  slug: string;
  view_count?: number;
}

interface BeritaPopulerProps {
  variant?: 'list' | 'grid';
  maxItems?: number;
  items?: PopularNewsItem[];
}

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

      {/* Content */}
      {variant === 'list' ? (
        <div className="space-y-3">
          {list.map((item, index) => (
            <Link
              key={item.id}
              href={route('news.show', item.slug)}
              className="flex gap-3 p-3 bg-white border border-[#E6EAE8] rounded-lg hover:border-[#0F766E] transition-colors cursor-pointer group"
            >
              {/* Thumbnail placeholder */}
              <div className="flex-shrink-0 w-20 h-20 bg-[#E6EAE8] rounded" />

              {/* Content */}
              <div className="flex-1 min-w-0">
                <h4 className="font-semibold text-[#0F172A] line-clamp-2 text-sm group-hover:text-[#0F766E] transition-colors">
                  {item.title}
                </h4>
                <p className="text-xs text-[#6B7280] mt-2">
                  👁️ {(((item.view_count ?? 0) / 1000) || 0).toFixed(1)}K views
                </p>
              </div>

              {/* Ranking */}
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
              {/* Thumbnail placeholder */}
              <div className="w-full aspect-square bg-[#E6EAE8]" />

              {/* Content */}
              <div className="p-3">
                <h4 className="font-semibold text-[#0F172A] line-clamp-2 text-sm group-hover:text-[#0F766E] transition-colors">
                  {item.title}
                </h4>
                <p className="text-xs text-[#6B7280] mt-1">
                  👁️ {(((item.view_count ?? 0) / 1000) || 0).toFixed(1)}K
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
