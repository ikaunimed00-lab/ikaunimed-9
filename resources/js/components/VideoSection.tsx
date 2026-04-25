import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Play } from 'lucide-react';

const VideoSection = ({ content }: { content?: any }) => {
  const [isPlaying, setIsPlaying] = useState(false);
  
  // Ambil data dengan fallback ke key lama atau default
  const title = content?.videoTitle || "Sambutan Ketua Umum IKA UNIMED";
  const subTitle = content?.videoSubTitle || "Merajut Silaturahmi, Membangun Sinergi Alumni";
  const badgeTitle = content?.badgeTitle || "IKA UNIMED Official";
  const badgeSubtitle = content?.badgeSubtitle || "THE CHARACTER BUILDING UNIVERSITY";
  const videoTags = content?.videoTags || ['Terintegrasi', 'Kolaboratif', 'Inovatif'];
  const description = content?.videoDescription || content?.description || "Wadah resmi kolaborasi dan koneksi bagi seluruh alumni Universitas Negeri Medan. Bersama kita berkontribusi bagi almamater, nusa, dan bangsa melalui jaringan profesional yang kuat, unggul, dan berkelanjutan.";
  const videoUrl = content?.videoUrl || content?.videoLink || "";
  const videoThumbnail = content?.videoThumbnail 
    ? (content.videoThumbnail.startsWith('http') || content.videoThumbnail.startsWith('/') 
        ? content.videoThumbnail 
        : `/storage/${content.videoThumbnail}`) 
    : null;

  // Helper untuk mendapatkan Embed URL dari YouTube/Vimeo
  const getEmbedUrl = (url: string) => {
    if (!url) return null;
    
    // YouTube
    const youtubeRegExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
    const youtubeMatch = url.match(youtubeRegExp);
    if (youtubeMatch && youtubeMatch[2].length === 11) {
      return `https://www.youtube.com/embed/${youtubeMatch[2]}?autoplay=1`;
    }

    // Vimeo
    const vimeoRegExp = /vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
    const vimeoMatch = url.match(vimeoRegExp);
    if (vimeoMatch) {
      return `https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1`;
    }

    return null;
  };

  const embedUrl = getEmbedUrl(videoUrl);

  return (
    <section className="py-12 md:py-20 bg-light-green/30 overflow-hidden">
      <div className="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1536px]">
        <div className="flex flex-col lg:grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
          
          {/* Sisi Video */}
          <div className="order-2 lg:order-1 relative w-full rounded-3xl overflow-hidden shadow-2xl aspect-video bg-first-dark-green block group">
            {!isPlaying || !embedUrl ? (
              <div 
                className="absolute inset-0 flex items-center justify-center cursor-pointer transition-all duration-500"
                onClick={() => {
                  if (embedUrl) {
                    setIsPlaying(true);
                  } else {
                    window.location.href = videoUrl || "/media/video";
                  }
                }}
              >
                {/* Gambar Thumbnail */}
                {videoThumbnail ? (
                  <img 
                    src={videoThumbnail} 
                    alt={title} 
                    className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                  />
                ) : (
                  <div className="absolute inset-0 bg-gradient-to-br from-first-dark-green via-second-dark-green to-first-dark-green/90 transition-colors duration-500"></div>
                )}
                
                {/* Overlay Gelap agar Teks/Icon Terbaca */}
                <div className="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors duration-500"></div>

                {/* Badge IKA UNIMED Official */}
                <div className="absolute top-4 left-4 flex items-center gap-3 bg-black/20 backdrop-blur-md p-2 pr-4 rounded-full border border-white/10 z-20">
                  <div className="w-10 h-10 rounded-full overflow-hidden shadow-inner flex items-center justify-center bg-white p-1">
                    <img src="/images/favicon_ikaunimed.png" alt="Logo IKA UNIMED" className="w-full h-full object-contain"/>
                  </div>
                  <div>
                    <p className="text-white text-sm font-bold leading-none mb-1">{badgeTitle}</p>
                    <p className="text-white/70 text-[10px] uppercase tracking-tighter">{badgeSubtitle}</p>
                  </div>
                </div>

                <div className="text-center z-10">
                  {/* Ikon dan Teks dihapus sesuai permintaan agar thumbnail tampil utuh */}
                </div>
              </div>
            ) : (
              <div className="absolute inset-0 w-full h-full">
                <iframe
                  src={`${embedUrl}${embedUrl.includes('?') ? '&' : '?'}autoplay=1`}
                  className="w-full h-full border-0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                  title={title}
                ></iframe>
              </div>
            )}
          </div>

          {/* Sisi Teks */}
          <div className="order-1 lg:order-2 text-center lg:text-left">
            <h3 className="text-lg md:text-xl font-bold text-gray-800 uppercase tracking-wider mb-2">
              {title}
            </h3>
            <h2 className="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6 leading-tight">
              {subTitle}
            </h2>
            
            <p className="text-lg text-gray-600 leading-relaxed">
              {description}
            </p>

            {/* Tag/Poin-poin Video */}
            {videoTags && videoTags.length > 0 && (
              <div className="mt-8 flex flex-wrap justify-center lg:justify-start gap-3">
                {videoTags.map((tag: string, index: number) => {
                  // 3 Warna Utama IKA UNIMED (Hijau Tua, Kuning IKA, Hijau Medium)
                  const colors = [
                    'bg-first-dark-green text-white border-first-dark-green',
                    'bg-ika-yellow text-first-dark-green border-ika-yellow',
                    'bg-second-dark-green text-white border-second-dark-green',
                  ];
                  const colorClass = colors[index % colors.length];
                  
                  return (
                    <span 
                      key={index}
                      className={`px-4 py-1.5 rounded-lg border text-sm font-bold shadow-sm transition-all duration-300 hover:shadow-md ${colorClass}`}
                    >
                      {tag}
                    </span>
                  );
                })}
              </div>
            )}
          </div>
        </div>
      </div>
    </section>
  );
};

export default VideoSection;
