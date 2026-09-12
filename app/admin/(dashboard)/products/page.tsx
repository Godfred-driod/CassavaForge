import Link from "next/link";
import { createClient } from "@/lib/supabase/server";
import type { Product } from "@/lib/types";
import { deleteProduct } from "@/app/admin/actions";

export default async function AdminProductsPage() {
  const supabase = await createClient();
  const { data } = await supabase
    .from("products")
    .select("*")
    .order("display_order", { ascending: true });

  const products = (data as Product[]) ?? [];

  return (
    <div className="flex flex-col gap-space-lg">
      <div className="flex items-center justify-between">
        <h1 className="font-headline-lg text-headline-lg text-on-surface">Products</h1>
        <Link
          href="/admin/products/new"
          className="bg-primary text-on-primary font-label-md text-label-md px-space-lg py-space-sm rounded-full"
        >
          + New Product
        </Link>
      </div>

      <div className="flex flex-col gap-space-sm">
        {products.length === 0 && (
          <p className="font-body-md text-on-surface-variant">No products yet.</p>
        )}
        {products.map((p) => (
          <div
            key={p.id}
            className="flex items-center gap-space-md p-space-md bg-surface-container-lowest rounded-xl shadow-sm"
          >
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src={p.image_url ?? "/images/cassavaforge-logo.png"}
              alt=""
              className="w-16 h-16 object-cover rounded-lg bg-surface-container flex-shrink-0"
            />
            <div className="flex-1 min-w-0">
              <p className="font-label-md text-label-md text-on-surface truncate">{p.name}</p>
              <p className="font-body-sm text-body-sm text-on-surface-variant">
                {p.category} {!p.published && "· draft"}
              </p>
            </div>
            <Link
              href={`/admin/products/${p.id}`}
              className="font-label-md text-label-md text-primary px-space-sm py-space-xs"
            >
              Edit
            </Link>
            <form action={deleteProduct}>
              <input type="hidden" name="id" value={p.id} />
              <button
                type="submit"
                className="font-label-md text-label-md text-error px-space-sm py-space-xs"
              >
                Delete
              </button>
            </form>
          </div>
        ))}
      </div>
    </div>
  );
}
