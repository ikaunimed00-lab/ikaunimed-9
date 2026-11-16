import type { ReactNode } from "react";

type SectionHeadingProps = {
  id: string;
  eyebrow: string;
  title: string;
  children?: ReactNode;
};

const SectionHeading = ({ id, eyebrow, title, children }: SectionHeadingProps) => (
  <div className="mx-auto mb-10 max-w-3xl text-center">
    <p className="mb-2 text-xs font-bold uppercase tracking-[0.3em] text-[#006837]">{eyebrow}</p>
    <h2 id={id} className="text-2xl font-extrabold text-[#0B3D2E] md:text-4xl">
      {title}
    </h2>
    {children && <div className="mt-4 text-base text-gray-600 md:text-lg">{children}</div>}
  </div>
);

export default SectionHeading;
