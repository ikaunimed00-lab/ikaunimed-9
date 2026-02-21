import React, { useState } from "react";
import { Head, Link, usePage, router } from "@inertiajs/react";
import { route } from "ziggy-js";
import AdminLayout from "@/Layouts/AdminLayout";
import EditorLayout from "@/Layouts/EditorLayout";
import SubscriberLayout from "@/Layouts/SubscriberLayout";
import { Users, Percent, Calendar, ChevronDown } from "lucide-react";

interface CourseCategory {
  id: number;
  name: string;
  slug: string;
}

interface Course {
  id: number;
  title: string;
  level: string;
  category?: CourseCategory | null;
}

interface EnrollmentUser {
  id: number;
  name: string;
  email: string;
  bidang_pekerjaan?: string | null;
  perusahaan?: string | null;
  kota_profesional?: string | null;
  status_pekerjaan?: string | null;
  angkatan?: string | null;
  public_profile: boolean;
  profile_level: number;
}

interface Enrollment {
  id: number;
  status: string;
  progress_percentage?: number | null;
  started_at?: string | null;
  completed_at?: string | null;
  user: EnrollmentUser;
}

interface EnrollmentsPagination {
  data: Enrollment[];
  links: any[];
  meta?: any;
}

interface Props {
  course: Course;
  enrollments: EnrollmentsPagination;
  stats: {
    total_participants: number;
    completed_participants: number;
    average_progress: number;
  };
  filters: {
    status?: string;
    progress?: string;
    sort?: string;
    direction?: string;
  };
  userRole: string;
}

const statusBadge = (status: string) => {
  if (status === "active") {
    return (
      <span className="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
        Aktif
      </span>
    );
  }
  if (status === "completed") {
    return (
      <span className="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
        Selesai
      </span>
    );
  }
  return (
    <span className="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
      {status}
    </span>
  );
};

const formatDate = (value?: string | null) => {
  if (!value) return "-";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "-";
  return date.toLocaleDateString("id-ID");
};

