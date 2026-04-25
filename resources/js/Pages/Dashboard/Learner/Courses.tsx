import React, { useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { route } from "ziggy-js";
import SubscriberLayout from "@/Layouts/SubscriberLayout";
import { BookOpen, Percent } from "lucide-react";

interface Course {
    id: number;
    title: string;
    slug: string;
    level?: string;
    status: string;
}

interface Enrollment {
    id: number;
    status: string;
    started_at?: string;
    completed_at?: string | null;
    progress_percentage?: number | null;
    course: Course;
}

interface EnrollmentPagination {
    data: Enrollment[];
    links: any[];
    meta?: any;
}

interface Props {
    enrollments: EnrollmentPagination;
    stats: {
        total_courses: number;
        completed_courses: number;
        average_progress: number;
        total_certificates?: number;
    };
    filters: {
        status?: string;
    };
    lmsRoles: {
        instructor: boolean;
        learner: boolean;
        lms_moderator: boolean;
    };
}

const statusLabel = (status: string) => {
    if (status === "active") return "Sedang diikuti";
    if (status === "completed") return "Selesai";
    return status;
};

const statusBadgeClass = (status: string) => {
    if (status === "active") return "bg-emerald-100 text-emerald-800 border-emerald-200";
    if (status === "completed") return "bg-blue-100 text-blue-800 border-blue-200";
    return "bg-gray-100 text-gray-800 border-gray-200";
};

export default function LearnerCourses({ enrollments, stats, filters }: Props) {
    const { flash }: any = usePage().props;
    const [cancellingId, setCancellingId] = useState<number | null>(null);
    const [status, setStatus] = useState(filters.status || "");
    const hasFilter = !!status;

    const handleFilterSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            route("dashboard.elearning.learner.courses.index"),
            {
                status,
            },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    };

    const handleReset = () => {
        setStatus("");
        router.get(
            route("dashboard.elearning.learner.courses.index"),
            {},
            { preserveState: false, preserveScroll: true },
        );
    };

    const handleCancel = (enrollmentId: number) => {
        if (!window.confirm("Yakin ingin membatalkan enrollment ini?")) {
            return;
        }

        setCancellingId(enrollmentId);

        router.post(
            route("dashboard.elearning.enrollments.cancel", enrollmentId),
            {},
            {
                preserveScroll: true,
                onFinish: () => setCancellingId(null),
            },
        );
    };

    return (
        <SubscriberLayout>
            <Head title="Kursus Saya (LMS)" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Kursus Saya (LMS)</h1>
                        <p className="text-gray-500">
                            Daftar kursus LMS yang sedang Anda ikuti melalui portal alumni.
                        </p>
                    </div>
                </div>

                {flash?.success && (
                    <div className="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm">
                        {flash.success}
                    </div>
                )}

                {flash?.error && (
                    <div className="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                        {flash.error}
                    </div>
                )}

                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div className="bg-white border border-gray-200 rounded-lg p-5 flex items-center gap-4">
                        <div className="h-10 w-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <BookOpen className="w-5 h-5" />
                        </div>
                        <div>
                            <div className="text-sm text-gray-600">Total Kursus</div>
                            <div className="text-2xl font-bold text-gray-900">{stats.total_courses || 0}</div>
                        </div>
                    </div>

                    <div className="bg-white border border-gray-200 rounded-lg p-5 flex items-center gap-4">
                        <div className="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                            <BookOpen className="w-5 h-5" />
                        </div>
                        <div>
                            <div className="text-sm text-gray-600">Selesai</div>
                            <div className="text-2xl font-bold text-gray-900">{stats.completed_courses || 0}</div>
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
                        <div className="h-10 w-10 rounded-lg bg-gray-50 flex items-center justify-center text-gray-600">
                            <span className="text-xs font-semibold">CERT</span>
                        </div>
                        <div>
                            <div className="text-sm text-gray-600">Sertifikat Diterbitkan</div>
                            <div className="text-2xl font-bold text-gray-900">{stats.total_certificates || 0}</div>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div className="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 className="text-lg font-semibold text-gray-900">Kursus LMS yang Diikuti</h2>
                            <p className="text-sm text-gray-600">
                                Lanjutkan belajar atau buka kembali kursus yang pernah Anda ikuti.
                            </p>
                        </div>
                        <form onSubmit={handleFilterSubmit} className="flex flex-col md:flex-row gap-3 md:items-center">
                            <select
                                value={status}
                                onChange={(e) => setStatus(e.target.value)}
                                className="border border-gray-300 rounded-md text-sm px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
                            >
                                <option value="">Semua Status</option>
                                <option value="active">Sedang diikuti</option>
                                <option value="completed">Selesai</option>
                            </select>
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

                    {enrollments.data.length === 0 ? (
                        <div className="px-6 py-10 text-center text-gray-500 space-y-4">
                            <div>
                                {hasFilter
                                    ? "Tidak ada kursus yang sesuai dengan filter status yang dipilih."
                                    : "Anda belum terdaftar di kursus LMS mana pun."}
                            </div>
                            {hasFilter ? (
                                <button
                                    type="button"
                                    onClick={handleReset}
                                    className="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
                                >
                                    Hapus Filter Status
                                </button>
                            ) : (
                                <Link
                                    href={route("courses.index")}
                                    className="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-md bg-emerald-600 text-white hover:bg-emerald-700"
                                >
                                    Jelajahi Kursus
                                </Link>
                            )}
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm text-gray-700">
                                <thead className="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th className="px-6 py-3 text-left font-semibold">Kursus</th>
                                        <th className="px-6 py-3 text-left font-semibold">Status</th>
                                        <th className="px-6 py-3 text-left font-semibold">Progres</th>
                                        <th className="px-6 py-3 text-right font-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {enrollments.data.map((enrollment) => {
                                        const progress = enrollment.progress_percentage ?? 0;
                                        const course = enrollment.course;
                                        return (
                                            <tr key={enrollment.id} className="hover:bg-gray-50">
                                                <td className="px-6 py-4">
                                                    <div className="font-semibold text-gray-900">
                                                        {course?.title || "Kursus"}
                                                    </div>
                                                    {course?.level && (
                                                        <div className="mt-1 text-xs inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">
                                                            {course.level}
                                                        </div>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4">
                                                    <span
                                                        className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${statusBadgeClass(
                                                            enrollment.status
                                                        )}`}
                                                    >
                                                        {statusLabel(enrollment.status)}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="flex flex-col gap-1">
                                                        <div className="text-sm font-semibold">
                                                            {progress}%
                                                        </div>
                                                        <div className="w-28 h-2 bg-gray-100 rounded-full overflow-hidden">
                                                            <div
                                                                className="h-full bg-emerald-500"
                                                                style={{ width: `${Math.min(Math.max(progress, 0), 100)}%` }}
                                                            />
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 text-right space-x-2">
                                                    {course?.slug && (
                                                        <Link
                                                            href={route("courses.show", course.slug)}
                                                            className="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-emerald-600 text-white hover:bg-emerald-700"
                                                        >
                                                            Lanjut Belajar
                                                        </Link>
                                                    )}
                                                    {enrollment.status === "active" && (
                                                        <button
                                                            type="button"
                                                            onClick={() => handleCancel(enrollment.id)}
                                                            disabled={cancellingId === enrollment.id}
                                                            className={`inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md border border-red-300 text-red-700 hover:bg-red-50 ${
                                                                cancellingId === enrollment.id ? "opacity-60 cursor-wait" : ""
                                                            }`}
                                                        >
                                                            {cancellingId === enrollment.id ? "Membatalkan..." : "Batalkan"}
                                                        </button>
                                                    )}
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
        </SubscriberLayout>
    );
}
