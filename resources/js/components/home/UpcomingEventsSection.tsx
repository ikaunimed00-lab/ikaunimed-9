import { Clock, MapPin } from "lucide-react";
import EventLink from "./EventLink";
import EventVisual from "./EventVisual";
import { eventInfoUrl, groupEvents, isEmphasized } from "./groupEvents";
import type { PortalEvent } from "./types";

const EventCard = ({ event }: { event: PortalEvent }) => {
  const emphasized = isEmphasized(event);
  const infoUrl = eventInfoUrl(event);

  return (
    <article
      className={`relative flex flex-col overflow-hidden rounded-2xl border shadow-sm sm:flex-row ${
        emphasized
          ? "border-[#FFD700]/30 bg-gradient-to-br from-[#0B3D2E] to-[#006837]"
          : "border-gray-100 bg-white"
      }`}
    >
      <span
        className={`absolute -left-[29px] top-6 hidden h-3.5 w-3.5 rounded-full border-2 md:block ${
          emphasized ? "border-[#FFD700] bg-[#0B3D2E]" : "border-[#00A69D] bg-white"
        }`}
      />
      <EventVisual event={event} className="aspect-[16/9] w-full sm:aspect-auto sm:w-48 sm:shrink-0" />
      <div className="flex flex-1 flex-col gap-2 p-5">
        <h3 className={`text-lg font-bold ${emphasized ? "text-white" : "text-gray-800"}`}>{event.title}</h3>
        <p className={`text-sm ${emphasized ? "text-white/85" : "text-gray-600"}`}>{event.summary}</p>
        <div className="flex flex-wrap gap-x-4 gap-y-1 text-sm">
          {event.time_label && (
            <span
              className={`inline-flex items-center gap-1.5 font-semibold ${
                emphasized ? "text-[#FFD700]" : "text-[#006837]"
              }`}
            >
              <Clock className="h-4 w-4" />
              {event.time_label}
            </span>
          )}
          {event.location && (
            <span className={`inline-flex items-center gap-1.5 ${emphasized ? "text-white/85" : "text-gray-500"}`}>
              <MapPin className={`h-4 w-4 shrink-0 ${emphasized ? "text-[#FFD700]" : ""}`} />
              {event.location}
            </span>
          )}
        </div>
        <div className="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2">
          <EventLink
            href={event.cta_url}
            className={`inline-flex rounded-xl px-4 py-2 text-sm font-semibold ${
              emphasized
                ? "bg-[#FFD700] font-bold text-[#0B3D2E] hover:bg-[#FFE34D]"
                : "bg-[#FF7E00] text-white hover:bg-orange-600"
            }`}
          >
            {event.cta_label}
          </EventLink>
          {infoUrl && (
            <EventLink
              href={infoUrl}
              className={`text-sm font-semibold underline underline-offset-4 ${emphasized ? "text-white" : "text-[#006837]"}`}
            >
              Info lengkap
            </EventLink>
          )}
        </div>
      </div>
    </article>
  );
};

const UpcomingEventsSection = ({ events }: { events: PortalEvent[] }) => {
  if (events.length === 0) {
    return null;
  }

  return (
    <section id="agenda" className="bg-[#F8FAF9] py-12 md:py-16">
      <div className="container">
        <div className="mb-10 text-center">
          <p className="mb-2 text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Agenda</p>
          <h2 className="text-3xl font-extrabold text-gray-800">Kegiatan terdekat</h2>
        </div>

        <div className="mx-auto flex max-w-5xl flex-col gap-10">
          {groupEvents(events).map((group) => (
            <div key={group.key} className="md:grid md:grid-cols-[200px_minmax(0,1fr)] md:gap-8">
              <div className="mb-4 md:mb-0 md:pt-2 md:text-right">
                <p className="text-xs font-bold uppercase tracking-wider text-[#00A69D]">
                  {group.isMultiDay ? "Periode" : "Tanggal"}
                </p>
                <p className="text-lg font-extrabold text-[#0B3D2E]">{group.label}</p>
              </div>

              <div className="flex flex-col gap-4 md:border-l-2 md:border-[#00A69D]/30 md:pl-5">
                {group.events.map((event) => (
                  <EventCard key={event.id} event={event} />
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default UpcomingEventsSection;
