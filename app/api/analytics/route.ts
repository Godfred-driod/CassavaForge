import { NextRequest, NextResponse } from "next/server";
import { supabase } from "@/lib/supabase";

const UUID_RE = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;
const MAX_REQUESTS_PER_WINDOW = 30;
const WINDOW_MS = 10 * 60 * 1000;
const requestCounts = new Map<string, { count: number; resetAt: number }>();

function isRateLimited(key: string) {
  const now = Date.now();
  const entry = requestCounts.get(key);
  if (!entry || entry.resetAt <= now) {
    requestCounts.set(key, { count: 1, resetAt: now + WINDOW_MS });
    return false;
  }

  entry.count += 1;
  return entry.count > MAX_REQUESTS_PER_WINDOW;
}

export async function POST(request: NextRequest) {
  const body = await request.json().catch(() => null);
  const visitorId = body?.visitorId;
  const clientKey = request.headers.get("x-forwarded-for")?.split(",")[0]?.trim() ?? "unknown";

  if (isRateLimited(clientKey)) {
    return NextResponse.json({ ok: false }, { status: 429 });
  }

  if (typeof visitorId !== "string" || !UUID_RE.test(visitorId)) {
    return NextResponse.json({ error: "Invalid visitor ID" }, { status: 400 });
  }

  const { error } = await supabase.from("site_visitors").upsert(
    { visitor_id: visitorId },
    { onConflict: "visitor_id", ignoreDuplicates: true }
  );

  if (error) {
    console.error("Failed to record visitor:", error.message);
    return NextResponse.json({ ok: false }, { status: 503 });
  }

  return NextResponse.json({ ok: true });
}