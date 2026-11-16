import { Head } from "@inertiajs/react";
import MainLayout from "@/components/MainLayout";
import PortalHero from "@/components/home/PortalHero";
import UpcomingEventsSection from "@/components/home/UpcomingEventsSection";
import UpcomingEventsModal from "@/components/home/UpcomingEventsModal";
import PimpinanSection from "@/components/home/PimpinanSection";
import LatestNewsSection from "@/components/home/LatestNewsSection";
import ServiceStrip from "@/components/home/ServiceStrip";
import type { Pimpinan, PortalEvent, PortalNews, PortalService, ReuniContact } from "@/components/home/types";

type PortalPageProps = {
  isPreview?: boolean;
  settings: Record<string, any>;
  footerContent?: unknown;
  hero: PortalEvent;
  events: PortalEvent[];
  latestNews: PortalNews[];
  services: PortalService[];
  pimpinan: Pimpinan[];
  contacts: ReuniContact[];
};

const Portal = ({
  isPreview = false,
  settings,
  footerContent,
  hero,
  events,
  latestNews,
  services,
  pimpinan,
  contacts,
}: PortalPageProps) => {
  const siteName = settings?.general?.site_name || settings?.identity?.site_name || "IKA UNIMED";
  const tagline = settings?.general?.site_tagline || settings?.identity?.site_tagline || "Connect, Collaborate, Contribute";

  return (
    <>
      <Head>
        <title>{`${siteName} - ${tagline}`}</title>
        <meta
          name="description"
          content={settings?.general?.site_description || "Official Website Ikatan Alumni Universitas Negeri Medan (IKA UNIMED)"}
        />
      </Head>

      <MainLayout variant="full" settings={settings} footerContent={footerContent}>
        {isPreview && (
          <div className="bg-[#FFD700] py-1 text-center text-[11px] font-bold uppercase tracking-widest text-[#0B3D2E]">
            Draf
          </div>
        )}
        <UpcomingEventsModal events={events} />
        <PortalHero hero={hero} contacts={contacts} />
        <UpcomingEventsSection events={events} />
        <PimpinanSection pimpinan={pimpinan} />
        <LatestNewsSection news={latestNews} />
        <ServiceStrip services={services} />
      </MainLayout>
    </>
  );
};

export default Portal;
