import React from 'react';
import { Link } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { id } from 'date-fns/locale';
import { formatNumber } from '@/lib/utils';

interface NewsCardProps {
  id: number;
  title: string;
  excerpt: string;
  slug: string;
  image: string | null;
  view_count: number;
  published_at: string;
  author?: {
    name: string;
  };
  organization?: {
    id: number;
    name: string;
    type: 'pp' | 'dpw' | 'dpc';
    slug: string;
  };
  categories?: Array<{
    name: string;
    slug: string;
  }>;
  reading_time?: string;
  size?: 'sm' | 'md' | 'lg';
  compact?: boolean;
}

const NewsCard: React.FC<NewsCardProps> = ({
  title,
  excerpt,
  slug,
  image,
  view_count,
  published_at,
  author,
  organization,
  categories,
  reading_time,
  size = 'md',
  compact = false,
}) => {
  const [imageError, setImageError] = React.useState(false);
  const [imageLoaded, setImageLoaded] = React.useState(false);

  const getImageUrl = (img: string | null): string | null => {
    if (!img) return null;
    // Handle various image path formats
    if (img.startsWith('http')) return img;
    if (img.startsWith('/')) return img;
    return `/storage/${img}`;
  };

  const imgUrl = getImageUrl(image);

  const badgeColor = {
    pp: "bg-indigo-100 text-indigo-700",
    dpw: "bg-emerald-100 text-emerald-700",
    dpc: "bg-sky-100 text-sky-700",
  };

  const imageHeight = {
    sm: 'h-40',
    md: 'h-48 sm:h-56',
    lg: 'h-64 sm:h-72',
  };

  const titleSize = {
    sm: 'text-base',
    md: 'text-lg',
    lg: 'text-xl sm:text-2xl',
  };

  return (
    <Link href={route('news.show', slug)}>
      <article className="bg-white rounded-lg overflow-hidden hover:border-[#0F766E] transition-colors duration-300 border border-[#E6EAE8] group cursor-pointer h-full flex flex-col">
        {/* Image Container dengan lazy loading */}
        <div className={`relative overflow-hidden bg-[#E6EAE8] ${imageHeight[size]}`}>
          {imgUrl && !imageError ? (
            <>
              {!imageLoaded && (
                <div className="absolute inset-0 bg-gray-200 animate-pulse" />
              )}
              <img
                src={imgUrl}
                alt={title}
                loading="lazy"
                onLoad={() => setImageLoaded(true)}
                onError={() => setImageError(true)}
                className={`w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 ${
                  imageLoaded ? 'opacity-100' : 'opacity-0'
                }`}
              />
            </>
          ) : (
            <div className="w-full h-full flex items-center justify-center bg-[#E6EAE8]">
              <svg className="w-12 h-12 text-[#6B7280]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          )}
          
          {/* Badge Kategori di corner */}
          {categories && categories.length > 0 && (
            <div className="absolute top-3 left-3 bg-[#0F766E] text-white px-2 py-1 rounded text-xs font-semibold z-10">
              {categories[0].name}
            </div>
          )}
          
          {/* View count badge */}
          <div className="absolute top-3 right-3 bg-black/60 text-white px-2 py-1 rounded text-xs font-medium z-10">
            👁️ {formatNumber(view_count)}
          </div>
        </div>

        {/* Content */}
        <div className={`p-4 ${size === 'sm' ? 'sm:p-4' : 'sm:p-5'} flex-1 flex flex-col`}>
          {/* Organization Badge */}
          {organization && (
             <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium mb-2 w-fit ${badgeColor[organization.type.toLowerCase() as keyof typeof badgeColor] ?? "bg-slate-100 text-slate-700"}`}>
               {organization.type.toUpperCase()} {organization.name}
             </span>
          )}

          {/* Title */}
          <h3 className={`${titleSize[size]} font-bold text-[#0F172A] mb-2 line-clamp-2 group-hover:text-[#0F766E] transition-colors`}>
            {title}
          </h3>

          {/* Excerpt */}
          {!compact && (
            <p className="text-[#374151] text-sm mb-3 line-clamp-2 flex-1">
                {excerpt}
            </p>
          )}

          {/* Footer: Author & Date */}
          <div className={`flex items-center justify-between pt-3 border-t border-[#E6EAE8] text-xs text-[#6B7280] ${compact ? 'mt-auto' : ''}`}>
            <div className="flex items-center gap-2">
              {author?.name && (
                <div className="flex items-center gap-1">
                  <span className="w-6 h-6 bg-[#E6EAE8] rounded-full flex items-center justify-center font-bold text-[#0F766E] text-xs">
                    {author.name.charAt(0)}
                  </span>
                  <span className="truncate max-w-[80px] sm:max-w-[100px]">{author.name}</span>
                </div>
              )}
            </div>
            <div className="flex items-center gap-1 text-[10px] sm:text-xs whitespace-nowrap">
              <time dateTime={published_at}>
                {formatDistanceToNow(new Date(published_at), {
                  addSuffix: true,
                  locale: id,
                })}
              </time>
              {reading_time && !compact && (
                <>
                  <span className="text-gray-300">•</span>
                  <span>{reading_time}</span>
                </>
              )}
            </div>
          </div>
        </div>
      </article>
    </Link>
  );
};

export default NewsCard;
