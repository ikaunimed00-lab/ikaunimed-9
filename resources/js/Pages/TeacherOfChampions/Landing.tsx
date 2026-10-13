import { Head, Link } from "@inertiajs/react";
import {
  Award,
  CalendarDays,
  CheckCircle2,
  ChevronDown,
  Clock,
  Download,
  FileQuestion,
  Heart,
  Instagram,
  Lightbulb,
  MessageCircle,
  MonitorPlay,
  Target,
  TrendingUp,
  Trophy,
} from "lucide-react";
import MainLayout from "@/components/MainLayout";
import FloatingCTA from "@/components/FloatingCTA";
import Countdown from "@/components/home/Countdown";
import ZoomableImage from "@/components/home/ZoomableImage";
import type { ReuniContact } from "@/components/home/types";
import RegisterButton, { REGISTER_URL } from "@/components/toc/RegisterButton";
import SectionHeading from "@/components/toc/SectionHeading";
import StickyRegisterBar from "@/components/toc/StickyRegisterBar";

type LandingPageProps = {
  settings: Record<string, any>;
  footerContent?: unknown;
  contacts: ReuniContact[];
  isOpen: boolean;
  closesAt: string;
  closesAtLabel: string;
  periodLabel: string;
  jenjangOptions: string[];
  instagramUrl: string | null;
  meta: { title: string; description: string };
};

const HERO_IMAGE = "/images/event-teacher-card-hijau.jpg";
const POSTER_IMAGE = "/images/teacher-of-champions-poster.jpg";

const highlights = [
  { icon: Award, title: "Apresiasi", text: "Penghargaan bagi guru berprestasi." },
  { icon: TrendingUp, title: "Pengembangan", text: "Meningkatkan kualitas dan kompetensi guru secara berkelanjutan." },
  { icon: Target, title: "Kompetensi", text: "Mengukur kemampuan guru lewat tahapan terstruktur dan objektif." },
  { icon: Lightbulb, title: "Dampak", text: "Melahirkan guru inspiratif untuk perubahan positif." },
];

const timeline: { date: string; title: string; text?: string }[] = [
  {
    date: "12–29 Okt 2026",
    title: "Pendaftaran Peserta ToC 2026",
    text: "Pendaftaran online untuk seluruh guru di Indonesia.",
  },
  { date: "1 Nov 2026", title: "Pengumuman peserta terpilih mengikuti ToC 2026" },
  {
    date: "9 Nov 2026",
    title: "Pelaksanaan Tahap Penyisihan (The Qualifiers)",
    text: "50 soal, 90 menit; 100 peserta terbaik akan lolos ke tahap final.",
  },
  { date: "12 Nov 2026", title: "Pengumuman 100 besar lolos ke Tahap Final (The Championship)" },
  { date: "13 Nov 2026", title: "Technical Meeting Babak Final" },
  {
    date: "14–22 Nov 2026",
    title: "Voting melalui Instagram Resmi IKA UNIMED",
    text: "Penentuan Favorite Teacher Award.",
  },
  { date: "22 Nov 2026", title: "Pelaksanaan dan Pengumuman Tahap Final (The Championship)", text: "Live quiz." },
];

const steps = [
  { title: "Isi formulir pendaftaran online", text: "Gratis, tidak dipungut biaya." },
  {
    title: "Tunggu pengumuman peserta terpilih",
    text: "Panitia memverifikasi data dan mengumumkan peserta terpilih pada 1 November 2026. Informasi lanjutan dikirim melalui WhatsApp resmi panitia.",
  },
  { title: "Ikuti tahap penyisihan dan final", text: "Kerjakan tahap penyisihan (CBT), lalu lanjutkan ke tahap final bila lolos." },
];

const scoring = [
  { label: "Sangat tepat", score: 100 },
  { label: "Cukup tepat", score: 50 },
  { label: "Kurang tepat", score: 25 },
  { label: "Tidak tepat", score: 0 },
];

const materials = [
  { name: "Kompetensi Pedagogik", questions: 20, weight: "40%" },
  { name: "Kompetensi Profesional", questions: 15, weight: "30%" },
  { name: "Situational Judgment Test (SJT)", questions: 10, weight: "20%" },
  { name: "Penalaran dan Pemecahan Masalah", questions: 5, weight: "10%" },
];

