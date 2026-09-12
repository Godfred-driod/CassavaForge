import type { MetadataRoute } from "next";

const publicRoutes = ["", "/about", "/products", "/impact", "/blog", "/contact"];

export default function sitemap(): MetadataRoute.Sitemap {
  const siteUrl = process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000";

  return publicRoutes.map((route) => ({
    url: `${siteUrl}${route}`,
    changeFrequency: route === "/blog" || route === "/products" ? "weekly" : "monthly",
    priority: route === "" ? 1 : 0.8,
  }));
}
