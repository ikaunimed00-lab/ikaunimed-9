import { Users, Briefcase, Newspaper, GraduationCap, CreditCard, HeartHandshake } from "lucide-react";
import { Link } from "@inertiajs/react";

const iconMap: Record<string, any> = {
  Users,
  Briefcase,
  Newspaper,
  GraduationCap,
  CreditCard,
  HeartHandshake
};

const defaultColors: Record<string, string> = {
  Users: '#006837',         // Primary Green
  Briefcase: '#00A69D',     // Oxygen Teal
  Newspaper: '#FFD700',     // IKA Yellow
  GraduationCap: '#006837', // Primary Green
  CreditCard: '#00A69D',    // Oxygen Teal
  HeartHandshake: '#FFD700' // IKA Yellow
};

const FeaturesSection = ({ content }: { content?: any }) => {
  const items = content?.items || [];
  
  // Transform dynamic items to match static design structure
  const features = items.map((f: any) => {
    // Map string icon name to component
    const IconComponent = typeof f.icon === 'string' ? (iconMap[f.icon] || Users) : f.icon;
    const iconName = typeof f.icon === 'string' ? f.icon : 'Users';
    
    // Get base color: Priority -> Dashboard Input (must be HEX) -> Default Map -> Default Green
    let baseColor = f.color;
    
    // Validate if baseColor is a valid hex code (starts with #)
    // If user put a class name like "text-primary", we ignore it and use default
    if (!baseColor || !baseColor.startsWith('#')) {
        baseColor = defaultColors[iconName] || '#006837';
    }
    
    return {
      ...f,
      icon: IconComponent,
      // Create Tailwind-like classes/styles dynamically
      styleColor: baseColor,
      styleBg: `${baseColor}1a`, // 10% opacity hex
      href: f.href || '#'
    };
  });

  // If no content provided, return null or fallback (optional)
  if (features.length === 0) return null;

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
          {features.map((feature: any, index: number) => {
            const Icon = feature.icon;
            
            return (
              <Link
                key={index}
                href={feature.href}
                className="bg-white border border-gray-100 rounded-2xl p-6 pb-8 text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden flex flex-col items-center"
              >
                <div 
                  className={`w-16 h-16 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300`}
                  style={{ backgroundColor: feature.styleBg }}
                >
                  <Icon className="w-8 h-8" style={{ color: feature.styleColor }} />
                </div>
                
                <h3 className="text-gray-800 font-bold mb-1 text-sm md:text-base group-hover:text-gray-900">
                  {feature.title}
                </h3>
                
                <p className="text-gray-500 text-xs leading-relaxed">
                  {feature.description}
                </p>

                {/* Garis Aksen di BAGIAN BAWAH - Muncul saat Hover */}
                <div 
                  className="absolute bottom-0 left-0 w-full h-1.5 opacity-0 group-hover:opacity-100 transition-all duration-300"
                  style={{ backgroundColor: feature.styleColor }}
                >
                </div>
              </Link>
            )
          })}
        </div>
      </div>
    </section>
  );
};

export default FeaturesSection;