export default function ParticipantsPage({ course, enrollments, stats, filters, userRole }: Props) {
  const { auth }: any = usePage().props;
  const role = userRole || auth?.user?.role || "subscriber";
  const Layout = role === "admin" ? AdminLayout : role === "editor" ? EditorLayout : SubscriberLayout;

  const [status, setStatus] = useState(filters.status || "");
  const [progressFilter, setProgressFilter] = useState(filters.progress || "");
  const [sort, setSort] = useState(filters.sort || "started_at");
  const [direction, setDirection] = useState<"asc" | "desc">(
    (filters.direction as "asc" | "desc") || "desc"
  );

  const handleFilterChange = (
    next: Partial<{ status: string; progress: string; sort: string; direction: "asc" | "desc" }>
  ) => {
    const nextStatus = next.status ?? status;
    const nextProgress = next.progress ?? progressFilter;
    const nextSort = next.sort ?? sort;
    const nextDirection = next.direction ?? direction;

    setStatus(nextStatus);
    setProgressFilter(nextProgress);
    setSort(nextSort);
    setDirection(nextDirection);

    router.get(
      route("dashboard.courses.participants", course.id),
      {
        status: nextStatus,
        progress: nextProgress,
        sort: nextSort,
        direction: nextDirection,
      },
      {
        preserveState: true,
        preserveScroll: true,
      }
    );
  };

  const toggleSort = (field: string) => {
    const nextDirection =
      sort === field && direction === "desc" ? ("asc" as const) : ("desc" as const);
    handleFilterChange({ sort: field, direction: nextDirection });
  };

  const resetFilters = () => {
    setStatus("");
    setProgressFilter("");
    setSort("started_at");
    setDirection("desc");
    router.get(
      route("dashboard.courses.participants", course.id),
      {},
      { preserveState: false, preserveScroll: true }
    );
  };

  return (
    <Layout>
      <Head title={`Peserta Kursus: ${course.title}`} />

      <div className="space-y-6">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">
              Peserta Kursus: {course.title}
            </h1>
            <div className="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-600">
              <span className="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium">
                Level {course.level}
              </span>
              {course.category && (
                <span className="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                  {course.category.name}
                </span>
              )}
            </div>
          </div>
          <Link
            href={route("dashboard.courses.index")}
            className="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Kembali ke Kursus Saya
          </Link>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="bg-white border border-gray-200 rounded-lg p-5 flex items-center gap-4">
            <div className="h-10 w-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
              <Users className="w-5 h-5" />
            </div>
            <div>
              <div className="text-sm text-gray-600">Total Peserta</div>
              <div className="text-2xl font-bold text-gray-900">
                {stats.total_participants || 0}
              </div>
            </div>
          </div>
          <div className="bg-white border border-gray-200 rounded-lg p-5 flex items-center gap-4">
            <div className="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
              <Users className="w-5 h-5" />
            </div>
            <div>
              <div className="text-sm text-gray-600">Peserta Selesai</div>
              <div className="text-2xl font-bold text-gray-900">
                {stats.completed_participants || 0}
              </div>
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
        </div>

        <div className="bg-white rounded-lg border border-gray-200 shadow-sm">
          <div className="px-6 py-4 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
              <h2 className="text-lg font-semibold text-gray-900">Peserta Course Ini</h2>
              <p className="text-sm text-gray-600">
                Lihat progres dan latar belakang profesional peserta kursus.
              </p>
            </div>
            <div className="flex flex-col md:flex-row gap-3 md:items-center">
              <select
                value={status}
                onChange={(e) => handleFilterChange({ status: e.target.value })}
                className="border border-gray-300 rounded-md text-sm px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
              >
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="completed">Selesai</option>
              </select>
              <select
                value={progressFilter}
                onChange={(e) => handleFilterChange({ progress: e.target.value })}
                className="border border-gray-300 rounded-md text-sm px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
              >
                <option value="">Semua Progres</option>
                <option value="not_started">Belum Mulai (0%)</option>
                <option value="lt_25">Kurang dari 25%</option>
                <option value="25_75">25–75%</option>
                <option value="gt_75">&gt; 75%</option>
              </select>
              <button
                type="button"
                onClick={resetFilters}
                className="px-3 py-2 rounded-md border border-gray-300 text-xs font-medium text-gray-700 hover:bg-gray-50"
              >
                Reset
              </button>
            </div>
          </div>

          {enrollments.data.length === 0 ? (
            <div className="px-6 py-10 text-center text-gray-500">
              Belum ada peserta yang mendaftar kursus ini.
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="min-w-full text-sm text-gray-700">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className="px-6 py-3 text-left font-semibold">Peserta</th>
                    <th className="px-6 py-3 text-left font-semibold">Status</th>
                    <th className="px-6 py-3 text-left font-semibold">
                      <button
                        type="button"
                        onClick={() => toggleSort("progress")}
                        className="inline-flex items-center gap-1 text-xs font-semibold text-gray-700"
                      >
                        Progres
                        <ChevronDown className="w-3 h-3" />
                      </button>
                    </th>
                    <th className="px-6 py-3 text-left font-semibold">
                      <button
                        type="button"
                        onClick={() => toggleSort("started_at")}
                        className="inline-flex items-center gap-1 text-xs font-semibold text-gray-700"
                      >
                        Mulai
                        <Calendar className="w-3 h-3" />
                      </button>
                    </th>
                    <th className="px-6 py-3 text-left font-semibold">Selesai</th>
                    <th className="px-6 py-3 text-left font-semibold">Profil Profesional</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {enrollments.data.map((enrollment) => {
                    const user = enrollment.user;
                    const progress = typeof enrollment.progress_percentage === "number"
                      ? Math.round(enrollment.progress_percentage)
                      : 0;
                    const canShowDirectoryDetails = user.public_profile && user.profile_level > 0;

                    return (
                      <tr key={enrollment.id} className="hover:bg-gray-50 align-top">
                        <td className="px-6 py-4">
                          <div className="font-semibold text-gray-900">{user.name}</div>
                          <div className="text-xs text-gray-500">{user.email}</div>
                          <div className="mt-1 text-xs text-gray-600">
                            Angkatan {user.angkatan || "-"}
                          </div>
                        </td>
                        <td className="px-6 py-4">
                          {statusBadge(enrollment.status)}
                        </td>
                        <td className="px-6 py-4">
                          <div className="flex flex-col gap-1">
                            <div className="text-sm font-semibold">
                              {progress}%
                            </div>
                            <div className="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                              <div
                                className="h-full bg-emerald-500"
                                style={{ width: `${Math.min(Math.max(progress, 0), 100)}%` }}
                              />
                            </div>
                          </div>
                        </td>
                        <td className="px-6 py-4 text-sm">
                          {formatDate(enrollment.started_at)}
                        </td>
                        <td className="px-6 py-4 text-sm">
                          {formatDate(enrollment.completed_at)}
                        </td>
                        <td className="px-6 py-4 text-sm">
                          <div className="space-y-1">
                            <div className="font-medium text-gray-800">
                              {user.posisi_saat_ini && user.perusahaan
                                ? `${user.posisi_saat_ini} – ${user.perusahaan}`
                                : user.perusahaan || user.bidang_pekerjaan || "Belum diisi"}
                            </div>
                            <div className="text-xs text-gray-600">
                              {user.kota_profesional || ""}
                            </div>
                            <div className="text-xs text-gray-500">
                              {canShowDirectoryDetails
                                ? "Tampil di Direktori Alumni"
                                : "Profil tidak dibuka di direktori"}
                            </div>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}

          {enrollments.links && enrollments.links.length > 3 && (
            <div className="px-6 py-4 border-t border-gray-200 flex justify-center">
              <div className="flex gap-1">
                {enrollments.links.map((link, index) => (
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

