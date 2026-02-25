import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import NewsCard from '@/Components/NewsCard';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { 
    MapPin, Phone, Mail, Globe, Calendar, Users, ChevronRight, 
    ChevronDown, Building2, Facebook, Instagram, Twitter 
} from 'lucide-react';

// --- Interfaces ---

interface Organization {
    id: number;
    name: string;
    slug: string;
    type: 'pp' | 'dpw' | 'dpc';
    logo: string | null;
    description: string | null;
    address: string | null;
    phone: string | null;
    email: string | null;
    is_active: boolean;
}

interface OrganizationMember {
    id: number;
    name: string;
    position: string;
    department: string | null;
    image: string | null;
    order: number;
}

interface Department {
    id: string;
    name: string;
    members: OrganizationMember[];
}

interface NewsItem {
    id: number;
    title: string;
    excerpt: string;
    slug: string;
    image: string | null;
    view_count: number;
    published_at: string;
    created_at: string;
    author?: {
        name: string;
    };
    categories?: Array<{
        name: string;
        slug: string;
    }>;
}

interface AgendaItem {
    id: number;
    title: string;
    date: string;
    description: string;
}

interface Props {
    organization: Organization;
    members: OrganizationMember[]; // Core members
    departments: Department[];
    news: NewsItem[];
    agenda: AgendaItem[];
}

// --- Components ---

