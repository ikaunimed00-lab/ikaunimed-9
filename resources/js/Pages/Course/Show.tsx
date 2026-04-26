import React, { useRef } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { route } from 'ziggy-js';
import {
  GraduationCap,
  CheckCircle2,
  Circle,
  Clock,
  ArrowLeft,
  PlayCircle,
  Lock,
  ShoppingCart,
} from 'lucide-react';
import { resolveLearnerCoursePrimaryAction, resolveLearnerCoursePrimaryActionUi } from '@/lib/learner-course-cta';

interface Lesson {
  id: number;
  title: string;
  order: number;
  type: string;
  duration_minutes: number;
  is_preview: boolean;
}

interface Product {
  id: number;
  slug: string;
  price: string;
  name?: string;
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
  product?: Product | null;
  requires_premium?: boolean;
  enrollment_eligibility?: {
    is_enrolled: boolean;
    can_enroll: boolean;
    requires_premium: boolean;
    is_premium_member: boolean;
    is_locked: boolean;
    reason: string | null;
  };
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
  nextLessonId: number | null;
  enrollmentEligibility?: {
    can_enroll: boolean;
    reason: string | null;
    requires_premium: boolean;
    is_premium_member: boolean;
  };
  premiumMembershipProduct?: Product | null;
}

