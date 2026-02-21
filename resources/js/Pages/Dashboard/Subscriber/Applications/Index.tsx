import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { route } from 'ziggy-js';
import SubscriberLayout from '@/Layouts/SubscriberLayout';
import { GraduationCap, Calendar } from 'lucide-react';

type Application = {
  id: number;
  status: 'pending' | 'review' | 'interview' | 'approved' | 'rejected';
  updated_at: string;
  scholarship: {
    title: string;
    slug: string;
  };
};

export default function Index({ applications }: { applications: { data: Application[] } }) {
  const { auth }: any = usePage().props;

  const badge = (status: string) => {
    const map: Record<string, string> = {
      pending: 'bg-yellow-100 text-yellow-800',
      review: 'bg-blue-100 text-blue-800',
      interview: 'bg-indigo-100 text-indigo-800',
      approved: 'bg-green-100 text-green-800',
      rejected: 'bg-red-100 text-red-800',
    };
    return <span className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${map[status] || 'bg-gray-100 text-gray-800'}`}>{status}</span>;
  };

  return (
    <SubscriberLayout>
      <Head title="Lamaran Saya" />
      <div className="space-y-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Lamaran Saya</h1>
          <p className="text-gray-500">Daftar pengajuan beasiswa milik Anda.</p>
        </div>

        <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <table className="w-full text-left text-sm text-gray-700">
            <thead className="bg-gray-50 text-gray-900 font-semibold border-b">
              <tr>
                <th className="px-6 py-4">Beasiswa</th>
                <th className="px-6 py-4">Status</th>
                <th className="px-6 py-4">Update Terakhir</th>
                <th className="px-6 py-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {applications.data.length > 0 ? (
                applications.data.map((app) => (
                  <tr key={app.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
                          <GraduationCap className="w-4 h-4 text-gray-500" />
                        </div>
                        <div className="font-medium text-gray-900">{app.scholarship.title}</div>
                      </div>
                    </td>
                    <td className="px-6 py-4">{badge(app.status)}</td>
                    <td className="px-6 py-4">
                      <div className="flex items-center text-xs text-gray-600">
                        <Calendar className="w-3 h-3 mr-1.5" />
                        {new Date(app.updated_at).toLocaleString('id-ID')}
                      </div>
                    </td>
                    <td className="px-6 py-4 text-right">
                      <Link
                        href={route('scholarships.show', app.scholarship.slug)}
                        className="inline-flex items-center px-3 py-1.5 text-sm border rounded hover:bg-gray-50"
                        target="_blank"
                      >
                        Lihat Beasiswa
                      </Link>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={4} className="px-6 py-12 text-center text-gray-500">
                    Belum ada lamaran yang diajukan.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </SubscriberLayout>
  );
}
