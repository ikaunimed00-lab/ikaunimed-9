import { Head } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';

interface PageProps {
    page?: {
        title: string;
        content: string;
    };
}

export default function Terms({ page }: PageProps) {
    return (
        <MainLayout>
            <Head title={page?.title || "Syarat & Ketentuan"} />
            <div className="container mx-auto px-4 py-12">
                <div className="max-w-4xl mx-auto">
                    <h1 className="text-3xl font-bold mb-6 text-slate-800">{page?.title || "Syarat & Ketentuan"}</h1>
                    {page ? (
                        <div 
                            className="prose prose-lg max-w-none prose-headings:text-slate-800 prose-p:text-slate-600"
                            dangerouslySetInnerHTML={{ __html: page.content }} 
                        />
                    ) : (
                        <div className="text-center py-12 text-gray-500">
                            Cek kembali dalam beberapa saat.
                        </div>
                    )}
                </div>
            </div>
        </MainLayout>
    );
}
