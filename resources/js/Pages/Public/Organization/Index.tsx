import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { Card, CardContent, CardFooter, CardHeader } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';

interface Organization {
    id: number;
    name: string;
    slug: string;
    type: 'pp' | 'dpw' | 'dpc';
    logo: string | null;
    description: string | null;
    is_active: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    organizations: {
        data: Organization[];
        current_page: number;
        last_page: number;
        links: PaginationLink[];
    };
    filters: {
        type?: string;
        search?: string;
    };
}

export default function Index({ organizations, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [type, setType] = useState(filters.type || '');

    // Debounce search
    useEffect(() => {
        const timeoutId = setTimeout(() => {
            if (search !== (filters.search || '')) {
                router.get(
                    route('organizations.index'),
                    { search, type },
                    { preserveState: true, preserveScroll: true }
                );
            }
        }, 500);

        return () => clearTimeout(timeoutId);
    }, [search]);

    const handleTypeChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        const newType = e.target.value;
        setType(newType);
        router.get(
            route('organizations.index'),
            { search, type: newType },
            { preserveState: true, preserveScroll: true }
        );
    };

    const getTypeLabel = (type: string) => {
        switch (type) {
            case 'pp': return 'Pengurus Pusat';
            case 'dpw': return 'Dewan Pengurus Wilayah';
            case 'dpc': return 'Dewan Pengurus Cabang';
            default: return type.toUpperCase();
        }
    };

    const getTypeColor = (type: string) => {
        switch (type) {
            case 'pp': return 'bg-red-100 text-red-800 hover:bg-red-200';
            case 'dpw': return 'bg-blue-100 text-blue-800 hover:bg-blue-200';
            case 'dpc': return 'bg-green-100 text-green-800 hover:bg-green-200';
            default: return 'bg-gray-100 text-gray-800 hover:bg-gray-200';
        }
    };

    return (
        <>
            <Head title="Organisasi IKA UNIMED" />

            <MainLayout variant="full">
                {/* Hero / Header Section */}
                <div className="bg-white border-b border-gray-200">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
                        <h1 className="text-4xl font-bold text-gray-900 mb-4">
                            Organisasi IKA UNIMED
                        </h1>
                        <p className="text-lg text-gray-600 max-w-2xl mx-auto">
                            Menghubungkan alumni Universitas Negeri Medan melalui jaringan organisasi yang tersebar di seluruh wilayah.
                        </p>
                    </div>
                </div>

                {/* Filter Section */}
                <div className="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                        <div className="flex flex-col md:flex-row gap-4 justify-between items-center">
                            {/* Type Filter */}
                            <div className="w-full md:w-64">
                                <select
                                    value={type}
                                    onChange={handleTypeChange}
                                    className="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm"
                                >
                                    <option value="">Semua Organisasi</option>
                                    <option value="pp">Pengurus Pusat (PP)</option>
                                    <option value="dpw">Pengurus Wilayah (DPW)</option>
                                    <option value="dpc">Pengurus Cabang (DPC)</option>
                                </select>
                            </div>

                            {/* Search Input */}
                            <div className="w-full md:w-96 relative">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Cari nama organisasi..."
                                    className="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 pl-10 text-sm"
                                />
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Organization Grid */}
                <main className="flex-grow py-12">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        {organizations.data.length > 0 ? (
                            <>
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                    {organizations.data.map((org) => (
                                        <Link 
                                            key={org.id} 
                                            href={route('organizations.show', org.slug)}
                                            className="block group h-full"
                                        >
                                            <Card className="h-full hover:shadow-lg transition-shadow duration-300 border-gray-200 overflow-hidden flex flex-col">
                                                <CardHeader className="p-6 pb-0 flex flex-row items-center gap-4">
                                                    <div className="w-16 h-16 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                                        {org.logo ? (
                                                            <img 
                                                                src={`/storage/${org.logo}`} 
                                                                alt={org.name} 
                                                                className="w-full h-full object-cover"
                                                            />
                                                        ) : (
                                                            <span className="text-xl font-bold text-gray-300">
                                                                {org.name.substring(0, 2).toUpperCase()}
                                                            </span>
                                                        )}
                                                    </div>
                                                    <div>
                                                        <Badge className={`${getTypeColor(org.type)} border-0 mb-2`}>
                                                            {org.type.toUpperCase()}
                                                        </Badge>
                                                        <h3 className="font-bold text-gray-900 group-hover:text-teal-600 transition-colors line-clamp-2">
                                                            {org.name}
                                                        </h3>
                                                    </div>
                                                </CardHeader>
                                                <CardContent className="p-6 pt-4 flex-grow">
                                                    <p className="text-gray-600 text-sm line-clamp-3">
                                                        {org.description || 'Tidak ada deskripsi.'}
                                                    </p>
                                                </CardContent>
                                                <CardFooter className="p-6 pt-0 mt-auto">
                                                    <span className="text-sm font-medium text-teal-600 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                                                        Lihat Profil
                                                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </span>
                                                </CardFooter>
                                            </Card>
                                        </Link>
                                    ))}
                                </div>

                                {/* Pagination */}
                                {organizations.last_page > 1 && (
                                    <div className="mt-12 flex justify-center">
                                        <div className="flex gap-1">
                                            {organizations.links.map((link, i) => (
                                                <Link
                                                    key={i}
                                                    href={link.url || '#'}
                                                    className={`px-4 py-2 text-sm rounded-md transition-colors ${
                                                        link.active
                                                            ? 'bg-teal-600 text-white'
                                                            : !link.url
                                                            ? 'text-gray-400 cursor-not-allowed'
                                                            : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                                                    }`}
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                                />
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </>
                        ) : (
                            <div className="text-center py-24 bg-white rounded-lg border border-gray-200 border-dashed">
                                <div className="mx-auto w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <svg className="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-medium text-gray-900 mb-1">Organisasi tidak ditemukan</h3>
                                <p className="text-gray-500">Coba ubah filter atau kata kunci pencarian Anda.</p>
                                {(search || type) && (
                                    <button
                                        onClick={() => { setSearch(''); setType(''); }}
                                        className="mt-4 px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    >
                                        Reset Filter
                                    </button>
                                )}
                            </div>
                        )}
                    </div>
                </main>
            </MainLayout>
        </>
    );
}
