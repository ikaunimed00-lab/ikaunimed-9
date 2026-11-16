import type { ReactNode } from "react";
import { Link } from "@inertiajs/react";
import { isExternalUrl } from "./groupEvents";

type EventLinkProps = {
  href: string;
  className?: string;
  children: ReactNode;
};

const EventLink = ({ href, className, children }: EventLinkProps) => {
  if (isExternalUrl(href) || href.includes("#")) {
    const external = isExternalUrl(href);

    return (
      <a
        href={href}
        className={className}
        target={external ? "_blank" : undefined}
        rel={external ? "noopener noreferrer" : undefined}
      >
        {children}
      </a>
    );
  }

  return (
    <Link href={href} className={className}>
      {children}
    </Link>
  );
};

export default EventLink;
