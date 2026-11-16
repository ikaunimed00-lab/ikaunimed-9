import { CalendarDays, MapPin, MessageCircle } from "lucide-react";
import Countdown from "./Countdown";
import EventLink from "./EventLink";
import PimpinanCard from "./PimpinanCard";
import { whatsappUrl } from "./groupEvents";
import type { PortalEvent, ReuniContact } from "./types";

type SpotlightHeroProps = {
  event: PortalEvent;
  contacts: ReuniContact[];
  headingLevel?: "h1" | "h2";
  showPrimaryCta?: boolean;
};

const SpotlightHero = ({ event, contacts, headingLevel = "h1", showPrimaryCta = true }: SpotlightHeroProps) => {
  const Heading = headingLevel;
  const pimpinan = event.spotlight?.pimpinan ?? [];
  const eyebrow = event.spotlight?.eyebrow;
  const headline = event.spotlight?.headline ?? event.title;
  const chatUrl = whatsappUrl(contacts[0], `Halo panitia IKA UNIMED, saya ingin bertanya tentang ${event.title}.`);
  const dateLine = [event.date_label, event.time_range_label ?? event.time_label].filter(Boolean).join(" · ");

  return (
    <section className="relative overflow-hidden bg-gradient-to-br from-[#0B3D2E] via-[#0B3D2E] to-[#006837]">
      <div
        className="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[#00A69D]/20 blur-3xl"
        aria-hidden="true"
      />
      <div
        className="pointer-events-none absolute -bottom-32 left-1/3 h-72 w-72 rounded-full bg-[#FFD700]/10 blur-3xl"
        aria-hidden="true"
      />

      <div className="container relative z-10 grid items-center gap-10 py-12 md:py-16 lg:grid-cols-2">
        <div>
          {eyebrow && (
            <p className="mb-3 text-xs font-extrabold uppercase tracking-[0.3em] text-[#FFD700]">{eyebrow}</p>
          )}
          <Heading className="text-3xl font-extrabold leading-tight text-white md:text-5xl">{headline}</Heading>
          {event.spotlight?.headline && event.spotlight.headline !== event.title && (
            <p className="mt-3 text-lg font-semibold text-[#00A69D] md:text-xl">{event.title}</p>
          )}

          <div className="mt-6 flex flex-col gap-2 text-sm text-white/90 md:text-base">
            {dateLine && (
              <span className="inline-flex items-center gap-2">
                <CalendarDays className="h-4 w-4 shrink-0 text-[#FFD700]" />
                {dateLine}
              </span>
            )}
            {event.location && (
              <span className="inline-flex items-center gap-2">
                <MapPin className="h-4 w-4 shrink-0 text-[#00A69D]" />
                {event.location}
              </span>
            )}
          </div>

          {!event.has_started && event.starts_at && (
            <div className="mt-6">
              <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-white/70">Dimulai dalam</p>
              <Countdown target={event.starts_at} />
            </div>
          )}

          <div className="mt-8 flex flex-wrap gap-3">
            {showPrimaryCta && (
              <EventLink
                href={event.cta_url}
                className="rounded-xl bg-[#FF7E00] px-6 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-orange-600"
              >
                {event.cta_label}
              </EventLink>
            )}
            {chatUrl && (
              <a
                href={chatUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 rounded-xl border-2 border-[#00A69D] px-6 py-2 text-sm font-semibold text-white transition hover:bg-[#00A69D]"
              >
                <MessageCircle className="h-4 w-4" />
                Chat panitia
              </a>
            )}
          </div>
        </div>

        {pimpinan.length > 0 && (
          <div
            className={`grid grid-cols-2 gap-3 sm:gap-4 ${
              pimpinan.length > 2 ? "sm:grid-cols-4" : "mx-auto w-full max-w-sm"
            }`}
          >
            {pimpinan.map((item) => (
              <PimpinanCard key={item.key} pimpinan={item} tone="dark" />
            ))}
          </div>
        )}
      </div>
    </section>
  );
};

export default SpotlightHero;
