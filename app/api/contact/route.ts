import { NextRequest, NextResponse } from "next/server";
import { supabase } from "@/lib/supabase";

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const MAX_NAME = 200;
const MAX_EMAIL = 254;
const MAX_MESSAGE = 5000;
const MAX_BODY_BYTES = 10000;
const RATE_LIMIT_WINDOW_MS = 10 * 60 * 1000;
const RATE_LIMIT_MAX = 5;

const requestCounts = new Map<string, { count: number; resetAt: number }>();

function getClientKey(req: NextRequest) {
  return (
    req.headers.get("x-forwarded-for")?.split(",")[0]?.trim() ??
    req.headers.get("x-real-ip") ??
    "unknown"
  );
}

function isRateLimited(key: string) {
  const now = Date.now();
  const current = requestCounts.get(key);

  if (!current || current.resetAt <= now) {
    requestCounts.set(key, { count: 1, resetAt: now + RATE_LIMIT_WINDOW_MS });
    return false;
  }

  current.count += 1;
  return current.count > RATE_LIMIT_MAX;
}

export async function POST(req: NextRequest) {
  if (req.headers.get("content-type")?.toLowerCase().split(";")[0] !== "application/json") {
    return NextResponse.json({ error: "Content-Type must be application/json" }, { status: 415 });
  }

  const origin = req.headers.get("origin");
  if (origin && origin !== new URL(req.url).origin) {
    return NextResponse.json({ error: "Origin not allowed" }, { status: 403 });
  }

  const contentLength = Number(req.headers.get("content-length") ?? 0);
  if (contentLength > MAX_BODY_BYTES) {
    return NextResponse.json({ error: "Request is too large" }, { status: 413 });
  }

  if (isRateLimited(getClientKey(req))) {
    return NextResponse.json({ error: "Too many requests" }, { status: 429 });
  }

  const body = await req.json().catch(() => null);

  if (!body) {
    return NextResponse.json({ error: "Invalid request" }, { status: 400 });
  }

  // Honeypot: a hidden field real visitors never fill in. Bots that
  // auto-fill every field will trip this and get silently accepted-looking
  // (200 OK) but never actually written to the database.
  if (typeof body.company === "string" && body.company.trim() !== "") {
    return NextResponse.json({ ok: true });
  }

  const fullName = typeof body.fullName === "string" ? body.fullName.trim() : "";
  const emailAddress = typeof body.emailAddress === "string" ? body.emailAddress.trim() : "";
  const messageBody = typeof body.messageBody === "string" ? body.messageBody.trim() : "";

  if (!fullName || !emailAddress || !messageBody) {
    return NextResponse.json({ error: "Missing required fields" }, { status: 400 });
  }

  if (fullName.length > MAX_NAME || emailAddress.length > MAX_EMAIL || messageBody.length > MAX_MESSAGE) {
    return NextResponse.json({ error: "One or more fields are too long" }, { status: 400 });
  }

  if (!EMAIL_RE.test(emailAddress)) {
    return NextResponse.json({ error: "Invalid email address" }, { status: 400 });
  }

  const { error } = await supabase.from("contact_messages").insert({
    full_name: fullName,
    email_address: emailAddress,
    message_body: messageBody,
  });

  if (error) {
    console.error("Failed to save contact message:", error.message);
    return NextResponse.json({ error: "Could not save message" }, { status: 500 });
  }

  // TODO (optional next step): send a notification email to
  // cassavaforge@gmail.com when a new inquiry comes in (e.g. via Resend).
  return NextResponse.json({ ok: true });
}


