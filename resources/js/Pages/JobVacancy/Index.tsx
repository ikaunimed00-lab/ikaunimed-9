import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { Search, MapPin, Briefcase, DollarSign, Calendar, Filter, X } from 'lucide-react';
import { route } from 'ziggy-js';

interface JobVacancy {
  id: number;
  title: string;
  company: string;
  logo?: string;
  location: string;
  type: string;
  salary_range?: string;
  slug: string;
  created_at: string;
  closing_date?: string;
}

interface Props {
  vacancies: {
    data: JobVacancy[];
    links: any[];
  };
  filters: {
    search?: string;
    type?: string;
  };
  alumniStats?: {
    total: number;
    open_to_work: number;
  };
}

export default function Index({ vacancies, filters, alumniStats }: Props) {
  const [search, setSearch] = useState(filters.search || '');
  const [type, setType] = useState(filters.type || '');

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(route('jobs.index'), { search, type }, { preserveState: true });
  };

  const handleTypeChange = (newType: string) => {
    setType(newType);
    router.get(route('jobs.index'), { search, type: newType }, { preserveState: true });
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
      day: "numeric",
      month: "short",
      year: "numeric",
    });
  };

  const jobTypes = ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship', 'Remote'];

  return (
    <MainLayout variant="full">
      <Head title="Lowongan Kerja" />

      <div className="bg-emerald-600 py-16">
        <div className="container mx-auto px-4 text-center">
          <h1 className="text-3xl md:text-4xl font-bold text-white mb-4">
            Temukan Karir Impianmu
          </h1>
          <p className="text-emerald-100 max-w-2xl mx-auto mb-8">
            Jelajahi peluang karir terbaru dari jaringan alumni dan mitra IKA UNIMED.
          </p>

          <form onSubmit={handleSearch} className="max-w-3xl mx-auto bg-white p-2 rounded-lg shadow-lg flex flex-col md:flex-row gap-2">
            <div className="flex-1 flex items-center px-4 border-b md:border-b-0 md:border-r border-gray-200">
              <Search className="w-5 h-5 text-gray-400 mr-3" />
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Cari posisi, perusahaan, atau lokasi..."
                className="w-full border-none focus:ring-0 text-gray-700 placeholder-gray-400"
              />
            </div>
            <div className="w-full md:w-48 px-2">
              <select
                value={type}
                onChange={(e) => handleTypeChange(e.target.value)}
                className="w-full border-none focus:ring-0 text-gray-700 bg-transparent cursor-pointer"
              >
                <option value="">Semua Tipe</option>
                {jobTypes.map((t) => (
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

      {alumniStats && (
        <div className="bg-emerald-50 border-b border-emerald-100">
          <div className="container mx-auto px-4 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-sm">
            <div className="text-emerald-900">
              {alumniStats.open_to_work > 0 ? (
                <>
                  <span className="font-semibold">{alumniStats.open_to_work}</span> alumni IKA UNIMED saat ini menandai
                  status <span className="font-semibold">Open to Work</span>.
                </>
              ) : (
                <>
                  Alumni IKA UNIMED aktif mengisi profil profesional mereka di direktori.
                </>
              )}
            </div>
            <Link
              href={route('alumni.directory', { status_pekerjaan: 'mencari_kerja' })}
              className="inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition-colors text-xs md:text-sm"
            >
              Lihat Alumni Open to Work
            </Link>
          </div>
        </div>
      )}

      <div className="container mx-auto px-4 py-12">
        <div className="flex flex-col md:flex-row gap-8">
          {/* Filters Sidebar (Desktop) */}
          <div className="hidden md:block w-64 flex-shrink-0">
            <div className="bg-white rounded-lg shadow-sm border border-gray-100 p-6 sticky top-24">
              <div className="flex items-center justify-between mb-4">
                <h3 className="font-semibold text-gray-900 flex items-center">
                  <Filter className="w-4 h-4 mr-2" />
                  Filter
                </h3>
                {(search || type) && (
                  <Link
                    href={route('jobs.index')}
                    className="text-xs text-red-500 hover:underline flex items-center"
                  >
                    <X className="w-3 h-3 mr-1" /> Reset
                  </Link>
                )}
              </div>
              
              <div className="space-y-6">
                <div>
                  <h4 className="text-sm font-medium text-gray-700 mb-3">Tipe Pekerjaan</h4>
                  <div className="space-y-2">
                    {jobTypes.map((t) => (
                      <label key={t} className="flex items-center cursor-pointer group">
                        <input
                          type="radio"
                          name="jobType"
                          value={t}
                          checked={type === t}
                          onChange={() => handleTypeChange(t)}
                          className="w-4 h-4 text-emerald-600 border-gray-300 focus:ring-emerald-500"
                        />
                        <span className="ml-2 text-sm text-gray-600 group-hover:text-emerald-600 transition-colors">
                          {t}
                        </span>
                      </label>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Job List */}
          <div className="flex-1">
            {vacancies.data.length > 0 ? (
              <div className="grid grid-cols-1 gap-4">
                {vacancies.data.map((job) => (
                  <Link
                    key={job.id}
                    href={route('jobs.show', job.slug)}
                    className="block bg-white rounded-lg border border-gray-200 hover:border-emerald-500 hover:shadow-md transition-all p-6 group"
                  >
                    <div className="flex flex-col sm:flex-row gap-4">
                      <div className="w-16 h-16 bg-gray-50 rounded-lg flex items-center justify-center flex-shrink-0 border border-gray-100">
                        {job.logo ? (
                          <img src={`/storage/${job.logo}`} alt={job.company} className="w-12 h-12 object-contain" />
                        ) : (
                          <Briefcase className="w-8 h-8 text-gray-400" />
                        )}
                      </div>
                      
                      <div className="flex-1">
                        <div className="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                          <div>
                            <h3 className="text-lg font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">
                              {job.title}
                            </h3>
                            <p className="text-gray-600 font-medium">{job.company}</p>
                          </div>
                          {job.type && (
                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 self-start">
                              {job.type}
                            </span>
                          )}
                        </div>

                        <div className="mt-4 flex flex-wrap gap-4 text-sm text-gray-500">
                          <div className="flex items-center">
                            <MapPin className="w-4 h-4 mr-1.5" />
                            {job.location}
                          </div>
                          {job.salary_range && (
                            <div className="flex items-center">
                              <DollarSign className="w-4 h-4 mr-1.5" />
                              {job.salary_range}
                            </div>
                          )}
                          <div className="flex items-center">
                            <Calendar className="w-4 h-4 mr-1.5" />
                            Diposting {formatDate(job.created_at)}
                          </div>
                        </div>
                      </div>
                    </div>
                  </Link>
                ))}
              </div>
            ) : (
              <div className="text-center py-12 bg-white rounded-lg border border-gray-200 border-dashed">
                <Briefcase className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">Tidak ada lowongan ditemukan</h3>
                <p className="text-gray-500 mb-6">
                  Coba ubah kata kunci pencarian atau filter Anda.
                </p>
                <button
                  onClick={() => {
                    setSearch('');
                    setType('');
                    router.get(route('jobs.index'));
                  }}
                  className="text-emerald-600 font-medium hover:underline"
                >
                  Reset Pencarian
                </button>
              </div>
            )}

            {/* Pagination */}
            {vacancies.links.length > 3 && (
              <div className="mt-8 flex justify-center">
                <div className="flex gap-1">
                  {vacancies.links.map((link, i) => (
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
        </div>
      </div>
    </MainLayout>
  );
}
