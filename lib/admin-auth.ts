import type { User } from "@supabase/supabase-js";
import { redirect } from "next/navigation";
import { createClient } from "@/lib/supabase/server";

function configuredAdminEmails() {
  return new Set(
    (process.env.ADMIN_EMAILS ?? "")
      .split(",")
      .map((email) => email.trim().toLowerCase())
      .filter(Boolean)
  );
}

export function isAdminUser(user: User | null | undefined) {
  if (!user) return false;

  const role = user.app_metadata?.role;
  const email = user.email?.toLowerCase();
  return role === "admin" || (email ? configuredAdminEmails().has(email) : false);
}

export async function requireAdmin() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();

  if (!isAdminUser(user)) {
    redirect("/admin/login?error=unauthorized");
  }

  return { supabase, user };
}