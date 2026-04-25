import React from 'react';
import { Link } from '@inertiajs/react';

interface OpinionColumnItem {
  id: number;
  title: string;
  slug: string;
  author?: string | null;
  category?: string | null;
  published_at?: string | null;
}

interface KolumOpiniProps {
  maxItems?: number;
  items?: OpinionColumnItem[];
}

const formatRelativeDate = (iso?: string | null): string | null => {
  if (!iso) return null;
  const ts = new Date(iso).getTime();
  if (Number.isNaN(ts)) return null;
  const dayMs = 24 * 60 * 60 * 1000;
  const days = Math.floor((Date.now() - ts) / dayMs);
  if (days <= 0) return 'Hari ini';
  if (days === 1) return '1 hari lalu';
  if (days < 7) return `${days} hari lalu`;
  if (days < 30) return `${Math.floor(days / 7)} minggu lalu`;
  return new Date(iso).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
};

/**
 * KolumOpini - blok kolom & opini alumni di kolom utama News/Index.
 *
 * Kontrak data (lihat NewsController::index → $opinionColumns):
 *   items = News[] dari kategori `opini`, urut DESC by published_at.
 *   Bentuk per item: { id, title, slug, author?, category?, published_at? (ISO) }.
 *   Field `published_at` dipakai untuk relative-date — jika null, tanggal
 *   tidak ditampilkan (BUKAN diganti dummy "2 hari lalu" agar tidak menyesatkan).
 */
export const KolumOpini: React.FC<KolumOpiniProps> = ({ maxItems = 4, items = [] }) => {
  const columns = items.slice(0, maxItems);

  if (!columns.length) {
    return null;
  }

  return (
    <div className="space-y-3 mb-8">
      {/* Header */}
      <div className="flex items-center justify-between px-1">
        <h3 className="text-lg font-bold text-[#0F172A]">📝 Kolom & Opini Alumni</h3>
        <span className="text-sm text-[#0F766E] font-medium">
          Suara dan gagasan alumni IKA UNIMED
        </span>
      </div>

      {/* Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {columns.map((column) => {
          const relativeDate = formatRelativeDate(column.published_at);

          return (
            <Link
              key={column.id}
              href={route('news.show', column.slug)}
              className="bg-white border border-[#E6EAE8] rounded-lg p-4 hover:border-[#0F766E] transition-colors cursor-pointer group block"
            >
              <div className="inline-block px-2 py-1 bg-[#0F766E]/10 text-[#0F766E] text-xs font-semibold rounded mb-3">
                {column.category || 'Opini'}
              </div>

              <p className="text-xs text-[#6B7280] font-medium mb-2">
                oleh{' '}
                <span className="text-[#0F172A] font-semibold">
                  {column.author || 'Penulis'}
                </span>
              </p>

              <h4 className="font-semibold text-[#0F172A] line-clamp-2 text-base group-hover:text-[#0F766E] transition-colors">
                {column.title}
              </h4>

              <div className="mt-3 pt-3 border-t border-[#E6EAE8] flex items-center justify-between text-xs text-[#6B7280]">
                <span>{relativeDate ?? ''}</span>
                <span className="text-[#0F766E] font-medium group-hover:text-[#115E59]">
                  Baca →
                </span>
              </div>
            </Link>
          );
        })}
      </div>
    </div>
  );
};

export default KolumOpini;
