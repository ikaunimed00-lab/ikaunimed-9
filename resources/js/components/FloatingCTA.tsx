import { MessageCircle } from "lucide-react";

const FloatingCTA = ({ settings }: { settings?: any }) => {
  const whatsappNumber = settings?.contact?.contact_phone || "+62 815 3238 7608";
  const whatsappMessage = encodeURIComponent("Halo IKA UNIMED, saya butuh bantuan.");
  const whatsappUrl = `https://wa.me/${whatsappNumber.replace(/\D/g, '')}?text=${whatsappMessage}`;

  return (
    <div className="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-2">
      {/* Mascot with speech bubble */}
      <div className="relative">
        {/* Bubble Chat: Menggunakan Oxygen Teal agar lebih adem di mata */}
        <div className="absolute -top-14 -left-36 bg-oxygen-teal text-white px-4 py-3 rounded-2xl text-sm font-bold shadow-xl shadow-oxygen-teal/20 animate-bounce">
          <span className="whitespace-nowrap">{settings?.floating_cta?.floating_cta_bubble_text || "Butuh bantuan IKA?"}</span>
          <span className="block text-[10px] font-medium opacity-90">{settings?.floating_cta?.floating_cta_bubble_subtext || "Tim kami siap membantu"}</span>
          {/* Segitiga Bubble */}
          <div className="absolute -bottom-2 right-6 w-0 h-0 border-l-8 border-l-transparent border-r-8 border-r-transparent border-t-8 border-t-oxygen-teal"></div>
        </div>

        {/* Tombol Chat: Menggunakan Primary Orange agar mencolok tapi elegan */}
        <a
          href={whatsappUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="w-16 h-16 bg-primary rounded-full flex items-center justify-center shadow-lg shadow-orange-200/50 hover:scale-110 hover:shadow-orange-300/60 transition-all duration-300 group"
          aria-label="Chat with support"
        >
          {/* Ikon tetap putih agar kontras */}
          <MessageCircle className="w-8 h-8 text-white group-hover:rotate-12 transition-transform" />
          
          {/* Efek Ring (Lingkaran Halo) di sekitar tombol agar terlihat "aktif" */}
          <span className="absolute inset-0 rounded-full bg-primary animate-ping opacity-20"></span>
        </a>
      </div>
    </div>
  );
};

export default FloatingCTA;
