import type { Metadata } from "next";
import AnalyticsTracker from "@/components/AnalyticsTracker";
import "./globals.css";

export const metadata: Metadata = {
  title: "CassavaForge | Engineered Bioplastics",
  description:
    "CassavaForge develops innovative, biodegradable bioplastic materials from cassava starch — offering a greener alternative to conventional plastics.",
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000"),
  alternates: {
    canonical: "/",
  },
  robots: {
    index: true,
    follow: true,
  },
  keywords: ["cassava bioplastics", "biodegradable packaging", "sustainable materials", "bioplastic films"],
  openGraph: {
    title: "CassavaForge | Engineered Bioplastics",
    description:
      "CassavaForge develops cassava-based bioplastic materials for packaging and selected plastic applications.",
    type: "website",
    url: "/",
  },
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <link
          href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&display=swap"
          rel="stylesheet"
        />
        <link
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap"
          rel="stylesheet"
        />
      </head>
      <body className="bg-surface font-body-md text-body-md text-on-surface antialiased">
        <AnalyticsTracker />
        {children}
      </body>
    </html>
  );
}
