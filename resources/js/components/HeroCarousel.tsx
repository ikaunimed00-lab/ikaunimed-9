import { useState, useEffect } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { Link } from '@inertiajs/react'; 

const HeroCarousel = ({ content }: { content?: any }) => {
  const [currentSlide, setCurrentSlide] = useState(0);

  const slides = content?.slides?.map((slide: any, index: number) => ({
    ...slide,
    id: slide.id || `custom-${index}`,
    bgDesktop: slide.image ? `/storage/${slide.image}` : (slide.bgDesktop ? (slide.bgDesktop.startsWith('/') ? slide.bgDesktop : `/images/${slide.bgDesktop}`) : null),
    bgMobile: slide.image_mobile ? `/storage/${slide.image_mobile}` : (slide.image ? `/storage/${slide.image}` : (slide.bgMobile ? (slide.bgMobile.startsWith('/') ? slide.bgMobile : `/images/${slide.bgMobile}`) : null)),
  })) || [];

  useEffect(() => {
    if (slides.length === 0) return;
    const timer = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % slides.length);
    }, 6000); 
    return () => clearInterval(timer);
  }, [slides.length]);

  if (slides.length === 0) return null;

  const nextSlide = () => setCurrentSlide((prev) => (prev + 1) % slides.length);
  const prevSlide = () => setCurrentSlide((prev) => (prev - 1 + slides.length) % slides.length);

  return (
    <section className="relative w-full overflow-hidden bg-gray-100 h-[500px] md:h-[480px]">
      <div className="relative w-full h-full">
        {slides.map((slide: any, index: number) => {
          const slideContent = (
            <div
              key={slide.id || `slide-${index}`}
              className={`absolute inset-0 w-full h-full transition-opacity duration-700 ease-in-out ${
                index === currentSlide ? "opacity-100 z-10" : "opacity-0 z-0"
              }`}
            >
              <picture className="w-full h-full">
                <source 
                  media="(max-width: 768px)" 
                  srcSet={slide.bgMobile || slide.bgDesktop || '/images/hero_slide_administrasi_mobile.png'} 
                />
                <img
                  src={slide.bgDesktop || '/images/hero_slide_administrasi.png'}
                  alt={slide.title || "IKA UNIMED"}
                  className="w-full h-full object-cover"
                />
              </picture>
            </div>
          );

          if (slide.buttonLink) {
            return (
              <Link 
                key={slide.id || `slide-${index}`} 
                href={slide.buttonLink}
                className={`absolute inset-0 block ${index === currentSlide ? "z-10" : "z-0"}`}
              >
                {slideContent}
              </Link>
            );
          }

          return slideContent;
        })}

        {/* Navigation Arrows */}
        {slides.length > 1 && (
          <div className="absolute inset-0 pointer-events-none flex items-center justify-between px-4 z-30">
            <button
              onClick={(e) => {
                e.preventDefault();
                prevSlide();
              }}
              className="pointer-events-auto bg-white/30 p-2 md:p-3 rounded-full shadow-lg hover:bg-white/80 transition-colors"
            >
              <ChevronLeft className="w-5 h-5 md:w-6 h-6 text-foreground" />
            </button>
            <button
              onClick={(e) => {
                e.preventDefault();
                nextSlide();
              }}
              className="pointer-events-auto bg-white/30 p-2 md:p-3 rounded-full shadow-lg hover:bg-white/80 transition-colors"
            >
              <ChevronRight className="w-5 h-5 md:w-6 h-6 text-foreground" />
            </button>
          </div>
        )}

        {/* Dots */}
        {slides.length > 1 && (
          <div className="absolute bottom-4 md:bottom-6 left-1/2 z-30 transform -translate-x-1/2 flex space-x-2">
            {slides.map((_: any, index: number) => (
              <button
                key={index}
                onClick={(e) => {
                  e.preventDefault();
                  setCurrentSlide(index);
                }}
                className={`w-2 h-2 md:w-3 md:h-3 rounded-full transition-colors ${
                  index === currentSlide ? "bg-ika-yellow" : "bg-white/50"
                }`}
              />
            ))}
          </div>
        )}
      </div>
    </section>
  );
};

export default HeroCarousel;
