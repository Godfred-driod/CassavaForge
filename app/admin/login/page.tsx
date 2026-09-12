import LoginForm from "@/components/admin/LoginForm";

export default async function AdminLoginPage({
  searchParams,
}: {
  searchParams: Promise<{ error?: string }>;
}) {
  const { error } = await searchParams;

  return (
    <div className="min-h-screen w-full flex items-center justify-center bg-surface-container-low px-layout-margin-mobile">
      <div className="w-full max-w-sm bg-surface-container-lowest rounded-2xl shadow-lg p-space-xl flex flex-col gap-space-lg">
        <div className="flex flex-col items-center gap-space-xs text-center">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img src="/images/cassavaforge-logo.png" alt="CassavaForge" className="h-10 w-auto object-contain" />
          <h1 className="font-headline-md text-headline-md text-on-surface">Admin</h1>
          <p className="font-body-sm text-body-sm text-on-surface-variant">
            Sign in to manage products, posts, and messages.
          </p>
        </div>
        <LoginForm initialError={error === "unauthorized" ? "Your account is signed in but is not authorized for the admin panel. Add your email to ADMIN_EMAILS or set app_metadata.role to admin." : undefined} />
      </div>
    </div>
  );
}
