import type { FormEvent, ReactNode } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import { CalendarDays, Lock } from "lucide-react";
import MainLayout from "@/components/MainLayout";
import MiniTimeline from "@/components/home/MiniTimeline";
import type { PortalEvent } from "@/components/home/types";
import GuidebookLink from "@/components/toc/GuidebookLink";

type Wilayah = {
  kode: string;
  nama: string;
  kabupaten_kota: string[];
};

type DaftarPageProps = {
  settings: Record<string, any>;
  footerContent?: unknown;
  kegiatan: PortalEvent | null;
  isOpen: boolean;
  closesAtLabel: string;
  wilayah: Wilayah[];
  jenjangOptions: { value: string; label: string }[];
  statusKepegawaianOptions: string[];
  universitasOptions: string[];
  guidebookUrl: string | null;
};

type FormData = {
  nama: string;
  whatsapp: string;
  email: string;
  provinsi: string;
  kabupaten_kota: string;
  asal_instansi: string;
  jenjang: string;
  status_kepegawaian: string;
  bidang_studi: string;
  lama_mengabdi_tahun: string;
  asal_universitas: string;
  instagram: string;
  persetujuan_data: boolean;
  website: string;
};

const inputClass =
  "w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#006837] focus:outline-none focus:ring-2 focus:ring-[#006837]/30 disabled:bg-gray-100";

type FieldProps = {
  id: string;
  label: string;
  error?: string;
  hint?: string;
  optional?: boolean;
  className?: string;
  children: ReactNode;
};

const Field = ({ id, label, error, hint, optional = false, className = "", children }: FieldProps) => (
  <div className={`flex flex-col gap-1.5 ${className}`}>
    <label htmlFor={id} className="border-l-4 border-[#FFD700] pl-2 text-sm font-semibold text-gray-800">
      {label}
      {optional ? <span className="ml-1 font-normal text-gray-400">(opsional)</span> : <span className="text-red-500"> *</span>}
    </label>
    {children}
    {hint && !error && <p className="text-xs text-gray-500">{hint}</p>}
    {error && <p className="text-xs font-medium text-red-600">{error}</p>}
  </div>
);

const pageTitle = "Pendaftaran Teacher of Champions";

