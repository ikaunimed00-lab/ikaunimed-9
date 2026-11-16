import { Form, Head, usePage } from "@inertiajs/react";
import { Clock3, MessageCircle } from "lucide-react";
import MainLayout from "@/components/MainLayout";
import FloatingCTA from "@/components/FloatingCTA";

type Contact = { name: string; role: string; whatsapp: string };

type PageProps = {
  settings: Record<string, any>;
  footerContent?: unknown;
  contacts: Contact[];
  pengumumanDibuka: boolean;
  helpText: string;
  errors: Record<string, string>;
};

const BerkasLogin = ({ settings, footerContent, contacts, pengumumanDibuka, helpText }: PageProps) => {
  const { errors } = usePage<PageProps>().props;
  const helpdesk = contacts[0];

  return (
    <>
      <Head>
        <title>Unggah Berkas Finalis - Teacher of Champions</title>
        <meta name="robots" content="noindex, nofollow" />
      </Head>

      <MainLayout variant="full" settings={settings} footerContent={footerContent}>
        <section className="bg-gradient-to-b from-[#0B3D2E] from-0% via-[#0B3D2E] via-40% to-gray-50 to-40% pb-28 pt-14 md:pb-32">
          <div className="container max-w-xl">
            <div className="rounded-3xl border border-gray-200 border-t-4 border-t-[#FFD700] bg-white p-8 shadow-xl">
              <p className="text-xs font-semibold uppercase tracking-wider text-[#006837]">
                Teacher of Champions 2026
              </p>
              <h1 className="mt-2 text-2xl font-extrabold text-[#0B3D2E]">Unggah Berkas Finalis</h1>
              <p className="mt-2 text-sm text-gray-600">Masuk dengan nomor pendaftaran Anda.</p>

              {!pengumumanDibuka ? (
                <div className="mt-6 rounded-2xl bg-amber-50 px-4 py-4 text-sm text-amber-900">
                  <div className="flex gap-3">
                    <Clock3 className="mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                      <p className="font-semibold">Pengumuman belum dibuka</p>
                      <p className="mt-1">
                        Unggah berkas belum tersedia. Silakan cek kembali setelah pengumuman
                        dibuka.
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
              ) : (
                <Form action="/toc/berkas" method="post" className="mt-6 space-y-4">
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
                        {processing ? "Memeriksa..." : "Masuk"}
                      </button>
                    </>
                  )}
                </Form>
              )}
            </div>
          </div>
        </section>

        <FloatingCTA settings={settings} hideBubble />
      </MainLayout>
    </>
  );
};

export default BerkasLogin;
