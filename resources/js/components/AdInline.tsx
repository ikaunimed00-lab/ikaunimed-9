import { ReactNode } from 'react';
import AdsenseUnit from './AdsenseUnit';

interface AdInlineProps {
  position?: string;
  className?: string;
  children?: ReactNode;
  slot?: string | null;
  /** Master switch dari `ads.enabled`. False → komponen mengembalikan null. */
  enabled?: boolean;
  provider?: string;
}

/**
 * AdInline - iklan di tengah artikel (in-article).
 *
 * KEBIJAKAN: hanya render <ins.adsbygoogle> jika
 *   enabled === true && provider === 'adsense' && slot truthy.
 * Tidak ada placeholder bila tidak aktif — artikel tetap mulus tanpa "kotak iklan kosong".
 */
export default function AdInline({
  position = 'middle',
  className = '',
  children = null,
  slot,
  enabled = false,
  provider = 'adsense',
}: AdInlineProps) {
  const shouldRender = Boolean(children) || (enabled && provider === 'adsense' && Boolean(slot));

  if (!shouldRender) {
    return null;
  }

  return (
    <div className={`ad-inline-container my-8 ${className}`} data-position={position}>
      <div className="ad-inline bg-gray-50 rounded-lg border border-gray-200 py-8 px-4 flex flex-col items-center justify-center overflow-hidden">
        <div className="text-[10px] uppercase tracking-wider text-[#6B7280] mb-2">
          Advertisement
        </div>
        <div className="ad-placeholder text-center text-gray-400 w-full">
          {children ? children : <AdsenseUnit slot={slot as string} />}
        </div>
      </div>
    </div>
  );
}
