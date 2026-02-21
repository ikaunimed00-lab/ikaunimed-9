import { MapPin, ShoppingBag, GraduationCap, TrendingUp, Heart } from "lucide-react";

const TopBar = () => {
  const links = [
    { 
      label: "Shop", 
      href: "#", 
      icon: <ShoppingBag className="w-3 h-3 text-orange-400" /> 
    },
    { 
      label: "Beasiswa", 
      href: "#", 
      icon: <GraduationCap className="w-3 h-3 text-sky-400" /> 
    },
    { 
      label: "Skills Up", 
      href: "#", 
      isNew: true, 
      icon: <TrendingUp className="w-3 h-3 text-emerald-400" /> 
    },
    { 
      label: "Donasi", 
      href: "#", 
      icon: <Heart className="w-3 h-3 text-red-500 fill-red-500" /> 
    },
  ];

  return (
    <div className="bg-foreground text-primary-foreground py-2 text-xs md:text-sm">
      <div className="container flex items-center justify-between gap-4">
        {/* Navigasi Kiri: Scrollable di mobile */}
        <nav className="flex items-center gap-4 overflow-x-auto no-scrollbar pb-1 md:pb-0">
          {links.map((link, index) => (
            <a
              key={link.label}
              href={link.href}
              className="group flex items-center gap-1.5 hover:text-primary transition-colors whitespace-nowrap"
            >
              <span className="transition-transform group-hover:scale-110">
                {link.icon}
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
          href="#" 
          className="group flex items-center gap-2 hover:text-primary transition-colors whitespace-nowrap"
        >
          <MapPin className="w-3 h-3 md:w-4 md:h-4 text-oxygen-teal transition-colors group-hover:text-primary" />
          <span className="hidden sm:inline font-medium">Cek Area</span>
        </a>
      </div>
    </div>
  );
};

export default TopBar;
