import { CalendarDays, MapPin } from "lucide-react";
import EventLink from "./EventLink";
import { eventInfoUrl } from "./groupEvents";
import MiniTimeline from "./MiniTimeline";
import type { PortalEvent } from "./types";
import GuidebookLink from "@/components/toc/GuidebookLink";

type TeacherHeroProps = {
  event: PortalEvent;
  headingLevel?: "h1" | "h2";
};

const TeacherHero = ({ event, headingLevel = "h1" }: TeacherHeroProps) => {
  const Heading = headingLevel;
  const poster = event.hero_image ?? event.image;
  const infoUrl = eventInfoUrl(event);

  return (
    <section className="relative overflow-hidden bg-[#0B3D2E]">
      <div
        className="pointer-events-none absolute -left-24 top-0 h-80 w-80 rounded-full bg-[#FFD700]/10 blur-3xl"
        aria-hidden="true"
      />
      <div
        className="pointer-events-none absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-[#00A69D]/20 blur-3xl"
        aria-hidden="true"
      />

      <div className="container relative z-10 grid items-start gap-10 py-12 md:py-16 lg:grid-cols-[minmax(0,1fr)_360px]">
        <div>
          <p className="mb-3 text-xs font-extrabold uppercase tracking-[0.3em] text-[#FFD700]">
            Hari Guru Nasional 2026
          </p>
          <Heading className="text-3xl font-extrabold leading-tight text-white md:text-5xl">{event.title}</Heading>
          <p className="mt-4 max-w-2xl text-base text-white/85 md:text-lg">{event.summary}</p>

          <div className="mt-6 flex flex-col gap-2 text-sm text-white/90">
            {event.date_label && (
              <span className="inline-flex items-center gap-2">
                <CalendarDays className="h-4 w-4 shrink-0 text-[#FFD700]" />
                {event.date_label}
              </span>
            )}
            {event.location && (
              <span className="inline-flex items-center gap-2">
                <MapPin className="h-4 w-4 shrink-0 text-[#FFD700]" />
                {event.location}
              </span>
            )}
          </div>

          {event.schedule.length > 0 && (
            <div className="mt-8 rounded-2xl border border-[#FFD700]/25 bg-black/20 p-5">
              <p className="mb-4 text-xs font-bold uppercase tracking-[0.2em] text-[#FFD700]">Tahapan</p>
              <MiniTimeline items={event.schedule} tone="dark" />
            </div>
          )}

          <div className="mt-8 flex flex-wrap items-center gap-3">
            <EventLink
              href={event.cta_url}
              className="inline-flex rounded-xl bg-[#FFD700] px-6 py-3 text-sm font-bold text-[#0B3D2E] shadow-lg shadow-black/20 transition hover:bg-[#FFE34D]"
            >
              {event.cta_label}
            </EventLink>
            {infoUrl && (
              <EventLink
                href={infoUrl}
                className="inline-flex rounded-xl border-2 border-white/70 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-white/10"
              >
                Info lengkap
              </EventLink>
            )}
            {event.guide_url && <GuidebookLink href={event.guide_url} tone="dark" />}
          </div>
        </div>

        {poster && (
          <picture className="mx-auto block w-full max-w-sm lg:max-w-none">
            {event.hero_image_mobile && <source media="(max-width: 640px)" srcSet={event.hero_image_mobile} />}
            <img
              src={poster}
              alt={`Poster ${event.title}`}
              className="w-full rounded-2xl border-2 border-white/80 shadow-2xl"
            />
          </picture>
        )}
      </div>
    </section>
  );
};

export default TeacherHero;
