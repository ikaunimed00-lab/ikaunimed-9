import { Head, usePage, useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';
import {
    GraduationCap,
    User,
    Briefcase,
    Plus,
    Trash2,
    Save,
    MapPin,
    Mail
} from 'lucide-react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { DashboardLayout } from '@/components/DashboardLayout';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Textarea } from '@/components/ui/textarea';

interface Education {
    id?: number;
    level: string;
    university: string;
    faculty: string;
    major: string;
    admission_year: string;
    graduation_year: string;
}

interface ProfileEditProps {
    user: any;
    notifications: any[];
    stats?: Record<string, number>;
}

export default function AlumniProfileEdit({ 
    user, 
    notifications = [], 
    stats = {} 
}: ProfileEditProps) {
    
    // Initialize form with user data
    const { data, setData, patch, processing, errors, transform } = useForm({
        // Identitas Pribadi
        name: user.name || '',
        email: user.email || '',
        nik: user.nik || '',
        wa: user.wa || '',
        gender: user.gender || '',
        tempat_lahir: user.tempat_lahir || '',
        tanggal_lahir: user.tanggal_lahir || '',
        alamat_lengkap: user.alamat_lengkap || '',
        domicile: user.domicile || '',
        occupation: user.occupation || '',

        // Riwayat Pendidikan (Dynamic)
        educations: (user.educations && user.educations.length > 0) ? user.educations.map((edu: any) => ({
            level: edu.level,
            university: edu.university,
            faculty: edu.faculty || '',
            major: edu.major,
            admission_year: edu.admission_year?.toString() || '',
            graduation_year: edu.graduation_year?.toString() || ''
        })) : [] as Education[],
        
        // Legacy Fields (keep them for now to avoid breaking if controller expects them, 
        // though we can send empty if not used)
        s1_fakultas: user.s1_fakultas || '',
        s1_prodi: user.s1_prodi || '',
        s1_tahun_masuk: user.s1_tahun_masuk || '',
        s1_tahun_tamat: user.s1_tahun_tamat || '',
        s2_prodi: user.s2_prodi || '',
        s2_tahun_masuk: user.s2_tahun_masuk || '',
        s2_tahun_tamat: user.s2_tahun_tamat || '',
        s3_prodi: user.s3_prodi || '',
        s3_tahun_masuk: user.s3_tahun_masuk || '',
        s3_tahun_tamat: user.s3_tahun_tamat || '',
    });

    const addEducation = () => {
        setData('educations', [
            ...data.educations,
            {
                level: 'S1',
                university: '',
                faculty: '',
                major: '',
                admission_year: '',
                graduation_year: ''
            }
        ]);
    };

    const removeEducation = (index: number) => {
        const newEducations = [...data.educations];
        newEducations.splice(index, 1);
        setData('educations', newEducations);
    };

    const updateEducation = (index: number, field: keyof Education, value: string) => {
        const newEducations = [...data.educations];
        newEducations[index] = { ...newEducations[index], [field]: value };
        setData('educations', newEducations);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(route('profile.update'), {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: Show toast
            }
        });
    };

    return (
        <DashboardLayout 
            user={user} 
            notifications={notifications} 
            title="Edit Profil Alumni"
            stats={stats}
        >
            <Head title="Edit Profil Alumni" />

            <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200 mb-10">
                {/* Header dengan gradient */}
                <div className="bg-gradient-to-r from-[#00A69D] to-[#00897B] p-8 text-white relative overflow-hidden">
                    <div className="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
                    <div className="relative z-10">
                        <h2 className="text-2xl font-bold mb-2">Informasi Alumni</h2>
                        <p className="text-teal-50/90 text-sm">
                            Lengkapi informasi profil untuk mengakses semua layanan IKA UNIMED
                        </p>
                    </div>
                </div>

                {/* Form Content */}
                <div className="p-8 md:p-12">
                    <form onSubmit={submit} className="space-y-12">
                        
                        {/* SEKSI 1: IDENTITAS PRIBADI */}
                        <section>
                            <div className="flex items-center gap-3 mb-6">
                                <div className="p-3 bg-blue-50 rounded-lg">
                                    <User className="h-5 w-5 text-blue-600" />
                                </div>
                                <div className="flex-1">
                                    <h3 className="text-lg font-bold text-slate-900">Identitas Pribadi</h3>
                                    <p className="text-sm text-slate-500">Data diri lengkap sesuai KTP</p>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 bg-blue-50/40 p-6 rounded-2xl border border-blue-100">
                                {/* Nama Lengkap */}
                                <div className="lg:col-span-2 grid gap-2">
                                    <Label htmlFor="name" className="font-bold text-slate-700">Nama Lengkap & Gelar</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Contoh: Prof. Dr. Budi Santoso, M.Pd"
                                        className="focus:ring-blue-500 border-slate-200"
                                        required
                                    />
                                    <InputError message={errors.name} className="text-xs" />
                                </div>

                                {/* Jenis Kelamin */}
                                <div className="grid gap-2">
                                    <Label htmlFor="gender" className="font-bold text-slate-700">Jenis Kelamin</Label>
                                    <Select 
                                        value={data.gender} 
                                        onValueChange={(val) => setData('gender', val)}
                                    >
                                        <SelectTrigger className="bg-white border-slate-200">
                                            <SelectValue placeholder="Pilih Jenis Kelamin" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="L">Laki-laki</SelectItem>
                                            <SelectItem value="P">Perempuan</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.gender} className="text-xs" />
                                </div>

                                {/* Email */}
                                <div className="grid gap-2">
                                    <Label htmlFor="email" className="font-bold text-slate-700">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="email@example.com"
                                        className="focus:ring-blue-500 border-slate-200"
                                        required
                                    />
                                    <InputError message={errors.email} className="text-xs" />
                                </div>

                                {/* WhatsApp */}
                                <div className="grid gap-2">
                                    <Label htmlFor="wa" className="font-bold text-slate-700">WhatsApp</Label>
                                    <Input
                                        id="wa"
                                        type="tel"
                                        value={data.wa}
                                        onChange={(e) => setData('wa', e.target.value)}
                                        placeholder="0812xxxxxx"
                                        className="focus:ring-blue-500 border-slate-200"
                                        required
                                    />
                                    <InputError message={errors.wa} className="text-xs" />
                                </div>

                                {/* NIK */}
                                <div className="grid gap-2">
                                    <Label htmlFor="nik" className="font-bold text-slate-700">NIK (16 Digit)</Label>
                                    <Input
                                        id="nik"
                                        value={data.nik}
                                        onChange={(e) => setData('nik', e.target.value)}
                                        placeholder="16 Digit NIK"
                                        maxLength={16}
                                        className="focus:ring-blue-500 border-slate-200"
                                        required
                                    />
                                    <InputError message={errors.nik} className="text-xs" />
                                </div>

                                {/* Tempat Lahir */}
                                <div className="grid gap-2">
                                    <Label htmlFor="tempat_lahir" className="font-bold text-slate-700">Tempat Lahir</Label>
                                    <Input
                                        id="tempat_lahir"
                                        value={data.tempat_lahir}
                                        onChange={(e) => setData('tempat_lahir', e.target.value)}
                                        placeholder="Contoh: Medan"
                                        className="focus:ring-blue-500 border-slate-200"
                                        required
                                    />
                                    <InputError message={errors.tempat_lahir} className="text-xs" />
                                </div>

                                {/* Tanggal Lahir */}
                                <div className="grid gap-2">
                                    <Label htmlFor="tanggal_lahir" className="font-bold text-slate-700">Tanggal Lahir</Label>
                                    <Input
                                        id="tanggal_lahir"
                                        type="date"
                                        value={data.tanggal_lahir}
                                        onChange={(e) => setData('tanggal_lahir', e.target.value)}
                                        className="focus:ring-blue-500 border-slate-200"
                                        required
                                    />
                                    <InputError message={errors.tanggal_lahir} className="text-xs" />
                                </div>

                                {/* Domisili */}
                                <div className="grid gap-2">
                                    <Label htmlFor="domicile" className="font-bold text-slate-700">Domisili (Kota/Kab)</Label>
                                    <Input
                                        id="domicile"
                                        value={data.domicile}
                                        onChange={(e) => setData('domicile', e.target.value)}
                                        placeholder="Contoh: Jakarta Selatan"
                                        className="focus:ring-blue-500 border-slate-200"
                                    />
                                    <InputError message={errors.domicile} className="text-xs" />
                                </div>

                                {/* Pekerjaan */}
                                <div className="grid gap-2">
                                    <Label htmlFor="occupation" className="font-bold text-slate-700">Pekerjaan Saat Ini</Label>
                                    <Input
                                        id="occupation"
                                        value={data.occupation}
                                        onChange={(e) => setData('occupation', e.target.value)}
                                        placeholder="Contoh: Guru PNS, Entrepreneur"
                                        className="focus:ring-blue-500 border-slate-200"
                                    />
                                    <InputError message={errors.occupation} className="text-xs" />
                                </div>

                                {/* Alamat Lengkap */}
                                <div className="lg:col-span-3 grid gap-2">
                                    <Label htmlFor="alamat_lengkap" className="font-bold text-slate-700">Alamat Lengkap</Label>
                                    <Textarea
                                        id="alamat_lengkap"
                                        value={data.alamat_lengkap}
                                        onChange={(e) => setData('alamat_lengkap', e.target.value)}
                                        placeholder="Jl. Nama Jalan No. XX, RT/RW, Kelurahan, Kecamatan"
                                        className="focus:ring-blue-500 border-slate-200 min-h-[80px]"
                                        required
                                    />
                                    <InputError message={errors.alamat_lengkap} className="text-xs" />
                                </div>
                            </div>
                        </section>

                        {/* SEKSI 2: RIWAYAT PENDIDIKAN */}
                        <section>
                            <div className="flex items-center justify-between mb-6">
                                <div className="flex items-center gap-3">
                                    <div className="p-3 bg-teal-50 rounded-lg">
                                        <GraduationCap className="h-5 w-5 text-teal-600" />
                                    </div>
                                    <div>
                                        <h3 className="text-lg font-bold text-slate-900">Riwayat Pendidikan</h3>
                                        <p className="text-sm text-slate-500">Tambahkan riwayat pendidikan dari D1 hingga S3</p>
                                    </div>
                                </div>
                                <Button 
                                    type="button" 
                                    onClick={addEducation}
                                    className="bg-teal-600 hover:bg-teal-700 text-white"
                                >
                                    <Plus className="h-4 w-4 mr-2" />
                                    Tambah Pendidikan
                                </Button>
                            </div>

                            <div className="space-y-6">
                                {data.educations.length === 0 && (
                                    <div className="text-center p-8 bg-slate-50 rounded-xl border border-slate-200 border-dashed">
                                        <p className="text-slate-500">Belum ada data pendidikan ditambahkan.</p>
                                    </div>
                                )}

                                {data.educations.map((edu, index) => (
                                    <div key={index} className="bg-teal-50/30 p-6 rounded-2xl border border-teal-100 relative group">
                                        <div className="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                size="sm"
                                                onClick={() => removeEducation(index)}
                                                title="Hapus Data Ini"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </div>

                                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            {/* Level */}
                                            <div className="grid gap-2">
                                                <Label className="text-xs font-semibold text-slate-600">Jenjang</Label>
                                                <Select 
                                                    value={edu.level} 
                                                    onValueChange={(val) => updateEducation(index, 'level', val)}
                                                >
                                                    <SelectTrigger className="bg-white border-slate-200">
                                                        <SelectValue placeholder="Pilih Jenjang" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="D1">D1</SelectItem>
                                                        <SelectItem value="D2">D2</SelectItem>
                                                        <SelectItem value="D3">D3</SelectItem>
                                                        <SelectItem value="D4">D4</SelectItem>
                                                        <SelectItem value="S1">S1</SelectItem>
                                                        <SelectItem value="S2">S2</SelectItem>
                                                        <SelectItem value="S3">S3</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            {/* Universitas */}
                                            <div className="grid gap-2">
                                                <Label className="text-xs font-semibold text-slate-600">Universitas / Institusi</Label>
                                                <Input
                                                    value={edu.university}
                                                    onChange={(e) => updateEducation(index, 'university', e.target.value)}
                                                    placeholder="Nama Universitas"
                                                    className="bg-white border-slate-200"
                                                    required
                                                />
                                            </div>

                                            {/* Fakultas */}
                                            <div className="grid gap-2">
                                                <Label className="text-xs font-semibold text-slate-600">Fakultas (Opsional)</Label>
                                                <Input
                                                    value={edu.faculty}
                                                    onChange={(e) => updateEducation(index, 'faculty', e.target.value)}
                                                    placeholder="Nama Fakultas"
                                                    className="bg-white border-slate-200"
                                                />
                                            </div>

                                            {/* Prodi */}
                                            <div className="grid gap-2">
                                                <Label className="text-xs font-semibold text-slate-600">Program Studi / Jurusan</Label>
                                                <Input
                                                    value={edu.major}
                                                    onChange={(e) => updateEducation(index, 'major', e.target.value)}
                                                    placeholder="Nama Jurusan"
                                                    className="bg-white border-slate-200"
                                                    required
                                                />
                                            </div>

                                            {/* Tahun Masuk */}
                                            <div className="grid gap-2">
                                                <Label className="text-xs font-semibold text-slate-600">Tahun Masuk</Label>
                                                <Input
                                                    type="number"
                                                    value={edu.admission_year}
                                                    onChange={(e) => updateEducation(index, 'admission_year', e.target.value)}
                                                    placeholder="YYYY"
                                                    min="1950"
                                                    max={new Date().getFullYear()}
                                                    className="bg-white border-slate-200"
                                                />
                                            </div>

                                            {/* Tahun Lulus */}
                                            <div className="grid gap-2">
                                                <Label className="text-xs font-semibold text-slate-600">Tahun Lulus</Label>
                                                <Input
                                                    type="number"
                                                    value={edu.graduation_year}
                                                    onChange={(e) => updateEducation(index, 'graduation_year', e.target.value)}
                                                    placeholder="YYYY"
                                                    min="1950"
                                                    max={new Date().getFullYear()}
                                                    className="bg-white border-slate-200"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                ))}
                                <InputError message={errors.educations as any} className="text-xs" />
                            </div>
                        </section>

                        {/* TOMBOL SIMPAN */}
                        <div className="flex justify-end pt-6 border-t border-slate-200">
                            <Button 
                                type="submit" 
                                disabled={processing}
                                className="bg-[#00A69D] hover:bg-[#00897B] text-white px-8 h-12 text-lg shadow-lg shadow-teal-500/30"
                            >
                                {processing ? (
                                    <>
                                        <Spinner className="mr-2 h-5 w-5" />
                                        Menyimpan...
                                    </>
                                ) : (
                                    <>
                                        <Save className="mr-2 h-5 w-5" />
                                        Simpan Perubahan
                                    </>
                                )}
                            </Button>
                        </div>

                    </form>
                </div>
            </div>
        </DashboardLayout>
    );
}
