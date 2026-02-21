import React from "react";
import { route } from "ziggy-js";

const PackageSection = ({ content }: { content?: any }) => {
  const ctaData = content?.items || [];

  if (!ctaData || ctaData.length === 0) return null;

  return (
    <section className="w-full overflow-hidden">
      {/* Container untuk jarak antar card di mobile & desktop */}
      <div className="flex flex-col gap-4 md:gap-6 px-4 md:px-0"> 
        {ctaData.map((item: any, index: number) => {
          // Handle image URL from storage or fallback
          const bgImageUrl = item.bgImage?.startsWith('http') 
            ? item.bgImage 
            : (item.bgImage ? `/storage/${item.bgImage}` : '/images/favicon_ikaunimed.png');

          return (
            <div
              key={item.id || `package-item-${index}`}
              // Container utama dengan tinggi dan border radius yang terlihat di mobile
              className={`relative w-full h-[650px] md:h-[550px] flex items-center group overflow-hidden shadow-xl
                rounded-2xl
                ${index === 0 ? "-mt-16 md:mt-0" : "mt-4 md:mt-0"}
              `}
            >
              <div 
                className={`absolute inset-0 bg-cover bg-no-repeat transition-transform duration-1000 group-hover:scale-105 
                  ${item.position === 'right' ? 'bg-left' : 'bg-right'} lg:bg-center`} 
                style={{ backgroundImage: `url('${bgImageUrl}')` }}
              />

              {/* Container Konten Fleksibel (rata kiri/kanan) */}
              <div className={`relative z-10 w-full flex h-full ${
                item.position === "right" ? "justify-end" : "justify-start"
              }`}>
                
                {/* Box Konten (Semi-transparan di kedua mode) */}
                <div
                  className={`w-full flex flex-col justify-center shadow-2xl self-center p-6 md:p-8 
                    bg-white/80 backdrop-blur-sm 
                    text-center 
                    
                    /* Mobile: Rounded penuh & full width */
                    rounded-2xl mx-4
                    
                    /* Desktop: Memanjang ke tengah, rounded hanya di sisi dalam */
                    lg:w-[55%] lg:mx-0 
                    ${item.position === "right" 
                      ? "lg:text-right lg:rounded-l-3xl lg:rounded-r-none" 
                      : "lg:text-left lg:rounded-r-3xl lg:rounded-l-none"}
                  `}
                >
                  <h2 className="text-3xl md:text-4xl font-extrabold mb-4 text-gray-800 leading-tight">
                    {item.title}
                  </h2>
                  
                  <p className="text-gray-600 mb-6 text-base md:text-lg leading-relaxed">
                    {item.description}
                  </p>

                  {/* Logo & Tagline Row */}
                  <div className={`flex items-center gap-4 mb-8 justify-center 
                    ${item.position === "right" ? "lg:justify-end" : "lg:justify-start"}`}
                  >
                    <img
                      src="/images/logo_ikaunimed.png"
                      alt="Logo"
                      className="h-10 md:h-12 w-auto"
                    />
                    <p className="text-md font-extrabold text-gray-800 uppercase tracking-wide">
                      {item.tagline}
                    </p>
                  </div>

                  {/* Action Button */}
                  <div className="w-full">
                    <a
                      href={item.href}
                      style={{ backgroundColor: item.buttonColor || '#10b981' }}
                      className={`inline-block text-white font-semibold rounded-lg shadow-md transition-all duration-300 hover:brightness-110 hover:shadow-lg
                        /* Mobile Button Slimmer */
                        px-6 py-2 w-full md:w-auto
                        /* Desktop Button Normal */
                        lg:px-8 lg:py-3
                      `}
                    >
                      lihat detail
                    </a>
                  </div>
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </section>
  );
};

export default PackageSection;
