import React from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { route } from 'ziggy-js';
import { GraduationCap, CheckCircle2, Circle, Clock, ArrowLeft, PlayCircle } from 'lucide-react';

interface Lesson {
  id: number;
  title: string;
  order: number;
  type: string;
  duration_minutes: number;
  is_preview: boolean;
}

interface Module {
  id: number;
  title: string;
  order_number: number;
  lessons: Lesson[];
}

interface Course {
  id: number;
  title: string;
  slug: string;
  excerpt?: string;
  description?: string;
  level: string;
  is_paid: boolean;
  price: string;
  status: string;
  thumbnail?: string;
  duration_minutes: number;
  category?: {
    id: number;
    name: string;
    slug: string;
  } | null;
  creator?: {
    id: number;
    name: string;
  } | null;
  modules: Module[];
  lessons: Lesson[];
}

interface Enrollment {
  id: number;
  status: string;
  progress_percentage: number;
  started_at?: string;
  completed_at?: string;
}

interface Props {
  course: Course;
  related: Course[];
  enrollment: Enrollment | null;
  lessonProgress: Record<number, boolean>;
}

export default function Show({ course, related, enrollment, lessonProgress }: Props) {
  const { auth }: any = usePage().props;

  const isEnrolled = !!enrollment;
  const isSubscriber = !!auth?.user && auth.user.role === 'subscriber';
  const canEnroll = isSubscriber && !course.is_paid;

  const handleEnroll = () => {
    router.post(
      route('courses.enroll', course.slug),
      {},
      {
        preserveScroll: true,
      }
    );
  };

  const handleToggleLesson = (lessonId: number) => {
    if (!isEnrolled) return;

    router.post(
      route('courses.lessons.complete', {
        course: course.slug,
        lesson: lessonId,
      }),
      {},
      {
        preserveScroll: true,
      }
    );
  };

  const totalLessons = course.lessons.length;
  const completedLessons = totalLessons
    ? Object.values(lessonProgress).filter((val) => val).length
    : 0;

  return (
    <MainLayout variant="full">
      <Head title={course.title} />

      <div className="bg-gray-50 min-h-screen pb-12">
        <div className="bg-white border-b border-gray-200 sticky top-16 md:top-20 z-10">
          <div className="container mx-auto px-4 py-4 flex items-center justify-between gap-4">
            <Link
              href={route('courses.index')}
              className="inline-flex items-center text-gray-600 hover:text-emerald-600 transition-colors text-sm"
            >
              <ArrowLeft className="w-4 h-4 mr-2" />
              Kembali ke Katalog Kelas
            </Link>

            {enrollment && (
              <div className="flex items-center gap-3 text-xs md:text-sm">
                <span className="text-gray-600 hidden sm:inline">Progress belajar:</span>
                <div className="flex items-center gap-2">
                  <div className="w-32 sm:w-40 bg-gray-200 rounded-full h-2 overflow-hidden">
                    <div
                      className="bg-emerald-500 h-2 rounded-full"
                      style={{ width: `${enrollment.progress_percentage}%` }}
                    />
                  </div>
                  <span className="text-emerald-700 font-semibold">
                    {enrollment.progress_percentage}%
                  </span>
                </div>
              </div>
            )}
          </div>
        </div>

        <div className="container mx-auto px-4 py-8">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div className="lg:col-span-2 space-y-6">
              <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                {course.thumbnail && (
                  <div className="h-56 md:h-72 bg-gray-100 overflow-hidden">
                    <img
                      src={`/storage/${course.thumbnail}`}
                      alt={course.title}
                      className="w-full h-full object-cover"
                    />
                  </div>
                )}
                <div className="p-6 md:p-8">
                  <div className="flex items-center gap-2 mb-3">
                    <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                      {course.level}
                    </span>
                    {course.category && (
                      <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                        {course.category.name}
                      </span>
                    )}
                  </div>

                  <h1 className="text-2xl md:text-3xl font-bold text-gray-900 mb-3">
                    {course.title}
                  </h1>

                  {course.creator && (
                    <p className="text-sm text-gray-600 mb-4">
                      Dibuat oleh{' '}
                      <span className="font-semibold text-gray-900">
                        {course.creator.name}
                      </span>
                    </p>
                  )}

                  {course.excerpt && (
                    <p className="text-gray-700 mb-4">{course.excerpt}</p>
                  )}

                  {course.description && (
                    <div className="prose prose-emerald max-w-none text-gray-700">
                      <div className="whitespace-pre-line">{course.description}</div>
                    </div>
                  )}
                </div>
              </div>

              <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4">
                  Silabus dan materi pembelajaran
                </h2>

                {course.modules.length > 0 ? (
                  <div className="space-y-4">
                    {course.modules
                      .slice()
                      .sort((a, b) => a.order_number - b.order_number)
                      .map((module) => (
                        <div key={module.id} className="border border-gray-100 rounded-lg">
                          <div className="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                            <span className="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                              Modul
                            </span>
                            <span className="text-sm font-medium text-gray-900">
                              {module.title}
                            </span>
                          </div>
                          <div className="divide-y divide-gray-100">
                            {module.lessons
                              .slice()
                              .sort((a, b) => a.order - b.order)
                              .map((lesson) => {
                                const completed = !!lessonProgress[lesson.id];

                                return (
                                  <button
                                    key={lesson.id}
                                    type="button"
                                    onClick={() => handleToggleLesson(lesson.id)}
                                    className="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                    disabled={!isEnrolled && !lesson.is_preview}
                                  >
                                    <div className="flex items-center gap-3">
                                      {completed ? (
                                        <CheckCircle2 className="w-5 h-5 text-emerald-500" />
                                      ) : (
                                        <Circle className="w-5 h-5 text-gray-300" />
                                      )}
                                      <div>
                                        <div className="text-sm font-medium text-gray-900">
                                          {lesson.title}
                                        </div>
                                        <div className="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                                          <span className="inline-flex items-center gap-1">
                                            <PlayCircle className="w-3 h-3" />
                                            {lesson.type === 'video'
                                              ? 'Video'
                                              : lesson.type === 'text'
                                              ? 'Materi Teks'
                                              : 'Quiz'}
                                          </span>
                                          {lesson.duration_minutes > 0 && (
                                            <span className="inline-flex items-center gap-1">
                                              <Clock className="w-3 h-3" />
                                              {lesson.duration_minutes} menit
                                            </span>
                                          )}
                                          {lesson.is_preview && (
                                            <span className="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-semibold">
                                              Preview Gratis
                                            </span>
                                          )}
                                        </div>
                                      </div>
                                    </div>

                                    {completed && (
                                      <span className="text-[10px] font-semibold text-emerald-600">
                                        Selesai
                                      </span>
                                    )}
                                  </button>
                                );
                              })}
                          </div>
                        </div>
                      ))}
                  </div>
                ) : (
                  <div className="text-sm text-gray-500">
                    Struktur modul akan segera tersedia.
                  </div>
                )}

                {totalLessons > 0 && (
                  <p className="mt-4 text-xs text-gray-500">
                    Total {totalLessons} materi, {completedLessons} sudah ditandai selesai.
                  </p>
                )}
              </div>
            </div>

            <div className="lg:col-span-1 space-y-6">
              <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 className="font-bold text-gray-900 mb-4">Informasi Kelas</h3>

                <div className="space-y-3 text-sm text-gray-700 mb-6">
                  <div className="flex items-center gap-2">
                    <GraduationCap className="w-5 h-5 text-emerald-600" />
                    <span>{course.level}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Clock className="w-5 h-5 text-emerald-600" />
                    <span>
                      Perkiraan durasi{' '}
                      {course.duration_minutes > 0
                        ? `${course.duration_minutes} menit`
                        : 'akan diumumkan'}
                    </span>
                  </div>
                </div>

                <div className="mb-6">
                  {course.is_paid ? (
                    <div>
                      <div className="text-sm text-gray-500 mb-1">Harga Kelas</div>
                      <div className="text-2xl font-bold text-gray-900">
                        Rp {Number(course.price).toLocaleString('id-ID')}
                      </div>
                      <p className="text-xs text-gray-500 mt-1">
                        Pembayaran online akan tersedia di tahap berikutnya.
                      </p>
                    </div>
                  ) : (
                    <div>
                      <div className="text-sm text-gray-500 mb-1">Status</div>
                      <div className="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">
                        Kelas Gratis untuk Alumni
                      </div>
                    </div>
                  )}
                </div>

                {auth?.user ? (
                  <>
                    {isEnrolled ? (
                      <button
                        type="button"
                        className="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-center font-semibold py-3 rounded-lg transition-colors flex items-center justify-center gap-2"
                      >
                        <PlayCircle className="w-5 h-5" />
                        Lanjutkan Belajar
                      </button>
                    ) : (
                      <button
                        type="button"
                        disabled={!canEnroll}
                        onClick={handleEnroll}
                        className={`w-full text-center font-semibold py-3 rounded-lg transition-colors flex items-center justify-center gap-2 ${
                          canEnroll
                            ? 'bg-emerald-600 hover:bg-emerald-700 text-white'
                            : 'bg-gray-200 text-gray-500 cursor-not-allowed'
                        }`}
                      >
                        <GraduationCap className="w-5 h-5" />
                        {course.is_paid ? 'Enrollment akan dibuka nanti' : 'Ikuti Kelas Ini'}
                      </button>
                    )}

                    <p className="text-xs text-gray-500 mt-3">
                      Progress belajarmu akan tersimpan di dashboard alumni.
                    </p>
                  </>
                ) : (
                  <div className="space-y-3">
                    <p className="text-sm text-gray-700">
                      Login sebagai alumni untuk mengikuti kelas dan menyimpan progress belajar.
                    </p>
                    <div className="flex gap-2">
                      <Link
                        href={route('login')}
                        className="flex-1 text-center px-4 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-colors"
                      >
                        Login
                      </Link>
                      <Link
                        href={route('register')}
                        className="flex-1 text-center px-4 py-2.5 rounded-lg border border-emerald-600 text-emerald-700 text-sm font-semibold hover:bg-emerald-50 transition-colors"
                      >
                        Daftar Alumni
                      </Link>
                    </div>
                  </div>
                )}
              </div>

              {related.length > 0 && (
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                  <h3 className="font-bold text-gray-900 mb-4">Kelas lain yang mungkin cocok</h3>
                  <div className="space-y-4">
                    {related.map((item) => (
                      <Link
                        key={item.id}
                        href={route('courses.show', item.slug)}
                        className="block group"
                      >
                        <div className="flex items-start gap-3">
                          <div className="w-12 h-12 bg-gray-50 rounded flex items-center justify-center flex-shrink-0 border border-gray-100">
                            {item.thumbnail ? (
                              <img
                                src={`/storage/${item.thumbnail}`}
                                alt={item.title}
                                className="w-10 h-10 object-cover rounded"
                              />
                            ) : (
                              <GraduationCap className="w-6 h-6 text-gray-400" />
                            )}
                          </div>
                          <div>
                            <h4 className="text-sm font-semibold text-gray-900 group-hover:text-emerald-600 line-clamp-2 transition-colors">
                              {item.title}
                            </h4>
                            {item.category && (
                              <p className="text-xs text-gray-500">{item.category.name}</p>
                            )}
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
