import AdminSidebar from "@/components/admin/AdminSidebar";
import { requireAdmin } from "@/lib/admin-auth";

export default async function DashboardLayout({ children }: { children: React.ReactNode }) {
  const { user } = await requireAdmin();

  return (
    <div className="min-h-screen w-full flex flex-col md:flex-row bg-surface">
      <AdminSidebar email={user?.email} />
      <main className="flex-1 p-space-lg lg:p-space-2xl max-w-[1100px]">{children}</main>
    </div>
  );
}
