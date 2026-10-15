import { Head, useForm, Link } from '@inertiajs/react';
import { route } from 'ziggy-js';
import SubscriberLayout from "@/Layouts/SubscriberLayout";
import { FileUp, CheckCircle, Clock, XCircle, Download } from 'lucide-react';
import React from 'react';

export default function Show({ legalization }: any) {
  const { data, setData, post, processing, errors, reset } = useForm({
    file: null as File | null,
  });

  const submitUpload = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('legalization.upload', legalization.id), {
        onSuccess: () => reset('file'),
    });
  };

  return (
    <SubscriberLayout>
      <Head title={`Detail Legalisir #${legalization.id}`} />

      <div className="max-w-3xl mx-auto">
        <div className="flex items-center gap-4 mb-6">
            <Link href={route('legalization.index')} className="text-gray-500 hover:text-gray-700">
                &larr; Kembali
            </Link>
            <h1 className="text-2xl font-bold text-gray-800">Detail Pengajuan #{legalization.id}</h1>
        </div>

        <div className="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <div className="grid grid-cols-2 gap-6">
                <div>
                    <div className="text-sm text-gray-500">Status</div>
                    <div className="font-medium capitalize">{legalization.status}</div>
                </div>
                <div>
                    <div className="text-sm text-gray-500">Tanggal Pengajuan</div>
                    <div>{new Date(legalization.created_at).toLocaleDateString("id-ID")}</div>
                </div>
                <div>
                    <div className="text-sm text-gray-500">Jenjang & Tahun Lulus</div>
                    <div>{legalization.jenjang} - {legalization.tahun_lulus}</div>
                </div>
                <div>
                    <div className="text-sm text-gray-500">Jumlah Lembar</div>
                    <div>{legalization.jumlah_lembar} Lembar</div>
                </div>
                <div className="col-span-2">
                    <div className="text-sm text-gray-500">Keperluan</div>
                    <div>{legalization.tujuan}</div>
                </div>
                {legalization.admin_note && (
                    <div className="col-span-2 bg-red-50 p-4 rounded-lg border border-red-100">
                        <div className="text-sm text-red-600 font-medium">Catatan Admin:</div>
                        <div className="text-red-700">{legalization.admin_note}</div>
                    </div>
                )}
            </div>
        </div>

        {legalization.status === 'submitted' && (
            <div className="bg-white rounded-xl shadow-sm border p-6">
                <h2 className="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <FileUp size={20} />
                    Upload Dokumen
                </h2>
                <p className="text-gray-600 mb-4">
                    Silakan upload scan ijazah asli Anda (format PDF/JPG, max 5MB) untuk diverifikasi.
                </p>

                <form onSubmit={submitUpload} className="space-y-4">
                    <div>
                        <input
                            type="file"
                            onChange={e => setData('file', e.target.files ? e.target.files[0] : null)}
                            className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        />
                        {errors.file && <div className="text-red-500 text-sm mt-1">{errors.file}</div>}
                    </div>
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                        {processing ? 'Uploading...' : 'Upload File'}
                    </button>
                </form>
            </div>
        )}

        {legalization.files && legalization.files.length > 0 && (
            <div className="bg-white rounded-xl shadow-sm border p-6 mt-6">
                <h2 className="text-lg font-bold text-gray-800 mb-4">File Terupload</h2>
                <ul className="space-y-2">
                    {legalization.files.map((file: any) => (
                        <li key={file.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span className="text-sm text-gray-700">{file.original_name}</span>
                            <span className="flex items-center gap-3">
                                <span className="text-xs text-gray-500">{(file.size / 1024).toFixed(0)} KB</span>
                                <a
                                    href={file.download_url}
                                    className="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700"
                                >
                                    <Download size={16} />
                                    Unduh
                                </a>
                            </span>
                        </li>
                    ))}
                </ul>
            </div>
        )}
      </div>
    </SubscriberLayout>
  );
}
