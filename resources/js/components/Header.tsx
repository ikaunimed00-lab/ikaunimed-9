import { useState } from "react";
import { ChevronDown, Menu, X, LogOut, LayoutDashboard, User, Briefcase, HeartHandshake, BookOpen, Users, Home, Building2, Newspaper, Calendar, Award, GraduationCap, MapPin, CreditCard, Vote, FileCheck, Handshake, TrendingUp, Image as ImageIcon, Video, HelpCircle, ShoppingCart } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Link, usePage, router } from "@inertiajs/react";
import { cn } from "@/lib/utils";
import React from "react";

const Header = () => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [activeMegaMenu, setActiveMegaMenu] = useState<string | null>(null);
  const { auth, cart } = usePage().props as any;
  const user = auth?.user;
  const cartCount = cart?.item_count ?? 0;

  const handleLogout = () => {
    router.post('/logout');
  };

  const megaMenuData = [
    {
      id: "tentang",
      label: "Tentang Kami",
      icon: <User className="w-4 h-4" />,
      color: "primary",
      sections: [
        {
          title: "Profil Organisasi",
          icon: <Building2 className="w-5 h-5" />,
          items: [
            { title: "Tentang IKA UNIMED", href: "/tentang-kami", description: "Sejarah dan profil Ikatan Alumni", icon: <Home className="w-4 h-4" /> },
            { title: "Visi & Misi", href: "/tentang-kami", description: "Arah dan tujuan organisasi", icon: <Award className="w-4 h-4" /> },
          ]
        },
        {
          title: "Struktur",
          icon: <Users className="w-5 h-5" />,
          items: [
            { title: "Struktur Organisasi", href: "/struktur-organisasi", description: "Susunan pengurus IKA UNIMED", icon: <Users className="w-4 h-4" /> },
            { title: "Organisasi", href: "/organisasi", description: "Direktori PP, DPW, dan DPC", icon: <Building2 className="w-4 h-4" /> },
          ]
        }
      ]
    },
    {
      id: "komunitas",
      label: "Komunitas",
      icon: <Users className="w-4 h-4" />,
      color: "oxygen-teal",
      sections: [
        {
          title: "Berita & Kegiatan",
          icon: <Newspaper className="w-5 h-5" />,
          items: [
            { title: "Kabar Alumni", href: "/kabar-alumni", description: "Berita dan cerita dari alumni", icon: <Newspaper className="w-4 h-4" /> },
            { title: "Agenda", href: "/agenda", description: "Kegiatan dan acara mendatang", icon: <Calendar className="w-4 h-4" /> },
          ]
        },
        {
          title: "Kontribusi",
          icon: <HeartHandshake className="w-5 h-5" />,
          items: [
            { title: "Ruang Pengabdian", href: "/pengabdian", description: "Kontribusi untuk masyarakat", icon: <HeartHandshake className="w-4 h-4" /> },
            { title: "E-Voting", href: "/voting", description: "Pemilihan ketua dan pengurus", icon: <Vote className="w-4 h-4" /> },
          ]
        }
      ]
    },
    {
      id: "karir",
      label: "Karir & Profesional",
      icon: <Briefcase className="w-4 h-4" />,
      color: "ika-yellow",
      sections: [
        {
          title: "Peluang Karir",
          icon: <Briefcase className="w-5 h-5" />,
          items: [
            { title: "Lowongan Kerja", href: "/lowongan-kerja", description: "Informasi karir untuk alumni", icon: <Briefcase className="w-4 h-4" /> },
            { title: "Kemitraan", href: "/kemitraan", description: "Kerjasama strategis", icon: <Handshake className="w-4 h-4" /> },
          ]
        },
        {
          title: "Pengembangan Diri",
          icon: <TrendingUp className="w-5 h-5" />,
          items: [
            { title: "Beasiswa", href: "/beasiswa", description: "Peluang studi lanjut", icon: <GraduationCap className="w-4 h-4" /> },
            { title: "Kelas & Kursus", href: "/courses", description: "Katalog kelas online", icon: <BookOpen className="w-4 h-4" /> },
            { title: "Micro Learning", href: "/learning", description: "Skill dan kompetensi", icon: <TrendingUp className="w-4 h-4" /> },
          ]
        }
      ]
    },
    {
      id: "layanan",
      label: "Layanan",
      icon: <HeartHandshake className="w-4 h-4" />,
      color: "primary",
      sections: [
        {
          title: "Layanan Digital",
          icon: <FileCheck className="w-5 h-5" />,
          items: [
            { title: "Legalisir Ijazah", href: "/legalization", description: "Layanan legalisir online", icon: <FileCheck className="w-4 h-4" /> },
            { title: "Kartu Alumni", href: "/kartu-alumni", description: "Identitas keanggotaan digital", icon: <CreditCard className="w-4 h-4" /> },
          ]
        },
        {
          title: "Dukungan",
          icon: <HeartHandshake className="w-5 h-5" />,
          items: [
            { title: "Donasi", href: "/donasi", description: "Dukungan untuk almamater", icon: <HeartHandshake className="w-4 h-4" /> },
          ]
        }
      ]
    },
    {
      id: "informasi",
      label: "Informasi",
      icon: <BookOpen className="w-4 h-4" />,
      color: "oxygen-teal",
      sections: [
        {
          title: "Berita & Update",
          icon: <Newspaper className="w-5 h-5" />,
          items: [
            { title: "Berita Terkini", href: "/news", description: "Update seputar kampus", icon: <Newspaper className="w-4 h-4" /> },
            { title: "FAQ", href: "/faq", description: "Pertanyaan umum", icon: <HelpCircle className="w-4 h-4" /> },
          ]
        },
        {
          title: "Media",
          icon: <ImageIcon className="w-5 h-5" />,
          items: [
            { title: "Galeri Foto", href: "/media/foto", description: "Dokumentasi kegiatan", icon: <ImageIcon className="w-4 h-4" /> },
            { title: "Galeri Video", href: "/media/video", description: "Video profil dan kegiatan", icon: <Video className="w-4 h-4" /> },
          ]
        }
      ]
    },
  ];

  const getColorClasses = (color: string) => {
    const colors = {
      'primary': {
        bg: 'bg-primary',
        text: 'text-primary',
        hover: 'hover:bg-primary/10',
        border: 'border-primary',
        gradient: 'from-primary/20 to-primary/5'
      },
      'oxygen-teal': {
        bg: 'bg-oxygen-teal',
        text: 'text-oxygen-teal',
        hover: 'hover:bg-oxygen-teal/10',
        border: 'border-oxygen-teal',
        gradient: 'from-oxygen-teal/20 to-oxygen-teal/5'
      },
      'ika-yellow': {
        bg: 'bg-ika-yellow',
        text: 'text-ika-yellow',
        hover: 'hover:bg-ika-yellow/10',
        border: 'border-ika-yellow',
        gradient: 'from-ika-yellow/20 to-ika-yellow/5'
      }
    };
    return colors[color as keyof typeof colors] || colors.primary;
  };

  return (
    <header className="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm transition-all duration-300">
      <div className="container flex items-center justify-between h-16 md:h-20">
        
        {/* Logo IKA UNIMED */}
        <Link href="/" className="flex items-center gap-3 group">
          <img 
            src="/images/favicon_ikaunimed.png" 
            alt="Logo IKA UNIMED" 
            className="h-9 md:h-12 w-auto transition-transform group-hover:scale-110" 
          />
          <div className="flex flex-col leading-none">
            <div className="text-xl md:text-2xl font-bold font-sans tracking-tighter flex items-center">
              <span className="text-primary">IKA</span>
              <span className="text-oxygen-teal">UNI</span>
              <span style={{ color: '#FFD700' }}>MED</span>
            </div>
            <span className="text-[7px] md:text-[9px] text-gray-500 mt-1 font-bold uppercase tracking-widest">
              Connect, Collaborate, Contribute
            </span>
          </div>
        </Link>

        {/* Mega Menu Desktop */}
        <div className="hidden lg:flex items-center gap-2"> 
          <nav className="flex items-center">
            {/* Home Link */}
            <Link 
              href="/"
              className="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-primary transition-colors rounded-lg hover:bg-gray-50"
            >
              Home
            </Link>

            {/* Kelas & Kursus Link */}
            <Link 
              href="/courses"
              className="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-primary transition-colors rounded-lg hover:bg-gray-50"
            >
              Kelas & Kursus
            </Link>

            {/* Mega Menu Items */}
            {megaMenuData.map((menu) => {
              const colorClasses = getColorClasses(menu.color);
              const isActive = activeMegaMenu === menu.id;
              
              return (
                <div 
                  key={menu.id}
                  className="relative"
                  onMouseEnter={() => setActiveMegaMenu(menu.id)}
                  onMouseLeave={() => setActiveMegaMenu(null)}
                >
                  <button
                    className={cn(
                      "px-4 py-2 text-sm font-semibold flex items-center gap-2 transition-all rounded-lg",
                      isActive 
                        ? `${colorClasses.text} bg-gray-50` 
                        : "text-gray-700 hover:text-gray-900 hover:bg-gray-50"
                    )}
                  >
                    {menu.label}
                    <ChevronDown className={cn(
                      "w-4 h-4 transition-transform",
                      isActive && "rotate-180"
                    )} />
                  </button>

                  {/* Mega Menu Dropdown */}
                  {isActive && (
                    <div className="absolute top-full left-0 mt-2 w-[600px] bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden animate-in slide-in-from-top-4 duration-200">
                      {/* Header dengan Gradient */}
                      <div className={cn(
                        "px-6 py-4 bg-gradient-to-r",
                        `${colorClasses.gradient} border-b border-gray-100`
                      )}>
                        <div className="flex items-center gap-3">
                          <div className={cn(
                            "w-10 h-10 rounded-xl flex items-center justify-center",
                            colorClasses.bg
                          )}>
                            <span className="text-white text-lg">{menu.icon}</span>
                          </div>
                          <div>
                            <h3 className="font-bold text-gray-900">{menu.label}</h3>
                            <p className="text-xs text-gray-500">Layanan dan Informasi</p>
                          </div>
                        </div>
                      </div>

                      {/* Content Grid */}
                      <div className="grid grid-cols-2 gap-6 p-6">
                        {menu.sections.map((section, sectionIdx) => (
                          <div key={sectionIdx}>
                            {/* Section Title */}
                            <div className="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100">
                              <span className={colorClasses.text}>
                                {section.icon}
                              </span>
                              <h4 className="font-bold text-sm text-gray-800">
                                {section.title}
                              </h4>
                            </div>

                            {/* Section Items */}
                            <div className="space-y-2">
                              {section.items.map((item, itemIdx) => (
                                <Link
                                  key={itemIdx}
                                  href={item.href}
                                  className={cn(
                                    "block p-3 rounded-lg transition-all group",
                                    colorClasses.hover
                                  )}
                                >
                                  <div className="flex items-start gap-3">
                                    <span className={cn(
                                      "mt-0.5 transition-colors",
                                      colorClasses.text,
                                      "group-hover:scale-110 transition-transform"
                                    )}>
                                      {item.icon}
                                    </span>
                                    <div className="flex-1 min-w-0">
                                      <div className="font-semibold text-sm text-gray-900 group-hover:text-gray-900 mb-0.5">
                                        {item.title}
                                      </div>
                                      <p className="text-xs text-gray-500 line-clamp-1">
                                        {item.description}
                                      </p>
                                    </div>
                                  </div>
                                </Link>
                              ))}
                            </div>
                          </div>
                        ))}
                      </div>

                      {/* Footer dengan Aksen Warna */}
                      <div className={cn(
                        "px-6 py-3 bg-gradient-to-r",
                        `${colorClasses.gradient} border-t border-gray-100`
                      )}>
                        <p className="text-xs text-gray-600 text-center">
                          Temukan lebih banyak layanan di dashboard Anda
                        </p>
                      </div>
                    </div>
                  )}
                </div>
              );
            })}

            {/* Hubungi Kami Link */}
            <Link 
              href="/hubungi-kami"
              className="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-primary transition-colors rounded-lg hover:bg-gray-50"
            >
              Hubungi Kami
            </Link>
          </nav>

          {/* Tombol CTA */}
          <div className="flex items-center gap-3 border-l-2 border-slate-100 pl-6 ml-4">
            <Link href="/shop/cart" className="relative inline-flex items-center justify-center">
              <Button
                variant="outline"
                size="icon"
                className="relative h-9 w-9 rounded-full border-2 border-oxygen-teal text-oxygen-teal hover:bg-oxygen-teal hover:text-white transition-all"
              >
                <ShoppingCart className="w-4 h-4" />
                {cartCount > 0 && (
                  <span className="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                    {cartCount}
                  </span>
                )}
              </Button>
            </Link>

            {user ? (
              <>
                {['admin', 'editor', 'writer'].includes(user.role) ? (
                  <a href="/admin">
                    <Button 
                      className="flex items-center gap-2 bg-gradient-to-r from-oxygen-teal to-[#00C2B6] hover:to-oxygen-teal text-white text-sm px-6 py-2.5 font-extrabold rounded-full shadow-[0_4px_14px_0_rgba(0,166,157,0.39)] transition-all duration-300 transform hover:scale-105"
                    >
                      <LayoutDashboard className="w-4 h-4" />
                      DASHBOARD
                    </Button>
                  </a>
                ) : (
                  <Link href={user.role === 'subscriber' ? '/dashboard/subscriber' : '/dashboard'}>
                    <Button 
                      className="flex items-center gap-2 bg-gradient-to-r from-oxygen-teal to-[#00C2B6] hover:to-oxygen-teal text-white text-sm px-6 py-2.5 font-extrabold rounded-full shadow-[0_4px_14px_0_rgba(0,166,157,0.39)] transition-all duration-300 transform hover:scale-105"
                    >
                      <LayoutDashboard className="w-4 h-4" />
                      DASHBOARD
                    </Button>
                  </Link>
                )}
                
                <Button 
                  onClick={handleLogout}
                  variant="outline" 
                  className="flex items-center gap-2 bg-white border-2 border-red-500 text-red-500 hover:bg-red-500 hover:text-white text-sm px-4 py-2.5 font-extrabold rounded-full transition-all duration-300 shadow-sm cursor-pointer"
                >
                  <LogOut className="w-4 h-4" />
                </Button>
              </>
            ) : (
              <>
                <Link href="/register">
                  <Button 
                    className="bg-gradient-to-r from-oxygen-teal to-[#00C2B6] hover:to-oxygen-teal text-white text-sm px-6 py-2.5 font-extrabold rounded-full shadow-[0_4px_14px_0_rgba(0,166,157,0.39)] transition-all duration-300 transform hover:scale-105"
                  >
                    DAFTAR
                  </Button>
                </Link>
                
                <Link href="/login">
                  <Button 
                    variant="outline" 
                    className="bg-white border-2 border-primary text-primary hover:bg-primary hover:text-white text-sm px-6 py-2.5 font-extrabold rounded-full transition-all duration-300 shadow-sm"
                  >
                    LOGIN
                  </Button>
                </Link>
              </>
            )}
          </div>
        </div>

        {/* Mobile Menu Button */}
        <button
          className={`lg:hidden p-2.5 rounded-2xl transition-all duration-300 ${
            isMobileMenuOpen ? "bg-oxygen-teal text-white rotate-90" : "bg-white border-2 border-slate-100 text-oxygen-teal"
          }`}
          onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
        >
          {isMobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
        </button>
      </div>

      {/* Mobile Menu Overlay */}
      {isMobileMenuOpen && (
        <div className="lg:hidden bg-white border-t-2 border-slate-50 shadow-2xl animate-in slide-in-from-top-4 duration-500 max-h-[80vh] overflow-y-auto">
          <nav className="container py-6 space-y-2">
            <Link href="/" className="flex items-center px-4 py-3 text-gray-800 bg-slate-50 rounded-xl font-bold">
              <Home className="w-5 h-5 mr-3 text-primary" />
              Home
            </Link>
            
            {megaMenuData.map((menu) => {
              const colorClasses = getColorClasses(menu.color);
              
              return (
                <div key={menu.id} className="space-y-1">
                  <div className={cn(
                    "px-4 py-3 rounded-xl flex items-center gap-3",
                    `bg-gradient-to-r ${colorClasses.gradient}`
                  )}>
                    <span className={colorClasses.text}>
                      {menu.icon}
                    </span>
                    <span className="text-sm font-bold text-gray-800 uppercase tracking-wider">
                      {menu.label}
                    </span>
                  </div>
                  
                  {menu.sections.map((section) => (
                    <div key={section.title} className="ml-4">
                      <div className="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {section.title}
                      </div>
                      {section.items.map((item) => (
                        <Link 
                          key={item.title} 
                          href={item.href}
                          className={cn(
                            "flex items-center gap-3 px-6 py-2.5 text-gray-700 rounded-lg transition-all",
                            colorClasses.hover
                          )}
                        >
                          <span className={colorClasses.text}>
                            {item.icon}
                          </span>
                          <span className="text-sm">{item.title}</span>
                        </Link>
                      ))}
                    </div>
                  ))}
                </div>
              );
            })}

            <Link href="/hubungi-kami" className="flex items-center px-4 py-3 text-gray-800 bg-slate-50 rounded-xl font-bold">
              <MapPin className="w-5 h-5 mr-3 text-oxygen-teal" />
              Hubungi Kami
            </Link>

            <div className="flex flex-col gap-4 pt-6 px-4">
              {user ? (
                <>
                  {['admin', 'editor', 'writer'].includes(user.role) ? (
                    <a href="/admin">
                      <Button className="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-oxygen-teal to-[#00C2B6] text-white py-4 font-extrabold rounded-xl">
                        <LayoutDashboard className="w-5 h-5" />
                        DASHBOARD
                      </Button>
                    </a>
                  ) : (
                    <Link href={user.role === 'subscriber' ? '/dashboard/subscriber' : '/dashboard'}>
                      <Button className="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-oxygen-teal to-[#00C2B6] text-white py-4 font-extrabold rounded-xl">
                        <LayoutDashboard className="w-5 h-5" />
                        DASHBOARD
                      </Button>
                    </Link>
                  )}
                  <Button 
                    onClick={handleLogout}
                    className="w-full flex items-center justify-center gap-2 bg-red-500 text-white py-4 font-extrabold rounded-xl hover:bg-red-600 cursor-pointer"
                  >
                    <LogOut className="w-5 h-5" />
                    LOGOUT
                  </Button>
                </>
              ) : (
                <div className="grid grid-cols-2 gap-3">
                  <Link href="/login">
                    <Button variant="outline" className="w-full border-2 border-primary text-primary py-4 font-extrabold rounded-xl bg-white">LOGIN</Button>
                  </Link>
                  <Link href="/register">
                    <Button className="w-full bg-gradient-to-r from-oxygen-teal to-[#00C2B6] text-white py-4 font-extrabold rounded-xl">DAFTAR</Button>
                  </Link>
                </div>
              )}
            </div>
          </nav>
        </div>
      )}
    </header>
  );
};

export default Header;
