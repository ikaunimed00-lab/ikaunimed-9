import React from 'react';
import { Link } from '@inertiajs/react';

const VideoSection = () => {
  return (
    <section className="py-12 md:py-20 bg-light-green/30">
      <div className="container mx-auto px-4 md:px-6 lg:px-8 max-w-[1536px]">
        {/* Layout Grid */}
        <div className="flex flex-col lg:grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
          
          {/* Sisi Konten Teks */}
          <div className="order-1 lg:order-2 text-center lg:text-left">
            <h2 className="text-3xl md:text-4xl lg:text-5xl font-bold text-foreground mb-6 leading-tight">
              Merajut Silaturahmi,{" "}
              {/* Menggunakan Kuning IKA untuk penekanan (pengganti orange) */}
              <span className="text-ika-yellow inline-block pb-1 drop-shadow-sm">
                Membangun Sinergi Alumni
              </span>
            </h2>
            <p className="text-lg text-muted-foreground leading-relaxed">
              Wadah resmi kolaborasi dan koneksi bagi seluruh alumni Universitas Negeri Medan. 
              Bersama kita berkontribusi bagi almamater, nusa, dan bangsa melalui jaringan 
              profesional yang kuat, unggul, dan berkelanjutan.
            </p>
            <div className="mt-8 flex flex-wrap justify-center lg:justify-start gap-4">
               <div className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full bg-first-dark-green"></div>
                  <span className="text-sm font-medium text-gray-600">Terintegrasi</span>
               </div>
               <div className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full bg-first-dark-green"></div>
                  <span className="text-sm font-medium text-gray-600">Kolaboratif</span>
               </div>
               <div className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full bg-first-dark-green"></div>
                  <span className="text-sm font-medium text-gray-600">Inovatif</span>
               </div>
            </div>
          </div>

          {/* Sisi Video */}
          <Link href="/media/video" className="order-2 lg:order-1 relative w-full rounded-3xl overflow-hidden shadow-2xl aspect-video bg-first-dark-green block group cursor-pointer">
            {/* Overlay Placeholder Video */}
            <div className="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-first-dark-green via-second-dark-green to-first-dark-green/90">
              <div className="text-center">
                {/* Icon Video Statis (Bukan Tombol Play) */}
                <div className="w-20 h-20 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm border border-white/20">
                  <span className="text-3xl ml-1 text-white opacity-80">🎬</span>
                </div>
                <p className="text-white font-medium tracking-wide opacity-90 group-hover:text-ika-yellow transition-colors">Lihat Galeri Video</p>
              </div>
            </div>
            
            {/* Header Info Video (Ala YouTube Style) */}
            <div className="absolute top-4 left-4 flex items-center gap-3 bg-black/20 backdrop-blur-md p-2 pr-4 rounded-full">
              {/* === KODE BARU UNTUK MENAMPILKAN FAVICON === */}
              <div className="w-10 h-10 rounded-full overflow-hidden shadow-inner flex items-center justify-center bg-white p-1">
                <img 
                  src="/images/favicon_ikaunimed.png" 
                  alt="Logo IKA UNIMED" 
                  className="w-full h-full object-contain"
                />
              </div>
              {/* =========================================== */}
              <div>
                <p className="text-white text-sm font-bold leading-none">IKA UNIMED Official</p>
                <p className="text-white/70 text-[10px] uppercase tracking-tighter">The Character Building University</p>
              </div>
            </div>
          </Link>

        </div>
      </div>
    </section>
  );
};

export default VideoSection;