export default function Show({
  course,
  related,
  enrollment,
  lessonProgress,
  nextLessonId,
  enrollmentEligibility,
  premiumMembershipProduct,
}: Props) {
  const { auth }: any = usePage().props;

  const isLoggedIn = !!auth?.user;
  const isEnrolled = !!enrollment;
  const isPremiumMember =
    !!auth?.user && Array.isArray(auth.user.roles) && auth.user.roles.includes('premium_member');
  const requiresPremium = (course as any).requires_premium === true;
  const canEnroll = enrollmentEligibility?.can_enroll ?? (isLoggedIn && !course.is_paid && (!requiresPremium || isPremiumMember));
  const needsPremiumUpgrade = !isEnrolled && requiresPremium && !isPremiumMember;
  const coursePrimaryAction = resolveLearnerCoursePrimaryAction(
    {
      is_enrolled: isEnrolled,
      can_enroll: canEnroll,
      requires_premium: requiresPremium,
      is_premium_member: isPremiumMember,
      is_locked: needsPremiumUpgrade,
      reason: enrollmentEligibility?.reason ?? null,
    },
    { fallback: 'view_course' }
  );
  const coursePrimaryActionUi = resolveLearnerCoursePrimaryActionUi(coursePrimaryAction, {
    size: 'card',
  });

  const syllabusRef = useRef<HTMLDivElement | null>(null);

  const handleEnroll = () => {
    router.post(
      route('courses.enroll', course.slug),
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

              <div
                ref={syllabusRef}
                className="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
              >
                <h2 className="text-lg font-semibold text-gray-900 mb-4">
                  Tinjau Silabus dan Materi
                </h2>

                {!isEnrolled && (
                  <p className="mb-4 text-xs text-gray-500">
                    Akses beberapa materi bertanda{' '}
                    <span className="font-semibold">Preview Gratis</span>. Materi lain akan
                    terbuka setelah Anda terdaftar pada kelas ini.
                  </p>
                )}

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
                                const locked = !isEnrolled && !lesson.is_preview;
                                const isNext = isEnrolled && nextLessonId === lesson.id;

                                return (
                                  <button
                                    key={lesson.id}
                                    type="button"
                                    onClick={() => {
                                      if (!locked) {
                                        router.visit(
                                          route('courses.lessons.show', {
                                            course: course.slug,
                                            lesson: lesson.id,
                                          })
                                        );
                                      }
                                    }}
                                    className={`w-full flex items-center justify-between px-4 py-3 text-left transition-colors ${
                                      locked
                                        ? 'bg-gray-50 cursor-not-allowed'
                                        : isNext
                                        ? 'bg-emerald-50 hover:bg-emerald-100'
                                        : 'hover:bg-gray-50'
                                    }`}
                                    disabled={locked}
                                  >
                                    <div className="flex items-center gap-3">
                                      {locked ? (
                                        <Lock className="w-5 h-5 text-gray-300" />
                                      ) : completed ? (
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

                                    <div className="flex items-center gap-2">
                                      {completed && (
                                        <span className="text-[10px] font-semibold text-emerald-600">
                                          Selesai
                                        </span>
                                      )}
                                      {isNext && !completed && (
                                        <span className="text-[10px] font-semibold text-emerald-700">
                                          Berikutnya
                                        </span>
                                      )}
                                    </div>
                                  </button>
                                );
                              })}
                          </div>
                        </div>
                      ))}
                  </div>
                ) : (
                  <div className="text-sm text-gray-500">
                    Tunggu pembaruan struktur modul.
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
                      <div className="text-sm text-gray-500 mb-1">Lihat Biaya Kelas</div>
                      <div className="text-2xl font-bold text-gray-900">
                        Rp {Number(course.price).toLocaleString('id-ID')}
                      </div>
                      <p className="text-xs text-gray-500 mt-1">
                        Pembayaran online akan tersedia di tahap berikutnya.
                      </p>
                    </div>
                  ) : (
                    <div>
                      <div className="text-sm text-gray-500 mb-1">Lihat Status</div>
                      <div className="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">
                        Kelas Gratis untuk Alumni
                      </div>
                    </div>
                  )}
                </div>

                {needsPremiumUpgrade && (
                  <div className="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p className="text-sm font-semibold text-amber-900">
                      Kelas ini khusus member premium.
                    </p>
                    <p className="mt-1 text-xs text-amber-800">
                      Upgrade ke Premium Member untuk membuka akses enrollment dan materi penuh.
                    </p>
                  </div>
                )}

                {auth?.user ? (
                  <>
                    {isEnrolled ? (
                      <button
                        type="button"
                        onClick={() => {
                          if (nextLessonId) {
                            router.visit(
                              route('courses.lessons.show', {
                                course: course.slug,
                                lesson: nextLessonId,
                              })
                            );
                          } else if (syllabusRef.current) {
                            syllabusRef.current.scrollIntoView({
                              behavior: 'smooth',
                              block: 'start',
                            });
                          }
                        }}
                        className="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-center font-semibold py-3 rounded-lg transition-colors flex items-center justify-center gap-2"
                      >
                        <PlayCircle className="w-5 h-5" />
                        Lanjutkan Belajar
                      </button>
                    ) : course.is_paid && course.product ? (
                      <button
                        type="button"
                        onClick={() => {
                          router.post(route('shop.cart.add', course.product!.slug));
                        }}
                        className="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-center font-semibold py-3 rounded-lg transition-colors flex items-center justify-center gap-2"
                      >
                        <ShoppingCart className="w-5 h-5" />
                        Beli Kelas
                      </button>
                    ) : coursePrimaryAction === 'upgrade_premium' ? (
                      <button
                        type="button"
                        onClick={() => {
                          if (premiumMembershipProduct?.slug) {
                            router.post(route('shop.cart.add', premiumMembershipProduct.slug));
                            return;
                          }

                          router.visit(route('shop.index'));
                        }}
                        className="w-full bg-amber-600 hover:bg-amber-700 text-white text-center font-semibold py-3 rounded-lg transition-colors flex items-center justify-center gap-2"
                      >
                        <ShoppingCart className="w-5 h-5" />
                        {coursePrimaryActionUi.label}
                      </button>
                    ) : coursePrimaryAction === 'join_course' ? (
                      <button
                        type="button"
                        onClick={handleEnroll}
                        className="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-center font-semibold py-3 rounded-lg transition-colors flex items-center justify-center gap-2"
                      >
                        <GraduationCap className="w-5 h-5" />
                        Ikuti Kelas Ini
                      </button>
                    ) : (
                      <Link
                        href={coursePrimaryAction === 'login' ? route('login') : route('courses.show', course.slug)}
                        className={coursePrimaryActionUi.className.replace('text-xs', 'text-sm').replace('py-2', 'py-3')}
                      >
                        {coursePrimaryActionUi.label}
                      </Link>
                    )}

                    <p className="text-xs text-gray-500 mt-3">
                      Progress belajarmu akan tersimpan di dashboard alumni.
                    </p>
                  </>
                ) : (
                  <div className="space-y-3">
                    <p className="text-sm text-gray-700">
                      Masuk sebagai alumni untuk mengikuti kelas dan menyimpan progres belajar.
                    </p>
                    <div className="flex gap-2">
                      <Link
                        href={route('login')}
                        className="flex-1 text-center px-4 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-colors"
                      >
                        Masuk
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
                  <h3 className="font-bold text-gray-900 mb-4">Eksplorasi Kelas Terkait</h3>
                  <div className="space-y-4">
                    {related.map((item) => {
                      const relatedEligibility = item.enrollment_eligibility;
                      const isRelatedLocked = !!relatedEligibility?.is_locked;
                      const relatedPrimaryAction = resolveLearnerCoursePrimaryAction(relatedEligibility, {
                        fallback: 'view_course',
                      });
                      const relatedPrimaryActionUi = resolveLearnerCoursePrimaryActionUi(
                        relatedPrimaryAction,
                        { size: 'card' }
                      );

                      return (
                        <div key={item.id} className="rounded-lg border border-gray-100 p-3">
                          <Link href={route('courses.show', item.slug)} className="block group">
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
                              <div className="min-w-0 flex-1">
                                <h4 className="text-sm font-semibold text-gray-900 group-hover:text-emerald-600 line-clamp-2 transition-colors">
                                  {item.title}
                                </h4>
                                {item.category && (
                                  <p className="text-xs text-gray-500">{item.category.name}</p>
                                )}
                                <div className="mt-2 flex flex-wrap gap-1.5">
                                  <span className="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                    {item.level}
                                  </span>
                                  {item.is_paid ? (
                                    <span className="inline-flex items-center rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-semibold text-white">
                                      Berbayar
                                    </span>
                                  ) : (
                                    <span className="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                      Gratis
                                    </span>
                                  )}
                                  {!item.is_paid && item.requires_premium && (
                                    <span
                                      className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ${
                                        isRelatedLocked ? 'bg-amber-100 text-amber-800' : 'bg-amber-50 text-amber-700'
                                      }`}
                                    >
                                      {isRelatedLocked && <Lock className="h-3 w-3" />}
                                      {isRelatedLocked ? 'Akses Terkunci' : 'Premium'}
                                    </span>
                                  )}
                                </div>
                              </div>
                            </div>
                          </Link>

                          <div className="mt-3">
                            {relatedPrimaryAction === 'upgrade_premium' ? (
                              <button
                                type="button"
                                onClick={() => {
                                  if (premiumMembershipProduct?.slug) {
                                    router.post(route('shop.cart.add', premiumMembershipProduct.slug));
                                    return;
                                  }

                                  router.visit(route('shop.index'));
                                }}
                                className="w-full inline-flex items-center justify-center gap-1 rounded-md bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700"
                              >
                                <ShoppingCart className="h-3.5 w-3.5" />
                                {relatedPrimaryActionUi.label}
                              </button>
                            ) : (
                              <Link
                                href={relatedPrimaryAction === 'login' || !auth?.user ? route('login') : route('courses.show', item.slug)}
                                className={relatedPrimaryActionUi.className}
                              >
                                {relatedPrimaryActionUi.label}
                              </Link>
                            )}
                          </div>
                        </div>
                      );
                    })}
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
