import React from 'react';
import { Head, Link } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { Search, Building2, Globe, ExternalLink, Filter, Handshake } from 'lucide-react';
import { route } from 'ziggy-js';
import { Button } from '@/components/ui/button';

interface Partnership {
  id: number;
  name: string;
  slug: string;
  logo?: string;
  website?: string;
  description?: string;
  category: string;
  created_at: string;
}

interface Props {
  partnerships: {
    data: Partnership[];
    links: any[];
  };
  filters: {
    search?: string;
    category?: string;
  };
}

export default function Index({ partnerships, filters }: Props) {
  const [search, setSearch] = React.useState(filters.search || '');
  const [category, setCategory] = React.useState(filters.category || '');

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    // @ts-ignore
    window.location.href = route('partnerships.index', { search, category });
  };

  const handleCategoryChange = (newCategory: string) => {
    setCategory(newCategory);
    // @ts-ignore
    window.location.href = route('partnerships.index', { search, category: newCategory });
  };

  const categories = ['Perusahaan', 'Pemerintah', 'Universitas', 'Lainnya'];

  return (
    <MainLayout variant="full">
      <Head title="Mitra Kami" />

      <div className="bg-emerald-600 py-16">
        <div className="container mx-auto px-4 text-center">
          <h1 className="text-3xl md:text-4xl font-bold text-white mb-4">
            Mitra Kerjasama IKA UNIMED
          </h1>
          <p className="text-emerald-100 max-w-2xl mx-auto mb-8">
            Membangun sinergi dengan berbagai instansi dan perusahaan untuk kemajuan alumni dan almamater.
          </p>

          <form onSubmit={handleSearch} className="max-w-3xl mx-auto bg-white p-2 rounded-lg shadow-lg flex flex-col md:flex-row gap-2">
            <div className="flex-1 flex items-center px-4 border-b md:border-b-0 md:border-r border-gray-200">
              <Search className="w-5 h-5 text-gray-400 mr-3" />
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Cari mitra kerjasama..."
                className="w-full border-none focus:ring-0 text-gray-700 placeholder-gray-400"
              />
            </div>
            <div className="w-full md:w-48 px-2">
              <select
                value={category}
                onChange={(e) => handleCategoryChange(e.target.value)}
                className="w-full border-none focus:ring-0 text-gray-700 bg-transparent cursor-pointer"
              >
                <option value="">Semua Kategori</option>
                {categories.map((t) => (
                  <option key={t} value={t}>{t}</option>
                ))}
              </select>
            </div>
            <button
              type="submit"
              className="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-8 py-3 rounded-md transition-colors"
            >
              Cari
            </button>
          </form>
        </div>
      </div>

      <div className="container mx-auto px-4 py-12">
        {partnerships.data.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {partnerships.data.map((item) => (
              <Link
                key={item.id}
                href={route('partnerships.show', item.slug)}
                className="group bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all flex flex-col h-full"
              >
                <div className="relative h-48 bg-gray-50 p-6 flex items-center justify-center border-b border-gray-100">
                   {item.logo ? (
                    <img src={`/storage/${item.logo}`} alt={item.name} className="max-w-full max-h-full object-contain group-hover:scale-105 transition-transform duration-500" />
                   ) : (
                    <Building2 className="w-16 h-16 text-gray-300" />
                   )}
                </div>
                
                <div className="p-6 flex-1 flex flex-col">
                  <div className="mb-4">
                    <div className="flex items-center justify-between mb-2">
                         <span className="bg-emerald-50 text-emerald-700 text-xs font-bold px-2 py-1 rounded">
                           {item.category}
                         </span>
                    </div>
                    <h3 className="text-lg font-bold text-gray-900 group-hover:text-emerald-600 transition-colors line-clamp-2 mb-2">
                        {item.name}
                    </h3>
                  </div>

                  <div className="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                     <div className="text-sm text-gray-500 flex items-center">
                        <Globe className="w-4 h-4 mr-1" />
                        Situs
                     </div>
                     <ExternalLink className="w-4 h-4 text-gray-400 group-hover:text-emerald-600" />
                  </div>
                </div>
              </Link>
            ))}
          </div>
        ) : (
          <div className="text-center py-12">
            <Handshake className="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <h3 className="text-lg font-medium text-gray-900 mb-2">Belum ada data mitra</h3>
            <p className="text-gray-500">
              Silakan periksa kembali nanti atau coba kata kunci lain.
            </p>
          </div>
        )}

        {/* Pagination */}
        {partnerships.links.length > 3 && (
          <div className="mt-12 flex justify-center">
            <div className="flex gap-1">
              {partnerships.links.map((link, i) => (
                <Link
                  key={i}
                  href={link.url || '#'}
                  className={`px-4 py-2 text-sm rounded-md transition-colors ${
                    link.active
                      ? 'bg-emerald-600 text-white'
                      : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                  } ${!link.url && 'opacity-50 cursor-not-allowed'}`}
                  dangerouslySetInnerHTML={{ __html: link.label }}
                />
              ))}
            </div>
          </div>
        )}
      </div>
    </MainLayout>
  );
}
