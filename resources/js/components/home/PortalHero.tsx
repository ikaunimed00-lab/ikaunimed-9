import EventLink from "./EventLink";
import SpotlightHero from "./SpotlightHero";
import TeacherHero from "./TeacherHero";
import { isEmphasized } from "./groupEvents";
import type { PortalEvent, ReuniContact } from "./types";

const PortalHero = ({ hero, contacts }: { hero: PortalEvent; contacts: ReuniContact[] }) => {
  if (!hero.is_fallback && isEmphasized(hero)) {
    return <TeacherHero event={hero} />;
  }

  if (!hero.is_fallback) {
    return <SpotlightHero event={hero} contacts={contacts} />;
  }

  return (
    <section className="bg-gradient-to-br from-[#0B3D2E] via-[#0B3D2E] to-[#006837]">
      <div className="container flex min-h-[360px] flex-col justify-center py-14">
        <p className="mb-3 text-[10px] font-bold uppercase tracking-[0.3em] text-[#FFD700]">Ikatan Alumni UNIMED</p>
        <h1 className="max-w-3xl text-3xl font-extrabold leading-tight text-white md:text-5xl">{hero.title}</h1>
        <p className="mt-4 max-w-2xl text-base text-white/85 md:text-lg">{hero.summary}</p>
        <div className="mt-8 flex flex-wrap gap-3">
          <EventLink
            href={hero.cta_url}
            className="rounded-xl bg-[#FF7E00] px-6 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-orange-600"
          >
            {hero.cta_label}
          </EventLink>
          {hero.secondary_cta_url && (
            <EventLink
              href={hero.secondary_cta_url}
              className="rounded-xl border-2 border-[#00A69D] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#00A69D]"
            >
              {hero.secondary_cta_label || "Selengkapnya"}
            </EventLink>
          )}
        </div>
      </div>
    </section>
  );
};

export default PortalHero;
