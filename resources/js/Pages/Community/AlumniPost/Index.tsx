import React, { useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import { route } from "ziggy-js";
import MainLayout from "@/components/MainLayout";

interface AlumniPost {
  id: number;
  title: string;
  slug: string;
  content: string;
  category: string;
  published_at: string;
  user: {
    id: number;
    name: string;
  };
}

interface Props {
  posts: {
    data: AlumniPost[];
    links?: any[];
    meta?: {
      current_page: number;
      last_page: number;
      total: number;
    };
  };
  categories: Record<string, string>;
  filters: {
    category?: string;
    search?: string;
  };
}

const getCategoryColor = (category: string) => {
  const colors: Record<string, string> = {
    prestasi: "bg-blue-100 text-blue-800 border-blue-300",
    pernikahan: "bg-pink-100 text-pink-800 border-pink-300",
    wafat: "bg-gray-100 text-gray-800 border-gray-300",
    usaha: "bg-green-100 text-green-800 border-green-300",
    beasiswa: "bg-purple-100 text-purple-800 border-purple-300",
    karir: "bg-orange-100 text-orange-800 border-orange-300",
    lainnya: "bg-yellow-100 text-yellow-800 border-yellow-300",
  };
  return colors[category] || "bg-gray-100 text-gray-800 border-gray-300";
};

export default function Index({ posts, categories, filters }: Props) {
  const [searchTerm, setSearchTerm] = useState(filters.search || "");

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(
      route("alumni-posts.index"),
      {
        category: filters.category,
        search: searchTerm,
      },
      {
        preserveState: true,
        preserveScroll: true,
      }
    );
  };

  const handleCategoryFilter = (category: string) => {
    router.get(
      route("alumni-posts.index"),
      {
        category: category === filters.category ? undefined : category,
        search: filters.search,
      },
      {
        preserveState: true,
        preserveScroll: true,
      }
    );
  };

  return (
    <MainLayout variant="full">
      <Head title="Kabar Alumni" />

      <div className="min-h-screen bg-gray-50">
        {/* Header */}
        <div className="bg-gradient-to-r from-emerald-600 to-teal-600 text-white">
          <div className="container mx-auto px-4 py-16">
            <h1 className="text-4xl font-bold mb-4">Kabar Alumni</h1>
            <p className="text-emerald-100 text-lg max-w-2xl">
              Bagikan cerita, prestasi, dan informasi terbaru komunitas alumni IKA UNIMED.
            </p>
          </div>
        </div>

        {/* Search & Filter */}
        <div className="bg-white border-b border-gray-200">
          <div className="container mx-auto px-4 py-6">
            <form onSubmit={handleSearch} className="mb-6">
              <div className="flex gap-3">
                <input
                  type="text"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  placeholder="Masukkan kata kunci kabar alumni"
                  className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
                <button
                  type="submit"
                  className="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors font-medium"
                >
                  Terapkan Pencarian
                </button>
              </div>
            </form>

            {/* Category Filters */}
            <div className="flex gap-2 flex-wrap">
              <button
                onClick={() => handleCategoryFilter("")}
                className={`px-4 py-2 rounded-full text-sm font-medium transition-colors ${
                  !filters.category
                    ? "bg-emerald-600 text-white"
                    : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                }`}
              >
                Tampilkan Semua
              </button>
              {Object.entries(categories).map(([key, label]) => (
                <button
                  key={key}
                  onClick={() => handleCategoryFilter(key)}
                  className={`px-4 py-2 rounded-full text-sm font-medium transition-colors ${
                    filters.category === key
                      ? "bg-emerald-600 text-white"
                      : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                  }`}
                >
                  {label}
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Posts Grid */}
        <div className="container mx-auto px-4 py-12">
          {!posts?.data || posts.data.length === 0 ? (
            <div className="text-center py-16">
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
                  d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
                />
              </svg>
              <h3 className="mt-4 text-lg font-medium text-gray-900">
                Belum ada kabar alumni
              </h3>
              <p className="mt-2 text-gray-500">
                {filters.search || filters.category
                  ? "Sesuaikan filter untuk menampilkan hasil."
                  : "Bagikan kabar pertama untuk komunitas alumni."}
              </p>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {posts.data.map((post) => (
                <Link
                  key={post.id}
                  href={route("alumni-posts.show", post.slug)}
                  className="bg-white rounded-lg border border-gray-200 hover:shadow-lg transition-shadow overflow-hidden group"
                >
                  <div className="p-6">
                    <div className="flex items-center justify-between mb-3">
                      <span
                        className={`inline-flex px-3 py-1 text-xs font-semibold rounded-full border ${getCategoryColor(
                          post.category
                        )}`}
                      >
                        {categories[post.category]}
                      </span>
                    </div>
                    <h3 className="text-lg font-bold text-gray-900 mb-2 group-hover:text-emerald-600 transition-colors line-clamp-2">
                      {post.title}
                    </h3>
                    <p className="text-gray-600 text-sm mb-4 line-clamp-3">
                      {post.content.substring(0, 150)}...
                    </p>
                    <div className="flex items-center justify-between text-sm text-gray-500">
                      <span>👤 {post.user.name}</span>
                      <span>
                        {new Date(post.published_at).toLocaleDateString("id-ID")}
                      </span>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          )}

          {/* Pagination */}
          {posts?.meta && posts.meta.last_page > 1 && posts.links && (
            <div className="mt-8 flex justify-center gap-2">
              {posts.links.map((link: any, idx: number) => (
                <Link
                  key={idx}
                  href={link.url || "#"}
                  className={`px-4 py-2 rounded-lg border transition-colors ${
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
      </div>
    </MainLayout>
  );
}