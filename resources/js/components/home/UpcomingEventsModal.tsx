import { useEffect, useState } from "react";
import { Clock, MapPin } from "lucide-react";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import GuidebookLink from "@/components/toc/GuidebookLink";
import EventLink from "./EventLink";
import { eventInfoUrl, groupEvents, isEmphasized } from "./groupEvents";
import MiniTimeline from "./MiniTimeline";
import type { PortalEvent } from "./types";

const storageKey = () => {
  const today = new Intl.DateTimeFormat("en-CA", { timeZone: "Asia/Jakarta" }).format(new Date());

  return `ikaunimed.events-modal.dismissed.${today}`;
};

const UpcomingEventsModal = ({ events }: { events: PortalEvent[] }) => {
  const [open, setOpen] = useState(false);

  useEffect(() => {
    if (events.length === 0) {
      return;
    }

    try {
      if (window.localStorage.getItem(storageKey())) {
        return;
      }
    } catch {
      // Storage can be blocked (private mode); the modal still opens.
    }

    setOpen(true);
  }, [events.length]);

  const handleOpenChange = (nextOpen: boolean) => {
    setOpen(nextOpen);

    if (!nextOpen) {
      try {
        window.localStorage.setItem(storageKey(), "1");
      } catch {
        // Storage can be blocked (private mode).
      }
    }
  };

  if (events.length === 0) {
    return null;
  }

  return (
    <Dialog open={open} onOpenChange={handleOpenChange}>
      <DialogContent className="max-h-[90vh] w-[calc(100%-2rem)] max-w-2xl overflow-y-auto rounded-2xl">
        <DialogHeader>
          <DialogTitle>Kegiatan IKA UNIMED terdekat</DialogTitle>
          <DialogDescription>Jadwal lengkap juga tersedia di bagian Agenda pada halaman ini.</DialogDescription>
        </DialogHeader>

        <div className="flex flex-col gap-5">
          {groupEvents(events).map((group) => (
            <div key={group.key}>
              <p className="mb-2 text-xs font-bold uppercase tracking-wider text-[#00A69D]">{group.label}</p>
              <div className="flex flex-col gap-3">
                {group.events.map((event) => {
                  const emphasized = isEmphasized(event);
                  const infoUrl = eventInfoUrl(event);

                  return (
                    <div
                      key={event.id}
                      className={`rounded-xl border p-4 ${
                        emphasized
                          ? "border-[#FFD700]/30 bg-gradient-to-br from-[#0B3D2E] to-[#006837]"
                          : "border-gray-100"
                      }`}
                    >
                      <h3 className={`font-bold ${emphasized ? "text-white" : "text-gray-800"}`}>{event.title}</h3>
                      <div className="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm">
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
                      {event.is_multi_day && event.schedule.length > 0 && (
                        <div className="mt-3">
                          <MiniTimeline items={event.schedule} tone={emphasized ? "dark" : "light"} />
                        </div>
                      )}
                      <div className="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">
                        <EventLink
                          href={event.cta_url}
                          className={`inline-flex rounded-lg px-3 py-1.5 text-sm font-semibold ${
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
                            className={`text-sm font-semibold underline underline-offset-4 ${
                              emphasized ? "text-white" : "text-[#006837]"
                            }`}
                          >
                            Info lengkap
                          </EventLink>
                        )}
                        {event.guide_url && (
                          <GuidebookLink
                            href={event.guide_url}
                            variant="text"
                            tone={emphasized ? "dark" : "light"}
                            label="Buku panduan"
                          />
                        )}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          ))}
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default UpcomingEventsModal;
