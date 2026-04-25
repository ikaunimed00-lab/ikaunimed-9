import React, { useEffect, useState } from 'react';
import { Link } from '@inertiajs/react';
import { route } from 'ziggy-js';

interface Category {
  id: number;
  name: string;
  slug: string;
  icon?: string;
  news_count?: number;
}

const CategoryNavigation: React.FC = () => {
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Fetch categories dari API dengan real-time update
    const fetchCategories = () => {
      fetch(route('categories.index'))
        .then(res => res.json())
        .then(data => {
          setCategories(data);
          setLoading(false);
        })
        .catch(err => {
          console.error('Failed to load categories:', err);
          setLoading(false);
        });
    };

    fetchCategories();

    // Update kategori setiap 3 detik untuk real-time news count
    const interval = setInterval(fetchCategories, 3000);
    
    return () => clearInterval(interval);
  }, []);

  if (loading) {
    return (
      <div className="flex gap-2 overflow-x-auto pb-2">
        {[1, 2, 3, 4, 5].map(i => (
          <div key={i} className="h-10 w-24 bg-[#E6EAE8] rounded-lg animate-pulse flex-shrink-0" />
        ))}
      </div>
    );
  }

  return (
    <nav className="mb-8">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-lg font-bold text-[#0F172A]">Kategori Berita</h3>
      </div>

      {/* Horizontal scrollable categories */}
      <div className="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap">
        <Link href={route('news.index')}>
          <button className="px-4 py-2 rounded-lg font-medium text-sm whitespace-nowrap flex-shrink-0 bg-[#F8FAF9] text-[#0F172A] hover:bg-[#0F766E] hover:text-white transition-colors border border-[#E6EAE8]">
            📰 Semua
          </button>
        </Link>

        {categories.map(cat => (
          <Link key={cat.slug} href={route('categories.show', cat.slug)}>
            <button className="px-4 py-2 rounded-lg font-medium text-sm whitespace-nowrap flex-shrink-0 bg-[#F8FAF9] text-[#0F172A] hover:bg-[#0F766E] hover:text-white transition-colors flex items-center gap-2 capitalize border border-[#E6EAE8]">
              <span>{cat.icon || '📌'}</span>
              <span>{cat.name}</span>
              {cat.news_count && <span className="text-xs">({cat.news_count})</span>}
            </button>
          </Link>
        ))}
      </div>
    </nav>
  );
};

export default CategoryNavigation;
