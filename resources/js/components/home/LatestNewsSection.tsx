import { useState } from "react";
import { Link } from "@inertiajs/react";
import type { PortalNews } from "./types";

const formatPublishedAt = (value: string | null): string | null => {
  if (!value) {
    return null;
  }

  return new Intl.DateTimeFormat("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
    timeZone: "Asia/Jakarta",
  }).format(new Date(value));
};

const resolveImage = (image: string | null): string | null => {
  if (!image) {
    return null;
  }

  return image.startsWith("http") || image.startsWith("/") ? image : `/storage/${image}`;
};

const NewsThumbnail = ({ image, title }: { image: string | null; title: string }) => {
  const [failed, setFailed] = useState(false);
  const src = resolveImage(image);

  if (src && !failed) {
    return (
      <img
        src={src}
        alt={title}
        loading="lazy"
        onError={() => setFailed(true)}
        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
      />
    );
  }

  return (
    <div className="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#006837] to-[#00A69D]">
      <span className="rounded-2xl bg-white px-4 py-3 shadow-lg">
        <img src="/images/logo_ikaunimed.png" alt="" className="h-10 w-auto" />
      </span>
    </div>
  );
};

const LatestNewsSection = ({ news }: { news: PortalNews[] }) => {
  return (
    <section className="bg-[#F8FAF9] py-12 md:py-16">
      <div className="container">
        <div className="mb-8 flex flex-col items-start justify-between gap-3 md:flex-row md:items-end">
          <div>
            <p className="mb-2 text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Kabar</p>
            <h2 className="text-3xl font-extrabold text-gray-800">Berita terbaru</h2>
          </div>
          <Link href="/news" className="text-sm font-semibold text-[#00A69D] hover:text-[#006837]">
            Semua berita
          </Link>
        </div>

        {news.length === 0 ? (
          <p className="rounded-2xl border border-dashed border-gray-200 bg-[#F8FAF9] px-6 py-10 text-center text-gray-500">
            Belum ada berita terbit.
          </p>
        ) : (
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {news.map((item) => (
              <Link
                key={item.id}
                href={`/news/${item.slug}`}
                className="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white transition hover:border-[#00A69D] hover:shadow-lg"
              >
                <div className="h-40 overflow-hidden bg-[#E6EAE8]">
                  <NewsThumbnail image={item.image} title={item.title} />
                </div>
                <div className="flex flex-1 flex-col gap-2 p-4">
                  <h3 className="line-clamp-2 font-bold text-gray-800 group-hover:text-[#006837]">{item.title}</h3>
                  {item.excerpt && <p className="line-clamp-2 text-sm text-gray-600">{item.excerpt}</p>}
                  {formatPublishedAt(item.published_at) && (
                    <p className="mt-auto pt-2 text-xs text-gray-400">{formatPublishedAt(item.published_at)}</p>
                  )}
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>
    </section>
  );
};

export default LatestNewsSection;
