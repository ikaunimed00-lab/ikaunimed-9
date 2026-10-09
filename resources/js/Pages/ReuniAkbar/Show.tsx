import { Head } from "@inertiajs/react";
import { Info, MessageCircle } from "lucide-react";
import MainLayout from "@/components/MainLayout";
import FloatingCTA from "@/components/FloatingCTA";
import MiniTimeline from "@/components/home/MiniTimeline";
import SpotlightHero from "@/components/home/SpotlightHero";
import TeacherHero from "@/components/home/TeacherHero";
import ZoomableImage from "@/components/home/ZoomableImage";
import { whatsappUrl } from "@/components/home/groupEvents";
import type { PortalEvent, ReuniContact } from "@/components/home/types";

type ReuniAkbarPageProps = {
  settings: Record<string, any>;
  footerContent?: unknown;
  funWalk: PortalEvent | null;
  catur: PortalEvent | null;
  teacherOfChampions: PortalEvent | null;
  contacts: ReuniContact[];
  bannerImage: string;
  meta: { title: string; description: string };
};

const OnSiteNotice = () => (
  <p className="flex items-start gap-2 rounded-xl bg-[#FFD700]/20 px-4 py-3 text-sm font-semibold text-[#0B3D2E]">
    <Info className="mt-0.5 h-4 w-4 shrink-0" />
    Registrasi peserta dilakukan langsung di lokasi acara.
  </p>
);

const ScheduleCard = ({ title, event }: { title: string; event: PortalEvent }) => (
  <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm md:p-8">
    <h3 className="text-xl font-extrabold text-[#0B3D2E] md:text-2xl">{title}</h3>
    <p className="mt-1 text-sm text-gray-500">
      {[event.date_label, event.time_range_label, event.location].filter(Boolean).join(" · ")}
    </p>
    <div className="mt-6">
      <MiniTimeline items={event.schedule} />
    </div>
  </div>
);

