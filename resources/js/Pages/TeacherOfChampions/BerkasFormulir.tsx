import { Form, Head, router, usePage } from "@inertiajs/react";
import { Download, LogOut, MessageCircle } from "lucide-react";
import { useEffect, useState } from "react";
import MainLayout from "@/components/MainLayout";
import FloatingCTA from "@/components/FloatingCTA";

type BerkasItem = {
  jenis: string;
  label: string;
  maxKb: number;
  extensions: string[];
  minWidth?: number | null;
  minHeight?: number | null;
  status?: string | null;
  statusLabel: string;
  catatan?: string | null;
  namaAsli?: string | null;
  uploadedAt?: string | null;
  bolehUnggah: boolean;
  butuhPersetujuan: boolean;
};

type TemplateItem = { key: string; label: string; url: string };
type Contact = { name: string; role: string; whatsapp: string };

type PageProps = {
  settings: Record<string, any>;
  footerContent?: unknown;
  contacts: Contact[];
  peserta: {
    nama: string;
    nomorPendaftaran: string;
    setujuPublikasi: boolean;
  };
  batasUnggah: string;
  batasUnggahLabel: string;
  terkunci: boolean;
  berkas: BerkasItem[];
  templates: TemplateItem[];
  errors: Record<string, string>;
  flash?: { success?: string };
};

