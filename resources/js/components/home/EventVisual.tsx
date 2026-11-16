import type { PortalEvent } from "./types";

const datePart = (iso: string | null, options: Intl.DateTimeFormatOptions): string | null => {
  if (!iso) {
    return null;
  }

  return new Intl.DateTimeFormat("id-ID", { ...options, timeZone: "Asia/Jakarta" }).format(new Date(iso));
};

type EventVisualProps = {
  event: PortalEvent;
  className?: string;
};

const EventVisual = ({ event, className = "" }: EventVisualProps) => {
  if (event.image) {
    return (
      <picture className={`block overflow-hidden ${className}`}>
        {event.image_sm && <source media="(max-width: 640px)" srcSet={event.image_sm} />}
        <img src={event.image} alt={event.title} loading="lazy" className="h-full w-full object-cover" />
      </picture>
    );
  }

  return (
    <div
      className={`flex flex-col items-center justify-center bg-gradient-to-br from-[#006837] to-[#0B3D2E] text-center text-white ${className}`}
    >
      <span className="text-5xl font-extrabold leading-none">{datePart(event.starts_at, { day: "numeric" })}</span>
      <span className="mt-1 text-sm font-bold uppercase tracking-widest text-[#FFD700]">
        {datePart(event.starts_at, { month: "short" })}
      </span>
    </div>
  );
};

export default EventVisual;
