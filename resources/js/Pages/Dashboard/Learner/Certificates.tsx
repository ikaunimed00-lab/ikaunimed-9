import React from "react";
import { Head, Link, usePage } from "@inertiajs/react";
import { route } from "ziggy-js";
import SubscriberLayout from "@/Layouts/SubscriberLayout";

interface Course {
    id: number;
    title: string;
    slug: string;
}

interface Certificate {
    id: number;
    certificate_number: string;
    issued_at: string;
    course: Course;
}

interface CertificatesPagination {
    data: Certificate[];
    links: any[];
    meta?: any;
}

interface Props {
    certificates: CertificatesPagination;
}

export default function LearnerCertificates({ certificates }: Props) {
    const { auth }: any = usePage().props;

    return (
        <SubscriberLayout>
            <Head title="Sertifikat Saya (LMS)" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Sertifikat Saya</h1>
                        <p className="text-gray-500">
                            Daftar sertifikat yang telah Anda peroleh dari kursus LMS.
                        </p>
                    </div>
                </div>

                <div className="bg-white rounded-lg border border-gray-200 shadow-sm">
                    {certificates.data.length === 0 ? (
                        <div className="px-6 py-10 text-center text-gray-500 space-y-4">
                            <p>Belum ada sertifikat yang diterbitkan untuk akun Anda.</p>
                            <Link
                                href={route("dashboard.elearning.learner.courses.index")}
                                className="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-md bg-emerald-600 text-white hover:bg-emerald-700"
                            >
                                Kembali ke Kursus LMS
                            </Link>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm text-gray-700">
                                <thead className="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th className="px-6 py-3 text-left font-semibold">Kursus</th>
                                        <th className="px-6 py-3 text-left font-semibold">Nomor Sertifikat</th>
                                        <th className="px-6 py-3 text-left font-semibold">Tanggal Terbit</th>
                                        <th className="px-6 py-3 text-right font-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {certificates.data.map((certificate) => (
                                        <tr key={certificate.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4">
                                                <div className="font-semibold text-gray-900">
                                                    {certificate.course?.title || "Kursus"}
                                                </div>
                                                {certificate.course?.slug && (
                                                    <div className="mt-1 text-xs">
                                                        <Link
                                                            href={route("courses.show", certificate.course.slug)}
                                                            className="text-emerald-600 hover:text-emerald-700"
                                                        >
                                                            Lihat Halaman Kursus
                                                        </Link>
                                                    </div>
                                                )}
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                                    {certificate.certificate_number}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                {new Date(certificate.issued_at).toLocaleDateString("id-ID", {
                                                    day: "2-digit",
                                                    month: "short",
                                                    year: "numeric",
                                                })}
                                            </td>
                                            <td className="px-6 py-4 text-right space-x-2">
                                                <a
                                                    href={route("dashboard.elearning.learner.certificates.download", certificate.id)}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50"
                                                >
                                                    Unduh Sertifikat
                                                </a>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {certificates.links && certificates.links.length > 3 && (
                        <div className="px-6 py-4 border-t border-gray-200 flex justify-center">
                            <div className="flex gap-1">
                                {certificates.links.map((link, index) => (
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
