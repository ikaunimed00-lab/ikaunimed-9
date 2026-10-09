import type { PortalEvent, ReuniContact } from "./types";

export type EventGroup = {
  key: string;
  label: string | null;
  isMultiDay: boolean;
  events: PortalEvent[];
};

/**
 * Single-day groups (concrete dates) come before ongoing multi-day programs.
 */
export const groupEvents = (events: PortalEvent[]): EventGroup[] => {
  const groups: EventGroup[] = [];

  for (const event of events) {
    const existing = groups.find((group) => group.key === event.group_key);

    if (existing) {
      existing.events.push(event);
      continue;
    }

    groups.push({
      key: event.group_key,
      label: event.date_label,
      isMultiDay: event.is_multi_day,
      events: [event],
    });
  }

  return [...groups.filter((group) => !group.isMultiDay), ...groups.filter((group) => group.isMultiDay)];
};

export const isExternalUrl = (url: string): boolean => /^https?:\/\//.test(url);

export const whatsappUrl = (contact: ReuniContact | undefined, message?: string): string | null => {
  if (!contact?.whatsapp) {
    return null;
  }

  const text = message ? `?text=${encodeURIComponent(message)}` : "";

  return `https://wa.me/${contact.whatsapp.replace(/\D/g, "")}${text}`;
};

/**
 * The "navy-emas" theme value is kept for existing records but now renders as
 * the emphasized dark-green + yellow variant of the site palette.
 */
export const isEmphasized = (event: Pick<PortalEvent, "theme">): boolean => event.theme === "navy-emas";
