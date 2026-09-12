"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

const NAV_LINKS = [
  { label: "Home", href: "/" },
  { label: "About", href: "/about" },
  { label: "Products", href: "/products" },
  { label: "Impact", href: "/impact" },
  { label: "Blog", href: "/blog" },
];

const LOGO_SRC = "/images/cassavaforge-logo.png";

export default function Header() {
  const pathname = usePathname();

  return (
    <header className="fixed top-0 left-0 right-0 z-50 bg-surface/85 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
      <div className="h-20 max-w-[1240px] mx-auto px-layout-margin-mobile lg:px-layout-margin-desktop flex items-center justify-between gap-space-lg">
        <Link href="/" className="flex items-center gap-space-sm">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img alt="CassavaForge Logo" className="h-8 w-auto object-contain" src={LOGO_SRC} />
          <span className="font-headline-sm text-headline-sm tracking-tight text-on-surface">
            CassavaForge
          </span>
        </Link>

        <nav className="hidden md:flex items-center gap-space-xl">
          {NAV_LINKS.map((link) => {
            const isActive = pathname === link.href;
            return (
              <Link
                key={link.href}
                href={link.href}
                aria-current={isActive ? "page" : undefined}
                className={
                  isActive
                    ? "transition-colors text-primary font-bold"
                    : "font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors"
                }
              >
                {link.label}
              </Link>
            );
          })}
        </nav>

        <div className="flex items-center gap-space-md">
          <Link
            href="/contact"
            className="hidden sm:inline-flex items-center justify-center bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md px-space-lg py-space-xs rounded-full transition-all"
          >
            Get in Touch
          </Link>
          <div className="w-8 h-8 rounded-full bg-primary flex items-center justify-center shadow-sm">
            <span className="material-symbols-outlined text-on-primary text-[18px]">person</span>
          </div>
        </div>
      </div>
    </header>
  );
}
