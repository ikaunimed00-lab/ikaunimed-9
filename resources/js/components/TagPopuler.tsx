import React from 'react';

interface PopularTag {
  id: number;
  name: string;
  slug: string;
  count: number;
}

interface TagPopulerProps {
  maxTags?: number;
  tags?: PopularTag[];
}

export const TagPopuler: React.FC<TagPopulerProps> = ({ maxTags = 12, tags = [] }) => {
  const list = tags.slice(0, maxTags);

  if (!list.length) {
    return null;
  }

  return (
    <div className="space-y-3">
      {/* Header */}
      <div className="flex items-center justify-between px-1">
        <h3 className="text-lg font-bold text-[#0F172A]">#️⃣ Topik Populer Alumni</h3>
        <span className="text-sm text-[#6B7280]">
          Tag yang paling sering dipakai di berita alumni
        </span>
      </div>

      {/* Tags grid */}
      <div className="flex flex-wrap gap-2">
        {list.map((tag) => (
          <button
            key={tag.id}
            className="px-3 py-1.5 bg-white border border-[#E6EAE8] rounded-full text-xs font-medium text-[#0F172A] hover:border-[#0F766E] hover:text-[#0F766E] hover:bg-[#0F766E]/5 transition-all group"
          >
            <span>#{tag.name}</span>
            <span className="text-[#6B7280] group-hover:text-[#0F766E] transition-colors">
              {' '}
              ({tag.count})
            </span>
          </button>
        ))}
      </div>

      {/* View all button */}
      <div className="pt-2">
        <button className="w-full px-4 py-2 bg-[#F8FAF9] border border-[#E6EAE8] text-[#0F172A] font-medium rounded-lg hover:border-[#0F766E] hover:bg-[#0F766E]/5 transition-colors text-sm">
          Lihat Semua Tag
        </button>
      </div>
    </div>
  );
};

export default TagPopuler;
