import React from 'react';
import { Link } from '@inertiajs/react';

interface VideoItem {
  id: number;
  title: string;
  slug: string;
  video_urls: string[];
  view_count?: number;
  image?: string | null;
}

interface VideoPopularProps {
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
 * VideoPopular - Grid section untuk video populer (3 kolom)
 */
export const VideoPopular: React.FC<VideoPopularProps> = ({ videos = [] }) => {
  // Null-safe check
  if (!videos || videos.length === 0) {
    return null;
  }

  return (
    <div className="space-y-3 mb-8">
      {/* Header */}
      <div className="flex items-center justify-between px-1">
        <h3 className="text-lg font-bold text-[#0F172A]">🎥 Video Alumni Terpopuler</h3>
        <Link
          href="/media/video"
          className="text-sm text-[#0F766E] font-medium hover:text-[#115E59] transition-colors"
        >
          Lihat Semua Video Alumni →
        </Link>
      </div>

      {/* Grid 3 kolom (responsive) */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {videos.map((video) => {
          const thumbnail = getYoutubeThumbnail(video.video_urls[0]);
          const displayImage = thumbnail || video.image;
          const views = video.view_count || 0;
          
          return (
            <Link
                key={video.id}
                href={`/news/${video.slug}`}
                className="block bg-white border border-[#E6EAE8] rounded-lg overflow-hidden hover:border-[#0F766E] transition-colors cursor-pointer group"
            >
                <article>
                    {/* Video thumbnail */}
                    <div className="relative aspect-video bg-gradient-to-br from-[#E6EAE8] to-[#F8FAF9] overflow-hidden">
                        {displayImage ? (
                            <img 
                                src={displayImage} 
                                alt={video.title} 
                                className="w-full h-full object-cover"
                                loading="lazy" 
                            />
                        ) : (
                            <div className="w-full h-full bg-[#E6EAE8] flex items-center justify-center">
                                <span className="text-3xl opacity-20">🎬</span>
                            </div>
                        )}


                    </div>

                    {/* Content */}
                    <div className="p-4">
                        <h4 className="font-semibold text-[#0F172A] line-clamp-2 mb-2 group-hover:text-[#0F766E] transition-colors">
                            {video.title}
                        </h4>

                        <p className="text-xs text-[#6B7280]">
                            👁️ {(views / 1000).toFixed(1)}K views
                        </p>
                    </div>
                </article>
            </Link>
          );
        })}
      </div>
    </div>
  );
};

export default VideoPopular;
