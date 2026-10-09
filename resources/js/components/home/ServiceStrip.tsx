import { Link } from "@inertiajs/react";
import {
  BookOpen,
  Briefcase,
  Building2,
  FileCheck,
  GraduationCap,
  Handshake,
  MessagesSquare,
  Newspaper,
  ShoppingBag,
  Users,
} from "lucide-react";
import type { PortalService } from "./types";

const iconMap: Record<string, typeof Newspaper> = {
  Newspaper,
  MessagesSquare,
  Users,
  Building2,
  Briefcase,
  Handshake,
  FileCheck,
  GraduationCap,
  BookOpen,
  ShoppingBag,
};

const ServiceStrip = ({ services }: { services: PortalService[] }) => {
  if (services.length === 0) {
    return null;
  }

  return (
    <section className="bg-white pb-28 pt-12 md:pb-32 md:pt-16">
      <div className="container">
        <div className="mb-8 text-center">
          <p className="mb-2 text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Layanan alumni</p>
          <h2 className="text-3xl font-extrabold text-gray-800">Akses cepat</h2>
        </div>
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
          {services.map((service) => {
            const Icon = iconMap[service.icon] || Newspaper;

            return (
              <Link
                key={service.href}
                href={service.href}
                className="flex flex-col items-center gap-3 rounded-2xl border border-gray-100 bg-[#F8FAF9] px-4 py-6 text-center transition hover:-translate-y-1 hover:shadow-lg"
              >
                <span className="flex h-12 w-12 items-center justify-center rounded-full bg-[#006837]/10 text-[#006837]">
                  <Icon className="h-5 w-5" />
                </span>
                <span className="text-sm font-bold text-gray-800">{service.title}</span>
                <span className="text-xs text-gray-500">{service.description}</span>
              </Link>
            );
          })}
        </div>
      </div>
    </section>
  );
};

export default ServiceStrip;
