import React from 'react';
import { Link } from '@inertiajs/react';

/**
 * KomentarTerbanyak - artikel dengan diskusi paling aktif.
 *
 * Kontrak data (TODO Fase 2):
 *   items = News[] yang diurutkan DESC oleh `comments_count`. Saat sistem
 *   komentar belum ada, prop `items` tidak dikirim dari controller dan blok
 *   ini menampilkan placeholder ramah — BUKAN data acak agar pembaca tidak
 *   tertipu angka bohong.
 *
 * Begitu sistem komentar tersedia, kirim items dengan bentuk:
 *   { id, title, slug, comments_count, last_commented_at }.
 */
interface DiscussionItem {
  id: number;
  title: string;
  slug: string;
  comments_count: number;
  last_commented_at?: string | null;
}

interface KomentarTerbanyakProps {
  maxItems?: number;
  items?: DiscussionItem[];
}

export const KomentarTerbanyak: React.FC<KomentarTerbanyakProps> = ({
  maxItems = 5,
  items = [],
}) => {
  const list = items.slice(0, maxItems);
  const hasData = list.length > 0;

  return (
    <div className="space-y-3">
      {/* Header */}
      <div className="flex items-center justify-between px-1">
        <h3 className="text-lg font-bold text-[#0F172A]">💬 Diskusi Terbanyak</h3>
        <span className="text-sm text-[#6B7280]">
          Ruang diskusi paling aktif di berita alumni
        </span>
      </div>

      {hasData ? (
        <div className="space-y-2">
          {list.map((item) => (
            <Link
              key={item.id}
              href={route('news.show', item.slug)}
              className="block p-3 bg-white border border-[#E6EAE8] rounded-lg hover:border-[#0F766E] transition-colors group"
            >
              <div className="flex items-start justify-between gap-2">
                <h4 className="font-semibold text-[#0F172A] text-sm line-clamp-2 group-hover:text-[#0F766E] transition-colors flex-1">
                  {item.title}
                </h4>
                <span className="flex-shrink-0 text-xs font-bold bg-[#0F766E]/10 text-[#0F766E] px-2 py-1 rounded-full">
                  {item.comments_count}
                </span>
              </div>
              {item.last_commented_at && (
                <p className="text-xs text-[#6B7280] mt-2">
                  Komentar terakhir: {new Date(item.last_commented_at).toLocaleString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit',
                  })}
                </p>
              )}
            </Link>
          ))}
        </div>
      ) : (
        <div className="bg-white border border-dashed border-[#CBD5E1] rounded-lg p-5 text-center">
          <div className="w-12 h-12 mx-auto mb-3 rounded-full bg-[#F0FDFA] flex items-center justify-center text-xl">
            💬
          </div>
          <p className="text-sm font-semibold text-[#0F172A] mb-1">
            Diskusi alumni segera hadir
          </p>
          <p className="text-xs text-[#6B7280]">
            Modul komentar sedang disiapkan. Sementara, silakan jelajahi berita
            terbaru di kolom utama.
          </p>
        </div>
      )}
    </div>
  );
};

export default KomentarTerbanyak;
