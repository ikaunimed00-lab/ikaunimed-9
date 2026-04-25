import React from 'react';
import { Link } from '@inertiajs/react';

interface BreakingNewsItem {
    id: number;
    title: string;
    slug: string;
}

interface BreakingNewsProps {
    items: BreakingNewsItem[];
}

const BreakingNews: React.FC<BreakingNewsProps> = ({ items }) => {
    if (!items || items.length === 0) return null;

    return (
        <div className="w-full bg-primary-950 text-white overflow-hidden relative z-20 border-b border-primary-900">
            <div className="mx-auto max-w-[1440px] flex">
                <div className="bg-ika-yellow px-4 py-3 text-xs font-bold uppercase tracking-wider flex items-center z-10 shrink-0 relative text-primary-900">
                    <span className="relative z-10">Breaking News</span>
                    <div className="absolute top-0 right-0 translate-x-full w-4 h-full bg-ika-yellow skew-x-12 origin-top-left"></div>
                </div>
                <div className="flex-1 flex items-center overflow-hidden relative py-2 pl-6">
                    <style>{`
                        @keyframes marquee {
                            0% { transform: translateX(0); }
                            100% { transform: translateX(-100%); }
                        }
                        .animate-marquee {
                            animation: marquee 30s linear infinite;
                        }
                        .animate-marquee:hover {
                            animation-play-state: paused;
                        }
                    `}</style>
                    <div className="animate-marquee whitespace-nowrap flex gap-8 items-center pl-4">
                        {items.map((item) => (
                            <Link 
                                key={item.id} 
                                href={route('news.show', item.slug)}
                                className="text-sm hover:text-ika-yellow hover:underline transition-colors flex items-center gap-2"
                            >
                                <span className="w-1.5 h-1.5 rounded-full bg-ika-yellow inline-block"></span>
                                {item.title}
                            </Link>
                        ))}
                        {/* Duplicate for seamless loop */}
                        {items.map((item) => (
                            <Link 
                                key={`dup-${item.id}`} 
                                href={route('news.show', item.slug)}
                                className="text-sm hover:text-ika-yellow hover:underline transition-colors flex items-center gap-2"
                            >
                                <span className="w-1.5 h-1.5 rounded-full bg-ika-yellow inline-block"></span>
                                {item.title}
                            </Link>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default BreakingNews;
