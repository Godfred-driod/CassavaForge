import StitchHtml from "@/components/StitchHtml";
import { pageHtml } from "./content";

// Refetch product data from Supabase at most once per hour, so edits made
// in the Supabase dashboard show up without needing to redeploy the site.
export const revalidate = 3600;

export default function ProductsPage() {
  return <StitchHtml html={pageHtml} />;
}
