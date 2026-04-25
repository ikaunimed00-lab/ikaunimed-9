import React from 'react';
import { Link } from '@inertiajs/react';
import { HorizontalScroll } from './HorizontalScroll';

interface EditorsPickItem {
  id: number;
  title: string;
  slug: string;
}

interface EditorsPicksProps {
  items?: EditorsPickItem[];
}

/**
 * EditorsPicks - Editor's curated picks dengan horizontal scroll
 * Data diisi dari daftar berita populer / kurasi editor.
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
          <div className="w-full aspect-video bg-[#E6EAE8] animate-pulse" />

          {/* Content */}
          <div className="p-4">
            <h4 className="font-semibold text-[#0F172A] line-clamp-2 text-sm group-hover:text-[#0F766E] transition-colors">
              {pick.title}
            </h4>
            <p className="text-xs text-[#6B7280] mt-2">2 hari lalu</p>
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
