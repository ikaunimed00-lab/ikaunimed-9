import { ReactNode } from 'react';
import AdsenseUnit from './AdsenseUnit';

interface AdInlineProps {
  position?: string;
  className?: string;
  children?: ReactNode;
  slot?: string;
}
export default function AdInline({ position = 'middle', className = '', children = null, slot }: AdInlineProps) {
  return (
    <div className={`ad-inline-container my-8 ${className}`} data-position={position}>
      <div className="ad-inline bg-gray-50 rounded-lg border border-gray-200 py-8 px-4 flex items-center justify-center overflow-hidden">
        <div className="ad-placeholder text-center text-gray-400 w-full">
          {children ? children : slot ? <AdsenseUnit slot={slot} /> : null}
        </div>
      </div>
    </div>
  );
}
