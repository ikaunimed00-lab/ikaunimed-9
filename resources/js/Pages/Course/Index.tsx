import React, { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { Search, GraduationCap, Filter, X, BadgeDollarSign, Lock, ShoppingCart } from 'lucide-react';
import { route } from 'ziggy-js';
import { resolveLearnerCoursePrimaryAction, resolveLearnerCoursePrimaryActionUi } from '@/lib/learner-course-cta';

interface CourseCategory {
  id: number;
  name: string;
  slug: string;
}

interface Course {
  id: number;
  title: string;
  slug: string;
  excerpt?: string;
  level: string;
  thumbnail?: string;
  is_paid: boolean;
  requires_premium?: boolean;
  price: string;
  category?: {
    id: number;
    name: string;
    slug: string;
  } | null;
  enrollment_eligibility?: {
    is_enrolled: boolean;
    can_enroll: boolean;
    requires_premium: boolean;
    is_premium_member: boolean;
    is_locked: boolean;
    reason: string | null;
  };
}

interface Product {
  id: number;
  slug: string;
  name: string;
  price: number | string;
}

interface Props {
  courses: {
    data: Course[];
    links: any[];
  };
  categories: CourseCategory[];
  filters: {
    search?: string;
    category?: string;
    level?: string;
    price_type?: string;
  };
  premiumMembershipProduct?: Product | null;
}

export default function Index({ courses, categories, filters, premiumMembershipProduct }: Props) {
  const { auth }: any = usePage().props;
  const [search, setSearch] = useState(filters.search || '');
  const [category, setCategory] = useState(filters.category || '');
  const [level, setLevel] = useState(filters.level || '');
  const [priceType, setPriceType] = useState(filters.price_type || '');

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(
      route('courses.index'),
      { search, category, level, price_type: priceType },
      { preserveState: true }
    );
  };

  const handleFilterChange = (key: 'category' | 'level' | 'price_type', value: string) => {
    if (key === 'category') {
      setCategory(value);
    } else if (key === 'level') {
      setLevel(value);
    } else if (key === 'price_type') {
      setPriceType(value);
    }

    router.get(
      route('courses.index'),
      {
        search,
        category: key === 'category' ? value : category,
        level: key === 'level' ? value : level,
        price_type: key === 'price_type' ? value : priceType,
      },
      { preserveState: true }
    );
  };

  const resetFilters = () => {
    setSearch('');
    setCategory('');
    setLevel('');
    setPriceType('');
    router.get(route('courses.index'));
  };

  const levels = ['Beginner', 'Intermediate', 'Advanced'];

  return (
    <MainLayout variant="full">
      <Head title="Kelas & Kursus" />

      <div className="bg-emerald-600 py-16">
        <div className="container mx-auto px-4 text-center">
          <h1 className="text-3xl md:text-4xl font-bold text-white mb-4">
            Ikuti Kelas Bersama Alumni
          </h1>
          <p className="text-emerald-100 max-w-2xl mx-auto mb-8">
            Pilih kelas online dari alumni dan praktisi untuk mengembangkan kompetensi profesional.
          </p>

          <form
            onSubmit={handleSearch}
            className="max-w-3xl mx-auto bg-white p-2 rounded-lg shadow-lg flex flex-col md:flex-row gap-2"
          >
            <div className="flex-1 flex items-center px-4 border-b md:border-b-0 md:border-r border-gray-200">
              <Search className="w-5 h-5 text-gray-400 mr-3" />
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Masukkan kelas, topik, atau instruktur"
                className="w-full border-none focus:ring-0 text-gray-700 placeholder-gray-400"
              />
            </div>
            <div className="w-full md:w-40 px-2">
              <select
                value={priceType}
                onChange={(e) => handleFilterChange('price_type', e.target.value)}
                className="w-full border-none focus:ring-0 text-gray-700 bg-transparent cursor-pointer"
              >
                <option value="">Semua Kelas</option>
                <option value="free">Gratis</option>
                <option value="paid">Berbayar</option>
              </select>
            </div>
            <button
              type="submit"
              className="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-8 py-3 rounded-md transition-colors"
            >
              Terapkan Pencarian
            </button>
          </form>
        </div>
      </div>

      <div className="container mx-auto px-4 py-12">
        <div className="flex flex-col md:flex-row gap-8">
          <div className="hidden md:block w-72 flex-shrink-0">
            <div className="bg-white rounded-lg shadow-sm border border-gray-100 p-6 sticky top-24">
              <div className="flex items-center justify-between mb-4">
                <h3 className="font-semibold text-gray-900 flex items-center">
                  <Filter className="w-4 h-4 mr-2" />
                  Filter
                </h3>
                {(search || category || level || priceType) && (
                  <button
                    type="button"
                    onClick={resetFilters}
                    className="text-xs text-red-500 hover:underline flex items-center"
                  >
                    <X className="w-3 h-3 mr-1" /> Atur Ulang
                  </button>
                )}
              </div>

              <div className="space-y-6">
                <div>
                  <h4 className="text-sm font-medium text-gray-700 mb-3">Kategori</h4>
                  <div className="space-y-2 max-h-48 overflow-y-auto pr-1">
                    <button
                      type="button"
                      onClick={() => handleFilterChange('category', '')}
                      className={`w-full text-left px-3 py-1.5 rounded-md text-sm ${
                        category === '' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50'
                      }`}
                    >
                      Semua Kategori
                    </button>
                    {categories.map((cat) => (
                      <button
                        key={cat.id}
                        type="button"
                        onClick={() => handleFilterChange('category', cat.slug)}
                        className={`w-full text-left px-3 py-1.5 rounded-md text-sm ${
                          category === cat.slug
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'text-gray-600 hover:bg-gray-50'
                        }`}
                      >
                        {cat.name}
                      </button>
                    ))}
                  </div>
                </div>

                <div>
                  <h4 className="text-sm font-medium text-gray-700 mb-3">Level</h4>
                  <div className="space-y-2">
                    <button
                      type="button"
                      onClick={() => handleFilterChange('level', '')}
                      className={`w-full text-left px-3 py-1.5 rounded-md text-sm ${
                        level === '' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50'
                      }`}
                    >
                      Semua Level
                    </button>
                    {levels.map((lv) => (
                      <button
                        key={lv}
                        type="button"
                        onClick={() => handleFilterChange('level', lv)}
                        className={`w-full text-left px-3 py-1.5 rounded-md text-sm ${
                          level === lv ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50'
                        }`}
                      >
                        {lv}
                      </button>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="flex-1">
            {courses.data.length > 0 ? (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {courses.data.map((course) => {
                  const eligibility = course.enrollment_eligibility;
                  const isLocked = !!eligibility?.is_locked;
                  const primaryAction = resolveLearnerCoursePrimaryAction(eligibility, {
                    fallback: 'view_course',
                  });
                  const primaryActionUi = resolveLearnerCoursePrimaryActionUi(primaryAction, {
                    size: 'card',
                  });

                  return (
                    <div
                      key={course.id}
                      className="group bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all flex flex-col h-full"
                    >
                    <Link href={route('courses.show', course.slug)} className="relative block h-44 bg-gray-100 overflow-hidden">
                      {course.thumbnail ? (
                        <img
                          src={`/storage/${course.thumbnail}`}
                          alt={course.title}
                          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                      ) : (
                        <div className="w-full h-full flex items-center justify-center bg-emerald-50 text-emerald-200">
                          <GraduationCap className="w-16 h-16" />
                        </div>
                      )}
                      <div className="absolute top-4 left-4 flex gap-2">
                        <span className="bg-white/90 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
                          {course.level}
                        </span>
                        {course.is_paid ? (
                          <span className="bg-emerald-600 text-white text-xs font-semibold px-3 py-1 rounded-full flex items-center gap-1">
                            <BadgeDollarSign className="w-3 h-3" />
                            Berbayar
                          </span>
                        ) : (
                          <span className="bg-emerald-50 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">
                            Gratis
                          </span>
                        )}
                        {!course.is_paid && course.requires_premium && (
                          <span
                            className={`text-xs font-semibold px-3 py-1 rounded-full inline-flex items-center gap-1 ${
                              isLocked ? 'bg-amber-100 text-amber-800' : 'bg-amber-50 text-amber-700'
                            }`}
                          >
                            {isLocked && <Lock className="w-3 h-3" />}
                            {isLocked ? 'Akses Terkunci' : 'Premium'}
                          </span>
                        )}
                      </div>
                    </Link>

                    <div className="p-5 flex-1 flex flex-col">
                      <Link href={route('courses.show', course.slug)} className="mb-2">
                        <h3 className="text-base font-bold text-gray-900 group-hover:text-emerald-600 transition-colors line-clamp-2">
                          {course.title}
                        </h3>
                      </Link>
                      {course.category && (
                        <p className="text-xs font-medium text-emerald-700 mb-2">
                          {course.category.name}
                        </p>
                      )}
                      {course.excerpt && (
                        <p className="text-sm text-gray-600 line-clamp-3 mb-4">{course.excerpt}</p>
                      )}
                      <div className="mt-auto flex items-center justify-between text-xs text-gray-500 pt-2">
                        <span>
                          {course.is_paid
                            ? `Mulai dari Rp ${Number(course.price).toLocaleString('id-ID')}`
                            : course.requires_premium
                            ? 'Gratis, khusus Premium Member'
                            : 'Kelas gratis untuk alumni'}
                        </span>
                        <span className="text-emerald-600 font-semibold text-xs group-hover:underline">
                          Lihat detail
                        </span>
                      </div>

                      {isLocked && (
                        <p className="mt-3 text-xs text-amber-700">
                          Akses materi dikunci. Upgrade ke Premium Member untuk lanjut belajar.
                        </p>
                      )}

                      <div className="mt-4">
                        {primaryAction === 'upgrade_premium' ? (
                          <button
                            type="button"
                            onClick={() => {
                              if (premiumMembershipProduct?.slug) {
                                router.post(route('shop.cart.add', premiumMembershipProduct.slug));
                                return;
                              }

                              router.visit(route('shop.index'));
                            }}
                            className={primaryActionUi.className}
                          >
                            {primaryActionUi.showCartIcon && <ShoppingCart className="h-3 w-3" />}
                            {primaryActionUi.label}
                          </button>
                        ) : (
                          <Link
                            href={primaryAction === 'login' || !auth?.user ? route('login') : route('courses.show', course.slug)}
                            className={primaryActionUi.className}
                          >
                            {primaryActionUi.label}
                          </Link>
                        )}
                      </div>
                    </div>
                    </div>
                  );
                })}
              </div>
            ) : (
              <div className="text-center py-12 bg-white rounded-lg border border-gray-200">
                <GraduationCap className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">Belum ada kelas tersedia</h3>
                <p className="text-gray-500">
                  Cek kembali dalam beberapa saat.
                </p>
              </div>
            )}

            {courses.links.length > 3 && (
              <div className="mt-10 flex justify-center">
                <div className="flex gap-1">
                  {courses.links.map((link, i) => (
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

