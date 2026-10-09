import { useEffect, useState } from "react";

const units = [
  { label: "Hari", size: 86_400_000 },
  { label: "Jam", size: 3_600_000 },
  { label: "Menit", size: 60_000 },
  { label: "Detik", size: 1_000 },
];

const Countdown = ({ target }: { target: string }) => {
  const targetTime = new Date(target).getTime();
  const [now, setNow] = useState<number | null>(null);

  useEffect(() => {
    setNow(Date.now());
    const timer = window.setInterval(() => setNow(Date.now()), 1000);

    return () => window.clearInterval(timer);
  }, [targetTime]);

  if (now === null || Number.isNaN(targetTime) || targetTime <= now) {
    return null;
  }

  let remaining = targetTime - now;
  const parts = units.map((unit) => {
    const value = Math.floor(remaining / unit.size);
    remaining -= value * unit.size;

    return { label: unit.label, value };
  });

  return (
    <div className="grid max-w-sm grid-cols-4 gap-2" aria-label="Hitung mundur menuju acara">
      {parts.map((part) => (
        <div key={part.label} className="rounded-xl bg-white/10 px-2 py-2 text-center backdrop-blur">
          <span className="block text-xl font-extrabold tabular-nums text-white sm:text-2xl">
            {String(part.value).padStart(2, "0")}
          </span>
          <span className="text-[10px] font-semibold uppercase tracking-wider text-[#FFD700]">{part.label}</span>
        </div>
      ))}
    </div>
  );
};

export default Countdown;
