import { Facebook, Instagram, Twitter, Youtube, MapPin, Phone, Mail } from "lucide-react";
import { Link } from '@inertiajs/react';

const iconMap: Record<string, any> = {
  Facebook,
  Instagram,
  Twitter,
  Youtube
};

const Footer = ({ settings, content }: { settings?: any, content?: any }) => {
  const defaultLinks = {
    layanan: [
      { label: "Direktori Alumni", href: "/alumni" },
      { label: "Karir & Profesional", href: "/karir" },
      { label: "Beasiswa", href: "/beasiswa" },
      { label: "Pengembangan Keahlian", href: "/skill-upgrading" },
      { label: "Kemitraan", href: "/kemitraan" },
      { label: "Kartu Alumni", href: "/kartu-alumni" },
      { label: "E-Voting", href: "/voting" },
      { label: "Legalisir Ijazah", href: "/legalisir" },
    ],
    informasi: [
      { label: "Kabar Alumni", href: "/kabar-alumni" },
      { label: "Berita", href: "/news" },
      { label: "Agenda", href: "/agenda" },
      { label: "Ruang Pengabdian", href: "/pengabdian" },
      { label: "Galeri Foto", href: "/media/foto" },
      { label: "Galeri Video", href: "/media/video" },
      { label: "FAQ", href: "/faq" },
    ],
    organisasi: [
      { label: "Tentang IKA UNIMED", href: "/tentang-kami" },
      { label: "Struktur Organisasi", href: "/struktur-organisasi" },
      { label: "Direktori Organisasi", href: "/organisasi" },
      { label: "Hubungi Kami & Sekretariat", href: "/hubungi-kami" },
      { label: "Donasi", href: "/donasi" },
      { label: "Syarat dan Ketentuan", href: "/syarat-ketentuan" },
      { label: "Kebijakan Privasi", href: "/kebijakan-privasi" },
    ],
  };

  // Transform footer_links if it's an array (from Filament Repeater)
  let footerGroups = defaultLinks;
  if (Array.isArray(content?.footer_links)) {
    footerGroups = content.footer_links.reduce((acc: any, item: any) => {
      acc[item.group_name] = item.links || [];
      return acc;
    }, {});
  } else if (content?.footer_links && typeof content.footer_links === 'object') {
    footerGroups = content.footer_links;
  }

  const defaultSocialLinks = [
    { icon: 'Facebook', href: "#", label: "Facebook" },
    { icon: 'Instagram', href: "#", label: "Instagram" },
    { icon: 'Twitter', href: "#", label: "Twitter" },
    { icon: 'Youtube', href: "#", label: "Youtube" },
  ];

  // Logic: Content (Homepage) > Settings (Global) > Default
  const socialLinks = [
    { 
      icon: 'Facebook', 
      href: content?.social_facebook || settings?.social?.social_facebook || "#", 
      label: "Facebook" 
    },
    { 
      icon: 'Instagram', 
      href: content?.social_instagram || settings?.social?.social_instagram || "#", 
      label: "Instagram" 
    },
    { 
      icon: 'Twitter', 
      href: content?.social_twitter || settings?.social?.social_twitter || "#", 
      label: "Twitter" 
    },
    { 
      icon: 'Youtube', 
      href: content?.social_youtube || settings?.social?.social_youtube || "#", 
      label: "Youtube" 
    },
  ];

  const siteDescription = content?.description || settings?.footer?.footer_description || "Wadah resmi kolaborasi alumni Universitas Negeri Medan untuk membangun jejaring profesional dan kontribusi bagi almamater.";
  const siteAddress = content?.address || settings?.contact?.contact_address || "Medan, Indonesia";
  const sitePhone = content?.phone || settings?.contact?.contact_phone || "+62 815 3238 7608";
  const siteEmail = content?.email || settings?.contact?.contact_email || "office@ikaunimed.or.id";
  const siteCopyright = content?.copyright_text || settings?.footer?.footer_copyright || "© 2026";

  return (
    <footer className="bg-[#0F172A] text-white pt-16 pb-8 border-t-2 border-[#FFD700]/20">
      <div className="container">
        <div className="grid md:grid-cols-2 lg:grid-cols-5 gap-8 mb-12">
          
          {/* Logo & Description Section */}
          <div className="lg:col-span-2">
            <div className="mb-6 flex items-center gap-3">
              <img 
                src={settings?.general?.site_logo || "/images/favicon_ikaunimed.png"}
                alt="Logo IKA UNIMED" 
                className="h-10 w-auto" 
              />
              <div className="flex flex-col leading-none">
                <div className="text-2xl font-bold tracking-tighter">
                  <span className="text-primary">IKA</span>
                  <span className="text-oxygen-teal">UNI</span>
                  <span style={{ color: '#FFD700' }}>MED</span>
                </div>
                <span className="text-[8px] text-gray-400 mt-1 uppercase tracking-widest font-medium">
                  {settings?.general?.site_tagline || "Terhubung, Berkolaborasi, Berkontribusi"}
                </span>
              </div>
            </div>
            
            <p className="text-gray-400 mb-8 max-w-sm text-sm leading-relaxed">
              {siteDescription}
            </p>
            
            {/* Info Kontak dengan Aksen Emas & Teal */}
            <div className="space-y-4">
              <div className="flex items-center gap-4 group">
                <div className="w-9 h-9 rounded-lg bg-white/5 flex items-center justify-center border border-white/10 group-hover:border-oxygen-teal transition-all">
                  <MapPin className="w-4 h-4 text-oxygen-teal" />
                </div>
                <span className="text-gray-300 text-sm">{siteAddress}</span>
              </div>
              <div className="flex items-center gap-4 group">
                <div className="w-9 h-9 rounded-lg bg-white/5 flex items-center justify-center border border-white/10 group-hover:border-[#FFD700] transition-all">
                  <Phone className="w-4 h-4 text-[#FFD700]" />
                </div>
                <span className="text-gray-300 text-sm">{sitePhone}</span>
              </div>
              <div className="flex items-center gap-4 group">
                <div className="w-9 h-9 rounded-lg bg-white/5 flex items-center justify-center border border-white/10 group-hover:border-primary transition-all">
                  <Mail className="w-4 h-4 text-primary" />
                </div>
                <span className="text-gray-300 text-sm">{siteEmail}</span>
              </div>
            </div>
          </div>

          {/* Kolom Link Navigasi */}
          {Object.keys(footerGroups).map((category) => (
            <div key={category}>
              <h4 className="font-bold mb-6 text-white text-sm uppercase tracking-widest relative inline-block text-nowrap">
                {category}
                <span className="absolute -bottom-1 left-0 w-1/2 h-0.5 bg-[#FFD700]"></span>
              </h4>
              <ul className="space-y-3">
                {footerGroups[category].map((link: any, index: number) => (
                  <li key={`${category}-${link.label}-${index}`}>
                    <Link href={link.href} className="text-gray-400 hover:text-oxygen-teal transition-all flex items-center gap-2 group text-sm">
                      <span className="h-px w-0 bg-oxygen-teal group-hover:w-3 transition-all"></span>
                      {link.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>

        {/* Bottom Bar: Social & Copyright */}
        <div className="border-t border-white/5 pt-8 flex flex-col md:flex-row items-center justify-between gap-6">
          <div className="flex items-center gap-3">
            {socialLinks.map((social: any, index: number) => {
              const Icon = typeof social.icon === 'string' ? (iconMap[social.icon] || Facebook) : social.icon;
              return (
                <a
                  key={social.label || `social-${index}`}
                  href={social.href}
                  className="w-10 h-10 bg-white/5 rounded-xl flex items-center justify-center hover:bg-oxygen-teal transition-all duration-500 group"
                >
                  <Icon className="w-5 h-5 text-gray-400 group-hover:text-white" />
                </a>
              );
            })}
          </div>
          
          <div className="text-right">
            <p className="text-gray-500 text-xs tracking-wide">
              {siteCopyright} <span className="text-oxygen-teal font-semibold">IKA</span>
              <span className="text-white">UNI</span>
              <span style={{ color: '#FFD700' }} className="font-semibold">MED</span>.
            </p>
            <p className="text-[10px] text-gray-600 uppercase mt-1">{settings?.footer?.footer_location || "Sumatera Utara, Indonesia"}</p>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
