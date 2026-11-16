import { Form, Head, Link, usePage } from "@inertiajs/react";
import { Search } from "lucide-react";
import MainLayout from "@/components/MainLayout";

type StatusResult = {
  nomorPendaftaran: string;
  namaMasked: string;
  status: string;
  statusLabel: string;
  tanggalDaftar: string;
  buktiUrl: string;
};

type PageProps = {
  settings: Record<string, any>;
  footerContent?: unknown;
  guidebookUrl: string | null;
  result: StatusResult | null;
  genericError: string | null;
  errors?: Record<string, string>;
};

const CekStatus = () => {
  const { settings, footerContent, result, genericError, errors = {} } = usePage<PageProps>().props;

  return (
    <>
      <Head>
        <title>Cek Status Pendaftaran - Teacher of Champions</title>
      </Head>

      <MainLayout variant="full" settings={settings} footerContent={footerContent}>
        <section className="bg-gradient-to-b from-[#0B3D2E] from-0% via-[#0B3D2E] via-35% to-gray-50 to-35% pb-28 pt-14">
          <div className="container max-w-xl">
            <div className="rounded-3xl border border-gray-200 border-t-4 border-t-[#FFD700] bg-white p-8 shadow-xl">
              <div className="flex items-center gap-3">
                <span className="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-[#E7F5E9] text-[#006837]">
                  <Search className="h-5 w-5" aria-hidden="true" />
                </span>
                <div>
                  <h1 className="text-2xl font-extrabold text-[#0B3D2E]">Cek Status Pendaftaran</h1>
                  <p className="text-sm text-gray-600">Isi WhatsApp dan email yang sama saat mendaftar.</p>
                </div>
              </div>

              <Form action="/toc/cek-status" method="post" className="mt-6 space-y-4">
                {({ processing }) => (
                  <>
                    <input
                      type="text"
                      name="website"
                      tabIndex={-1}
                      autoComplete="off"
                      aria-hidden="true"
                      className="absolute left-[-10000px] h-0 w-0 opacity-0"
                    />

                    <div>
                      <label htmlFor="whatsapp" className="mb-1.5 block text-sm font-semibold text-[#0B3D2E]">
                        Nomor WhatsApp
                      </label>
                      <input
                        id="whatsapp"
                        name="whatsapp"
                        type="tel"
                        required
                        placeholder="081234567890"
                        className="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-[#006837] focus:outline-none focus:ring-2 focus:ring-[#006837]/20"
                      />
                      {errors.whatsapp && <p className="mt-1.5 text-xs font-medium text-red-600">{errors.whatsapp}</p>}
                    </div>

                    <div>
                      <label htmlFor="email" className="mb-1.5 block text-sm font-semibold text-[#0B3D2E]">
                        Email
                      </label>
                      <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        placeholder="nama@email.com"
                        className="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-[#006837] focus:outline-none focus:ring-2 focus:ring-[#006837]/20"
                      />
                      {errors.email && <p className="mt-1.5 text-xs font-medium text-red-600">{errors.email}</p>}
                    </div>

                    {genericError && (
                      <p className="rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{genericError}</p>
                    )}

                    <button
                      type="submit"
                      disabled={processing}
                      className="w-full rounded-xl bg-[#FFD700] px-6 py-3 text-sm font-bold text-[#0B3D2E] hover:bg-[#FFC400] disabled:opacity-60"
                    >
                      {processing ? "Memeriksa..." : "Cek status"}
                    </button>
                  </>
                )}
              </Form>

              {result && (
                <div className="mt-8 rounded-2xl border border-[#006837]/20 bg-[#F2FAF4] p-5">
                  <p className="text-xs font-bold uppercase tracking-wider text-[#006837]">Hasil pencarian</p>
                  <p className="mt-3 font-mono text-2xl font-extrabold tracking-wider text-[#0B3D2E]">
                    {result.nomorPendaftaran}
                  </p>
                  <dl className="mt-4 grid gap-2 text-sm">
                    <div className="flex justify-between gap-4">
                      <dt className="text-gray-600">Nama</dt>
                      <dd className="font-semibold text-[#0B3D2E]">{result.namaMasked}</dd>
                    </div>
                    <div className="flex justify-between gap-4">
                      <dt className="text-gray-600">Status</dt>
                      <dd className="font-semibold text-[#0B3D2E]">{result.statusLabel}</dd>
                    </div>
                    <div className="flex justify-between gap-4">
                      <dt className="text-gray-600">Tanggal daftar</dt>
                      <dd className="font-semibold text-[#0B3D2E]">{result.tanggalDaftar}</dd>
                    </div>
                  </dl>
                  <a
                    href={result.buktiUrl}
                    target="_blank"
                    rel="noreferrer"
                    className="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-[#0B3D2E] px-5 py-2.5 text-sm font-bold text-[#FFD700] hover:bg-[#006837]"
                  >
                    Simpan / Cetak Bukti Pendaftaran
                  </a>
                </div>
              )}

              <p className="mt-6 text-center text-sm text-gray-600">
                Belum mendaftar?{" "}
                <Link href="/teacher-of-champions/daftar" className="font-semibold text-[#006837] hover:underline">
                  Buka formulir pendaftaran
                </Link>
              </p>
            </div>
          </div>
        </section>
      </MainLayout>
    </>
  );
};

export default CekStatus;
