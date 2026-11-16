import type { Pimpinan } from "./types";

type PimpinanCardProps = {
  pimpinan: Pimpinan;
  tone?: "light" | "dark";
};

const PimpinanCard = ({ pimpinan, tone = "light" }: PimpinanCardProps) => {
  const isDark = tone === "dark";

  return (
    <figure className="flex flex-col gap-2">
      <div
        className={`aspect-[4/5] overflow-hidden rounded-2xl border-4 shadow-lg ${
          isDark ? "border-white/90 bg-white/10" : "border-white bg-[#E6EAE8] ring-1 ring-gray-200"
        }`}
      >
        <picture>
          {pimpinan.image_sm && <source media="(max-width: 640px)" srcSet={pimpinan.image_sm} />}
          <img
            src={pimpinan.image}
            alt={pimpinan.name}
            loading="lazy"
            className="h-full w-full origin-top object-cover object-top"
            style={pimpinan.image_zoom > 1 ? { transform: `scale(${pimpinan.image_zoom})` } : undefined}
          />
        </picture>
      </div>
      <figcaption className="text-center">
        <span className={`block text-xs font-bold leading-snug sm:text-sm ${isDark ? "text-white" : "text-gray-800"}`}>
          {pimpinan.name}
        </span>
        <span className={`block text-[11px] leading-snug sm:text-xs ${isDark ? "text-[#FFD700]" : "text-[#006837]"}`}>
          {pimpinan.position}
        </span>
      </figcaption>
    </figure>
  );
};

export default PimpinanCard;
