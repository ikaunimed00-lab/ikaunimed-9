import { useState } from "react";
import { ZoomIn } from "lucide-react";
import { Dialog, DialogContent, DialogTitle } from "@/components/ui/dialog";

type ZoomableImageProps = {
  src: string;
  alt: string;
  className?: string;
};

const ZoomableImage = ({ src, alt, className = "" }: ZoomableImageProps) => {
  const [open, setOpen] = useState(false);

  return (
    <>
      <button
        type="button"
        onClick={() => setOpen(true)}
        className={`group relative block w-full overflow-hidden rounded-2xl border border-gray-100 ${className}`}
        aria-label={`Perbesar ${alt}`}
      >
        <img src={src} alt={alt} loading="lazy" className="w-full" />
        <span className="absolute bottom-3 right-3 inline-flex items-center gap-1.5 rounded-full bg-black/60 px-3 py-1.5 text-xs font-semibold text-white">
          <ZoomIn className="h-4 w-4" />
          Perbesar
        </span>
      </button>

      <Dialog open={open} onOpenChange={setOpen}>
        <DialogContent className="max-h-[95vh] w-[calc(100%-1rem)] max-w-6xl overflow-auto p-2 sm:p-4">
          <DialogTitle className="sr-only">{alt}</DialogTitle>
          <img src={src} alt={alt} className="h-auto w-full" />
          <a
            href={src}
            target="_blank"
            rel="noopener noreferrer"
            className="mt-2 inline-block text-sm font-semibold text-[#006837]"
          >
            Buka gambar ukuran penuh
          </a>
        </DialogContent>
      </Dialog>
    </>
  );
};

export default ZoomableImage;
