import { CSSProperties, useEffect } from 'react';

interface AdsenseUnitProps {
  slot: string;
  /**
   * AdSense Publisher ID (`ca-pub-XXXXXXXXXXXXXXXX`). Saat truthy, dirender
   * sebagai atribut `data-ad-client` pada `<ins>`. Kebijakan kontrak:
   * fallback ke mode auto-infer (dari URL script `?client=...` di app.blade.php)
   * saat tidak diset — supaya komponen tetap aman dipakai sebelum Phase B.
   */
  client?: string | null;
  style?: CSSProperties;
  format?: string;
  layout?: string;
  fullWidth?: boolean;
}

export default function AdsenseUnit({
  slot,
  client,
  style,
  format = 'auto',
  layout,
  fullWidth = true,
}: AdsenseUnitProps) {
  useEffect(() => {
    try {
      if (typeof window !== 'undefined' && (window as any).adsbygoogle) {
        (window as any).adsbygoogle.push({});
      }
    } catch {
    }
  }, [slot, client, format, layout, fullWidth]);

  const props: Record<string, string> = {
    'data-ad-slot': slot,
  };

  if (client) {
    props['data-ad-client'] = client;
  }

  if (format) {
    props['data-ad-format'] = format;
  }

  if (layout) {
    props['data-ad-layout'] = layout;
  }

  if (fullWidth) {
    props['data-full-width-responsive'] = 'true';
  }

  return <ins className="adsbygoogle" style={style} {...props} />;
}

