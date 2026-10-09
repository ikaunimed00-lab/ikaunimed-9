export type ScheduleItem = {
  time: string | null;
  label: string;
};

export type KegiatanTheme = "hijau" | "navy-emas";

export type Pimpinan = {
  key: string;
  name: string;
  position: string;
  image: string;
  image_sm: string | null;
  image_zoom: number;
};

export type HeroSpotlight = {
  eyebrow: string | null;
  headline: string | null;
  pimpinan: Pimpinan[];
};

export type PortalEvent = {
  id: number;
  slug: string;
  title: string;
  summary: string;
  location: string | null;
  image: string | null;
  image_sm: string | null;
  route_map_image: string | null;
  hero_image: string | null;
  hero_image_mobile: string | null;
  theme: KegiatanTheme;
  cta_label: string;
  cta_url: string;
  secondary_cta_label?: string | null;
  secondary_cta_url?: string | null;
  featured: boolean;
  is_fallback: boolean;
  starts_at: string | null;
  ends_at: string | null;
  date_label: string | null;
  time_label: string | null;
  time_range_label: string | null;
  group_key: string;
  is_multi_day: boolean;
  has_started: boolean;
  schedule: ScheduleItem[];
  spotlight: HeroSpotlight | null;
};

export type PortalNews = {
  id: number;
  title: string;
  excerpt: string | null;
  slug: string;
  image: string | null;
  view_count: number;
  author?: { name?: string | null };
  published_at: string | null;
};

export type PortalService = {
  title: string;
  description: string;
  href: string;
  icon: string;
};

export type ReuniContact = {
  name: string;
  role?: string | null;
  whatsapp?: string | null;
};
