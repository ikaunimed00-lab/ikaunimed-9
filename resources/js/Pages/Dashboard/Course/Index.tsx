import React, { useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { route } from "ziggy-js";
import AdminLayout from "@/Layouts/AdminLayout";
import EditorLayout from "@/Layouts/EditorLayout";
import SubscriberLayout from "@/Layouts/SubscriberLayout";
import { BookOpen, Users, Percent, Search, Coins } from "lucide-react";

interface CourseCategory {
  id: number;
  name: string;
  slug: string;
}

interface Course {
  id: number;
  title: string;
  slug: string;
  level: string;
  status: string;
  category?: CourseCategory | null;
  enrollments_count?: number;
  enrollments_avg_progress?: number | null;
  revenue_total?: number;
}

interface CoursesPagination {
  data: Course[];
  links: any[];
  meta?: any;
}

interface Props {
  courses: CoursesPagination;
  stats: {
    total_courses: number;
    active_participants: number;
    average_progress: number;
    total_revenue?: number;
  };
  categories: CourseCategory[];
  filters: {
    status?: string;
    category?: string;
    search?: string;
    date_from?: string;
    date_to?: string;
  };
  lmsRoles?: {
    instructor: boolean;
    learner: boolean;
    lms_moderator: boolean;
  };
}

const statusLabel = (status: string) => {
  if (status === "draft") return "Draft";
  if (status === "published") return "Published";
  if (status === "archived") return "Archived";
  return status;
};

const statusBadgeClass = (status: string) => {
  if (status === "draft") return "bg-yellow-100 text-yellow-800 border-yellow-200";
  if (status === "published") return "bg-green-100 text-green-800 border-green-200";
  if (status === "archived") return "bg-gray-100 text-gray-700 border-gray-200";
  return "bg-gray-100 text-gray-800 border-gray-200";
};

export default function Index({ courses, stats, categories, filters }: Props) {
  const { auth }: any = usePage().props;
  const role = auth?.user?.role || "subscriber";
  const Layout = role === "admin" ? AdminLayout : role === "editor" ? EditorLayout : SubscriberLayout;

  const [search, setSearch] = useState(filters.search || "");
  const [status, setStatus] = useState(filters.status || "");
  const [category, setCategory] = useState(filters.category || "");
  const [dateFrom, setDateFrom] = useState(filters.date_from || "");
  const [dateTo, setDateTo] = useState(filters.date_to || "");

  const formatRupiah = (value: number | undefined | null) => {
    if (!value) return "0";
    return value.toLocaleString("id-ID");
  };

  const handleFilterSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(
      route("dashboard.courses.index"),
      {
        search,
        status,
        category,
        date_from: dateFrom,
        date_to: dateTo,
      },
      {
        preserveState: true,
        preserveScroll: true,
      }
    );
  };

  const handleReset = () => {
    setSearch("");
    setStatus("");
    setCategory("");
    setDateFrom("");
    setDateTo("");
    router.get(route("dashboard.courses.index"), {}, { preserveState: false, preserveScroll: true });
  };

  return (
    <Layout>
      <Head title="Kursus Saya" />

      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Kursus Saya</h1>
            <p className="text-gray-500">Daftar kursus yang Anda kelola sebagai instruktur.</p>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div className="bg-white border border-gray-200 rounded-lg p-5 flex items-center gap-4">
            <div className="h-10 w-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
              <BookOpen className="w-5 h-5" />
            </div>
            <div>
              <div className="text-sm text-gray-600">Kursus Dikelola</div>
              <div className="text-2xl font-bold text-gray-900">{stats.total_courses || 0}</div>
            </div>
          </div>

          <div className="bg-white border border-gray-200 rounded-lg p-5 flex items-center gap-4">
            <div className="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
              <Users className="w-5 h-5" />
            </div>
            <div>
              <div className="text-sm text-gray-600">Peserta Aktif</div>
              <div className="text-2xl font-bold text-gray-900">{stats.active_participants || 0}</div>
            </div>
          </div>

          <div className="bg-white border border-gray-200 rounded-lg p-5 flex items-center gap-4">
            <div className="h-10 w-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
              <Percent className="w-5 h-5" />
            </div>
            <div>
              <div className="text-sm text-gray-600">Rata-rata Progres</div>
              <div className="text-2xl font-bold text-gray-900">
                {stats.average_progress ? `${stats.average_progress}%` : "0%"}
              </div>
            </div>
          </div>

          <div className="bg-white border border-gray-200 rounded-lg p-5 flex items-center gap-4">
            <div className="h-10 w-10 rounded-lg bg-yellow-50 flex items-center justify-center text-yellow-600">
              <Coins className="w-5 h-5" />
            </div>
            <div>
              <div className="text-sm text-gray-600">Total Revenue Kursus</div>
              <div className="text-2xl font-bold text-gray-900">
                Rp {formatRupiah(stats.total_revenue ?? 0)}
              </div>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg border border-gray-200 shadow-sm">
          <div className="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <h2 className="text-lg font-semibold text-gray-900">Kursus Saya</h2>
              <p className="text-sm text-gray-600">
                Kelola konten dan pantau progres peserta di setiap kursus.
              </p>
            </div>
            <form onSubmit={handleFilterSubmit} className="flex flex-col md:flex-row gap-3 md:items-center">
              <div className="relative">
                <span className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Search className="w-4 h-4 text-gray-400" />
                </span>
                <input
                  type="text"
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  placeholder="Cari judul kursus"
                  className="pl-9 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-emerald-500 focus:border-emerald-500"
                />
              </div>
              <select
                value={status}
                onChange={(e) => setStatus(e.target.value)}
                className="border border-gray-300 rounded-md text-sm px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
              >
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
              </select>
              <select
                value={category}
                onChange={(e) => setCategory(e.target.value)}
                className="border border-gray-300 rounded-md text-sm px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
              >
                <option value="">Semua Kategori</option>
                {categories.map((cat) => (
                  <option key={cat.id} value={cat.slug}>
                    {cat.name}
                  </option>
                ))}
              </select>
              <input
                type="date"
                value={dateFrom}
                onChange={(e) => setDateFrom(e.target.value)}
                className="border border-gray-300 rounded-md text-sm px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
              />
              <input
                type="date"
                value={dateTo}
                onChange={(e) => setDateTo(e.target.value)}
                className="border border-gray-300 rounded-md text-sm px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
              />
              <div className="flex gap-2">
                <button
                  type="submit"
                  className="px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700"
                >
                  Terapkan
                </button>
                <button
                  type="button"
                  onClick={handleReset}
                  className="px-3 py-2 rounded-md border border-gray-300 text-xs font-medium text-gray-700 hover:bg-gray-50"
                >
                  Reset
                </button>
              </div>
            </form>
          </div>

          {courses.data.length === 0 ? (
            <div className="px-6 py-10 text-center text-gray-500">
              Belum ada kursus yang Anda kelola.
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="min-w-full text-sm text-gray-700">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className="px-6 py-3 text-left font-semibold">Kursus</th>
                    <th className="px-6 py-3 text-left font-semibold">Status</th>
                    <th className="px-6 py-3 text-left font-semibold">Peserta Aktif</th>
                    <th className="px-6 py-3 text-left font-semibold">Rata-rata Progres</th>
                    <th className="px-6 py-3 text-left font-semibold">Revenue</th>
                    <th className="px-6 py-3 text-right font-semibold">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {courses.data.map((course) => {
                    const average = course.enrollments_avg_progress ?? 0;
                    const progressValue = typeof average === "number" ? Math.round(average) : 0;
                    const revenue = course.revenue_total ?? 0;
                    return (
                      <tr key={course.id} className="hover:bg-gray-50">
                        <td className="px-6 py-4">
                          <div className="font-semibold text-gray-900">{course.title}</div>
                          <div className="flex items-center gap-2 mt-1 text-xs">
                            <span className="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium">
                              {course.level}
                            </span>
                            {course.category && (
                              <span className="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                {course.category.name}
                              </span>
                            )}
                          </div>
                        </td>
                        <td className="px-6 py-4">
                          <span
                            className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${statusBadgeClass(
                              course.status
                            )}`}
                          >
                            {statusLabel(course.status)}
                          </span>
                        </td>
                        <td className="px-6 py-4">
                          <div className="font-semibold">
                            {course.enrollments_count || 0} peserta
                          </div>
                        </td>
                        <td className="px-6 py-4">
                          <div className="flex flex-col gap-1">
                            <div className="text-sm font-semibold">
                              {progressValue}%
                            </div>
                            <div className="w-28 h-2 bg-gray-100 rounded-full overflow-hidden">
                              <div
                                className="h-full bg-emerald-500"
                                style={{ width: `${Math.min(Math.max(progressValue, 0), 100)}%` }}
                              />
                            </div>
                          </div>
                        </td>
                        <td className="px-6 py-4">
                          <div className="text-sm font-semibold">
                            Rp {formatRupiah(revenue)}
                          </div>
                        </td>
                        <td className="px-6 py-4 text-right space-x-2">
                          <Link
                            href={route("courses.show", course.slug)}
                            className="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50"
                          >
                            Kelola Konten
                          </Link>
                          <Link
                            href={route("dashboard.courses.participants", course.id)}
                            className="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-emerald-600 text-white hover:bg-emerald-700"
                          >
                            Lihat Peserta
                          </Link>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}

          {courses.links && courses.links.length > 3 && (
            <div className="px-6 py-4 border-t border-gray-200 flex justify-center">
              <div className="flex gap-1">
                {courses.links.map((link, index) => (
                  <Link
                    key={index}
                    href={link.url || "#"}
                    className={`px-3 py-1.5 text-xs rounded-md border ${
                      link.active
                        ? "bg-emerald-600 text-white border-emerald-600"
                        : "bg-white text-gray-700 border-gray-300 hover:bg-gray-50"
                    } ${!link.url && "opacity-50 cursor-not-allowed"}`}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                  />
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </Layout>
  );
}
