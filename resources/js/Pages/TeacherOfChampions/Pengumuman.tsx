import { Form, Head, Link, usePage } from "@inertiajs/react";
import { AlertCircle, CheckCircle2, Clock3, MessageCircle } from "lucide-react";
import MainLayout from "@/components/MainLayout";
import FloatingCTA from "@/components/FloatingCTA";

type Contact = { name: string; role: string; whatsapp: string };

type JadwalItem = { label: string; tanggal: string; info: string };

type Result = {
  nama: string;
  status: "lulus" | "tidak_lulus" | "belum_tersedia";
  linkGrupWa?: string;
  jadwal?: { tmFinal: JadwalItem; final: JadwalItem };
  batasUnggahLabel?: string;
  berkasUrl?: string;
};

type PageProps = {
  settings: Record<string, any>;
  footerContent?: unknown;
  contacts: Contact[];
  pengumumanDibuka: boolean;
  helpText: string;
  result: Result | null;
  errors: Record<string, string>;
};

const Pengumuman = ({
  settings,
  footerContent,
  contacts,
  pengumumanDibuka,
  helpText,
  result,
}: PageProps) => {
  const { errors } = usePage<PageProps>().props;
  const helpdesk = contacts[0];

  return (
    <>
      <Head>
        <title>Pengumuman Penyisihan - Teacher of Champions</title>
        <meta name="robots" content="noindex, nofollow" />
      </Head>

      <MainLayout variant="full" settings={settings} footerContent={footerContent}>
        <section className="bg-gradient-to-b from-[#0B3D2E] from-0% via-[#0B3D2E] via-40% to-gray-50 to-40% pb-28 pt-14 md:pb-32">
          <div className="container max-w-xl">
            <div className="rounded-3xl border border-gray-200 border-t-4 border-t-[#FFD700] bg-white p-8 shadow-xl">
              <p className="text-xs font-semibold uppercase tracking-wider text-[#006837]">
                Teacher of Champions 2026
              </p>
              <h1 className="mt-2 text-2xl font-extrabold text-[#0B3D2E]">Pengumuman Babak Penyisihan</h1>

              {!pengumumanDibuka && (
                <div className="mt-6 space-y-4">
                  <div className="rounded-2xl bg-amber-50 px-4 py-4 text-sm text-amber-900">
                    <div className="flex gap-3">
                      <Clock3 className="mt-0.5 h-5 w-5 shrink-0" />
                      <div>
                        <p className="font-semibold">Pengumuman belum dibuka</p>
                        <p className="mt-1">
                          Hasil Babak Penyisihan belum dapat ditampilkan. Silakan kembali lagi nanti
                          atau hubungi helpdesk bila memerlukan bantuan.
                        </p>
                        {helpdesk?.whatsapp && (
                          <a
                            href={`https://wa.me/${helpdesk.whatsapp}`}
                            target="_blank"
                            rel="noreferrer"
                            className="mt-3 inline-flex items-center gap-2 font-semibold text-[#006837]"
                          >
                            <MessageCircle className="h-4 w-4" />
                            Hubungi {helpdesk.name}
                          </a>
                        )}
                      </div>
                    </div>
                  </div>
                  <a href="/toc" className="inline-block text-sm font-semibold text-[#006837] hover:underline">
                    Kembali ke halaman Teacher of Champions
                  </a>
                </div>
              )}

              {pengumumanDibuka && !result && (
                <Form action="/toc/pengumuman" method="post" className="mt-6 space-y-4">
                  {({ processing }) => (
                    <>
                      <div>
                        <label htmlFor="nomor_pendaftaran" className="block text-sm font-semibold text-gray-700">
                          Nomor pendaftaran
                        </label>
                        <input
                          id="nomor_pendaftaran"
                          name="nomor_pendaftaran"
                          type="text"
                          required
                          placeholder="TOC-2026-0071"
                          className="mt-1 w-full rounded-xl border border-gray-300 px-4 py-2.5 font-mono text-sm focus:border-[#006837] focus:outline-none focus:ring-2 focus:ring-[#006837]/30"
                        />
                        <p className="mt-2 text-xs text-gray-500">{helpText}</p>
                      </div>
                      {errors.credentials && (
                        <p className="rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700">{errors.credentials}</p>
                      )}
                      {errors.nomor_pendaftaran && (
                        <p className="rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700">
                          {errors.nomor_pendaftaran}
                        </p>
                      )}
                      <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-xl bg-[#FFD700] px-6 py-2.5 text-sm font-bold text-[#0B3D2E] hover:bg-[#FFC400] disabled:opacity-60"
                      >
                        {processing ? "Memeriksa..." : "Cek hasil"}
                      </button>
                    </>
                  )}
                </Form>
              )}

              {pengumumanDibuka && result?.status === "lulus" && (
                <div className="mt-6 space-y-4 text-left">
                  <div className="flex gap-3 rounded-2xl bg-emerald-50 px-4 py-4 text-emerald-900">
                    <CheckCircle2 className="mt-0.5 h-6 w-6 shrink-0" />
                    <p className="text-sm leading-relaxed">
                      Selamat! Bapak/Ibu <span className="font-bold">{result.nama}</span> dinyatakan
                      lolos Babak Penyisihan dan berhak melanjutkan kompetisi ke tahap Final.
                    </p>
                  </div>

                  {result.linkGrupWa && (
                    <div>
                      <a
                        href={result.linkGrupWa}
                        target="_blank"
                        rel="noreferrer"
                        className="flex w-full items-center justify-center gap-2 rounded-xl bg-[#25D366] px-6 py-3 text-sm font-bold text-white hover:bg-[#1ebe5d]"
                      >
                        <MessageCircle className="h-4 w-4" />
                        Gabung grup WhatsApp finalis
                      </a>
                      <p className="mt-2 text-center text-xs font-semibold text-red-700">
                        Jangan membagikan link ini kepada pihak lain.
                      </p>
                    </div>
                  )}

                  <div className="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 text-sm text-gray-700">
                    <p className="font-semibold text-[#0B3D2E]">Informasi penting</p>
                    <ul className="mt-2 list-disc space-y-1 pl-5">
                      <li>
                        {result.jadwal?.tmFinal.label}: {result.jadwal?.tmFinal.tanggal}
                        {result.jadwal?.tmFinal.info ? ` — ${result.jadwal.tmFinal.info}` : ""}
                      </li>
                      <li>
                        {result.jadwal?.final.label}: {result.jadwal?.final.tanggal}
                        {result.jadwal?.final.info ? ` — ${result.jadwal.final.info}` : ""}
                      </li>
                      <li>Batas unggah berkas: {result.batasUnggahLabel}</li>
                    </ul>
                  </div>

                  {result.berkasUrl && (
                    <Link
                      href={result.berkasUrl}
                      className="inline-flex w-full items-center justify-center rounded-xl bg-[#0B3D2E] px-6 py-3 text-sm font-bold text-white hover:bg-[#06281e]"
                    >
                      Unggah berkas
                    </Link>
                  )}
                </div>
              )}

              {pengumumanDibuka && result?.status === "tidak_lulus" && (
                <div className="mt-6 flex gap-3 rounded-2xl bg-gray-50 px-4 py-4 text-sm text-gray-700">
                  <AlertCircle className="mt-0.5 h-6 w-6 shrink-0 text-gray-500" />
                  <p className="leading-relaxed">
                    Terima kasih Bapak/Ibu <span className="font-bold">{result.nama}</span> telah
                    berpartisipasi dalam Teacher of Champions 2026. Mohon maaf, Anda belum lolos ke
                    tahap Final. Tetap semangat dan jangan pernah berhenti menginspirasi, karena guru
                    hebat adalah inspirasi bagi generasi hebat.
                  </p>
                </div>
              )}

              {pengumumanDibuka && result?.status === "belum_tersedia" && (
                <div className="mt-6 rounded-2xl bg-amber-50 px-4 py-4 text-sm text-amber-900">
                  <p className="font-semibold">Hasil Anda belum tersedia</p>
                  <p className="mt-1">
                    Silakan hubungi admin
                    {helpdesk?.whatsapp ? (
                      <>
                        {" "}
                        melalui WhatsApp{" "}
                        <a
                          href={`https://wa.me/${helpdesk.whatsapp}`}
                          target="_blank"
                          rel="noreferrer"
                          className="font-semibold underline"
                        >
                          {helpdesk.name}
                        </a>
                      </>
                    ) : (
                      "."
                    )}
                  </p>
                </div>
              )}

              {pengumumanDibuka && result && (
                <a href="/toc" className="mt-6 inline-block text-sm font-semibold text-[#006837] hover:underline">
                  Kembali ke halaman Teacher of Champions
                </a>
              )}
            </div>
          </div>
        </section>

        <FloatingCTA settings={settings} hideBubble />
      </MainLayout>
    </>
  );
};

export default Pengumuman;
