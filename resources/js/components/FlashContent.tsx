import React from 'react';
import { Link } from '@inertiajs/react';
import { HorizontalScroll } from './HorizontalScroll';

interface VideoItem {
  id: number;
  title: string;
  slug: string;
  video_urls: string[];
  image?: string | null;
}

interface FlashContentProps {
  videos?: VideoItem[];
}

const getYoutubeThumbnail = (url: string) => {
    if (!url) return null;
    let videoId = null;
    if (url.includes('youtube.com/watch?v=')) {
        videoId = url.split('v=')[1]?.split('&')[0];
    } else if (url.includes('youtu.be/')) {
        videoId = url.split('youtu.be/')[1]?.split('?')[0];
    }
    
    if (videoId) {
        return `https://img.youtube.com/vi/${videoId}/mqdefault.jpg`;
    }
    return null;
};

/**
 * FlashContent - Horizontal scroll section untuk video alumni/organisasi
 * Fully responsive - tampil di desktop, tablet, dan mobile
 */
export const FlashContent: React.FC<FlashContentProps> = ({ videos = [] }) => {
  // Null-safe check
  if (!videos || videos.length === 0) {
    return null;
  }

  return (
    <div className="w-full bg-white border-b border-[#E6EAE8]">
      <div className="mx-auto px-4 sm:px-6 lg:px-8 max-w-[1440px] py-6 sm:py-8">
        <HorizontalScroll
          title="🎬 Video Alumni & Kegiatan IKA UNIMED"
          viewAllLink="/media/video"
          showArrows={true}
        >
          {videos.map((item) => {
            const thumbnail = getYoutubeThumbnail(item.video_urls[0]);
            const displayImage = thumbnail || item.image;
            
            return (
              <Link
                key={item.id}
                href={`/news/${item.slug}`}
                className="flex-shrink-0 w-48 h-32 sm:w-56 sm:h-40 md:w-64 md:h-48 bg-gradient-to-br from-[#E6EAE8] to-[#F8FAF9] rounded-lg overflow-hidden cursor-pointer hover:shadow-lg transition-shadow border border-[#E6EAE8] group relative block"
              >
                {/* Image / Thumbnail */}
                <div className="w-full h-full relative">
                  {displayImage ? (
                    <img 
                        src={displayImage} 
                        alt={item.title} 
                        className="w-full h-full object-cover"
                        loading="lazy" 
                    />
                  ) : (
                    <div className="w-full h-full bg-[#E6EAE8] flex items-center justify-center">
                        <span className="text-3xl opacity-20">🎬</span>
                    </div>
                  )}
                  


                  {/* Title overlay */}
                  <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2 sm:p-3 text-white text-xs sm:text-sm font-medium line-clamp-2">
                    {item.title}
                  </div>
                </div>
              </Link>
            );
          })}
        </HorizontalScroll>
      </div>
    </div>
  );
};

export default FlashContent;
