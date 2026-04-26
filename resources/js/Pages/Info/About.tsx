import { Head } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';

interface PageProps {
    page?: {
        title: string;
        content: string;
    };
    vision?: {
        title: string;
        content: string;
    };
}

export default function About({ page, vision }: PageProps) {
    return (
        <MainLayout>
            <Head title={page?.title || "Tentang IKA UNIMED"} />
            <div className="container mx-auto px-4 py-12">
                <div className="max-w-4xl mx-auto">
                    {page && (
                        <div className="mb-12">
                            <h1 className="text-3xl font-bold mb-6 text-slate-800">{page.title}</h1>
                            <div 
                                className="prose prose-lg max-w-none prose-headings:text-slate-800 prose-p:text-slate-600"
                                dangerouslySetInnerHTML={{ __html: page.content }} 
                            />
                        </div>
                    )}

                    {vision && (
                        <div className="bg-slate-50 p-8 rounded-2xl border border-slate-100">
                            <h2 className="text-2xl font-bold mb-6 text-slate-800">{vision.title}</h2>
                            <div 
                                className="prose prose-lg max-w-none prose-headings:text-slate-800 prose-p:text-slate-600"
                                dangerouslySetInnerHTML={{ __html: vision.content }} 
                            />
                        </div>
                    )}

                    {!page && !vision && (
                        <div className="text-center py-12 text-gray-500">
                            Cek kembali dalam beberapa saat.
                        </div>
                    )}
                </div>
            </div>
        </MainLayout>
    );
}
