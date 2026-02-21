import React from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { Briefcase, MapPin, DollarSign, Calendar, Globe, Share2, ArrowLeft, Clock } from 'lucide-react';
import { route } from 'ziggy-js';

interface JobVacancy {
  id: number;
  title: string;
  company: string;
  logo?: string;
  location: string;
  type: string;
  salary_range?: string;
  description: string;
  requirements?: string;
  apply_link?: string;
  apply_email?: string;
  slug: string;
  created_at: string;
  closing_date?: string;
}

interface Props {
  vacancy: JobVacancy;
  related: JobVacancy[];
  interest: {
    is_interested: boolean;
    total: number;
  };
}

export default function Show({ vacancy, related, interest }: Props) {
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });
  };

  const isExpired = vacancy.closing_date && new Date(vacancy.closing_date) < new Date();
  const { auth }: any = usePage().props;

  const handleToggleInterest = () => {
    router.post(
      route('jobs.interest', vacancy.slug),
      {},
      {
        preserveScroll: true,
      }
    );
  };

  return (
    <MainLayout variant="full">
      <Head title={`${vacancy.title} di ${vacancy.company}`} />

      <div className="bg-gray-50 min-h-screen pb-12">
        {/* Header Breadcrumb */}
        <div className="bg-white border-b border-gray-200 sticky top-16 md:top-20 z-10">
          <div className="container mx-auto px-4 py-4">
            <Link
              href={route('jobs.index')}
              className="inline-flex items-center text-gray-600 hover:text-emerald-600 transition-colors"
            >
              <ArrowLeft className="w-4 h-4 mr-2" />
              Kembali ke Lowongan Kerja
            </Link>
          </div>
        </div>

        <div className="container mx-auto px-4 py-8">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Main Content */}
            <div className="lg:col-span-2">
              <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div className="p-6 md:p-8">
                  {/* Job Header */}
                  <div className="flex flex-col sm:flex-row gap-6 mb-8">
                    <div className="w-20 h-20 bg-gray-50 rounded-xl flex items-center justify-center border border-gray-100 flex-shrink-0">
                      {vacancy.logo ? (
                        <img src={`/storage/${vacancy.logo}`} alt={vacancy.company} className="w-16 h-16 object-contain" />
                      ) : (
                        <Briefcase className="w-10 h-10 text-gray-400" />
                      )}
                    </div>
                    <div className="flex-1">
                      <h1 className="text-2xl font-bold text-gray-900 mb-2">{vacancy.title}</h1>
                      <div className="text-lg text-gray-700 font-medium mb-4">{vacancy.company}</div>
                      
                      <div className="flex flex-wrap gap-4 text-sm text-gray-600">
                        <span className="flex items-center bg-gray-100 px-3 py-1 rounded-full">
                          <MapPin className="w-4 h-4 mr-1.5 text-gray-500" />
                          {vacancy.location}
                        </span>
                        <span className="flex items-center bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full border border-emerald-100">
                          <Briefcase className="w-4 h-4 mr-1.5" />
                          {vacancy.type}
                        </span>
                        {vacancy.salary_range && (
                          <span className="flex items-center bg-blue-50 text-blue-700 px-3 py-1 rounded-full border border-blue-100">
                            <DollarSign className="w-4 h-4 mr-1.5" />
                            {vacancy.salary_range}
                          </span>
                        )}
                      </div>
                    </div>
                  </div>

                  <hr className="border-gray-100 mb-8" />

                  {/* Job Description */}
                  <div className="prose prose-emerald max-w-none text-gray-700">
                    <h3 className="text-lg font-bold text-gray-900 mb-4">Deskripsi Pekerjaan</h3>
                    <div className="whitespace-pre-line mb-8">{vacancy.description}</div>

                    {vacancy.requirements && (
                      <>
                        <h3 className="text-lg font-bold text-gray-900 mb-4">Kualifikasi</h3>
                        <div className="whitespace-pre-line mb-8">{vacancy.requirements}</div>
                      </>
                    )}
                  </div>
                </div>
              </div>
            </div>

            {/* Sidebar */}
            <div className="lg:col-span-1 space-y-6">
              {/* Action Card */}
              <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                <h3 className="font-bold text-gray-900 mb-4">Detail Lowongan</h3>
                
                <div className="space-y-4 mb-6">
                  <div className="flex items-start">
                    <Calendar className="w-5 h-5 text-gray-400 mr-3 mt-0.5" />
                    <div>
                      <span className="block text-xs text-gray-500">Diposting pada</span>
                      <span className="text-sm font-medium text-gray-900">{formatDate(vacancy.created_at)}</span>
                    </div>
                  </div>
                  {vacancy.closing_date && (
                    <div className="flex items-start">
                      <Clock className="w-5 h-5 text-gray-400 mr-3 mt-0.5" />
                      <div>
                        <span className="block text-xs text-gray-500">Batas Lamaran</span>
                        <span className={`text-sm font-medium ${isExpired ? 'text-red-600' : 'text-gray-900'}`}>
                          {formatDate(vacancy.closing_date)}
                          {isExpired && ' (Berakhir)'}
                        </span>
                      </div>
                    </div>
                  )}
                </div>

                {!isExpired ? (
                  <div className="space-y-3">
                    {vacancy.apply_link && (
                      <a
                        href={vacancy.apply_link}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="block w-full bg-emerald-600 hover:bg-emerald-700 text-white text-center font-semibold py-3 rounded-lg transition-colors"
                      >
                        Lamar Sekarang
                      </a>
                    )}
                    {vacancy.apply_email && (
                      <a
                        href={`mailto:${vacancy.apply_email}?subject=Lamaran: ${vacancy.title}`}
                        className="block w-full bg-white border-2 border-emerald-600 text-emerald-600 hover:bg-emerald-50 text-center font-semibold py-3 rounded-lg transition-colors"
                      >
                        Kirim Email
                      </a>
                    )}
                    {!vacancy.apply_link && !vacancy.apply_email && (
                      <div className="bg-gray-100 text-gray-500 text-center py-3 rounded-lg text-sm">
                        Info lamaran tidak tersedia
                      </div>
                    )}
                  </div>
                ) : (
                  <div className="bg-red-50 text-red-600 text-center py-3 rounded-lg font-medium border border-red-100">
                    Lowongan Sudah Ditutup
                  </div>
                )}

                {auth?.user && (
                  <div className="mt-4 pt-4 border-t border-gray-100 space-y-2">
                    <button
                      type="button"
                      onClick={handleToggleInterest}
                      className={`w-full px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors ${
                        interest.is_interested
                          ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'
                          : 'bg-emerald-600 text-white hover:bg-emerald-700'
                      }`}
                    >
                      {interest.is_interested ? 'Batalkan ketertarikan' : 'Saya tertarik dengan lowongan ini'}
                    </button>
                    <p className="text-xs text-gray-500 text-center">
                      {interest.total > 0
                        ? `${interest.total} alumni telah menyatakan tertarik pada lowongan ini.`
                        : 'Belum ada alumni yang menyatakan tertarik.'}
                    </p>
                  </div>
                )}

                <div className="mt-6 pt-6 border-t border-gray-100">
                  <p className="text-sm text-gray-500 mb-3 text-center">Bagikan lowongan ini</p>
                  <div className="flex justify-center gap-2">
                    <button 
                      onClick={() => {
                        navigator.clipboard.writeText(window.location.href);
                        alert('Link berhasil disalin!');
                      }}
                      className="p-2 text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-full transition-colors"
                    >
                      <Share2 className="w-5 h-5" />
                    </button>
                    {/* Add other share buttons if needed */}
                  </div>
                </div>
              </div>

              {/* Related Jobs */}
              {related.length > 0 && (
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                  <h3 className="font-bold text-gray-900 mb-4">Lowongan Serupa</h3>
                  <div className="space-y-4">
                    {related.map((job) => (
                      <Link
                        key={job.id}
                        href={route('jobs.show', job.slug)}
                        className="block group"
                      >
                        <div className="flex items-start gap-3">
                          <div className="w-10 h-10 bg-gray-50 rounded flex items-center justify-center flex-shrink-0 border border-gray-100">
                            {job.logo ? (
                              <img src={`/storage/${job.logo}`} alt={job.company} className="w-8 h-8 object-contain" />
                            ) : (
                              <Briefcase className="w-5 h-5 text-gray-400" />
                            )}
                          </div>
                          <div>
                            <h4 className="text-sm font-semibold text-gray-900 group-hover:text-emerald-600 line-clamp-2 transition-colors">
                              {job.title}
                            </h4>
                            <p className="text-xs text-gray-500">{job.company}</p>
                            <p className="text-xs text-emerald-600 mt-1">{job.location}</p>
                          </div>
                        </div>
                      </Link>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </MainLayout>
  );
}
