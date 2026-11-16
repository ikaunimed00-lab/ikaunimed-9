import { MessageCircle } from "lucide-react";

export const DEFAULT_CONTACT_PHONE = "+62 895-4292-17077";

type FloatingCTAProps = {
  settings?: any;
  hideBubble?: boolean;
  /** Sits above a sticky bottom bar on small screens. */
  liftOnMobile?: boolean;
};

const FloatingCTA = ({ settings, hideBubble = false, liftOnMobile = false }: FloatingCTAProps) => {
  const whatsappNumber = settings?.contact?.contact_phone || DEFAULT_CONTACT_PHONE;
  const whatsappMessage = encodeURIComponent("Halo IKA UNIMED, saya butuh bantuan.");
  const whatsappUrl = `https://wa.me/${whatsappNumber.replace(/\D/g, "")}?text=${whatsappMessage}`;
  const bubbleText = settings?.floating_cta?.floating_cta_bubble_text || "Chat WhatsApp";
  const bubbleSubtext = settings?.floating_cta?.floating_cta_bubble_subtext || "Hubungi IKA UNIMED";

  return (
    <div
      className={`fixed right-6 z-50 flex flex-col items-end gap-2 ${liftOnMobile ? "bottom-24 md:bottom-6" : "bottom-6"}`}
    >
      <div className="relative">
        {!hideBubble && (
          <div className="absolute -top-14 -left-40 bg-oxygen-teal text-white px-4 py-3 rounded-2xl text-sm font-bold shadow-xl shadow-oxygen-teal/20 animate-bounce">
            <span className="whitespace-nowrap">{bubbleText}</span>
            <span className="block text-[10px] font-medium opacity-90">{bubbleSubtext}</span>
            <div className="absolute -bottom-2 right-6 w-0 h-0 border-l-8 border-l-transparent border-r-8 border-r-transparent border-t-8 border-t-oxygen-teal"></div>
          </div>
        )}

        <a
          href={whatsappUrl}
          target="_blank"
          rel="noopener noreferrer"
          className="relative w-16 h-16 bg-primary rounded-full flex items-center justify-center shadow-lg shadow-orange-200/50 hover:scale-110 hover:shadow-orange-300/60 transition-all duration-300 group"
          aria-label="Chat WhatsApp IKA UNIMED"
          title="Chat WhatsApp IKA UNIMED"
        >
          <MessageCircle className="w-8 h-8 text-white group-hover:rotate-12 transition-transform" />
          <span className="absolute inset-0 rounded-full bg-primary animate-ping opacity-20"></span>
        </a>

        <p className="mt-1 text-center text-[10px] font-bold uppercase tracking-wide text-[#0F172A]/80">
          WhatsApp
        </p>
      </div>
    </div>
  );
};

export default FloatingCTA;