const ReuniAkbarShow = ({
  settings,
  footerContent,
  funWalk,
  catur,
  teacherOfChampions,
  contacts,
  bannerImage,
  meta,
}: ReuniAkbarPageProps) => {
  return (
    <>
      <Head>
        <title>{meta.title}</title>
        <meta name="description" content={meta.description} />
        <meta property="og:title" content={meta.title} />
        <meta property="og:description" content={meta.description} />
      </Head>

      <MainLayout variant="full" settings={settings} footerContent={footerContent}>
        {funWalk ? (
          <SpotlightHero
            event={{ ...funWalk, cta_url: "#fun-walk", cta_label: "Lihat jadwal & denah rute" }}
            contacts={contacts}
          />
        ) : (
          <section className="bg-gradient-to-br from-[#0B3D2E] to-[#006837] py-14">
            <div className="container">
              <h1 className="text-3xl font-extrabold text-white md:text-5xl">{meta.title}</h1>
              <p className="mt-4 max-w-2xl text-white/85">{meta.description}</p>
            </div>
          </section>
        )}

        <nav className="border-b border-gray-100 bg-white">
          <div className="container flex gap-2 overflow-x-auto py-3 text-sm font-semibold">
            {funWalk && (
              <a href="#fun-walk" className="shrink-0 rounded-full bg-[#006837]/10 px-4 py-1.5 text-[#006837]">
                Fun Walk
              </a>
            )}
            {catur && (
              <a href="#turnamen-catur" className="shrink-0 rounded-full bg-[#006837]/10 px-4 py-1.5 text-[#006837]">
                Turnamen Catur
              </a>
            )}
            <a href="#banner-resmi" className="shrink-0 rounded-full bg-[#006837]/10 px-4 py-1.5 text-[#006837]">
              Banner resmi
            </a>
            {teacherOfChampions && (
              <a href="#teacher-of-champions" className="shrink-0 rounded-full bg-[#006837]/10 px-4 py-1.5 text-[#006837]">
                Teacher of Champions
              </a>
            )}
            <a href="#kontak-panitia" className="shrink-0 rounded-full bg-[#006837]/10 px-4 py-1.5 text-[#006837]">
              Kontak panitia
            </a>
          </div>
        </nav>

        <div className="bg-[#F8FAF9] py-12">
          <div className="container flex max-w-5xl flex-col gap-8">
            {funWalk && (
              <section id="fun-walk" className="flex scroll-mt-28 flex-col gap-6">
                <OnSiteNotice />
                <div className="grid gap-6 lg:grid-cols-2">
                  <ScheduleCard title="Susunan acara Fun Walk" event={funWalk} />
                  {funWalk.route_map_image && (
                    <div className="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm md:p-8">
                      <h3 className="mb-4 text-xl font-extrabold text-[#0B3D2E] md:text-2xl">Denah rute Fun Walk</h3>
                      <ZoomableImage src={funWalk.route_map_image} alt="Denah rute Fun Walk IKA UNIMED 2026" />
                    </div>
                  )}
                </div>
              </section>
            )}
          </div>
        </div>

        {catur && (
          <section id="turnamen-catur" className="scroll-mt-28">
            <SpotlightHero
              event={{ ...catur, cta_url: "#jadwal-catur", cta_label: "Lihat jadwal" }}
              contacts={contacts}
              headingLevel="h2"
            />
            <div id="jadwal-catur" className="scroll-mt-28 bg-[#F8FAF9] py-12">
              <div className="container grid max-w-5xl gap-6 lg:grid-cols-2">
                <ScheduleCard title="Susunan acara Turnamen Catur" event={catur} />
                {catur.hero_image && (
                  <img
                    src={catur.hero_image}
                    alt={catur.title}
                    loading="lazy"
                    className="h-full w-full rounded-3xl object-cover shadow-sm"
                  />
                )}
              </div>
            </div>
          </section>
        )}

        <section id="banner-resmi" className="scroll-mt-28 bg-white py-12">
          <div className="container max-w-5xl">
            <h2 className="mb-6 text-2xl font-extrabold text-[#0B3D2E] md:text-3xl">Banner resmi</h2>
            <ZoomableImage src={bannerImage} alt="Banner resmi Grand Reunion IKA UNIMED 2026" />
          </div>
        </section>

        {teacherOfChampions && (
          <section id="teacher-of-champions" className="scroll-mt-28">
            <TeacherHero event={teacherOfChampions} headingLevel="h2" />
            {teacherOfChampions.route_map_image && (
              <div className="bg-[#0B3D2E] pb-12">
                <div className="container max-w-5xl">
                  <ZoomableImage
                    src={teacherOfChampions.route_map_image}
                    alt={`Timeline ${teacherOfChampions.title}`}
                    className="border-[#FFD700]/40"
                  />
                </div>
              </div>
            )}
          </section>
        )}

        <section id="kontak-panitia" className="scroll-mt-28 bg-[#F8FAF9] pb-28 pt-12 md:pb-32">
          <div className="container max-w-5xl">
            <h2 className="text-2xl font-extrabold text-[#0B3D2E] md:text-3xl">Kontak panitia</h2>
            <p className="mt-2 text-gray-600">Pertanyaan seputar acara dapat disampaikan melalui WhatsApp.</p>
            <div className="mt-6 flex flex-wrap gap-3">
              {contacts.map((contact) => {
                const url = whatsappUrl(contact, "Halo panitia IKA UNIMED, saya ingin bertanya tentang Grand Reunion 2026.");

                return url ? (
                  <a
                    key={contact.name}
                    href={url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-2 rounded-xl bg-[#25D366] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#1ebe5a]"
                  >
                    <MessageCircle className="h-5 w-5 shrink-0" />
                    <span className="flex flex-col text-left leading-tight">
                      <span>Chat {contact.name}</span>
                      {contact.role && <span className="text-xs font-normal opacity-90">{contact.role}</span>}
                    </span>
                  </a>
                ) : null;
              })}
            </div>
          </div>
        </section>

        <FloatingCTA settings={settings} hideBubble />
      </MainLayout>
    </>
  );
};

export default ReuniAkbarShow;