const rounds = [
  { number: 1, name: "The Opening Battle", participants: 100, questions: 30 },
  { number: 2, name: "The Champions' Challenge", participants: 50, questions: 30 },
  { number: 3, name: "The Grand Showdown", participants: 25, questions: 40 },
];

const finalTopics = [
  "Kompetensi Pedagogik",
  "Kompetensi Profesional",
  "SJT",
  "Penalaran dan Pemecahan Masalah",
  "Pengetahuan Umum",
];

const finalRules = [
  "Wajib hadir di ruang Zoom sesuai jadwal dan mengikuti arahan moderator.",
  "Wajib menggunakan nama sesuai identitas.",
  "Dilarang bekerja sama atau menggunakan bantuan pihak lain.",
];

const favoriteRules = [
  "Hanya like dari akun aktif dan asli yang dihitung.",
  "Penggunaan akun palsu, bot, atau bentuk kecurangan lain didiskualifikasi.",
  "Pemenang ditetapkan berdasarkan jumlah like pada periode yang ditetapkan panitia.",
];

const whatsappLink = (contact: ReuniContact): string | null =>
  contact.whatsapp
    ? `https://wa.me/${contact.whatsapp.replace(/\D/g, "")}?text=${encodeURIComponent("Halo panitia, saya ingin bertanya tentang Teacher of Champions 2026.")}`
    : null;

const CheckList = ({ items }: { items: string[] }) => (
  <ul className="flex flex-col gap-3">
    {items.map((item) => (
      <li key={item} className="flex items-start gap-3 text-gray-700">
        <CheckCircle2 className="mt-0.5 h-5 w-5 shrink-0 text-[#006837]" aria-hidden="true" />
        <span>{item}</span>
      </li>
    ))}
  </ul>
);

