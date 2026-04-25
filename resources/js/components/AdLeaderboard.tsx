import React from 'react';
import AdsenseUnit from './AdsenseUnit';

interface AdLeaderboardProps {
  className?: string;
  /** AdSense ad unit slot id; bila kosong → tidak render unit (placeholder dev only). */
  slot?: string | null;
  /** Master switch dari SiteSetting `ads_enabled`. False → komponen render null. */
  enabled?: boolean;
  /** Provider iklan; saat ini hanya 'adsense' yang dirender oleh komponen ini. */
  provider?: string;
}

/**
 * AdLeaderboard - top-of-page banner (970x90 desktop, responsive).
 *
 * KEBIJAKAN: hanya render <ins.adsbygoogle> jika
 *   enabled === true && provider === 'adsense' && slot truthy.
 * Saat tidak aktif → komponen mengembalikan `null` (tidak ada placeholder
 * "advertisement" yang menyita ruang) supaya halaman tetap bersih untuk
 * pengguna dan tidak melanggar kebijakan AdSense.
 */
const AdLeaderboard: React.FC<AdLeaderboardProps> = ({
  className = '',
  slot = null,
  enabled = false,
  provider = 'adsense',
}) => {
  const shouldRender = enabled && provider === 'adsense' && Boolean(slot);

  if (!shouldRender) {
    return null;
  }

  return (
    <div className={`w-full bg-[#F8FAF9] border-b border-[#E6EAE8] py-4 ${className}`}>
      <div className="mx-auto px-4 sm:px-6 lg:px-8 max-w-[1440px]">
        <div className="flex flex-col items-center justify-center">
          <div className="text-[10px] uppercase tracking-wider text-[#6B7280] mb-1">Advertisement</div>
          <div className="w-full max-w-[970px] min-h-[90px] flex items-center justify-center">
            <AdsenseUnit
              slot={slot as string}
              format="auto"
              style={{ display: 'block', width: '100%' }}
            />
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdLeaderboard;
