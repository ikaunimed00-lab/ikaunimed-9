import React, { useState } from 'react';
import { Head, Link, usePage, router } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { Briefcase, MapPin, GraduationCap } from 'lucide-react';
import { route } from 'ziggy-js';

interface PrimaryEducation {
  level: string;
  major: string;
  admission_year: number;
  graduation_year?: number | null;
}

interface ElearningCourseSummary {
  title: string;
  status: string;
  progress_percentage?: number | null;
}

interface ElearningActivity {
  total_courses: number;
  completed_courses: number;
  recent_courses: ElearningCourseSummary[];
}

interface AlumniItem {
  id: number;
  name: string;
  bidang_pekerjaan?: string | null;
  posisi_saat_ini?: string | null;
  perusahaan?: string | null;
  kota_profesional?: string | null;
  status_pekerjaan?: string | null;
  profile_level: number;
  profile_completion_score: number;
  primary_education?: PrimaryEducation | null;
  elearning_activity?: ElearningActivity | null;
}

interface PaginatedAlumni {
  data: AlumniItem[];
  links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
  alumni: PaginatedAlumni;
  filters: {
    search?: string;
    bidang_pekerjaan?: string;
    status_pekerjaan?: string;
    angkatan?: string;
  };
}

const statusBadge = (status?: string | null) => {
  if (!status) return null;

  const map: Record<string, { label: string; className: string }> = {
    tetap: { label: 'Tetap', className: 'bg-emerald-100 text-emerald-800' },
    kontrak: { label: 'Kontrak', className: 'bg-blue-100 text-blue-800' },
    wirausaha: { label: 'Entrepreneur', className: 'bg-amber-100 text-amber-800' },
    freelancer: { label: 'Freelancer', className: 'bg-purple-100 text-purple-800' },
    studi_lanjut: { label: 'Studi Lanjut', className: 'bg-indigo-100 text-indigo-800' },
    mencari_kerja: { label: 'Open to Work', className: 'bg-rose-100 text-rose-800' },
  };

  const config = map[status] ?? { label: status, className: 'bg-gray-100 text-gray-800' };

  return (
    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${config.className}`}>
      {config.label}
    </span>
  );
};

export default function Index({ alumni, filters }: Props) {
  const { auth }: any = usePage().props;

  const [search, setSearch] = useState(filters.search || '');
  const [bidang, setBidang] = useState(filters.bidang_pekerjaan || '');
  const [status, setStatus] = useState(filters.status_pekerjaan || '');
  const [angkatan, setAngkatan] = useState(filters.angkatan || '');

  const handleFilterSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(
      route('alumni.directory'),
      {
        search,
        bidang_pekerjaan: bidang,
        status_pekerjaan: status,
        angkatan,
      },
      {
        preserveState: true,
        preserveScroll: true,
      }
    );
  };

  const handleReset = () => {
    setSearch('');
    setBidang('');
    setStatus('');
    setAngkatan('');
    router.get(route('alumni.directory'), {}, { preserveState: false, preserveScroll: true });
  };

  const bidangOptions = [
    'Pendidikan',
    'Pemerintahan',
    'Swasta',
    'Wirausaha',
    'BUMN/BUMD',
    'Non-Profit/LSM',
    'Freelance/Konsultan',
    'Teknologi Informasi',
    'Kreatif/Media',
    'Kesehatan',
    'Industri/Manufaktur',
    'Lainnya',
  ];

  const statusOptions = [
    { value: 'tetap', label: 'Tetap' },
    { value: 'kontrak', label: 'Kontrak' },
    { value: 'wirausaha', label: 'Wirausaha' },
    { value: 'freelancer', label: 'Freelancer' },
    { value: 'studi_lanjut', label: 'Studi Lanjut' },
    { value: 'mencari_kerja', label: 'Open to Work' },
  ];

  return (
    <MainLayout variant="full">
      <Head title="Direktori Alumni IKA UNIMED" />

      <div className="bg-sky-700 py-16">
        <div className="container mx-auto px-4 text-center">
          <h1 className="text-3xl md:text-4xl font-bold text-white mb-4">
            Direktori Alumni IKA UNIMED
          </h1>
          <p className="text-sky-100 max-w-2xl mx-auto mb-8">
            Temukan jejaring profesional alumni IKA UNIMED lintas angkatan dan bidang kerja.
          </p>

          <div className="flex flex-col md:flex-row md:items-center md:justify-center gap-4">
            <Link
              href={route('register')}
              className="inline-flex items-center justify-center px-6 py-3 rounded-md bg-white text-sky-700 font-semibold shadow hover:bg-sky-50 transition-colors"
            >
              Gabung & Tampilkan Profil Anda
            </Link>

            {auth?.user && (
              <Link
                href={route('profile.edit')}
                className="inline-flex items-center justify-center px-6 py-3 rounded-md border border-sky-100 text-white font-semibold hover:bg-sky-600 transition-colors"
              >
                Lengkapi Profil Alumni
              </Link>
            )}
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-12">
        <div className="bg-white rounded-lg border border-gray-200 shadow-sm p-4 mb-8">
          <form
            onSubmit={handleFilterSubmit}
            className="flex flex-col lg:flex-row gap-4 items-stretch lg:items-end"
          >
            <div className="flex-1">
              <label className="block text-xs font-semibold text-gray-600 mb-1">
                Cari nama alumni
              </label>
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Ketik nama alumni..."
                className="w-full border-gray-300 rounded-md text-sm focus:ring-sky-500 focus:border-sky-500"
              />
            </div>

            <div className="w-full lg:w-56">
              <label className="block text-xs font-semibold text-gray-600 mb-1">
                Bidang pekerjaan
              </label>
              <select
                value={bidang}
                onChange={(e) => setBidang(e.target.value)}
                className="w-full border-gray-300 rounded-md text-sm focus:ring-sky-500 focus:border-sky-500"
              >
                <option value="">Semua bidang</option>
                {bidangOptions.map((b) => (
                  <option key={b} value={b}>
                    {b}
                  </option>
                ))}
              </select>
            </div>

            <div className="w-full lg:w-48">
              <label className="block text-xs font-semibold text-gray-600 mb-1">
                Status pekerjaan
              </label>
              <select
                value={status}
                onChange={(e) => setStatus(e.target.value)}
                className="w-full border-gray-300 rounded-md text-sm focus:ring-sky-500 focus:border-sky-500"
              >
                <option value="">Semua status</option>
                {statusOptions.map((s) => (
                  <option key={s.value} value={s.value}>
                    {s.label}
                  </option>
                ))}
              </select>
            </div>

            <div className="w-full lg:w-36">
              <label className="block text-xs font-semibold text-gray-600 mb-1">
                Angkatan
              </label>
              <input
                type="number"
                value={angkatan}
                onChange={(e) => setAngkatan(e.target.value)}
                placeholder="2010"
                className="w-full border-gray-300 rounded-md text-sm focus:ring-sky-500 focus:border-sky-500"
              />
            </div>

            <div className="flex gap-2">
              <button
                type="submit"
                className="inline-flex items-center justify-center px-4 py-2 rounded-md bg-sky-600 text-white text-sm font-semibold hover:bg-sky-700 transition-colors"
              >
                Terapkan
              </button>
              <button
                type="button"
                onClick={handleReset}
                className="inline-flex items-center justify-center px-3 py-2 rounded-md border border-gray-300 text-xs font-medium text-gray-700 hover:bg-gray-50"
              >
                Reset
              </button>
            </div>
          </form>
        </div>

        {alumni.data.length === 0 ? (
          <div className="bg-white rounded-lg border border-dashed border-gray-300 p-12 text-center">
            <GraduationCap className="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <h2 className="text-xl font-semibold text-gray-900 mb-2">
              Belum ada alumni yang tampil di direktori
            </h2>
            <p className="text-gray-600 mb-6 max-w-xl mx-auto">
              Direktori ini akan terisi seiring alumni melengkapi profil profesional mereka
              dan mengaktifkan opsi tampil di direktori.
            </p>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {alumni.data.map((item) => (
              <div
                key={item.id}
                className="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-5 flex flex-col gap-3"
              >
                <div className="flex items-start justify-between gap-3">
                  <div>
                    <h2 className="text-lg font-semibold text-gray-900">
                      {item.name}
                    </h2>
                    {item.primary_education && (
                      <p className="text-sm text-gray-600 mt-1">
                        {item.primary_education.level} {item.primary_education.major}
                      </p>
                    )}
                  </div>
                  <div className="flex flex-col items-end gap-2">
                    {statusBadge(item.status_pekerjaan)}
                    {item.profile_level > 1 && (
                      <span className="text-[10px] uppercase tracking-wide text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                        Profil Lengkap
                      </span>
                    )}
                  </div>
                </div>

                <div className="flex items-start gap-3 text-sm text-gray-700">
                  <div className="mt-1">
                    <Briefcase className="w-4 h-4 text-gray-400" />
                  </div>
                  <div>
                    {item.posisi_saat_ini && item.perusahaan ? (
                      <p className="font-medium">
                        {item.posisi_saat_ini} – {item.perusahaan}
                      </p>
                    ) : (
                      <p className="font-medium text-gray-500">
                        Posisi belum diisi
                      </p>
                    )}
                    {item.kota_profesional && (
                      <p className="text-gray-500 flex items-center gap-1 mt-1">
                        <MapPin className="w-3 h-3 text-gray-400" />
                        {item.kota_profesional}
                      </p>
                    )}
                  </div>
                </div>

                <div className="border-t border-gray-100 pt-3 mt-1">
                  <div className="flex items-center gap-2 text-xs font-semibold text-emerald-700 uppercase tracking-wide">
                    <GraduationCap className="w-4 h-4" />
                    <span>Aktivitas E-Learning</span>
                  </div>

                  {item.elearning_activity && item.elearning_activity.total_courses > 0 ? (
                    <>
                      <p className="mt-1 text-sm text-gray-700">
                        Mengikuti {item.elearning_activity.total_courses} kelas;{' '}
                        {item.elearning_activity.completed_courses} selesai.
                      </p>
                      {item.elearning_activity.recent_courses.length > 0 && (
                        <ul className="mt-2 space-y-1">
                          {item.elearning_activity.recent_courses.map((course) => (
                            <li
                              key={`${course.title}-${course.status}`}
                              className="flex items-center justify-between gap-2 text-xs text-gray-600"
                            >
                              <span className="truncate">{course.title}</span>
                              <span
                                className={`px-1.5 py-0.5 rounded-full border text-[10px] capitalize ${
                                  course.status === 'completed'
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                    : 'bg-sky-50 text-sky-700 border-sky-100'
                                }`}
                              >
                                {course.status === 'completed' ? 'Selesai' : 'Sedang berjalan'}
                              </span>
                            </li>
                          ))}
                        </ul>
                      )}
                    </>
                  ) : (
                    <p className="mt-1 text-sm text-gray-500">
                      Belum ada aktivitas e-learning yang tercatat.
                    </p>
                  )}
                </div>
              </div>
            ))}
          </div>
        )}

        {alumni.links.length > 3 && (
          <div className="mt-10 flex justify-center">
            <div className="flex gap-1">
              {alumni.links.map((link, i) => (
                <Link
                  key={i}
                  href={link.url || '#'}
                  className={`px-3 py-1.5 text-sm rounded-md transition-colors ${
                    link.active
                      ? 'bg-sky-600 text-white'
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
