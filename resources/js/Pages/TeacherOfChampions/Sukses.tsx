import { Head, Link } from "@inertiajs/react";
import { CheckCircle2, Printer } from "lucide-react";
import MainLayout from "@/components/MainLayout";
import GuidebookLink from "@/components/toc/GuidebookLink";

type SuksesPageProps = {
  settings: Record<string, any>;
  footerContent?: unknown;
  nomorPendaftaran: string;
  nama?: string | null;
  buktiUrl?: string | null;
  cekStatusUrl?: string | null;
  guidebookUrl: string | null;
};

const Sukses = ({
  settings,
  footerContent,
  nomorPendaftaran,
  nama,
  buktiUrl,
  cekStatusUrl,
  guidebookUrl,
}: SuksesPageProps) => {
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
              {nama && <p className="mt-2 text-sm text-gray-600">Terima kasih, {nama}.</p>}
              <p className="mt-2 text-gray-600">Simpan nomor pendaftaran Anda:</p>
              <p className="mt-4 rounded-2xl bg-[#0B3D2E] px-4 py-4 font-mono text-2xl font-extrabold tracking-widest text-[#FFD700] sm:text-3xl">
                {nomorPendaftaran}
              </p>
              <p className="mt-4 text-sm text-gray-600">
                Pengumuman peserta terpilih: 1 November 2026. Pantau Instagram resmi IKA UNIMED untuk informasi tahapan
                berikutnya.
              </p>
              <div className="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row sm:flex-wrap">
                {buktiUrl && (
                  <a
                    href={buktiUrl}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center gap-2 rounded-xl bg-[#0B3D2E] px-6 py-2.5 text-sm font-bold text-[#FFD700] hover:bg-[#006837]"
                  >
                    <Printer className="h-4 w-4" aria-hidden="true" />
                    Simpan / Cetak Bukti Pendaftaran
                  </a>
                )}
                <Link
                  href="/reuni-akbar-2026#teacher-of-champions"
                  className="inline-block rounded-xl bg-[#FFD700] px-6 py-2.5 text-sm font-bold text-[#0B3D2E] hover:bg-[#FFC400]"
                >
                  Lihat timeline kompetisi
                </Link>
                {cekStatusUrl && (
                  <Link
                    href={cekStatusUrl}
                    className="inline-block rounded-xl border border-[#0B3D2E]/20 px-6 py-2.5 text-sm font-bold text-[#0B3D2E] hover:bg-[#E7F5E9]"
                  >
                    Cek Status nanti
                  </Link>
                )}
                {guidebookUrl && <GuidebookLink href={guidebookUrl} />}
              </div>
            </div>
          </div>
        </section>
      </MainLayout>
    </>
  );
};

export default Sukses;
