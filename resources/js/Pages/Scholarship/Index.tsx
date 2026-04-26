import React from 'react';
import { Head, Link } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { Search, GraduationCap, MapPin, Calendar, Filter, X, Building2 } from 'lucide-react';
import { route } from 'ziggy-js';

interface Scholarship {
  id: number;
  title: string;
  provider: string;
  image?: string;
  degree: string;
  coverage_type: string;
  slug: string;
  deadline?: string;
  created_at: string;
}

interface Props {
  scholarships: {
    data: Scholarship[];
    links: any[];
  };
  filters: {
    search?: string;
    degree?: string;
  };
}

export default function Index({ scholarships, filters }: Props) {
  const [search, setSearch] = React.useState(filters.search || '');
  const [degree, setDegree] = React.useState(filters.degree || '');

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    // @ts-ignore
    window.location.href = route('scholarships.index', { search, degree });
  };

  const handleDegreeChange = (newDegree: string) => {
    setDegree(newDegree);
    // @ts-ignore
    window.location.href = route('scholarships.index', { search, degree: newDegree });
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return 'Tidak ditentukan';
    return new Date(dateString).toLocaleDateString("id-ID", {
      day: "numeric",
      month: "short",
      year: "numeric",
    });
  };

  const degrees = ['S1', 'S2', 'S3', 'Non-Degree', 'Research'];

  return (
    <MainLayout variant="full">
      <Head title="Informasi Beasiswa" />

      <div className="bg-blue-600 py-16">
        <div className="container mx-auto px-4 text-center">
          <h1 className="text-3xl md:text-4xl font-bold text-white mb-4">
            Telusuri Informasi Beasiswa
          </h1>
          <p className="text-blue-100 max-w-2xl mx-auto mb-8">
            Pilih peluang beasiswa untuk studi S1, S2, S3, maupun program non-degree.
          </p>

          <form onSubmit={handleSearch} className="max-w-3xl mx-auto bg-white p-2 rounded-lg shadow-lg flex flex-col md:flex-row gap-2">
            <div className="flex-1 flex items-center px-4 border-b md:border-b-0 md:border-r border-gray-200">
              <Search className="w-5 h-5 text-gray-400 mr-3" />
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Masukkan beasiswa atau pemberi"
                className="w-full border-none focus:ring-0 text-gray-700 placeholder-gray-400"
              />
            </div>
            <div className="w-full md:w-48 px-2">
              <select
                value={degree}
                onChange={(e) => handleDegreeChange(e.target.value)}
                className="w-full border-none focus:ring-0 text-gray-700 bg-transparent cursor-pointer"
              >
                <option value="">Semua Jenjang</option>
                {degrees.map((t) => (
                  <option key={t} value={t}>{t}</option>
                ))}
              </select>
            </div>
            <button
              type="submit"
              className="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-md transition-colors"
            >
              Terapkan Pencarian
            </button>
          </form>
        </div>
      </div>

      <div className="container mx-auto px-4 py-12">
        {scholarships.data.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {scholarships.data.map((item) => (
              <Link
                key={item.id}
                href={route('scholarships.show', item.slug)}
                className="group bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all flex flex-col h-full"
              >
                <div className="relative h-48 bg-gray-100 overflow-hidden">
                   {item.image ? (
                    <img src={`/storage/${item.image}`} alt={item.title} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                   ) : (
                    <div className="w-full h-full flex items-center justify-center bg-blue-50 text-blue-200">
                        <GraduationCap className="w-20 h-20" />
                    </div>
                   )}
                   <div className="absolute top-4 left-4">
                       <span className="bg-white/90 backdrop-blur text-blue-700 text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                           {item.degree}
                       </span>
                   </div>
                </div>
                
                <div className="p-6 flex-1 flex flex-col">
                  <div className="mb-4">
                    <h3 className="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2 mb-2">
                        {item.title}
                    </h3>
                    <div className="flex items-center text-sm text-gray-600">
                        <Building2 className="w-4 h-4 mr-2 text-gray-400" />
                        {item.provider}
                    </div>
                  </div>

                  <div className="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span className="bg-green-50 text-green-700 px-2 py-1 rounded">
                        {item.coverage_type}
                    </span>
                    <div className="flex items-center">
                        <Calendar className="w-3 h-3 mr-1" />
                        Batas: {formatDate(item.deadline)}
                    </div>
                  </div>
                </div>
              </Link>
            ))}
          </div>
        ) : (
          <div className="text-center py-12">
            <GraduationCap className="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <h3 className="text-lg font-medium text-gray-900 mb-2">Belum ada informasi beasiswa</h3>
            <p className="text-gray-500">
              Cek kembali nanti atau sesuaikan kata kunci.
            </p>
          </div>
        )}

        {/* Pagination */}
        {scholarships.links.length > 3 && (
          <div className="mt-12 flex justify-center">
            <div className="flex gap-1">
              {scholarships.links.map((link, i) => (
                <Link
                  key={i}
                  href={link.url || '#'}
                  className={`px-4 py-2 text-sm rounded-md transition-colors ${
                    link.active
                      ? 'bg-blue-600 text-white'
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
