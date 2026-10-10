import { useEffect, useState } from "react";
import RegisterButton from "./RegisterButton";

type StickyRegisterBarProps = {
  /** CSS selectors of areas that already show a register button (or the footer); the bar hides while any is on screen. */
  hideWhenVisible: string;
};

/**
 * Mobile-only bottom bar shown while registration is open. The floating help
 * button sits above it (FloatingCTA with liftOnMobile).
 */
const StickyRegisterBar = ({ hideWhenVisible }: StickyRegisterBarProps) => {
  const [visibleCount, setVisibleCount] = useState(1);

  useEffect(() => {
    const targets = Array.from(document.querySelectorAll(hideWhenVisible));

    if (typeof IntersectionObserver === "undefined" || targets.length === 0) {
      setVisibleCount(0);

      return;
    }

    const visible = new Set<Element>();
    const observer = new IntersectionObserver((entries) => {
      for (const entry of entries) {
        if (entry.isIntersecting) {
          visible.add(entry.target);
        } else {
          visible.delete(entry.target);
        }
      }

      setVisibleCount(visible.size);
    });

    targets.forEach((target) => observer.observe(target));

    return () => observer.disconnect();
  }, [hideWhenVisible]);

  if (visibleCount > 0) {
    return null;
  }

  return (
    <div className="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white/95 px-4 py-3 shadow-[0_-4px_16px_rgba(0,0,0,0.08)] backdrop-blur md:hidden">
      <RegisterButton isOpen className="w-full" />
    </div>
  );
};

export default StickyRegisterBar;
