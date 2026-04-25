import React from 'react';
import { Link } from '@inertiajs/react';
import { HorizontalScroll } from './HorizontalScroll';

interface EditorsPickItem {
  id: number;
  title: string;
  slug: string;
  image?: string | null;
  published_at?: string;
}

interface EditorsPicksProps {
  items?: EditorsPickItem[];
}

const formatRelativeDate = (iso?: string): string => {
  if (!iso) return '';
  const then = new Date(iso).getTime();
  if (Number.isNaN(then)) return '';
  const diffMs = Date.now() - then;
  const dayMs = 24 * 60 * 60 * 1000;
  const days = Math.floor(diffMs / dayMs);
  if (days <= 0) return 'Hari ini';
  if (days === 1) return '1 hari lalu';
  if (days < 7) return `${days} hari lalu`;
  return new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

/**
 * EditorsPicks - kurasi editorial dengan horizontal scroll.
 *
 * Kontrak data (lihat NewsController::index → $editorsPicks):
 *   - Berita ber-image terbaru, EXCLUDE id yang sudah tampil di hero & top-3
 *     trending (BeritaPopuler) agar tidak duplikasi blok.
 *   - Bukan ranking otomatis: blok ini secara semantik mewakili "pilihan editor"
 *     dan bisa diganti ke flag `is_editors_pick` di kemudian hari tanpa
 *     mengubah props frontend.
 */
export const EditorsPicks: React.FC<EditorsPicksProps> = ({ items = [] }) => {
  const picks = items.slice(0, 6);

  if (!picks.length) {
    return null;
  }

  return (
    <HorizontalScroll
      title="📌 Pilihan Editor IKA UNIMED"
      viewAllLink={route('news.index')}
      showArrows={true}
    >
      {picks.map((pick) => (
        <Link
          key={pick.id}
          href={route('news.show', pick.slug)}
          className="flex-shrink-0 w-56 bg-white border border-[#E6EAE8] rounded-lg overflow-hidden hover:border-[#0F766E] transition-colors cursor-pointer group relative block"
        >
          {/* Thumbnail */}
          <div className="w-full aspect-video bg-[#F0FDFA] overflow-hidden">
            {pick.image ? (
              <img
                src={pick.image}
                alt={pick.title}
                loading="lazy"
                decoding="async"
                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
            ) : (
              <div className="w-full h-full flex items-center justify-center text-3xl opacity-30">📰</div>
            )}
          </div>

          {/* Content */}
          <div className="p-4">
            <h4 className="font-semibold text-[#0F172A] line-clamp-2 text-sm group-hover:text-[#0F766E] transition-colors">
              {pick.title}
            </h4>
            <p className="text-xs text-[#6B7280] mt-2">{formatRelativeDate(pick.published_at) || 'Pilihan editor'}</p>
          </div>

          {/* Pick badge */}
          <div className="absolute top-2 right-2 bg-[#0F766E] text-white text-xs font-bold px-2 py-1 rounded">
            ⭐
          </div>
        </Link>
      ))}
    </HorizontalScroll>
  );
};

export default EditorsPicks;