const DepartmentCard = ({ dept }: { dept: Department }) => {
    const [isOpen, setIsOpen] = useState(true);

    return (
        <div className="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <button 
                onClick={() => setIsOpen(!isOpen)}
                className="w-full px-6 py-4 flex items-center justify-between bg-gray-50 hover:bg-gray-100 transition-colors"
            >
                <div className="flex items-center gap-3">
                    <Building2 className="w-5 h-5 text-teal-600" />
                    <h3 className="font-bold text-gray-800">{dept.name}</h3>
                    <Badge variant="outline" className="bg-white text-gray-500 border-gray-200">
                        {dept.members.length} Anggota
                    </Badge>
                </div>
                <ChevronDown className={`w-5 h-5 text-gray-400 transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`} />
            </button>
            
            {isOpen && (
                <div className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {dept.members.map((member) => (
                        <div key={member.id} className="flex items-center gap-4 p-3 rounded-lg border border-gray-100 bg-white hover:border-teal-100 transition-colors">
                            <div className="w-12 h-12 flex-shrink-0 rounded-full bg-gray-100 overflow-hidden border border-gray-200">
                                {member.image ? (
                                    <img src={`/storage/${member.image}`} alt={member.name} className="w-full h-full object-cover" />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-gray-400">
                                        <Users className="w-6 h-6" />
                                    </div>
                                )}
                            </div>
                            <div>
                                <h4 className="font-bold text-gray-900 text-sm">{member.name}</h4>
                                <p className="text-xs text-teal-600 font-medium uppercase">{member.position}</p>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default function Show({ organization, members, departments, news, agenda }: Props) {
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
            case 'pp': return 'bg-red-100 text-red-800 border-red-200';
            case 'dpw': return 'bg-blue-100 text-blue-800 border-blue-200';
            case 'dpc': return 'bg-green-100 text-green-800 border-green-200';
            default: return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    };

    return (
        <>
            <Head title={`${organization.name} - Struktur & Profil`} />

            <MainLayout variant="full">

                {/* 1️⃣ Header Organisasi */}
                <div className="bg-white border-b border-gray-200 relative overflow-hidden">
                    <div className="absolute top-0 right-0 w-96 h-96 bg-teal-50 rounded-full blur-3xl opacity-50 translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
                    
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 relative z-10">
                        <div className="flex flex-col md:flex-row items-start gap-8">
                            {/* Logo */}
                            <div className="flex-shrink-0 w-32 h-32 md:w-40 md:h-40 bg-white rounded-2xl border-2 border-gray-100 shadow-xl overflow-hidden flex items-center justify-center p-2 mx-auto md:mx-0">
                                {organization.logo ? (
                                    <img 
                                        src={`/storage/${organization.logo}`} 
                                        alt={organization.name} 
                                        className="w-full h-full object-contain"
                                    />
                                ) : (
                                    <span className="text-4xl font-bold text-gray-300 select-none">
                                        {organization.name.substring(0, 2).toUpperCase()}
                                    </span>
                                )}
                            </div>

                            {/* Info Utama */}
                            <div className="flex-1 text-center md:text-left w-full">
                                <div className="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-3">
                                    <span className={`px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border ${getTypeColor(organization.type)}`}>
                                        {organization.type}
                                    </span>
                                    {organization.is_active ? (
                                        <span className="px-3 py-1 bg-teal-50 text-teal-700 border border-teal-200 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1">
                                            <span className="w-2 h-2 bg-teal-500 rounded-full animate-pulse"></span>
                                            Aktif
                                        </span>
                                    ) : (
                                        <span className="px-3 py-1 bg-gray-100 text-gray-600 border border-gray-200 rounded-full text-xs font-bold uppercase tracking-wider">
                                            Non-Aktif
                                        </span>
                                    )}
                                </div>
                                
                                <h1 className="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3 leading-tight">
                                    {organization.name}
                                </h1>
                                <p className="text-lg text-gray-600 font-medium flex items-center justify-center md:justify-start gap-2">
                                    {getTypeLabel(organization.type)} 
                                    <span className="w-1 h-1 bg-gray-400 rounded-full"></span>
                                    IKA UNIMED
                                </p>

                                {/* Quick Contact Icons */}
                                <div className="mt-6 flex flex-wrap justify-center md:justify-start gap-4">
                                    {organization.email && (
                                        <a href={`mailto:${organization.email}`} className="flex items-center gap-2 text-sm text-gray-600 hover:text-teal-600 transition-colors bg-gray-50 px-3 py-2 rounded-lg border border-gray-200">
                                            <Mail className="w-4 h-4" />
                                            {organization.email}
                                        </a>
                                    )}
                                    {organization.phone && (
                                        <a href={`tel:${organization.phone}`} className="flex items-center gap-2 text-sm text-gray-600 hover:text-teal-600 transition-colors bg-gray-50 px-3 py-2 rounded-lg border border-gray-200">
                                            <Phone className="w-4 h-4" />
                                            {organization.phone}
                                        </a>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-10">
                        
                        {/* Main Content (8 cols) */}
                        <div className="lg:col-span-8 space-y-12">
                            
                            {/* 2️⃣ Ringkasan & Deskripsi */}
                            <section>
                                <div className="flex items-center gap-3 mb-6">
                                    <div className="p-2 bg-teal-100 rounded-lg">
                                        <Globe className="w-6 h-6 text-teal-700" />
                                    </div>
                                    <h2 className="text-2xl font-bold text-gray-900">Tentang Organisasi</h2>
                                </div>
                                <div className="prose prose-lg text-gray-600 bg-white p-8 rounded-2xl border border-gray-100 shadow-sm leading-relaxed">
                                    <p>
                                        {organization.description || 
                                        "Organisasi ini merupakan bagian integral dari Ikatan Alumni Universitas Negeri Medan (IKA UNIMED) yang berdedikasi untuk mempererat tali silaturahmi antar alumni serta memberikan kontribusi nyata bagi almamater dan masyarakat."}
                                    </p>
                                </div>
                            </section>

                            {/* 3️⃣ Struktur Organisasi */}
                            <section>
                                <div className="flex items-center gap-3 mb-8">
                                    <div className="p-2 bg-teal-100 rounded-lg">
                                        <Users className="w-6 h-6 text-teal-700" />
                                    </div>
                                    <h2 className="text-2xl font-bold text-gray-900">Struktur Kepengurusan</h2>
                                </div>

                                {/* Core Members Grid */}
                                {members.length > 0 ? (
                                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-8">
                                        {members.map((member) => (
                                            <div key={member.id} className="bg-white rounded-xl p-6 border border-gray-200 text-center shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
                                                <div className="absolute top-0 left-0 w-full h-1 bg-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></div>
                                                <div className="w-24 h-24 mx-auto bg-gray-50 rounded-full mb-4 overflow-hidden border-4 border-white shadow-lg ring-1 ring-gray-100">
                                                    {member.image ? (
                                                        <img 
                                                            src={`/storage/${member.image}`} 
                                                            alt={member.name}
                                                            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                                        />
                                                    ) : (
                                                        <div className="w-full h-full flex items-center justify-center text-gray-300">
                                                            <Users className="w-10 h-10" />
                                                        </div>
                                                    )}
                                                </div>
                                                <h3 className="font-bold text-gray-900 text-lg group-hover:text-teal-600 transition-colors mb-1">{member.name}</h3>
                                                <p className="text-sm text-teal-600 font-bold uppercase tracking-wide">{member.position}</p>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200 mb-8">
                                        <p className="text-gray-500 italic">Belum ada data pengurus inti.</p>
                                    </div>
                                )}

                                {/* Departments Collapsible */}
                                {departments.length > 0 && (
                                    <div className="space-y-4">
                                        {departments.map((dept) => (
                                            <DepartmentCard key={dept.id} dept={dept} />
                                        ))}
                                    </div>
                                )}
                            </section>

                            {/* 4️⃣ Berita Organisasi */}
                            <section>
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center gap-3">
                                        <div className="w-1 h-8 bg-teal-500 rounded-full"></div>
                                        <h2 className="text-2xl font-bold text-gray-900">Berita & Kegiatan</h2>
                                    </div>
                                    <Link 
                                        href={route('news.index', { organization: organization.slug })}
                                        className="text-sm font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1 px-4 py-2 bg-teal-50 rounded-lg hover:bg-teal-100 transition-colors"
                                    >
                                        Lihat Semua <ChevronRight className="w-4 h-4" />
                                    </Link>
                                </div>

                                {news.length > 0 ? (
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        {news.map((item) => (
                                            <div key={item.id} className="h-full">
                                                <NewsCard 
                                                    {...item} 
                                                    organization={organization}
                                                    size="sm"
                                                />
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12 bg-white rounded-xl border border-gray-200 border-dashed">
                                        <p className="text-gray-500">Belum ada berita dipublikasikan dari organisasi ini.</p>
                                    </div>
                                )}
                            </section>

                             {/* 5️⃣ Agenda / Program (Optional) */}
                             {agenda && agenda.length > 0 && (
                                <section>
                                    <div className="flex items-center gap-3 mb-6">
                                        <div className="p-2 bg-orange-100 rounded-lg">
                                            <Calendar className="w-6 h-6 text-orange-600" />
                                        </div>
                                        <h2 className="text-2xl font-bold text-gray-900">Agenda Mendatang</h2>
                                    </div>
                                    <div className="grid gap-4">
                                        {agenda.map((item) => (
                                            <div key={item.id} className="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex gap-4">
                                                <div className="bg-orange-50 text-orange-700 p-3 rounded-lg text-center min-w-[80px]">
                                                    <span className="block text-2xl font-bold">{new Date(item.date).getDate()}</span>
                                                    <span className="block text-xs font-bold uppercase">{new Date(item.date).toLocaleString('id-ID', { month: 'short' })}</span>
                                                </div>
                                                <div>
                                                    <h3 className="font-bold text-gray-900">{item.title}</h3>
                                                    <p className="text-sm text-gray-600 mt-1">{item.description}</p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </section>
                             )}

                        </div>

                        {/* Sidebar (4 cols) */}
                        <div className="lg:col-span-4 space-y-8">
                            
                            {/* Kontak Card */}
                            <div className="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 sticky top-24">
                                <h3 className="font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100 flex items-center gap-2">
                                    <MapPin className="w-5 h-5 text-teal-600" />
                                    Sekretariat
                                </h3>
                                
                                <div className="space-y-6">
                                    <div className="text-gray-600 text-sm leading-relaxed">
                                        {organization.address || "Alamat sekretariat belum ditambahkan."}
                                    </div>

                                    <div className="space-y-3">
                                        {organization.phone && (
                                            <a href={`tel:${organization.phone}`} className="flex items-center gap-3 text-gray-700 hover:text-teal-600 transition-colors p-3 bg-gray-50 rounded-lg">
                                                <Phone className="w-5 h-5 text-teal-500" />
                                                <span className="font-medium text-sm">{organization.phone}</span>
                                            </a>
                                        )}
                                        {organization.email && (
                                            <a href={`mailto:${organization.email}`} className="flex items-center gap-3 text-gray-700 hover:text-teal-600 transition-colors p-3 bg-gray-50 rounded-lg">
                                                <Mail className="w-5 h-5 text-teal-500" />
                                                <span className="font-medium text-sm">{organization.email}</span>
                                            </a>
                                        )}
                                    </div>

                                    <div className="pt-6 border-t border-gray-100">
                                        <h4 className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Social Media</h4>
                                        <div className="flex gap-2">
                                            <button className="p-2 bg-gray-100 rounded-full hover:bg-blue-50 hover:text-blue-600 transition-colors"><Facebook className="w-5 h-5" /></button>
                                            <button className="p-2 bg-gray-100 rounded-full hover:bg-pink-50 hover:text-pink-600 transition-colors"><Instagram className="w-5 h-5" /></button>
                                            <button className="p-2 bg-gray-100 rounded-full hover:bg-sky-50 hover:text-sky-600 transition-colors"><Twitter className="w-5 h-5" /></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </MainLayout>
        </>
    );
}