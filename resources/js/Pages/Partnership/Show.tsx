import React from 'react';
import { Head, Link } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { ArrowLeft, Building2, ExternalLink, Share2, Globe, CheckCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { route } from 'ziggy-js';

interface Partnership {
  id: number;
  name: string;
  slug: string;
  logo?: string;
  website?: string;
  description?: string;
  benefit_details?: string;
  category: string;
  created_at: string;
}

interface Props {
  partnership: Partnership;
  related: Partnership[];
}

export default function Show({ partnership, related }: Props) {
  const handleShare = async () => {
    if (navigator.share) {
      try {
        await navigator.share({
          title: partnership.name,
          text: `Mitra IKA UNIMED: ${partnership.name}`,
          url: window.location.href,
        });
      } catch (err) {
        console.error('Share failed:', err);
      }
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Tautan telah disalin ke clipboard!');
    }
  };

  return (
    <MainLayout variant="full">
      <Head title={`${partnership.name} - Mitra IKA UNIMED`} />

      <div className="bg-gray-50 border-b border-gray-200">
        <div className="container mx-auto px-4 py-8">
            <Link
                href={route('partnerships.index')}
                className="inline-flex items-center text-gray-500 hover:text-emerald-600 mb-6 transition-colors"
            >
                <ArrowLeft className="w-4 h-4 mr-2" />
                Kembali ke Daftar Mitra
            </Link>

            <div className="flex flex-col md:flex-row gap-8 items-start">
                <div className="w-full md:w-48 h-32 md:h-48 bg-white rounded-lg shadow-sm border border-gray-200 flex items-center justify-center p-4 flex-shrink-0">
                    {partnership.logo ? (
                        <img src={`/storage/${partnership.logo}`} alt={partnership.name} className="max-w-full max-h-full object-contain" />
                    ) : (
                        <Building2 className="w-16 h-16 text-gray-300" />
                    )}
                </div>
                
                <div className="flex-1">
                    <div className="flex flex-wrap gap-2 mb-3">
                        <span className="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-semibold">
                            {partnership.category}
                        </span>
                    </div>
                    <h1 className="text-3xl font-bold text-gray-900 mb-4">{partnership.name}</h1>
                    
                    {partnership.website && (
                        <a 
                            href={partnership.website} 
                            target="_blank" 
                            rel="noopener noreferrer"
                            className="inline-flex items-center text-emerald-600 hover:text-emerald-700 font-medium"
                        >
                            <Globe className="w-4 h-4 mr-2" />
                            Kunjungi Situs
                            <ExternalLink className="w-3 h-3 ml-1" />
                        </a>
                    )}
                </div>

                <div className="flex flex-col gap-3 w-full md:w-auto">
                    <Button variant="outline" onClick={handleShare} className="w-full">
                        <Share2 className="w-4 h-4 mr-2" />
                        Bagikan halaman ini
                    </Button>
                </div>
            </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
            {/* Main Content */}
            <div className="lg:col-span-2 space-y-8">
                {partnership.description && (
                    <section className="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
                        <h2 className="text-xl font-bold text-gray-900 mb-6 border-b pb-2">Profil Mitra</h2>
                        <div 
                            className="prose prose-emerald max-w-none text-gray-600"
                            dangerouslySetInnerHTML={{ __html: partnership.description }}
                        />
                    </section>
                )}

                {partnership.benefit_details && (
                    <section className="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
                        <h2 className="text-xl font-bold text-gray-900 mb-6 border-b pb-2 flex items-center">
                            <CheckCircle className="w-5 h-5 mr-2 text-emerald-600" />
                            Manfaat Kerjasama
                        </h2>
                        <div 
                            className="prose prose-emerald max-w-none text-gray-600"
                            dangerouslySetInnerHTML={{ __html: partnership.benefit_details }}
                        />
                    </section>
                )}
            </div>

            {/* Sidebar / Related */}
            <div className="space-y-8">
                {related.length > 0 && (
                    <section>
                        <h3 className="text-lg font-bold text-gray-900 mb-4">Mitra Lainnya</h3>
                        <div className="space-y-4">
                            {related.map((item) => (
                                <Link 
                                    key={item.id} 
                                    href={route('partnerships.show', item.slug)}
                                    className="block bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition-shadow"
                                >
                                    <div className="flex items-center gap-3 mb-2">
                                        <div className="w-10 h-10 bg-gray-50 rounded flex items-center justify-center flex-shrink-0">
                                            {item.logo ? (
                                                <img src={`/storage/${item.logo}`} className="w-6 h-6 object-contain" />
                                            ) : (
                                                <Building2 className="w-5 h-5 text-gray-400" />
                                            )}
                                        </div>
                                        <h4 className="font-bold text-gray-900 line-clamp-2 group-hover:text-emerald-600 text-sm">
                                            {item.name}
                                        </h4>
                                    </div>
                                    <div className="text-xs text-emerald-600 bg-emerald-50 inline-block px-2 py-0.5 rounded">
                                        {item.category}
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </section>
                )}
            </div>
        </div>
      </div>
    </MainLayout>
  );
}
