import React from "react";
import { Head, Link } from "@inertiajs/react";
import { route } from "ziggy-js";
import { Calendar, User, ArrowLeft, Share2, MapPin, Facebook, Twitter, Linkedin, Copy, Instagram } from "lucide-react";
import MainLayout from "@/components/MainLayout";

// Custom Icon for TikTok
const TiktokIcon = ({ className }: { className?: string }) => (
  <svg 
    xmlns="http://www.w3.org/2000/svg" 
    viewBox="0 0 24 24" 
    fill="none" 
    stroke="currentColor" 
    strokeWidth="2" 
    strokeLinecap="round" 
    strokeLinejoin="round" 
    className={className}
  >
    <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5" />
  </svg>
);

interface AlumniPost {
  id: number;
  title: string;
  slug: string;
  content: string;
  category: string;
  image?: string;
  map_location?: string;
  published_at: string;
  user: {
    id: number;
    name: string;
    s1_prodi?: string;
    s1_tahun_tamat?: string;
  };
}

interface Props {
  post: AlumniPost;
  relatedPosts: AlumniPost[];
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

export default function Show({ post, relatedPosts }: Props) {
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  };

  const copyToClipboard = () => {
    navigator.clipboard.writeText(window.location.href);
    alert('Tautan berhasil disalin!');
  };

  const shareUrl = typeof window !== 'undefined' ? window.location.href : '';
  const shareText = `Baca informasi alumni berikut: ${post.title}`;

