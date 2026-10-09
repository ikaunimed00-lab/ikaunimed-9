import type { ScheduleItem } from "./types";

type Tone = "light" | "dark";

const tones: Record<Tone, { line: string; dot: string; time: string; label: string }> = {
  light: {
    line: "border-[#00A69D]/30",
    dot: "border-[#00A69D] bg-white",
    time: "text-[#006837]",
    label: "text-gray-700",
  },
  dark: {
    line: "border-[#FFD700]/40",
    dot: "border-[#FFD700] bg-[#0B3D2E]",
    time: "text-[#FFD700]",
    label: "text-white/90",
  },
};

type MiniTimelineProps = {
  items: ScheduleItem[];
  tone?: Tone;
};

const MiniTimeline = ({ items, tone = "light" }: MiniTimelineProps) => {
  if (items.length === 0) {
    return null;
  }

  const styles = tones[tone];

  return (
    <ol className={`relative flex flex-col gap-3 border-l-2 pl-5 ${styles.line}`}>
      {items.map((item, index) => (
        <li key={`${item.time}-${index}`} className="relative">
          <span className={`absolute -left-[27px] top-1.5 h-3 w-3 rounded-full border-2 ${styles.dot}`} />
          {item.time && (
            <span className={`block text-xs font-bold uppercase tracking-wide ${styles.time}`}>{item.time}</span>
          )}
          <span className={`text-sm ${styles.label}`}>{item.label}</span>
        </li>
      ))}
    </ol>
  );
};

export default MiniTimeline;
