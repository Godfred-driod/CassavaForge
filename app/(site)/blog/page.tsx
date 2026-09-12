import StitchHtml from "@/components/StitchHtml";
import { pageHtml } from "./content";

// Refetch blog posts from Supabase at most once per hour, so new posts
// published via the Supabase dashboard show up without a redeploy.
export const revalidate = 3600;

export default function BlogPage() {
  return <StitchHtml html={pageHtml} />;
}