  return (
    <MainLayout variant="full">
      <Head title={post.title} />

      <div className="min-h-screen bg-gray-50 pb-12">
        {/* Header / Breadcrumb */}
        <div className="bg-white border-b border-gray-200 shadow-sm sticky top-16 md:top-20 z-10">
          <div className="container mx-auto px-4 py-4">
            <Link
              href={route("alumni-posts.index")}
              className="inline-flex items-center text-gray-600 hover:text-emerald-600 transition-colors"
            >
              <ArrowLeft className="w-4 h-4 mr-2" />
              Kembali ke Kabar Alumni
            </Link>
          </div>
        </div>

        <div className="container mx-auto px-4 py-8">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {/* Main Content */}
            <div className="lg:col-span-2">
              <article className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                {post.image && (
                  <div className="w-full h-64 md:h-96 bg-gray-100 relative">
                     <img 
                       src={`/storage/${post.image}`} 
                       alt={post.title}
                       className="w-full h-full object-cover"
                     />
                  </div>
                )}
                
                <div className="p-6 md:p-8">
                  <div className="flex flex-wrap items-center gap-3 mb-4">
                    <span
                      className={`px-3 py-1 rounded-full text-xs font-medium border ${getCategoryColor(
                        post.category
                      )}`}
                    >
                      {post.category.charAt(0).toUpperCase() + post.category.slice(1)}
                    </span>
                    <span className="text-gray-400 text-sm flex items-center">
                      <Calendar className="w-4 h-4 mr-1" />
                      {formatDate(post.published_at)}
                    </span>
                  </div>

                  <h1 className="text-2xl md:text-3xl font-bold text-gray-900 mb-6">
                    {post.title}
                  </h1>

                  <div className="flex items-center p-4 bg-gray-50 rounded-lg mb-8 border border-gray-100">
                    <div className="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 font-bold text-lg mr-3">
                      {post.user.name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                      <p className="font-medium text-gray-900">{post.user.name}</p>
                      <p className="text-xs text-gray-500">
                        {post.user.s1_prodi} 
                        {post.user.s1_tahun_tamat ? ` • Angkatan ${post.user.s1_tahun_tamat}` : ''}
                      </p>
                    </div>
                  </div>

                  <div className="prose prose-emerald max-w-none text-gray-700 leading-relaxed whitespace-pre-line mb-8">
                    {post.content}
                  </div>

                  {post.map_location && (
                    <div className="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                      <h3 className="flex items-center font-semibold text-gray-900 mb-3">
                        <MapPin className="w-5 h-5 mr-2 text-emerald-600" />
                        Lokasi
                      </h3>
                      <div 
                        className="w-full aspect-video rounded-md overflow-hidden bg-gray-200 [&>iframe]:w-full [&>iframe]:h-full [&>iframe]:border-0"
                        dangerouslySetInnerHTML={{ __html: post.map_location }}
                      />
                      {/* Helper text if map is not showing correctly */}
                      <p className="text-xs text-gray-400 mt-2 italic">
                        * Gunakan kode embed iframe dari Google Maps jika peta tidak tampil.
                      </p>
                    </div>
                  )}

                  <div className="mt-8 pt-8 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p className="text-sm text-gray-500">
                      Bagikan halaman ini
                    </p>
                    <div className="flex gap-2 flex-wrap justify-center">
                      <a
                        href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-full transition-colors"
                        title="Bagikan ke Facebook"
                      >
                        <Facebook className="w-5 h-5" />
                      </a>
                      <a
                        href={`https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(shareUrl)}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="p-2 text-sky-500 bg-sky-50 hover:bg-sky-100 rounded-full transition-colors"
                        title="Bagikan ke X"
                      >
                        <Twitter className="w-5 h-5" />
                      </a>
                      <a
                        href={`https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(shareUrl)}&title=${encodeURIComponent(post.title)}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="p-2 text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-full transition-colors"
                        title="Bagikan ke LinkedIn"
                      >
                        <Linkedin className="w-5 h-5" />
                      </a>
                      <a
                        href={`https://wa.me/?text=${encodeURIComponent(shareText + ' ' + shareUrl)}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="p-2 text-green-600 bg-green-50 hover:bg-green-100 rounded-full transition-colors"
                        title="Bagikan ke WhatsApp"
                      >
                        <Share2 className="w-5 h-5" />
                      </a>
                      
                      {/* TikTok & Instagram (Just Links/Copy because no direct web share) */}
                      <button 
                        onClick={() => {
                             navigator.clipboard.writeText(shareUrl);
                             alert('Tautan berhasil disalin! Anda bisa menempelkannya di Instagram/TikTok.');
                        }}
                        className="p-2 text-pink-600 bg-pink-50 hover:bg-pink-100 rounded-full transition-colors"
                        title="Salin tautan untuk Instagram"
                      >
                        <Instagram className="w-5 h-5" />
                      </button>
                      <button 
                        onClick={() => {
                            navigator.clipboard.writeText(shareUrl);
                            alert('Tautan berhasil disalin! Anda bisa menempelkannya di TikTok.');
                        }}
                        className="p-2 text-black bg-gray-100 hover:bg-gray-200 rounded-full transition-colors"
                        title="Salin tautan untuk TikTok"
                      >
                        <TiktokIcon className="w-5 h-5" />
                      </button>

                      <button 
                        onClick={copyToClipboard}
                        className="p-2 text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-full transition-colors"
                        title="Salin tautan"
                      >
                        <Copy className="w-5 h-5" />
                      </button>
                    </div>
                  </div>
                </div>
              </article>
            </div>

            {/* Sidebar / Related Posts */}
            <div className="lg:col-span-1">
              <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                <h3 className="font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">
                  Kabar Terkait
                </h3>
                
                {relatedPosts.length > 0 ? (
                  <div className="space-y-4">
                    {relatedPosts.map((related) => (
                      <Link
                        key={related.id}
                        href={route('alumni-posts.show', related.slug)}
                        className="block group"
                      >
                        <div className="flex gap-3">
                          <div className="flex-1">
                            <span className="text-xs font-medium text-emerald-600 mb-1 block">
                              {formatDate(related.published_at)}
                            </span>
                            <h4 className="font-medium text-gray-800 group-hover:text-emerald-600 line-clamp-2 transition-colors">
                              {related.title}
                            </h4>
                          </div>
                        </div>
                      </Link>
                    ))}
                  </div>
                ) : (
                  <p className="text-gray-500 text-sm italic">
                    Belum ada kabar terkait lainnya.
                  </p>
                )}

                <div className="mt-8 pt-6 border-t border-gray-100">
                  <Link 
                    href={route('alumni-posts.index')}
                    className="block w-full text-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                  >
                    Lihat Seluruh Kabar
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </MainLayout>
  );
}
