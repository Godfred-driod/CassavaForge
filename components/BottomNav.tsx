"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

const TABS = [
  { label: "Home", href: "/", icon: "eco" },
  { label: "About", href: "/about", icon: "science" },
  { label: "Products", href: "/products", icon: "inventory_2" },
  { label: "Impact", href: "/impact", icon: "compost" },
  { label: "Blog", href: "/blog", icon: "article" },
  { label: "Contact", href: "/contact", icon: "contact_support" },
];

export default function BottomNav() {
  const pathname = usePathname();

  return (
    <nav className="md:hidden fixed bottom-0 inset-x-0 z-50 pb-safe bg-surface/85 backdrop-blur-xl shadow-[0_-2px_12px_rgba(0,0,0,0.04)]">
      <div className="flex items-center justify-around h-16 px-space-xs">
        {TABS.map((tab) => {
          const isActive = pathname === tab.href;
          return (
            <Link
              key={tab.href}
              href={tab.href}
              aria-current={isActive ? "page" : undefined}
              className={
                isActive
                  ? "flex flex-col items-center justify-center w-12 min-h-[44px] transition-colors text-primary font-bold"
                  : "flex flex-col items-center justify-center w-12 min-h-[44px] text-on-surface-variant transition-colors hover:text-primary"
              }
            >
              <span className="material-symbols-outlined text-[22px]">{tab.icon}</span>
              <span className="font-label-caps text-[10px] leading-tight">{tab.label}</span>
            </Link>
          );
        })}
      </div>
    </nav>
  );
}
