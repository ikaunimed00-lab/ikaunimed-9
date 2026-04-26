import React from "react";
import { Head, useForm, Link } from "@inertiajs/react";
import { route } from "ziggy-js";
import SubscriberLayout from "@/Layouts/SubscriberLayout";

interface Props {
  categories: Record<string, string>;
}

export default function Create({ categories }: Props) {
  const { data, setData, post, processing, errors } = useForm({
    title: "",
    content: "",
    category: "",
    image: null as File | null,
    map_location: "",
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route("dashboard.subscriber.alumni-posts.store"), {
      forceFormData: true,
    });
  };

  return (
    <SubscriberLayout>
      <Head title="Kirim Kabar Alumni" />

      <div className="max-w-4xl mx-auto space-y-6">
        {/* Header */}
        <div className="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-xl shadow-lg p-8 text-white">
          <h1 className="text-3xl font-bold mb-2">Kirim Kabar Alumni</h1>
          <p className="text-emerald-100">
            Bagikan cerita, prestasi, atau kabar penting lainnya kepada komunitas alumni
          </p>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
          {/* Title */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Judul Kabar <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              value={data.title}
              onChange={(e) => setData("title", e.target.value)}
              placeholder="Contoh: Alumni UNIMED Raih Medali Emas Olimpiade Matematika"
              className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${
                errors.title ? "border-red-500" : "border-gray-300"
              }`}
            />
            {errors.title && (
              <p className="mt-1 text-sm text-red-600">{errors.title}</p>
            )}
          </div>

          {/* Category */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Kategori <span className="text-red-500">*</span>
            </label>
            <select
              value={data.category}
              onChange={(e) => setData("category", e.target.value)}
              className={`w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent ${
                errors.category ? "border-red-500" : "border-gray-300"
              }`}
            >
              <option value="">Pilih Kategori</option>
              {Object.entries(categories).map(([key, label]) => (
                <option key={key} value={key}>
                  {label}
                </option>
              ))}
            </select>
            {errors.category && (
              <p className="mt-1 text-sm text-red-600">{errors.category}</p>
            )}
          </div>

          {/* Content */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Konten Kabar <span className="text-red-500">*</span>
            </label>
            <textarea
              value={data.content}
              onChange={(e) => setData("content", e.target.value)}
              placeholder="Tuliskan kabar alumni Anda di sini... (minimal 50 karakter)"
              rows={10}
              className={`w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none ${
                errors.content ? "border-red-500" : "border-gray-300"
              }`}
            />
            <div className="flex items-center justify-between mt-2">
              <p className="text-sm text-gray-500">
                {data.content.length} karakter (minimal 50)
              </p>
              {errors.content && (
                <p className="text-sm text-red-600">{errors.content}</p>
              )}
            </div>
          </div>

          {/* Optional Fields for Specific Categories */}
          {(data.category === 'pernikahan' || data.category === 'wafat' || data.category === 'usaha') && (
             <div className="space-y-6 pt-4 border-t border-gray-100">
                <h3 className="font-medium text-gray-900">Data Tambahan (Opsional)</h3>
                
                {/* Image Upload */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Foto / Gambar
                  </label>
                  <input
                    type="file"
                    accept="image/*"
                    onChange={(e) => setData("image", e.target.files ? e.target.files[0] : null)}
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                  />
                  {errors.image && (
                    <p className="mt-1 text-sm text-red-600">{errors.image}</p>
                  )}
                  <p className="mt-1 text-xs text-gray-500">Format: JPG, PNG, JPEG. Maks: 2MB.</p>
                </div>

                {/* Map Location */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Lokasi / Peta (Google Maps Embed URL)
                  </label>
                  <input
                    type="text"
                    value={data.map_location}
                    onChange={(e) => setData("map_location", e.target.value)}
                    placeholder='<iframe src="https://www.google.com/maps/embed?...'
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  />
                  {errors.map_location && (
                    <p className="mt-1 text-sm text-red-600">{errors.map_location}</p>
                  )}
                  <p className="mt-1 text-xs text-gray-500">
                    Paste kode embed (iframe) dari Google Maps untuk menampilkan peta lokasi acara/usaha.
                  </p>
                </div>
             </div>
          )}

          {/* Info Box */}
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 className="font-medium text-blue-900 mb-2">📝 Catatan Penting:</h4>
            <ul className="text-sm text-blue-800 space-y-1 list-disc list-inside">
              <li>Kabar alumni akan direview oleh moderator sebelum dipublikasikan</li>
              <li>Pastikan informasi yang Anda berikan akurat dan faktual</li>
              <li>Hindari konten yang bersifat SARA, politik, atau menyinggung pihak lain</li>
              <li>Anda akan mendapat notifikasi saat kabar disetujui atau ditolak</li>
            </ul>
          </div>

          {/* Actions */}
          <div className="flex items-center justify-between pt-4 border-t border-gray-200">
            <Link
              href={route("dashboard.subscriber.alumni-posts.index")}
              className="text-gray-600 hover:text-gray-900 font-medium"
            >
              ← Kembali
            </Link>
            <button
              type="submit"
              disabled={processing}
              className="px-6 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium disabled:bg-emerald-400"
            >
              {processing ? "Mengirim..." : "Kirim Kabar Alumni"}
            </button>
          </div>
        </form>
      </div>
    </SubscriberLayout>
  );
}