function countdownLabel(targetIso: string): string {
  const target = new Date(targetIso).getTime();
  const diff = target - Date.now();
  if (diff <= 0) {
    return "Batas waktu telah berakhir";
  }
  const totalSeconds = Math.floor(diff / 1000);
  const days = Math.floor(totalSeconds / 86400);
  const hours = Math.floor((totalSeconds % 86400) / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;
  return `${days}h ${hours}j ${minutes}m ${seconds}d`;
}

const BerkasFormulir = ({
  settings,
  footerContent,
  contacts,
  peserta,
  batasUnggah,
  batasUnggahLabel,
  terkunci,
  berkas,
  templates,
}: PageProps) => {
  const page = usePage<PageProps>().props;
  const [countdown, setCountdown] = useState(() => countdownLabel(batasUnggah));
  const helpdesk = contacts?.[0];

  useEffect(() => {
    const id = window.setInterval(() => setCountdown(countdownLabel(batasUnggah)), 1000);
    return () => window.clearInterval(id);
  }, [batasUnggah]);

  return (
    <>
      <Head>
        <title>Formulir Unggah Berkas - Teacher of Champions</title>
        <meta name="robots" content="noindex, nofollow" />
      </Head>

      <MainLayout variant="full" settings={settings} footerContent={footerContent}>
        <section className="bg-gradient-to-b from-[#0B3D2E] from-0% via-[#0B3D2E] via-35% to-gray-50 to-35% pb-28 pt-14 md:pb-32">
          <div className="container max-w-3xl space-y-6">
            <div className="rounded-3xl border border-gray-200 border-t-4 border-t-[#FFD700] bg-white p-8 shadow-xl">
              <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <p className="text-xs font-semibold uppercase tracking-wider text-[#006837]">
                    Finalis Teacher of Champions 2026
                  </p>
                  <h1 className="mt-2 text-2xl font-extrabold text-[#0B3D2E]">Halo, {peserta.nama}</h1>
                  <p className="mt-1 font-mono text-sm text-gray-600">{peserta.nomorPendaftaran}</p>
                </div>
                <button
                  type="button"
                  onClick={() => router.post("/toc/berkas/keluar")}
                  className="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                  <LogOut className="h-4 w-4" />
                  Keluar
                </button>
              </div>

              <div className="mt-6 grid gap-3 sm:grid-cols-2">
                <div className="rounded-2xl bg-[#0B3D2E] px-4 py-3 text-white">
                  <p className="text-xs uppercase tracking-wide text-[#FFD700]">Batas unggah</p>
                  <p className="mt-1 text-sm font-semibold">{batasUnggahLabel}</p>
                </div>
                <div className="rounded-2xl bg-gray-50 px-4 py-3">
                  <p className="text-xs uppercase tracking-wide text-gray-500">Hitung mundur</p>
                  <p className="mt-1 font-mono text-sm font-semibold text-[#0B3D2E]">{countdown}</p>
                </div>
              </div>

              {terkunci && (
                <div className="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-950">
                  <p className="font-semibold">Unggah berkas telah ditutup</p>
                  <p className="mt-1">
                    Batas waktu unggah sudah lewat ({batasUnggahLabel}). Anda masih dapat melihat status
                    berkas, tetapi unggahan baru tidak diterima.
                  </p>
                  {helpdesk?.whatsapp && (
                    <a
                      href={`https://wa.me/${helpdesk.whatsapp.replace(/\D/g, "")}`}
                      target="_blank"
                      rel="noreferrer"
                      className="mt-3 inline-flex items-center gap-2 font-semibold text-[#006837]"
                    >
                      <MessageCircle className="h-4 w-4" />
                      Hubungi panitia {helpdesk.name}
                    </a>
                  )}
                </div>
              )}

              {page.flash?.success && (
                <p className="mt-4 rounded-xl bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                  {page.flash.success}
                </p>
              )}
            </div>

            <div className="rounded-3xl border border-gray-200 bg-white p-8 shadow-sm">
              <h2 className="text-lg font-bold text-[#0B3D2E]">Unduh template</h2>
              <p className="mt-1 text-sm text-gray-600">
                Unduh, isi/tandatangani, lalu unggah kembali pada formulir di bawah.
              </p>
              <div className="mt-4 grid gap-3 sm:grid-cols-2">
                {templates.map((tpl) => (
                  <a
                    key={tpl.key}
                    href={tpl.url}
                    className="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-[#0B3D2E] hover:bg-gray-50"
                  >
                    <Download className="h-4 w-4" />
                    {tpl.label}
                  </a>
                ))}
              </div>
            </div>

            <div className="space-y-4">
              {berkas.map((item) => (
                <div key={item.jenis} className="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <h3 className="text-base font-bold text-[#0B3D2E]">{item.label}</h3>
                      <p className="mt-1 text-xs text-gray-500">
                        Format: {item.extensions.join(", ").toUpperCase()} · Maks{" "}
                        {(item.maxKb / 1024).toFixed(0)} MB
                        {item.minWidth && item.minHeight
                          ? ` · Min ${item.minWidth}×${item.minHeight} px`
                          : ""}
                      </p>
                    </div>
                    <span
                      className={`rounded-full px-3 py-1 text-xs font-semibold ${
                        item.status === "diterima"
                          ? "bg-emerald-100 text-emerald-800"
                          : item.status === "ditolak"
                            ? "bg-red-100 text-red-800"
                            : item.status === "menunggu"
                              ? "bg-amber-100 text-amber-800"
                              : "bg-gray-100 text-gray-700"
                      }`}
                    >
                      {item.statusLabel}
                    </span>
                  </div>

                  {item.namaAsli && (
                    <p className="mt-3 text-sm text-gray-600">
                      File: {item.namaAsli}
                      {item.uploadedAt ? ` · ${item.uploadedAt} WIB` : ""}
                    </p>
                  )}

                  {item.status === "ditolak" && item.catatan && (
                    <p className="mt-2 rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700">
                      Catatan panitia: {item.catatan}
                    </p>
                  )}

                  {item.status === "diterima" && (
                    <p className="mt-4 text-sm text-emerald-800">
                      Berkas ini sudah diterima dan tidak dapat diganti. Hubungi admin bila perlu
                      revisi.
                    </p>
                  )}

                  {item.bolehUnggah ? (
                    <Form
                      action={`/toc/berkas/unggah/${item.jenis}`}
                      method="post"
                      encType="multipart/form-data"
                      className="mt-4 space-y-3"
                      resetOnSuccess
                    >
                      {({ processing, errors }) => (
                        <>
                          <input
                            type="file"
                            name="file"
                            required
                            accept={item.extensions.map((ext) => `.${ext}`).join(",")}
                            className="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-[#0B3D2E] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white"
                          />
                          {item.butuhPersetujuan && (
                            <label className="flex items-start gap-3 text-sm text-gray-700">
                              <input
                                type="checkbox"
                                name="setuju_publikasi"
                                value="1"
                                required
                                defaultChecked={peserta.setujuPublikasi}
                                className="mt-1"
                              />
                              <span>
                                Saya menyetujui nama dan foto saya dipublikasikan oleh panitia di
                                website dan media sosial IKA UNIMED terkait Teacher of Champions
                                2026.
                              </span>
                            </label>
                          )}
                          {(errors.file || errors.setuju_publikasi || page.errors.file) && (
                            <p className="rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700">
                              {errors.file || errors.setuju_publikasi || page.errors.file}
                            </p>
                          )}
                          <button
                            type="submit"
                            disabled={processing}
                            className="rounded-xl bg-[#FFD700] px-5 py-2.5 text-sm font-bold text-[#0B3D2E] hover:bg-[#FFC400] disabled:opacity-60"
                          >
                            {processing ? "Mengunggah..." : item.namaAsli ? "Ganti berkas" : "Unggah"}
                          </button>
                        </>
                      )}
                    </Form>
                  ) : (
                    !item.status || item.status !== "diterima" ? (
                      <p className="mt-4 text-sm text-gray-500">
                        {terkunci
                          ? "Unggah ditutup. Hubungi panitia bila Anda memerlukan bantuan."
                          : null}
                      </p>
                    ) : null
                  )}
                </div>
              ))}
            </div>
          </div>
        </section>

        <FloatingCTA settings={settings} hideBubble />
      </MainLayout>
    </>
  );
};

export default BerkasFormulir;
