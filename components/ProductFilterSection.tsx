import { supabase } from "@/lib/supabase";
import type { Product } from "@/lib/types";
import ProductFilter from "./ProductFilter";

export default async function ProductFilterSection() {
  const { data, error } = await supabase
    .from("products")
    .select("*")
    .order("display_order", { ascending: true });

  if (error) {
    console.error("Failed to load products:", error.message);
  }

  return <ProductFilter products={(data as Product[]) ?? []} />;
}
