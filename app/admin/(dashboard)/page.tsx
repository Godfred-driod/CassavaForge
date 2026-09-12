import Link from "next/link";
import { createClient } from "@/lib/supabase/server";
import { createAdminClient } from "@/lib/supabase/admin";

export default async function AdminDashboardPage() {
  const supabase = await createClient();
  let visitorCount: number | null = null;
  if (process.env.SUPABASE_SERVICE_ROLE_KEY) {
    try {
    const analytics = createAdminClient();
    const { count } = await analytics
      .from("site_visitors")
      .select("visitor_id", { count: "exact", head: true });
    visitorCount = count;
    } catch (error) {
      console.error("Analytics is not configured:", error);
    }
  }

  const [{ count: productCount }, { count: postCount }, { count: unreadCount }] =
    await Promise.all([
      supabase.from("products").select("*", { count: "exact", head: true }),
      supabase.from("blog_posts").select("*", { count: "exact", head: true }),
      supabase.from("contact_messages").select("*", { count: "exact", head: true }).eq("read", false),
    ]);

  const cards = [
    { label: "Products", count: productCount ?? 0, href: "/admin/products", icon: "inventory_2" },
    { label: "Blog Posts", count: postCount ?? 0, href: "/admin/blog", icon: "article" },
    { label: "Unread Messages", count: unreadCount ?? 0, href: "/admin/messages", icon: "mail" },
    { label: "Unique Visits", count: visitorCount ?? "-", href: "/admin", icon: "visibility" },
  ];

  return (
    <div className="flex flex-col gap-space-lg">
      <div>
        <h1 className="font-headline-lg text-headline-lg text-on-surface">Dashboard</h1>
        <p className="font-body-md text-body-md text-on-surface-variant">
          Quick overview of your CassavaForge site.
        </p>
      </div>
      <div className="grid grid-cols-1 gap-space-md sm:grid-cols-2 lg:grid-cols-4">
        {cards.map((card) => (
          <Link
            key={card.href}
            href={card.href}
            className="flex flex-col gap-space-xs p-space-lg bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-md transition-all"
          >
            <span className="material-symbols-outlined text-primary text-[28px]">{card.icon}</span>
            <span className="font-display-hero text-headline-xl text-on-surface font-bold">
              {card.count}
            </span>
            <span className="font-label-md text-label-md text-on-surface-variant">{card.label}</span>
          </Link>
        ))}
      </div>
    </div>
  );
}
