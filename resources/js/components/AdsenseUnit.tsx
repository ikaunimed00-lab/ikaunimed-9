import { CSSProperties, useEffect } from 'react';

interface AdsenseUnitProps {
  slot: string;
  style?: CSSProperties;
  format?: string;
  layout?: string;
  fullWidth?: boolean;
}

export default function AdsenseUnit({
  slot,
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
  }, [slot, format, layout, fullWidth]);

  const props: Record<string, string> = {
    'data-ad-slot': slot,
  };

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

