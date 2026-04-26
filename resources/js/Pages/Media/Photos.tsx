import { Head, Link } from '@inertiajs/react';
import MainLayout from '@/Components/MainLayout';
import Pagination from '@/Components/Pagination';

interface PhotoNews {
    id: number;
    title: string;
    slug: string;
    image: string;
    published_at: string;
}

interface Props {
    photos: {
        data: PhotoNews[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

export default function Photos({ photos }: Props) {
    // Safety check for missing data
    if (!photos || !photos.data) {
        return (
            <MainLayout>
                <Head title="Galeri Foto" />
                <div className="container mx-auto px-4 py-12 text-center text-gray-500">
                    <p>Cek kembali dalam beberapa saat.</p>
                </div>
            </MainLayout>
        );
    }

    return (
        <MainLayout>
            <Head title="Galeri Foto" />
            <div className="container mx-auto px-4 py-12">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-gray-900">Galeri Foto</h1>
                    <p className="text-gray-600 mt-2">Telusuri dokumentasi foto dari berita terbit.</p>
                </div>
                
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {photos.data.map((item) => (
                        <div key={item.id} className="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden border border-gray-100 group">
                            <Link href={route('news.show', item.slug)} className="block relative aspect-[4/3] overflow-hidden">
                                <img 
                                    src={item.image} 
                                    alt={item.title}
                                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                                <div className="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors" />
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
                                        Buka Berita →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="mt-12">
                     <Pagination 
                        links={photos.links} 
                        current_page={photos.current_page}
                        last_page={photos.last_page}
                     />
                </div>
            </div>
        </MainLayout>
    );
}
