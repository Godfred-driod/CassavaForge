import ProductForm from "@/components/admin/ProductForm";
import { createProduct } from "@/app/admin/actions";

export default function NewProductPage() {
  return (
    <div className="flex flex-col gap-space-lg">
      <h1 className="font-headline-lg text-headline-lg text-on-surface">New Product</h1>
      <ProductForm action={createProduct} />
    </div>
  );
}
