import PimpinanCard from "./PimpinanCard";
import type { Pimpinan } from "./types";

const PimpinanSection = ({ pimpinan }: { pimpinan: Pimpinan[] }) => {
  if (pimpinan.length === 0) {
    return null;
  }

  return (
    <section className="bg-white py-12 md:py-16">
      <div className="container">
        <div className="mb-8 text-center">
          <p className="mb-2 text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Organisasi</p>
          <h2 className="text-3xl font-extrabold text-gray-800">Pimpinan IKA UNIMED</h2>
        </div>
        <div className="mx-auto grid max-w-5xl grid-cols-2 gap-4 sm:grid-cols-3 md:gap-6 lg:grid-cols-5">
          {pimpinan.map((item) => (
            <PimpinanCard key={item.key} pimpinan={item} />
          ))}
        </div>
      </div>
    </section>
  );
};

export default PimpinanSection;
