import { BookOpen } from "lucide-react";

type GuidebookLinkProps = {
  href: string;
  tone?: "light" | "dark";
  variant?: "button" | "text";
  size?: "md" | "lg";
  label?: string;
  className?: string;
};

const buttonTones = {
  light: "border-[#006837] bg-white text-[#006837] hover:bg-[#E7F5E9]",
  dark: "border-white/70 text-white hover:bg-white/10",
};

const buttonSizes = {
  md: "px-5 py-2.5 text-sm",
  lg: "px-6 py-3 text-base",
};

const textTones = {
  light: "text-[#006837]",
  dark: "text-white",
};

const GuidebookLink = ({
  href,
  tone = "light",
  variant = "button",
  size = "md",
  label = "Unduh buku panduan",
  className = "",
}: GuidebookLinkProps) => {
  const style =
    variant === "button"
      ? `inline-flex items-center justify-center gap-2 rounded-xl border-2 font-bold transition ${buttonTones[tone]} ${buttonSizes[size]}`
      : `inline-flex items-center gap-1.5 text-sm font-semibold underline underline-offset-4 ${textTones[tone]}`;

  return (
    <a
      href={href}
      target="_blank"
      rel="noopener noreferrer"
      aria-label={`${label} Teacher of Champions (Google Drive, tab baru)`}
      className={`${style} focus:outline-none focus-visible:ring-4 focus-visible:ring-[#FFD700]/60 ${className}`}
    >
      <BookOpen className="h-4 w-4 shrink-0" aria-hidden="true" />
      {label}
    </a>
  );
};

export default GuidebookLink;
