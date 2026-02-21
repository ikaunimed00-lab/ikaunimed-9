import { Users, Briefcase, Newspaper, GraduationCap, CreditCard, HeartHandshake } from "lucide-react";
import { Link } from "@inertiajs/react";

const FeaturesSection = () => {
  const features = [
    { icon: Users, title: "Database Alumni", description: "Terhubung kembali dengan rekan sejawat", color: "text-primary", bg: "bg-primary/10", border: "bg-primary", href: "/database" },
    { icon: Briefcase, title: "Info Karir", description: "Loker & peluang bisnis alumni", color: "text-oxygen-teal", bg: "bg-oxygen-teal/10", border: "bg-oxygen-teal", href: "/karir" },
    { icon: Newspaper, title: "Berita Kampus", description: "Update terkini agenda UNIMED", color: "text-[#FFD700]", bg: "bg-[#FFD700]/10", border: "bg-[#FFD700]", href: "/news" },
    { icon: GraduationCap, title: "Program Beasiswa", description: "Bantuan pendidikan mahasiswa", color: "text-primary", bg: "bg-primary/10", border: "bg-primary", href: "/beasiswa" },
    { icon: CreditCard, title: "Kartu Alumni", description: "Akses identitas digital khusus", color: "text-oxygen-teal", bg: "bg-oxygen-teal/10", border: "bg-oxygen-teal", href: "/kartu-alumni" },
    { icon: HeartHandshake, title: "Ruang Pengabdian", description: "Kontribusi nyata bagi almamater", color: "text-[#FFD700]", bg: "bg-[#FFD700]/10", border: "bg-[#FFD700]", href: "/pengabdian" },
  ];

  return (
    <section className="py-12 md:py-20 bg-white">
      <div className="container">
        <div className="text-center mb-12">
          <div className="flex items-center justify-center gap-2 mb-3">
             <span className="h-px w-8 bg-primary"></span>
             <span className="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Keunggulan Kami</span>
             <span className="h-px w-8 bg-[#FFD700]"></span>
          </div>
          
          <h2 className="text-3xl md:text-4xl font-extrabold text-gray-800 mb-4">
            Kenapa <span className="text-oxygen-teal">IKA UNIMED</span>?
          </h2>
          <p className="text-gray-600 max-w-2xl mx-auto text-base md:text-lg">
            Fokus Kepada Kemajuan Universitas, Alumni dan Keluarga Alumni
          </p>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 md:gap-6">
          {features.map((feature, index) => (
            <Link
              key={index}
              href={feature.href}
              className="bg-white border border-gray-100 rounded-2xl p-6 pb-8 text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden flex flex-col items-center"
            >
              <div className={`w-16 h-16 ${feature.bg} rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300`}>
                <feature.icon className={`w-8 h-8 ${feature.color}`} />
              </div>
              
              <h3 className="text-gray-800 font-bold mb-1 text-sm md:text-base group-hover:text-gray-900">
                {feature.title}
              </h3>
              
              <p className="text-gray-500 text-xs leading-relaxed">
                {feature.description}
              </p>

              {/* Garis Aksen di BAGIAN BAWAH - Muncul saat Hover */}
              <div className={`absolute bottom-0 left-0 w-full h-1.5 opacity-0 group-hover:opacity-100 transition-all duration-300 ${feature.border}`}>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
};

export default FeaturesSection;
