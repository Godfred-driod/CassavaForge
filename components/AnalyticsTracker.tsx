"use client";

import { useEffect } from "react";
import { usePathname } from "next/navigation";

const SESSION_KEY = "cassavaforge-visitor-session";

export default function AnalyticsTracker() {
  const pathname = usePathname();

  useEffect(() => {
    if (pathname.startsWith("/admin")) return;

    let visitorId = sessionStorage.getItem(SESSION_KEY);
    if (!visitorId) {
      visitorId = crypto.randomUUID();
      sessionStorage.setItem(SESSION_KEY, visitorId);
    }

    void fetch("/api/analytics", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ visitorId }),
      keepalive: true,
    }).catch(() => {
      // Analytics must never interfere with the visitor experience.
    });
  }, [pathname]);

  return null;
}