const Landing = ({
  settings,
  footerContent,
  contacts,
  isOpen,
  closesAt,
  closesAtLabel,
  periodLabel,
  jenjangOptions,
  instagramUrl,
  meta,
}: LandingPageProps) => {
  const jenjangLabel =
    jenjangOptions.length > 1
      ? `${jenjangOptions.slice(0, -1).join(", ")}, atau ${jenjangOptions[jenjangOptions.length - 1]}`
      : jenjangOptions.join("");

  const faqs = [
    {
      question: "Siapa yang boleh ikut?",
      answer: `Guru di seluruh Indonesia yang mengajar pada jenjang ${jenjangLabel}.`,
    },
    { question: "Apakah berbayar?", answer: "Tidak. Pendaftaran gratis dan tidak dipungut biaya." },
    {
      question: "Bagaimana tesnya?",
      answer: "Tahap penyisihan berupa CBT online selama 90 menit dengan 50 soal pilihan ganda.",
    },
    { question: "Kapan pengumuman?", answer: "Peserta terpilih diumumkan pada 1 November 2026." },
    {
      question: "Bagaimana jika ada kendala?",
      answer: "Hubungi WhatsApp panitia yang tercantum di bagian Kontak panitia di halaman ini.",
    },
  ];

  return (
    <>
      <Head>
        <title>{meta.title}</title>
        <meta name="description" content={meta.description} />
      </Head>

      <MainLayout variant="full" settings={settings} footerContent={footerContent}>
        {/* 1. Hero */}
        <section
          aria-labelledby="toc-title"
          className="relative overflow-hidden bg-gradient-to-br from-[#0B3D2E] via-[#0B3D2E] to-[#006837]"
        >
          <div
            className="pointer-events-none absolute -left-24 top-0 h-80 w-80 rounded-full bg-[#FFD700]/10 blur-3xl"
            aria-hidden="true"
          />
          <div className="container relative z-10 grid items-center gap-10 py-12 md:py-20 lg:grid-cols-[minmax(0,1fr)_420px]">
            <div className="min-w-0">
              <p className="mb-3 text-xs font-extrabold uppercase tracking-[0.3em] text-[#FFD700]">
                Hari Guru Nasional 2026
              </p>
              <h1 id="toc-title" className="text-4xl font-extrabold leading-tight text-white md:text-6xl">
                Teacher of Champions
              </h1>
              <p className="mt-3 text-lg font-semibold text-[#FFD700] md:text-xl">
                Empowering Educators, Inspiring Generations
              </p>
              <p className="mt-5 max-w-2xl text-base text-white/90 md:text-lg">
                Kompetisi online untuk guru se-Indonesia, program kerja IKA UNIMED dalam rangka memperingati Hari Guru
                Nasional 25 November.
              </p>

              <div id="toc-hero-cta" className="mt-8 flex flex-col gap-3 sm:flex-row">
                <RegisterButton isOpen={isOpen} />
                <a
                  href="#jadwal"
                  className="inline-flex items-center justify-center rounded-xl border-2 border-white/70 px-6 py-3 text-base font-bold text-white transition hover:bg-white/10 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#FFD700]/60"
                >
                  Lihat jadwal
                </a>
              </div>

              <div className="mt-8 max-w-md rounded-2xl border border-[#FFD700]/30 bg-black/20 p-5">
                <p className="flex items-center gap-2 text-sm font-bold text-white">
                  <CalendarDays className="h-4 w-4 shrink-0 text-[#FFD700]" aria-hidden="true" />
                  Pendaftaran 12–29 Oktober 2026
                </p>
                {isOpen ? (
                  <div className="mt-4">
                    <p className="mb-2 text-xs text-white/80">Pendaftaran ditutup dalam:</p>
                    <Countdown target={closesAt} />
                    <p className="mt-2 text-xs text-white/70">Batas akhir {closesAtLabel}</p>
                  </div>
                ) : (
                  <p className="mt-3 text-sm font-semibold text-[#FFD700]">Pendaftaran ditutup</p>
                )}
              </div>
            </div>

            <img
              src={HERO_IMAGE}
              alt="Teacher of Champions 2026, kompetisi online guru se-Indonesia oleh IKA UNIMED"
              className="mx-auto w-full max-w-md rounded-2xl border-2 border-white/80 shadow-2xl lg:max-w-none"
            />
          </div>
        </section>

        {/* 2. Tentang */}
        <section aria-labelledby="tentang" className="bg-white py-14 md:py-20">
          <div className="container">
            <SectionHeading id="tentang" eyebrow="Tentang" title="Apa itu Teacher of Champions?">
              IKA UNIMED mengadakan Teacher of Champions sebagai wadah bagi guru untuk menunjukkan kompetensi pedagogik,
              profesional, penalaran, dan kemampuan mengambil keputusan.
            </SectionHeading>
            <ul className="mx-auto grid max-w-5xl gap-4 sm:grid-cols-2 lg:grid-cols-4">
              {highlights.map(({ icon: Icon, title, text }) => (
                <li key={title} className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                  <span className="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-[#E7F5E9] text-[#006837]">
                    <Icon className="h-6 w-6" aria-hidden="true" />
                  </span>
                  <h3 className="mt-4 text-lg font-bold text-[#0B3D2E]">{title}</h3>
                  <p className="mt-1 text-sm text-gray-600">{text}</p>
                </li>
              ))}
            </ul>
          </div>
        </section>

        {/* 3. Jadwal */}
        <section id="jadwal" aria-labelledby="jadwal-title" className="scroll-mt-24 bg-[#F2FAF4] py-14 md:py-20">
          <div className="container">
            <SectionHeading id="jadwal-title" eyebrow="Jadwal" title="Tahapan kompetisi">
              Rangkaian kegiatan: {periodLabel}
            </SectionHeading>
            <ol className="mx-auto flex max-w-3xl flex-col gap-0">
              {timeline.map((item, index) => (
                <li key={item.title} className="relative flex gap-4 pb-8 last:pb-0">
                  {index < timeline.length - 1 && (
                    <span className="absolute left-[15px] top-8 h-[calc(100%-2rem)] w-0.5 bg-[#006837]/25" aria-hidden="true" />
                  )}
                  <span className="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#006837] text-sm font-bold text-white">
                    {index + 1}
                  </span>
                  <div className="min-w-0 flex-1 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p className="text-sm font-bold text-[#B45309]">{item.date}</p>
                    <h3 className="text-lg font-bold text-[#0B3D2E]">{item.title}</h3>
                    {item.text && <p className="mt-1 text-sm text-gray-600">{item.text}</p>}
                  </div>
                </li>
              ))}
            </ol>
          </div>
        </section>

        {/* 4. Cara mengikuti */}
        <section aria-labelledby="cara-ikut" className="bg-white py-14 md:py-20">
          <div className="container">
            <SectionHeading id="cara-ikut" eyebrow="Cara mengikuti" title="Tiga langkah menjadi peserta" />
            <ol className="mx-auto grid max-w-5xl gap-4 md:grid-cols-3">
              {steps.map((step, index) => (
                <li key={step.title} className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                  <span className="text-3xl font-extrabold text-[#FF7E00]" aria-hidden="true">
                    {String(index + 1).padStart(2, "0")}
                  </span>
                  <h3 className="mt-2 text-lg font-bold text-[#0B3D2E]">{step.title}</h3>
                  <p className="mt-1 text-sm text-gray-600">{step.text}</p>
                </li>
              ))}
            </ol>
            <div className="mt-10 text-center">
              {isOpen ? (
                <Link
                  href={REGISTER_URL}
                  className="inline-flex items-center justify-center rounded-xl bg-[#FF7E00] px-6 py-3 text-base font-bold text-[#0B3D2E] shadow-lg shadow-black/10 transition hover:bg-[#FF9533] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#FFD700]/60"
                >
                  Buka formulir pendaftaran
                </Link>
              ) : (
                <RegisterButton isOpen={false} />
              )}
            </div>
          </div>
        </section>

        {/* 5. Tahap Penyisihan */}
        <section aria-labelledby="penyisihan" className="bg-[#F2FAF4] py-14 md:py-20">
          <div className="container">
            <SectionHeading id="penyisihan" eyebrow="Tahap Penyisihan" title="The Qualifiers (CBT)">
              Tes online selama 90 menit dengan 50 soal pilihan ganda empat opsi.
            </SectionHeading>

            <div className="mx-auto grid max-w-5xl gap-6 lg:grid-cols-2">
              <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="flex items-center gap-2 text-lg font-bold text-[#0B3D2E]">
                  <Clock className="h-5 w-5 text-[#006837]" aria-hidden="true" />
                  Penilaian
                </h3>
                <p className="mt-2 text-sm text-gray-600">
                  Setiap soal memiliki bobot skor sesuai kualitas jawaban:
                </p>
                <dl className="mt-4 grid grid-cols-2 gap-3">
                  {scoring.map((item) => (
                    <div key={item.label} className="rounded-xl bg-[#E7F5E9] px-4 py-3">
                      <dt className="text-sm text-gray-700">{item.label}</dt>
                      <dd className="text-2xl font-extrabold text-[#0B3D2E]">{item.score}</dd>
                    </div>
                  ))}
                </dl>
                <p className="mt-4 text-sm text-gray-700">
                  Skor maksimal <strong>100</strong> per soal dan <strong>5.000</strong> total.
                </p>
              </div>

              <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="flex items-center gap-2 text-lg font-bold text-[#0B3D2E]">
                  <FileQuestion className="h-5 w-5 text-[#006837]" aria-hidden="true" />
                  Struktur materi
                </h3>
                <table className="mt-4 w-full text-left text-sm">
                  <caption className="sr-only">Struktur materi tahap penyisihan</caption>
                  <thead>
                    <tr className="border-b border-gray-200 text-gray-500">
                      <th scope="col" className="py-2 pr-2 font-semibold">Materi</th>
                      <th scope="col" className="py-2 pr-2 text-right font-semibold">Soal</th>
                      <th scope="col" className="py-2 text-right font-semibold">Bobot</th>
                    </tr>
                  </thead>
                  <tbody>
                    {materials.map((item) => (
                      <tr key={item.name} className="border-b border-gray-100 last:border-0">
                        <th scope="row" className="py-3 pr-2 font-medium text-gray-800">{item.name}</th>
                        <td className="py-3 pr-2 text-right tabular-nums text-gray-700">{item.questions}</td>
                        <td className="py-3 text-right font-bold tabular-nums text-[#006837]">{item.weight}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>

            <p className="mx-auto mt-8 max-w-3xl rounded-2xl border-l-4 border-[#FFD700] bg-white px-5 py-4 text-center font-semibold text-[#0B3D2E] shadow-sm">
              100 peserta terbaik akan lolos ke Tahap Final, diumumkan 12 November 2026.
            </p>
          </div>
        </section>

        {/* 6. Tahap Final */}
        <section aria-labelledby="final" className="bg-white py-14 md:py-20">
          <div className="container">
            <SectionHeading id="final" eyebrow="Tahap Final" title="The Championship (Live Quiz)">
              Dilaksanakan online melalui Zoom dan Kahoot pada 22 November 2026, terdiri dari 3 ronde eliminasi. Technical meeting babak final diadakan 13 November 2026.
            </SectionHeading>

            <ol className="mx-auto grid max-w-5xl gap-4 md:grid-cols-3">
              {rounds.map((round) => (
                <li key={round.number} className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                  <p className="text-sm font-bold uppercase tracking-wider text-[#B45309]">Ronde {round.number}</p>
                  <h3 className="mt-1 text-lg font-bold text-[#0B3D2E]">{round.name}</h3>
                  <p className="mt-3 text-sm text-gray-600">
                    {round.participants} peserta · {round.questions} soal
                  </p>
                </li>
              ))}
            </ol>

            <p className="mx-auto mt-6 flex max-w-3xl items-start gap-3 rounded-2xl bg-[#E7F5E9] px-5 py-4 text-[#0B3D2E]">
              <Trophy className="mt-0.5 h-5 w-5 shrink-0 text-[#006837]" aria-hidden="true" />
              Tiga peserta dengan skor tertinggi pada Ronde 3 menjadi Juara 1, 2, dan 3.
            </p>

            <div className="mx-auto mt-10 grid max-w-5xl gap-6 lg:grid-cols-2">
              <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="flex items-center gap-2 text-lg font-bold text-[#0B3D2E]">
                  <MonitorPlay className="h-5 w-5 text-[#006837]" aria-hidden="true" />
                  Materi live quiz
                </h3>
                <ul className="mt-4 flex flex-wrap gap-2">
                  {finalTopics.map((topic) => (
                    <li key={topic} className="rounded-full bg-[#E7F5E9] px-3 py-1.5 text-sm font-medium text-[#0B3D2E]">
                      {topic}
                    </li>
                  ))}
                </ul>
              </div>
              <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="text-lg font-bold text-[#0B3D2E]">Ketentuan peserta</h3>
                <div className="mt-4">
                  <CheckList items={finalRules} />
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* 7. Favorite Teacher Award */}
        <section aria-labelledby="favorite" className="bg-[#F2FAF4] py-14 md:py-20">
          <div className="container">
            <SectionHeading id="favorite" eyebrow="Kompetisi tambahan" title="Favorite Teacher Award">
              Masyarakat memberi dukungan kepada 100 finalis melalui voting di Instagram Resmi IKA UNIMED (@ikaunimed),
              14–22 November 2026. Peserta dengan like terbanyak ditetapkan sebagai Favorite Teacher of
              Champions dan mendapat apresiasi khusus.
            </SectionHeading>

            <div className="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
              <h3 className="flex items-center gap-2 text-lg font-bold text-[#0B3D2E]">
                <Heart className="h-5 w-5 text-[#006837]" aria-hidden="true" />
                Ketentuan
              </h3>
              <div className="mt-4">
                <CheckList items={favoriteRules} />
              </div>
              <div className="mt-6">
                {instagramUrl ? (
                  <a
                    href={instagramUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-2 rounded-xl bg-[#006837] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#00522c] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#FFD700]/60"
                  >
                    <Instagram className="h-5 w-5" aria-hidden="true" />
                    Ikuti Instagram IKA UNIMED
                  </a>
                ) : (
                  <p className="inline-flex items-center gap-2 font-bold text-[#006837]">
                    <Instagram className="h-5 w-5" aria-hidden="true" />
                    @ikaunimed
                  </p>
                )}
              </div>
            </div>
          </div>
        </section>

        {/* 8. Poster resmi */}
        <section aria-labelledby="poster" className="bg-white py-14 md:py-20">
          <div className="container">
            <SectionHeading id="poster" eyebrow="Poster resmi" title="Bagikan informasi ini" />
            <div className="mx-auto max-w-3xl">
              <ZoomableImage src={POSTER_IMAGE} alt="Poster resmi Teacher of Champions 2026 IKA UNIMED" />
              <div className="mt-4 text-center">
                <a
                  href={POSTER_IMAGE}
                  download
                  className="inline-flex items-center gap-2 rounded-xl border-2 border-[#006837] px-5 py-2.5 text-sm font-bold text-[#006837] transition hover:bg-[#E7F5E9] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#FFD700]/60"
                >
                  <Download className="h-4 w-4" aria-hidden="true" />
                  Unduh poster
                </a>
              </div>
            </div>
          </div>
        </section>

        {/* 9. FAQ */}
        <section aria-labelledby="faq" className="bg-[#F2FAF4] py-14 md:py-20">
          <div className="container">
            <SectionHeading id="faq" eyebrow="FAQ" title="Pertanyaan yang sering diajukan" />
            <div className="mx-auto flex max-w-3xl flex-col gap-3">
              {faqs.map((faq) => (
                <details key={faq.question} className="group rounded-2xl border border-gray-200 bg-white shadow-sm">
                  <summary className="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-semibold text-[#0B3D2E] [&::-webkit-details-marker]:hidden">
                    <h3 className="text-base">{faq.question}</h3>
                    <ChevronDown
                      className="h-5 w-5 shrink-0 text-[#006837] transition group-open:rotate-180"
                      aria-hidden="true"
                    />
                  </summary>
                  <p className="px-5 pb-5 text-gray-600">{faq.answer}</p>
                </details>
              ))}
            </div>
          </div>
        </section>

        {/* 10. Kontak panitia */}
        <section aria-labelledby="kontak" className="bg-white py-14 md:py-20">
          <div className="container">
            <SectionHeading id="kontak" eyebrow="Kontak panitia" title="Butuh bantuan?">
              Hubungi panitia melalui WhatsApp.
            </SectionHeading>
            <div className="mx-auto flex max-w-xl flex-col gap-3 sm:flex-row sm:justify-center">
              {contacts.map((contact) => {
                const href = whatsappLink(contact);

                return href ? (
                  <a
                    key={contact.name}
                    href={href}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-[#0B3D2E] shadow-sm transition hover:border-[#006837] hover:bg-[#E7F5E9] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#FFD700]/60"
                  >
                    <MessageCircle className="h-5 w-5 text-[#006837]" aria-hidden="true" />
                    <span>
                      {contact.name}
                      {contact.role && <span className="font-normal text-gray-500"> · {contact.role}</span>}
                    </span>
                  </a>
                ) : null;
              })}
            </div>
          </div>
        </section>

        {/* 11. Penutup */}
        <section
          id="toc-penutup"
          aria-labelledby="penutup"
          className="bg-gradient-to-br from-[#0B3D2E] via-[#0B3D2E] to-[#006837] py-14 md:py-20"
        >
          <div className="container text-center">
            <h2 id="penutup" className="text-2xl font-extrabold text-white md:text-4xl">
              Siap menjadi Teacher of Champions?
            </h2>
            <p className="mx-auto mt-3 max-w-xl text-white/85">
              {isOpen ? `Pendaftaran ditutup ${closesAtLabel}.` : "Pendaftaran telah ditutup. Ikuti tahapan berikutnya melalui Instagram resmi IKA UNIMED."}
            </p>
            <div className="mt-8">
              <RegisterButton isOpen={isOpen} label="Daftar sekarang" />
            </div>
          </div>
        </section>

        {isOpen && <StickyRegisterBar hideWhenVisible="#toc-hero-cta, #toc-penutup, footer" />}
        <FloatingCTA settings={settings} hideBubble liftOnMobile={isOpen} />
      </MainLayout>
    </>
  );
};

export default Landing;
