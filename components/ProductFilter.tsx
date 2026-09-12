"use client";

import { useState } from "react";
import ProductCard from "./ProductCard";
import type { Product } from "@/lib/types";

const FILTERS: { key: "all" | Product["category"]; label: string }[] = [
  { key: "all", label: "All Applications" },
  { key: "packaging", label: "Rigid Packaging" },
  { key: "films", label: "Flexible Films" },
  { key: "cutlery", label: "Molded Cutlery" },
];

const ACTIVE_CLASS =
  "filter-btn active px-3 py-1 rounded-full text-label-caps uppercase bg-primary text-on-primary transition-colors";
const INACTIVE_CLASS =
  "filter-btn px-3 py-1 rounded-full text-label-caps uppercase bg-surface text-on-surface-variant hover:text-primary transition-colors";

export default function ProductFilter({ products }: { products: Product[] }) {
  const [active, setActive] = useState<"all" | Product["category"]>("all");

  const visible =
    active === "all" ? products : products.filter((p) => p.category === active);

  return (
    <>
      <div className="flex flex-wrap items-center gap-1.5" id="productFilterButtons">
        {FILTERS.map((f) => (
          <button
            key={f.key}
            type="button"
            onClick={() => setActive(f.key)}
            className={active === f.key ? ACTIVE_CLASS : INACTIVE_CLASS}
          >
            {f.label}
          </button>
        ))}
      </div>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-layout-gutter" id="productsGrid">
        {visible.length === 0 ? (
          <p className="font-body-md text-on-surface-variant col-span-full py-space-xl text-center">
            No products in this category yet — check back soon.
          </p>
        ) : (
          visible.map((product) => <ProductCard key={product.id} product={product} />)
        )}
      </div>
    </>
  );
}
