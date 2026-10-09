import { Head, Link } from "@inertiajs/react";
import { CheckCircle2 } from "lucide-react";
import MainLayout from "@/components/MainLayout";
import FloatingCTA from "@/components/FloatingCTA";

type SuksesPageProps = {
  settings: Record<string, any>;
  footerContent?: unknown;
  nomorPendaftaran: string;
};

const Sukses = ({ settings, footerContent, nomorPendaftaran }: SuksesPageProps) => {
  return (
    <>
      <Head>
        <title>Pendaftaran berhasil - Teacher of Champions</title>
      </Head>

      <MainLayout variant="full" settings={settings} footerContent={footerContent}>
        <section className="bg-gradient-to-b from-[#0B3D2E] from-0% via-[#0B3D2E] via-40% to-gray-50 to-40% pb-28 pt-14 md:pb-32">
          <div className="container max-w-xl">
            <div className="rounded-3xl border border-gray-200 border-t-4 border-t-[#FFD700] bg-white p-8 text-center shadow-xl">
              <CheckCircle2 className="mx-auto h-12 w-12 text-[#006837]" />
              <h1 className="mt-4 text-2xl font-extrabold text-[#0B3D2E]">Pendaftaran berhasil</h1>
              <p className="mt-2 text-gray-600">Simpan nomor pendaftaran Anda:</p>
              <p className="mt-4 rounded-2xl bg-[#0B3D2E] px-4 py-4 font-mono text-2xl font-extrabold tracking-widest text-[#FFD700] sm:text-3xl">
                {nomorPendaftaran}
              </p>
              <p className="mt-4 text-sm text-gray-600">
                Pengumuman peserta terpilih: 30 Oktober 2026. Pantau Instagram resmi IKA UNIMED untuk informasi tahapan berikutnya.
              </p>
              <Link
                href="/reuni-akbar-2026#teacher-of-champions"
                className="mt-6 inline-block rounded-xl bg-[#FFD700] px-6 py-2.5 text-sm font-bold text-[#0B3D2E] hover:bg-[#FFC400]"
              >
                Lihat timeline kompetisi
              </Link>
            </div>
          </div>
        </section>

        <FloatingCTA settings={settings} hideBubble />
      </MainLayout>
    </>
  );
};

export default Sukses;
