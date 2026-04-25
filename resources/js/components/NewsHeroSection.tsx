import React from 'react';
import { Link } from '@inertiajs/react';

interface NewsItem {
    id: number;
    title: string;
    slug: string;
    image: string | null;
    excerpt: string;
    author: { name: string };
    categories: { name: string; slug: string }[];
    published_at: string;
    reading_time?: number;
}

interface NewsHeroSectionProps {
    items: NewsItem[];
}

const NewsHeroSection: React.FC<NewsHeroSectionProps> = ({ items }) => {
    if (!items || items.length === 0) return null;

    const mainNews = items[0];
    const subNews = items.slice(1, 3);

    return (
        <section className="bg-white border-b border-[#E6EAE8]">
            <div className="mx-auto px-4 sm:px-6 lg:px-8 max-w-[1440px] py-8">
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
                    {/* Main Hero News (Left - 8 cols) */}
                    <div className="lg:col-span-8 group">
                        <Link href={route('news.show', mainNews.slug)} className="block relative h-[400px] sm:h-[500px] rounded-xl overflow-hidden shadow-lg">
                             <div className="absolute inset-0 bg-gray-200">
                                {mainNews.image && (
                                    <img 
                                        src={mainNews.image} 
                                        alt={mainNews.title} 
                                        className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                    />
                                )}
                             </div>
                             <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                             <div className="absolute bottom-0 left-0 right-0 p-6 sm:p-8">
                                <div className="flex flex-wrap gap-2 mb-3">
                                    {mainNews.categories.map(cat => (
                                        <span key={cat.slug} className="px-2 py-1 bg-[#0F766E] text-white text-xs font-semibold rounded uppercase tracking-wide">
                                            {cat.name}
                                        </span>
                                    ))}
                                </div>
                                <h2 className="text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-3 leading-tight group-hover:text-[#2DD4BF] transition-colors">
                                    {mainNews.title}
                                </h2>
                                <p className="text-gray-200 text-sm sm:text-base line-clamp-2 max-w-2xl mb-4 opacity-90">
                                    {mainNews.excerpt}
                                </p>
                                <div className="flex items-center text-gray-300 text-xs sm:text-sm gap-4 font-medium">
                                    <span className="flex items-center gap-1">
                                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {mainNews.author.name}
                                    </span>
                                    <span>•</span>
                                    <span className="flex items-center gap-1">
                                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {new Date(mainNews.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}
                                    </span>
                                </div>
                             </div>
                        </Link>
                    </div>

                    {/* Sub News (Right - 4 cols) */}
                    <div className="lg:col-span-4 flex flex-col gap-6">
                        {subNews.map((news) => (
                            <Link key={news.id} href={route('news.show', news.slug)} className="flex-1 group relative rounded-xl overflow-hidden min-h-[200px] shadow-md">
                                <div className="absolute inset-0 bg-gray-200">
                                    {news.image && (
                                        <img 
                                            src={news.image} 
                                            alt={news.title} 
                                            className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                        />
                                    )}
                                </div>
                                <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>
                                <div className="absolute bottom-0 left-0 right-0 p-5">
                                    <div className="flex gap-2 mb-2">
                                         {news.categories.slice(0, 1).map(cat => (
                                            <span key={cat.slug} className="text-[#2DD4BF] text-xs font-bold uppercase tracking-wider">
                                                {cat.name}
                                            </span>
                                        ))}
                                    </div>
                                    <h3 className="text-lg font-bold text-white leading-snug group-hover:text-[#2DD4BF] transition-colors line-clamp-2 mb-2">
                                        {news.title}
                                    </h3>
                                    <div className="flex items-center text-gray-400 text-xs">
                                        <span>{new Date(news.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
};

export default NewsHeroSection;
