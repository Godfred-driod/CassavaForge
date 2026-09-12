import { createClient } from "@supabase/supabase-js";

const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL!;
const supabaseAnonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!;

if (!supabaseUrl || !supabaseAnonKey) {
  throw new Error(
    "Missing Supabase environment variables. Copy .env.local.example to .env.local and fill in your project's URL and anon key."
  );
}

// Safe to use on both the server and the client: this key is the public
// "anon" key, not a secret. What it's allowed to do is governed entirely by
// the Row Level Security policies on each table (see the migration in
// supabase/schema.sql) — e.g. it can insert contact messages but never read
// them back, and it can only read products/posts marked published = true.
export const supabase = createClient(supabaseUrl, supabaseAnonKey);
