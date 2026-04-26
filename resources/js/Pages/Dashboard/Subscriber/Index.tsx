import React, { useState } from "react";
import { Link } from "@inertiajs/react";
import { route } from "ziggy-js";
import SubscriberLayout from "@/Layouts/SubscriberLayout";

interface Legalization {
  id: number;
  jenjang: string;
  tahun_lulus: number;
  jumlah_lembar: number;
  tujuan: string;
  status: string;
  admin_note?: string;
  submitted_at: string;
  verified_at?: string;
  completed_at?: string;
  files: any[];
}

interface SubscriberDashboardProps {
  user: any;
  legalizations: {
    data: Legalization[];
    links: any;
    meta: any;
  };
  notifications: any[];
  stats: Record<string, number>;
}

const getStatusColor = (status: string) => {
  switch (status) {
    case "approved": return "bg-green-100 text-green-800 border-green-300";
    case "rejected": return "bg-red-100 text-red-800 border-red-300";
    case "pending": return "bg-yellow-100 text-yellow-800 border-yellow-300";
    case "verified": return "bg-blue-100 text-blue-800 border-blue-300";
    default: return "bg-gray-100 text-gray-800 border-gray-300";
  }
};

const getStatusBadge = (status: string) => {
  const labels: Record<string, string> = {
    approved: "Disetujui",
    rejected: "Ditolak",
    pending: "Menunggu",
    verified: "Terverifikasi",
  };
  return labels[status] || status;
};

