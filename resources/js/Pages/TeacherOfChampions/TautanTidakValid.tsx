import { Head, Link } from "@inertiajs/react";
import { AlertTriangle } from "lucide-react";
import MainLayout from "@/components/MainLayout";

type Props = {
  settings: Record<string, any>;
  footerContent?: unknown;
  cekStatusUrl: string;
};

const TautanTidakValid = ({ settings, footerContent, cekStatusUrl }: Props) => {
  return (
    <>
      <Head>
        <title>Tautan tidak valid - Teacher of Champions</title>
      </Head>

      <MainLayout variant="full" settings={settings} footerContent={footerContent}>
        <section className="bg-gradient-to-b from-[#0B3D2E] from-0% via-[#0B3D2E] via-40% to-gray-50 to-40% pb-28 pt-14">
          <div className="container max-w-xl">
            <div className="rounded-3xl border border-gray-200 border-t-4 border-t-[#FFD700] bg-white p-8 text-center shadow-xl">
              <AlertTriangle className="mx-auto h-12 w-12 text-[#B45309]" />
              <h1 className="mt-4 text-2xl font-extrabold text-[#0B3D2E]">Tautan tidak valid atau kedaluwarsa</h1>
              <p className="mt-3 text-sm text-gray-600">
                Halaman bukti/sukses ini sudah tidak dapat dibuka. Anda tetap bisa melihat nomor pendaftaran melalui Cek
                Status dengan WhatsApp dan email yang digunakan saat mendaftar.
              </p>
              <div className="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <Link
                  href={cekStatusUrl}
                  className="inline-block rounded-xl bg-[#FFD700] px-6 py-2.5 text-sm font-bold text-[#0B3D2E] hover:bg-[#FFC400]"
                >
                  Buka Cek Status
                </Link>
                <Link
                  href="/toc"
                  className="inline-block rounded-xl border border-[#0B3D2E]/20 px-6 py-2.5 text-sm font-bold text-[#0B3D2E] hover:bg-[#E7F5E9]"
                >
                  Kembali ke Teacher of Champions
                </Link>
              </div>
            </div>
          </div>
        </section>
      </MainLayout>
    </>
  );
};

export default TautanTidakValid;
