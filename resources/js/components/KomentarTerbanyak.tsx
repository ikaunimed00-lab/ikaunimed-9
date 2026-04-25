import React from 'react';

/**
 * KomentarTerbanyak - Section untuk most commented articles
 * Menampilkan artikel dengan most comments
 */
interface KomentarTerbanyakProps {
  maxItems?: number;
}

export const KomentarTerbanyak: React.FC<KomentarTerbanyakProps> = ({ maxItems = 5 }) => {
  const mockCommented = Array.from({ length: maxItems }, (_, i) => ({
    id: i + 1,
    title: `Artikel Banyak Komentar ${i + 1}`,
    comments: Math.floor(Math.random() * 200) + 10,
    lastComment: `${Math.floor(Math.random() * 24)} jam lalu`,
  }));

  return (
    <div className="space-y-3">
      {/* Header */}
      <div className="flex items-center justify-between px-1">
        <h3 className="text-lg font-bold text-[#0F172A]">💬 Diskusi Terbanyak</h3>
        <span className="text-sm text-[#6B7280]">
          Ruang diskusi paling aktif di berita alumni
        </span>
      </div>

      {/* List */}
      <div className="space-y-2">
        {mockCommented.map((item) => (
          <div
            key={item.id}
            className="p-3 bg-white border border-[#E6EAE8] rounded-lg hover:border-[#0F766E] transition-colors cursor-pointer group"
          >
            <div className="flex items-start justify-between gap-2">
              <h4 className="font-semibold text-[#0F172A] text-sm line-clamp-2 group-hover:text-[#0F766E] transition-colors flex-1">
                {item.title}
              </h4>
              <span className="flex-shrink-0 text-xs font-bold bg-[#0F766E]/10 text-[#0F766E] px-2 py-1 rounded-full">
                {item.comments}
              </span>
            </div>
            <p className="text-xs text-[#6B7280] mt-2">
              Komentar terakhir: {item.lastComment}
            </p>
          </div>
        ))}
      </div>
    </div>
  );
};

export default KomentarTerbanyak;
