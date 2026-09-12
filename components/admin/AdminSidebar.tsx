"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { signOut } from "@/app/admin/actions";

const LINKS = [
  { href: "/admin", label: "Dashboard", icon: "dashboard", exact: true },
  { href: "/admin/products", label: "Products", icon: "inventory_2" },
  { href: "/admin/blog", label: "Blog Posts", icon: "article" },
  { href: "/admin/messages", label: "Messages", icon: "mail" },
];

export default function AdminSidebar({ email }: { email?: string }) {
  const pathname = usePathname();

  return (
    <aside className="w-full md:w-64 flex-shrink-0 bg-surface-container-lowest md:min-h-screen border-b md:border-b-0 md:border-r border-surface-container-high flex md:flex-col">
      <div className="p-space-lg flex flex-col gap-space-xs hidden md:flex">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img src="/images/cassavaforge-logo.png" alt="CassavaForge" className="h-7 w-auto object-contain" />
        <span className="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider">
          Admin
        </span>
      </div>
      <nav className="flex md:flex-col gap-1 p-space-sm md:p-space-md flex-1 overflow-x-auto">
        {LINKS.map((link) => {
          const isActive = link.exact ? pathname === link.href : pathname.startsWith(link.href);
          return (
            <Link
              key={link.href}
              href={link.href}
              className={
                isActive
                  ? "flex items-center gap-space-sm px-space-md py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md whitespace-nowrap"
                  : "flex items-center gap-space-sm px-space-md py-space-sm rounded-lg text-on-surface-variant hover:bg-surface-container font-label-md text-label-md whitespace-nowrap"
              }
            >
              <span className="material-symbols-outlined text-[20px]">{link.icon}</span>
              {link.label}
            </Link>
          );
        })}
      </nav>
      <div className="p-space-md hidden md:flex flex-col gap-space-xs border-t border-surface-container-high">
        {email && (
          <span className="font-body-sm text-body-sm text-on-surface-variant truncate">{email}</span>
        )}
        <form action={signOut}>
          <button
            type="submit"
            className="w-full text-left font-label-md text-label-md text-error px-space-md py-space-xs rounded-lg hover:bg-error-container transition-colors"
          >
            Sign Out
          </button>
        </form>
      </div>
    </aside>
  );
}