const Daftar = ({
  settings,
  footerContent,
  kegiatan,
  isOpen,
  closesAtLabel,
  wilayah,
  jenjangOptions,
  statusKepegawaianOptions,
  universitasOptions,
  guidebookUrl,
}: DaftarPageProps) => {
  const form = useForm<FormData>({
    nama: "",
    whatsapp: "",
    email: "",
    provinsi: "",
    kabupaten_kota: "",
    asal_instansi: "",
    jenjang: "",
    status_kepegawaian: "",
    bidang_studi: "",
    lama_mengabdi_tahun: "",
    asal_universitas: "",
    instagram: "",
    persetujuan_data: false,
    website: "",
  });

  const errors = form.errors as Partial<Record<keyof FormData | "form", string>>;
  const kabupatenOptions = wilayah.find((item) => item.nama === form.data.provinsi)?.kabupaten_kota ?? [];

  const submit = (event: FormEvent) => {
    event.preventDefault();
    form.post("/teacher-of-champions/daftar", { preserveScroll: true });
  };

  return (
    <>
      <Head>
        <title>{`${pageTitle} - IKA UNIMED`}</title>
        <meta
          name="description"
          content="Daftar kompetisi online Teacher of Champions untuk guru se-Indonesia, program IKA UNIMED dalam rangka Hari Guru Nasional 2026."
        />
      </Head>

      <MainLayout variant="full" settings={settings} footerContent={footerContent}>
        <section className="bg-gradient-to-br from-[#0B3D2E] via-[#0B3D2E] to-[#006837] py-12 md:py-16">
          <div className="container grid gap-10 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div>
              <p className="mb-3 text-xs font-extrabold uppercase tracking-[0.3em] text-[#FFD700]">
                Hari Guru Nasional 2026
              </p>
              <h1 className="text-3xl font-extrabold leading-tight text-white md:text-5xl">{pageTitle}</h1>
              {kegiatan && <p className="mt-4 max-w-2xl text-white/85">{kegiatan.summary}</p>}
              <p className="mt-6 inline-flex items-center gap-2 text-sm text-white/90">
                <CalendarDays className="h-4 w-4 shrink-0 text-[#FFD700]" />
                Pendaftaran ditutup {closesAtLabel}
              </p>
              {guidebookUrl && (
                <div className="mt-6">
                  <GuidebookLink href={guidebookUrl} tone="dark" />
                </div>
              )}
            </div>
            {kegiatan && kegiatan.schedule.length > 0 && (
              <div className="rounded-2xl border border-[#FFD700]/25 bg-black/20 p-5">
                <p className="mb-4 text-xs font-bold uppercase tracking-[0.2em] text-[#FFD700]">Tahapan</p>
                <MiniTimeline items={kegiatan.schedule} tone="dark" />
              </div>
            )}
          </div>
        </section>

        <section className="bg-gray-50 pb-28 pt-10 md:pb-32">
          <div className="container max-w-3xl">
            {!isOpen ? (
              <div className="rounded-3xl border border-gray-200 border-t-4 border-t-[#FFD700] bg-white p-8 text-center shadow-sm">
                <Lock className="mx-auto h-10 w-10 text-[#006837]" />
                <h2 className="mt-4 text-2xl font-extrabold text-[#0B3D2E]">Pendaftaran ditutup</h2>
                <p className="mt-2 text-gray-600">
                  Terima kasih atas antusiasme Bapak/Ibu guru. Ikuti tahapan berikutnya melalui Instagram resmi IKA UNIMED.
                </p>
                <Link href="/reuni-akbar-2026#teacher-of-champions" className="mt-6 inline-block font-semibold text-[#006837] underline">
                  Lihat timeline kompetisi
                </Link>
              </div>
            ) : (
              <form
                onSubmit={submit}
                noValidate
                className="relative overflow-hidden rounded-3xl border border-gray-200 border-t-4 border-t-[#FFD700] bg-white p-6 shadow-sm md:p-8"
              >
                {errors.form && (
                  <p className="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{errors.form}</p>
                )}

                <div aria-hidden="true" className="absolute -left-[9999px] h-0 w-0 overflow-hidden">
                  <label htmlFor="website">Website</label>
                  <input
                    id="website"
                    name="website"
                    type="text"
                    tabIndex={-1}
                    autoComplete="off"
                    value={form.data.website}
                    onChange={(event) => form.setData("website", event.target.value)}
                  />
                </div>

                <div className="grid gap-5 md:grid-cols-2">
                  <Field id="nama" label="Nama lengkap (beserta gelar)" error={errors.nama} className="md:col-span-2">
                    <input
                      id="nama"
                      className={inputClass}
                      value={form.data.nama}
                      onChange={(event) => form.setData("nama", event.target.value)}
                      autoComplete="name"
                      required
                    />
                  </Field>

                  <Field id="whatsapp" label="Nomor WhatsApp" error={errors.whatsapp} hint="Contoh: 081234567890">
                    <input
                      id="whatsapp"
                      type="tel"
                      inputMode="tel"
                      className={inputClass}
                      value={form.data.whatsapp}
                      onChange={(event) => form.setData("whatsapp", event.target.value)}
                      autoComplete="tel"
                      required
                    />
                  </Field>

                  <Field id="email" label="Email" error={errors.email}>
                    <input
                      id="email"
                      type="email"
                      className={inputClass}
                      value={form.data.email}
                      onChange={(event) => form.setData("email", event.target.value)}
                      autoComplete="email"
                      required
                    />
                  </Field>

                  <Field id="provinsi" label="Provinsi" error={errors.provinsi}>
                    <select
                      id="provinsi"
                      className={inputClass}
                      value={form.data.provinsi}
                      onChange={(event) => form.setData((data) => ({ ...data, provinsi: event.target.value, kabupaten_kota: "" }))}
                      required
                    >
                      <option value="">Pilih provinsi</option>
                      {wilayah.map((item) => (
                        <option key={item.kode} value={item.nama}>
                          {item.nama}
                        </option>
                      ))}
                    </select>
                  </Field>

                  <Field id="kabupaten_kota" label="Kabupaten / kota" error={errors.kabupaten_kota}>
                    <select
                      id="kabupaten_kota"
                      className={inputClass}
                      value={form.data.kabupaten_kota}
                      onChange={(event) => form.setData("kabupaten_kota", event.target.value)}
                      disabled={kabupatenOptions.length === 0}
                      required
                    >
                      <option value="">{form.data.provinsi ? "Pilih kabupaten/kota" : "Pilih provinsi dulu"}</option>
                      {kabupatenOptions.map((nama) => (
                        <option key={nama} value={nama}>
                          {nama}
                        </option>
                      ))}
                    </select>
                  </Field>

                  <Field id="asal_instansi" label="Asal instansi / sekolah" error={errors.asal_instansi}>
                    <input
                      id="asal_instansi"
                      className={inputClass}
                      value={form.data.asal_instansi}
                      onChange={(event) => form.setData("asal_instansi", event.target.value)}
                      required
                    />
                  </Field>

                  <Field id="jenjang" label="Jenjang mengajar" error={errors.jenjang} optional>
                    <select
                      id="jenjang"
                      className={inputClass}
                      value={form.data.jenjang}
                      onChange={(event) => form.setData("jenjang", event.target.value)}
                    >
                      <option value="">Pilih jenjang</option>
                      {jenjangOptions.map((option) => (
                        <option key={option.value} value={option.value}>
                          {option.label}
                        </option>
                      ))}
                    </select>
                  </Field>

                  <Field id="status_kepegawaian" label="Status kepegawaian" error={errors.status_kepegawaian} optional>
                    <select
                      id="status_kepegawaian"
                      className={inputClass}
                      value={form.data.status_kepegawaian}
                      onChange={(event) => form.setData("status_kepegawaian", event.target.value)}
                    >
                      <option value="">Pilih status</option>
                      {statusKepegawaianOptions.map((option) => (
                        <option key={option} value={option}>
                          {option}
                        </option>
                      ))}
                    </select>
                  </Field>

                  <Field id="bidang_studi" label="Bidang studi yang diampu" error={errors.bidang_studi}>
                    <input
                      id="bidang_studi"
                      className={inputClass}
                      value={form.data.bidang_studi}
                      onChange={(event) => form.setData("bidang_studi", event.target.value)}
                      required
                    />
                  </Field>

                  <Field id="lama_mengabdi_tahun" label="Lama mengabdi (tahun)" error={errors.lama_mengabdi_tahun}>
                    <input
                      id="lama_mengabdi_tahun"
                      type="number"
                      min={0}
                      max={60}
                      inputMode="numeric"
                      className={inputClass}
                      value={form.data.lama_mengabdi_tahun}
                      onChange={(event) => form.setData("lama_mengabdi_tahun", event.target.value)}
                      required
                    />
                  </Field>

                  <Field
                    id="asal_universitas"
                    label="Asal universitas (S1)"
                    error={errors.asal_universitas}
                    hint="Ketik atau pilih dari saran"
                  >
                    <input
                      id="asal_universitas"
                      list="universitas-options"
                      className={inputClass}
                      value={form.data.asal_universitas}
                      onChange={(event) => form.setData("asal_universitas", event.target.value)}
                      autoComplete="off"
                      required
                    />
                    <datalist id="universitas-options">
                      {universitasOptions.map((nama) => (
                        <option key={nama} value={nama} />
                      ))}
                    </datalist>
                  </Field>

                  <Field
                    id="instagram"
                    label="Akun Instagram"
                    error={errors.instagram}
                    hint="Untuk voting Favorite Teacher Award, contoh: @namaguru"
                    className="md:col-span-2"
                  >
                    <input
                      id="instagram"
                      className={inputClass}
                      value={form.data.instagram}
                      onChange={(event) => form.setData("instagram", event.target.value)}
                      autoCapitalize="none"
                      required
                    />
                  </Field>
                </div>

                <label className="mt-6 flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                  <input
                    type="checkbox"
                    className="mt-0.5 h-4 w-4 shrink-0 accent-[#006837]"
                    checked={form.data.persetujuan_data}
                    onChange={(event) => form.setData("persetujuan_data", event.target.checked)}
                  />
                  <span>
                    Saya menyetujui data di atas digunakan panitia IKA UNIMED hanya untuk keperluan kompetisi Teacher of
                    Champions (verifikasi, pengumuman, dan komunikasi peserta). Data tidak ditampilkan di halaman publik.
                  </span>
                </label>
                {errors.persetujuan_data && (
                  <p className="mt-1.5 text-xs font-medium text-red-600">{errors.persetujuan_data}</p>
                )}

                <button
                  type="submit"
                  disabled={form.processing}
                  className="mt-8 w-full rounded-xl bg-[#FFD700] px-8 py-3 text-base font-bold text-[#0B3D2E] shadow-md transition hover:bg-[#FFC400] focus:outline-none focus:ring-2 focus:ring-[#006837]/40 disabled:opacity-60 md:w-auto"
                >
                  {form.processing ? "Mengirim..." : "Daftar"}
                </button>
              </form>
            )}
          </div>
        </section>
      </MainLayout>
    </>
  );
};

export default Daftar;
