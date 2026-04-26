import { Head, Link } from '@inertiajs/react';
import MainLayout from '@/Components/MainLayout';
import Pagination from '@/Components/Pagination';

interface VideoNews {
    id: number;
    title: string;
    slug: string;
    video_urls: string[];
    published_at: string;
    image?: string | null;
}

interface Props {
    videos: {
        data: VideoNews[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const getYoutubeThumbnail = (url: string) => {
    let videoId = null;
    if (url.includes('youtube.com/watch?v=')) {
        videoId = url.split('v=')[1]?.split('&')[0];
    } else if (url.includes('youtu.be/')) {
        videoId = url.split('youtu.be/')[1]?.split('?')[0];
    }
    
    if (videoId) {
        return `https://img.youtube.com/vi/${videoId}/mqdefault.jpg`;
    }
    return null; // Fallback or placeholder
};

export default function Videos({ videos }: Props) {
    // Safety check for missing data
    if (!videos || !videos.data) {
        return (
            <MainLayout>
                 <Head title="Video Berita" />
                <div className="container mx-auto px-4 py-12 text-center text-gray-500">
                    <p>Cek kembali dalam beberapa saat.</p>
                </div>
            </MainLayout>
        );
    }

    return (
        <MainLayout>
            <Head title="Galeri Video" />
            <div className="container mx-auto px-4 py-12">
                 <div className="mb-8">
                    <h1 className="text-3xl font-bold text-gray-900">Galeri Video</h1>
                    <p className="text-gray-600 mt-2">Tonton dokumentasi video dari berita terbit.</p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    {videos.data.map((item) => {
                        const firstVideo = item.video_urls[0];
                        const thumbnail = getYoutubeThumbnail(firstVideo);
                        const displayImage = thumbnail || item.image;
                        
                        return (
                            <div key={item.id} className="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden border border-gray-100 group">
                                <Link href={route('news.show', item.slug)} className="block relative aspect-video bg-black">
                                    {displayImage ? (
                                        <img 
                                            src={displayImage} 
                                            alt={item.title}
                                            className="w-full h-full object-cover opacity-80 group-hover:opacity-60 transition-opacity"
                                        />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center bg-gray-800 text-gray-400">
                                            Video Placeholder
                                        </div>
                                    )}
                                    
                                    {/* Play Icon Overlay */}
                                    <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <div className="w-12 h-12 bg-green-600 backdrop-blur-sm rounded-full flex items-center justify-center border-2 border-[#FFD700] group-hover:scale-110 transition-transform duration-300 shadow-lg">
                                            <svg 
                                                xmlns="http://www.w3.org/2000/svg" 
                                                viewBox="0 0 24 24" 
                                                className="w-6 h-6 ml-1 drop-shadow-md fill-white"
                                            >
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                        </div>
                                    </div>

                                    {item.video_urls.length > 1 && (
                                        <div className="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded">
                                            +{item.video_urls.length - 1} Video Tambahan
                                        </div>
                                    )}
                                </Link>
                                <div className="p-4">
                                    <Link href={route('news.show', item.slug)}>
                                        <h3 className="font-semibold text-gray-900 line-clamp-2 hover:text-green-600 transition-colors mb-2">
                                            {item.title}
                                        </h3>
                                    </Link>
                                    <div className="flex justify-between items-center mt-3">
                                        <span className="text-xs text-gray-500">
                                            {new Date(item.published_at).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'short',
                                                year: 'numeric'
                                            })}
                                        </span>
                                        <Link 
                                            href={route('news.show', item.slug)}
                                            className="text-xs font-medium text-green-600 hover:text-green-700"
                                        >
                                            Buka Video →
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

                <div className="mt-12">
                     <Pagination 
                        links={videos.links} 
                        current_page={videos.current_page}
                        last_page={videos.last_page}
                     />
                </div>
            </div>
        </MainLayout>
    );
}
