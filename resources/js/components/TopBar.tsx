import { MapPin, ShoppingBag, GraduationCap, TrendingUp, Heart } from "lucide-react";
import React from "react";
import { usePage } from "@inertiajs/react";

const iconMap = {
  ShoppingBag: <ShoppingBag className="w-3 h-3 text-orange-400" />,
  GraduationCap: <GraduationCap className="w-3 h-3 text-sky-400" />,
  TrendingUp: <TrendingUp className="w-3 h-3 text-emerald-400" />,
  Heart: <Heart className="w-3 h-3 text-red-500 fill-red-500" />,
};

const TopBar = ({ content }: { content?: any }) => {
  const { topbar } = usePage().props as any;
  const finalContent = content || topbar;
  const links = finalContent?.links || [];
  const rightLabel = finalContent?.right_label || "Cek Area";
  const rightHref = finalContent?.right_href || "#";

  if (!links || links.length === 0) return null;

  return (
    <div className="bg-foreground text-primary-foreground py-2 text-xs md:text-sm">
      <div className="container flex items-center justify-between gap-4">
        {/* Navigasi Kiri: Scrollable di mobile */}
        <nav className="flex items-center gap-4 overflow-x-auto no-scrollbar pb-1 md:pb-0">
          {links.map((link: any, index: number) => (
            <a
              key={link.label || `topbar-link-${index}`}
              href={link.href}
              className="group flex items-center gap-1.5 hover:text-primary transition-colors whitespace-nowrap"
            >
              <span className="transition-transform group-hover:scale-110">
                {iconMap[link.icon as keyof typeof iconMap] || <ShoppingBag className="w-3 h-3" />}
              </span>
              <span>{link.label}</span>
              {link.isNew && (
                <span className="text-primary font-bold animate-pulse text-[10px] md:text-xs">*</span>
              )}
              {index < links.length - 1 && (
                <span className="ml-2 text-gray-700 hidden md:inline">|</span>
              )}
            </a>
          ))}
        </nav>
        
        {/* Tombol Kanan: Cek Area */}
        <a 
          href={rightHref}
          className="group flex items-center gap-2 hover:text-primary transition-colors whitespace-nowrap"
        >
          <MapPin className="w-3 h-3 md:w-4 md:h-4 text-oxygen-teal transition-colors group-hover:text-primary" />
          <span className="hidden sm:inline font-medium">{rightLabel}</span>
        </a>
      </div>
    </div>
  );
};

export default TopBar;
