import { LucideIcon } from 'lucide-react';
import {
  Home,
  Info,
  Users,
  Briefcase,
  FileCheck,
  Newspaper,
  Building2,
  Target,
  Network,
  CalendarDays,
  Heart,
  Vote,
  Search,
  Handshake,
  GraduationCap,
  Lightbulb,
  FileText,
  CreditCard,
  DollarSign,
  HelpCircle,
  Image,
  Video,
  BookOpen,
} from 'lucide-react';

export interface MenuItem {
  title: string;
  description?: string;
  href: string;
  icon?: LucideIcon;
}

export interface MenuSection {
  id: string;
  title: string;
  items: MenuItem[];
  icon?: LucideIcon;
}

export interface MainMenuItem {
  id: string;
  label: string;
  href?: string;
  sections?: MenuSection[];
  color: {
    main: string;
    gradient: string;
    hover: string;
    iconClass?: string;
  };
  description?: string;
  icon?: LucideIcon;
}

export const menuConfig: MainMenuItem[] = [
  {
    id: 'home',
    label: 'Beranda',
    href: '/',
    color: {
      main: '#0F766E',
      gradient: 'from-[#0F766E]/20 to-[#0F766E]/5',
      hover: 'hover:bg-[#0F766E]/10',
    },
    icon: Home,
  },
  {
    id: 'tentang-ika',
    label: 'Tentang IKA',
    description: 'Mengenal lebih dekat Ikatan Alumni UNIMED',
    icon: Info,
    color: {
      main: '#0F766E',
      gradient: 'from-[#0F766E]/20 to-[#0F766E]/5',
      hover: 'hover:bg-[#0F766E]/10',
    },
    sections: [
      {
        id: 'profil-organisasi',
        title: 'Profil Organisasi',
        icon: Building2,
        items: [
          {
            title: 'Tentang IKA UNIMED',
            description: 'Sejarah dan perjalanan organisasi',
            href: '/tentang-kami',
            icon: Info,
          },
          {
            title: 'Visi & Misi',
            description: 'Tujuan dan arah organisasi',
            href: '/tentang-kami',
            icon: Target,
          },
        ],
      },
      {
        id: 'struktur',
        title: 'Struktur',
        icon: Network,
        items: [
          {
            title: 'Struktur Organisasi',
            description: 'Susunan kepengurusan IKA',
            href: '/struktur-organisasi',
            icon: Network,
          },
          {
            title: 'Organisasi (PP / DPW / DPC)',
            description: 'Jaringan organisasi IKA di seluruh Indonesia',
            href: '/organisasi',
            icon: Building2,
          },
        ],
      },
    ],
  },
  {
    id: 'komunitas-alumni',
    label: 'Komunitas Alumni',
    description: 'Terhubung dan berkontribusi bersama sesama alumni',
    icon: Users,
    color: {
      main: '#37D67A',
      gradient: 'from-[#37D67A]/25 to-[#37D67A]/10',
      hover: 'hover:bg-[#37D67A]/10',
    },
    sections: [
      {
        id: 'berita-kegiatan',
        title: 'Berita & Kegiatan',
        icon: CalendarDays,
        items: [
          {
            title: 'Kabar Alumni',
            description: 'Berita dan informasi terkini alumni',
            href: '/kabar-alumni',
            icon: Newspaper,
          },
          {
            title: 'Agenda',
            description: 'Jadwal kegiatan dan acara mendatang',
            href: '/agenda',
            icon: CalendarDays,
          },
        ],
      },
      {
        id: 'kontribusi',
        title: 'Kontribusi',
        icon: Heart,
        items: [
          {
            title: 'Ruang Pengabdian',
            description: 'Berkontribusi untuk sesama alumni',
            href: '/pengabdian',
            icon: Heart,
          },
          {
            title: 'E-Voting',
            description: 'Partisipasi dalam keputusan organisasi',
            href: '/voting',
            icon: Vote,
          },
        ],
      },
    ],
  },
  {
    id: 'karir-pengembangan',
    label: 'Karir & Pengembangan',
    description: 'Peluang karier dan pengembangan kompetensi',
    icon: Briefcase,
    color: {
      main: '#E9CF35',
      gradient: 'from-[#E9CF35]/25 to-[#E9CF35]/10',
      hover: 'hover:bg-[#E9CF35]/15',
      iconClass: 'text-gray-800',
    },
    sections: [
      {
        id: 'peluang-karir',
        title: 'Peluang Karir',
        icon: Search,
        items: [
          {
            title: 'Lowongan Kerja',
            description: 'Temukan peluang karier terbaru',
            href: '/lowongan-kerja',
            icon: Search,
          },
          {
            title: 'Kemitraan',
            description: 'Jalin kerjasama strategis',
            href: '/kemitraan',
            icon: Handshake,
          },
        ],
      },
      {
        id: 'pengembangan-diri',
        title: 'Pengembangan Diri',
        icon: GraduationCap,
        items: [
          {
            title: 'Beasiswa',
            description: 'Program beasiswa untuk alumni',
            href: '/beasiswa',
            icon: GraduationCap,
          },
          {
            title: 'Kelas & Kursus',
            description: 'Katalog kelas daring dan pembelajaran ringkas',
            href: '/courses',
            icon: BookOpen,
          },
          {
            title: 'Micro Learning',
            description: 'Konten pembelajaran singkat untuk pengembangan diri',
            href: '/learning',
            icon: Lightbulb,
          },
        ],
      },
    ],
  },
  {
    id: 'layanan-alumni',
    label: 'Layanan Alumni',
    description: 'Layanan digital dan dukungan untuk alumni',
    icon: FileCheck,
    color: {
      main: '#085A18',
      gradient: 'from-[#085A18]/20 to-[#085A18]/5',
      hover: 'hover:bg-[#085A18]/10',
    },
    sections: [
      {
        id: 'layanan-digital',
        title: 'Layanan Digital',
        icon: FileText,
        items: [
          {
            title: 'Legalisir Ijazah',
            description: 'Layanan legalisir dokumen online',
            href: '/legalization',
            icon: FileText,
          },
          {
            title: 'Kartu Alumni',
            description: 'Pendaftaran dan pengelolaan kartu',
            href: '/kartu-alumni',
            icon: CreditCard,
          },
        ],
      },
      {
        id: 'dukungan',
        title: 'Dukungan',
        icon: Heart,
        items: [
          {
            title: 'Donasi',
            description: 'Dukung kegiatan IKA UNIMED',
            href: '/donasi',
            icon: DollarSign,
          },
        ],
      },
    ],
  },
  {
    id: 'berita',
    label: 'Berita',
    description: 'Informasi terkini dan galeri media',
    icon: Newspaper,
    color: {
      main: '#64748B',
      gradient: 'from-slate-200 to-slate-50',
      hover: 'hover:bg-slate-100',
    },
    sections: [
      {
        id: 'berita-update',
        title: 'Berita & Update',
        icon: Newspaper,
        items: [
          {
            title: 'Berita Terkini',
            description: 'Informasi terbaru seputar IKA UNIMED dan almamater',
            href: '/news',
            icon: Newspaper,
          },
          {
            title: 'FAQ',
            description: 'Pertanyaan yang sering diajukan',
            href: '/faq',
            icon: HelpCircle,
          },
        ],
      },
      {
        id: 'media',
        title: 'Media',
        icon: Image,
        items: [
          {
            title: 'Galeri Foto',
            description: 'Dokumentasi foto kegiatan',
            href: '/media/foto',
            icon: Image,
          },
          {
            title: 'Galeri Video',
            description: 'Dokumentasi video kegiatan',
            href: '/media/video',
            icon: Video,
          },
        ],
      },
    ],
  },
];