const SubscriberDashboard: React.FC<SubscriberDashboardProps> = ({
  user,
  legalizations,
  notifications,
  stats = {},
}) => {
  const [selectedLegalization, setSelectedLegalization] = useState<Legalization | null>(
    null
  );

  const handleViewDetail = (legalization: Legalization) => {
    setSelectedLegalization(legalization);
  };

  const handleDownload = (file: any) => {
    window.location.href = route("legalization.files.download", file.id);
  };

  return (
    <SubscriberLayout>
      <div className="space-y-6">
        {/* Welcome Section */}
        <div className="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-xl shadow-lg p-8 text-white">
          <h2 className="text-3xl font-bold mb-2">Legalisir Dokumen Online</h2>
          <p className="text-emerald-100 mb-6">
            Selamat datang, {user.name}! Kelola pengajuan legalisir dokumen Anda dengan mudah. 
            Ajukan legalisir baru, pantau status, dan unduh dokumen yang sudah disetujui.
          </p>
          <div className="flex gap-4">
            <Link
              href={route("legalization.create")}
              className="bg-white text-emerald-600 px-6 py-2.5 rounded-lg font-semibold hover:bg-emerald-50 transition-colors inline-flex items-center gap-2"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
              </svg>
              Ajukan Legalisir Baru
            </Link>
            <Link
              href={route("legalization.index")}
              className="bg-emerald-500 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-emerald-600 transition-colors"
            >
              Lihat Semua Pengajuan
            </Link>
          </div>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Total Pengajuan</p>
                <p className="text-2xl font-bold text-gray-900">{stats.total || 0}</p>
              </div>
              <div className="h-12 w-12 bg-gray-100 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Pending</p>
                <p className="text-2xl font-bold text-yellow-600">{stats.pending || 0}</p>
              </div>
              <div className="h-12 w-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Disetujui</p>
                <p className="text-2xl font-bold text-green-600">{stats.approved || 0}</p>
              </div>
              <div className="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">Ditolak</p>
                <p className="text-2xl font-bold text-red-600">{stats.rejected || 0}</p>
              </div>
              <div className="h-12 w-12 bg-red-100 rounded-lg flex items-center justify-center">
                <svg className="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Link
            href={route("legalization.create")}
            className="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-lg p-6 hover:shadow-lg transition-shadow group"
          >
            <div className="flex items-center gap-4">
              <div className="h-14 w-14 bg-white/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                </svg>
              </div>
              <div>
                <h3 className="text-lg font-semibold mb-1">Ajukan Legalisir</h3>
                <p className="text-emerald-100 text-sm">Buat pengajuan baru</p>
              </div>
            </div>
          </Link>

          <Link
            href={route("legalization.index")}
            className="bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-lg p-6 hover:shadow-lg transition-shadow group"
          >
            <div className="flex items-center gap-4">
              <div className="h-14 w-14 bg-white/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <div>
                <h3 className="text-lg font-semibold mb-1">Riwayat</h3>
                <p className="text-blue-100 text-sm">Lihat semua pengajuan</p>
              </div>
            </div>
          </Link>

          <Link
            href={route("profile.edit")}
            className="bg-gradient-to-br from-purple-500 to-pink-600 text-white rounded-lg p-6 hover:shadow-lg transition-shadow group"
          >
            <div className="flex items-center gap-4">
              <div className="h-14 w-14 bg-white/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div>
                <h3 className="text-lg font-semibold mb-1">Profil</h3>
                <p className="text-purple-100 text-sm">Edit profil Anda</p>
              </div>
            </div>
          </Link>
        </div>

        {/* Legalizations List */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div className="px-6 py-4 border-b border-gray-200">
            <h3 className="text-lg font-semibold text-gray-900">
              Riwayat Pengajuan Legalisir
            </h3>
            <p className="text-sm text-gray-600 mt-1">
              Total: {legalizations?.meta?.total || 0} pengajuan
            </p>
          </div>

          {!legalizations?.data || legalizations.data.length === 0 ? (
            <div className="p-8 text-center">
              <svg
                className="mx-auto h-12 w-12 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
              <h4 className="mt-2 text-lg font-medium text-gray-900">
                Belum ada pengajuan
              </h4>
              <p className="text-gray-500 mt-1">
                Mulai dengan mengajukan legalisir dokumen Anda
              </p>
              <Link
                href={route("legalization.create")}
                className="inline-block mt-4 bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition-colors"
              >
                Ajukan Sekarang
              </Link>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                      Jenjang
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                      Tahun Lulus
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                      Status
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                      Tanggal Pengajuan
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                      Aksi
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {legalizations?.data?.map((legalization) => (
                    <tr key={legalization.id} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {legalization.jenjang}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        {legalization.tahun_lulus}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span
                          className={`inline-flex px-3 py-1 text-xs font-semibold rounded-full border ${getStatusColor(
                            legalization.status
                          )}`}
                        >
                          {getStatusBadge(legalization.status)}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        {new Date(legalization.submitted_at).toLocaleDateString(
                          "id-ID"
                        )}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm">
                        <button
                          onClick={() => handleViewDetail(legalization)}
                          className="text-blue-600 hover:text-blue-900 font-medium"
                        >
                          Lihat detail
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          {/* Pagination */}
          {legalizations?.links && legalizations?.meta?.last_page > 1 && (
            <div className="px-6 py-4 border-t border-gray-200 flex justify-center gap-2">
              {legalizations.links.map((link: any, idx: number) => (
                <Link
                  key={link.url || `link-${idx}`}
                  href={link.url || "#"}
                  className={`px-3 py-1 rounded border transition-colors ${
                    link.active
                      ? "bg-emerald-600 text-white border-emerald-600"
                      : "border-gray-300 text-gray-700 hover:bg-gray-50"
                  }`}
                  dangerouslySetInnerHTML={{ __html: link.label }}
                />
              ))}
            </div>
          )}
        </div>

        {/* Detail Modal */}
        {selectedLegalization && (
          <div
            className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            onClick={() => setSelectedLegalization(null)}
          >
            <div
              className="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
                <h3 className="text-lg font-semibold text-gray-900">
                  Detail Pengajuan Legalisir
                </h3>
                <button
                  onClick={() => setSelectedLegalization(null)}
                  className="text-gray-500 hover:text-gray-700 text-2xl"
                >
                  ×
                </button>
              </div>

              <div className="p-6 space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="text-sm font-medium text-gray-700">
                      Jenjang
                    </label>
                    <p className="text-gray-900 font-medium">
                      {selectedLegalization.jenjang}
                    </p>
                  </div>
                  <div>
                    <label className="text-sm font-medium text-gray-700">
                      Tahun Lulus
                    </label>
                    <p className="text-gray-900 font-medium">
                      {selectedLegalization.tahun_lulus}
                    </p>
                  </div>
                  <div>
                    <label className="text-sm font-medium text-gray-700">
                      Jumlah Lembar
                    </label>
                    <p className="text-gray-900 font-medium">
                      {selectedLegalization.jumlah_lembar} lembar
                    </p>
                  </div>
                  <div>
                    <label className="text-sm font-medium text-gray-700">
                      Status
                    </label>
                    <span
                      className={`inline-flex px-3 py-1 text-xs font-semibold rounded-full border ${getStatusColor(
                        selectedLegalization.status
                      )}`}
                    >
                      {getStatusBadge(selectedLegalization.status)}
                    </span>
                  </div>
                </div>

                {selectedLegalization.tujuan && (
                  <div>
                    <label className="text-sm font-medium text-gray-700">
                      Tujuan
                    </label>
                    <p className="text-gray-900">{selectedLegalization.tujuan}</p>
                  </div>
                )}

                {selectedLegalization.admin_note && (
                  <div className="bg-yellow-50 border border-yellow-200 rounded p-3">
                    <label className="text-sm font-medium text-yellow-900">
                      Catatan Admin
                    </label>
                    <p className="text-yellow-800 text-sm">
                      {selectedLegalization.admin_note}
                    </p>
                  </div>
                )}

                {selectedLegalization.files && selectedLegalization.files.length > 0 && (
                  <div>
                    <label className="text-sm font-medium text-gray-700 block mb-2">
                      File Dokumen
                    </label>
                    <div className="space-y-2">
                      {selectedLegalization.files.map((file: any) => (
                        <div
                          key={file.id}
                          className="flex items-center justify-between p-3 bg-gray-50 rounded border border-gray-200"
                        >
                          <div className="flex items-center gap-2">
                            <svg
                              className="w-4 h-4 text-gray-400"
                              fill="currentColor"
                              viewBox="0 0 20 20"
                            >
                              <path d="M8 16.5a1 1 0 11-2 0 1 1 0 012 0zM15 7H4m12 0a1 1 0 011 1v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8a1 1 0 011-1m12 0V6a2 2 0 00-2-2H6a2 2 0 00-2 2v1m12 0H4" />
                            </svg>
                            <div>
                              <p className="text-sm font-medium text-gray-900">
                                {file.original_name}
                              </p>
                              <p className="text-xs text-gray-500">
                                {(file.size / 1024).toFixed(2)} KB
                              </p>
                            </div>
                          </div>
                          {selectedLegalization.status === "approved" && (
                            <button
                              onClick={() => handleDownload(file)}
                              className="text-blue-600 hover:text-blue-900 text-sm font-medium"
                            >
                              Download
                            </button>
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>
        )}
      </div>
    </SubscriberLayout>
  );
};

export default SubscriberDashboard;