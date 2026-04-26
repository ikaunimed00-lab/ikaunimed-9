import { Head } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { MapPin, Phone, Mail, Clock } from 'lucide-react';

interface PageProps {
    page?: {
        title: string;
        content: string;
    };
}

export default function Contact({ page }: PageProps) {
    return (
        <MainLayout>
            <Head title={page?.title || "Hubungi Kami & Sekretariat"} />
            <div className="container mx-auto px-4 py-12">
                <h1 className="text-3xl font-bold mb-8 text-center">{page?.title || "Hubungi Kami"}</h1>
                
                {page && (
                    <div 
                        className="prose prose-lg max-w-none prose-headings:text-slate-800 prose-p:text-slate-600 mb-12 text-center mx-auto"
                        dangerouslySetInnerHTML={{ __html: page.content }} 
                    />
                )}

                <div className="grid grid-cols-1 md:grid-cols-2 gap-12">
                    {/* Informasi Sekretariat */}
                    <div>
                        <h2 className="text-2xl font-semibold mb-6">Sekretariat IKA UNIMED</h2>
                        <div className="space-y-6">
                            <div className="flex items-start gap-4">
                                <div className="bg-teal-100 p-3 rounded-lg">
                                    <MapPin className="h-6 w-6 text-teal-700" />
                                </div>
                                <div>
                                    <h3 className="font-semibold text-lg">Alamat</h3>
                                    <p className="text-slate-600">
                                        Gedung Ikatan Alumni UNIMED<br />
                                        Jl. Willem Iskandar Pasar V, Medan Estate<br />
                                        Sumatera Utara, Indonesia
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-start gap-4">
                                <div className="bg-teal-100 p-3 rounded-lg">
                                    <Phone className="h-6 w-6 text-teal-700" />
                                </div>
                                <div>
                                    <h3 className="font-semibold text-lg">Telepon / WhatsApp</h3>
                                    <p className="text-slate-600">+62 812-3456-7890</p>
                                </div>
                            </div>

                            <div className="flex items-start gap-4">
                                <div className="bg-teal-100 p-3 rounded-lg">
                                    <Mail className="h-6 w-6 text-teal-700" />
                                </div>
                                <div>
                                    <h3 className="font-semibold text-lg">Email</h3>
                                    <p className="text-slate-600">sekretariat@ikaunimed.or.id</p>
                                </div>
                            </div>

                            <div className="flex items-start gap-4">
                                <div className="bg-teal-100 p-3 rounded-lg">
                                    <Clock className="h-6 w-6 text-teal-700" />
                                </div>
                                <div>
                                    <h3 className="font-semibold text-lg">Jam Operasional</h3>
                                    <p className="text-slate-600">
                                        Senin - Jumat: 08.00 - 16.00 WIB<br />
                                        Sabtu - Minggu: Tutup
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Form Kontak */}
                    <div className="bg-white p-6 rounded-2xl shadow-lg border border-slate-100">
                        <h2 className="text-2xl font-semibold mb-6">Kirim Pesan</h2>
                        <form className="space-y-4">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nama Lengkap</Label>
                                <Input id="name" placeholder="Masukkan nama lengkap" />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input id="email" type="email" placeholder="email@contoh.com" />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="subject">Subjek</Label>
                                <Input id="subject" placeholder="Masukkan subjek pesan" />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="message">Pesan</Label>
                                <Textarea id="message" placeholder="Tuliskan pesan secara ringkas" className="min-h-[120px]" />
                            </div>
                            <Button className="w-full bg-[#00A69D] hover:bg-[#00897B]">Kirim Pesan</Button>
                        </form>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
