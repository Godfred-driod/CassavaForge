import ImageUploadField from "./ImageUploadField";
import type { Product } from "@/lib/types";

const inputClass =
  "w-full bg-surface-container-low text-on-surface font-body-md text-body-md px-space-md py-space-sm rounded-lg focus:outline-none focus:shadow-[0_0_0_2px_#00652c] transition-all";
const labelClass = "font-label-md text-label-md text-on-surface-variant";

export default function ProductForm({
  action,
  product,
}: {
  action: (formData: FormData) => void;
  product?: Product;
}) {
  return (
    <form action={action} className="flex flex-col gap-space-md max-w-xl">
      <div className="flex flex-col gap-space-2xs">
        <label className={labelClass} htmlFor="name">
          Name *
        </label>
        <input
          id="name"
          name="name"
          required
          defaultValue={product?.name}
          className={inputClass}
        />
      </div>
      <div className="flex flex-col gap-space-2xs">
        <label className={labelClass} htmlFor="slug">
          Slug * <span className="text-[11px]">(unique, used in the URL — e.g. bioplastic-films)</span>
        </label>
        <input
          id="slug"
          name="slug"
          required
          defaultValue={product?.slug}
          className={inputClass}
        />
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
        <div className="flex flex-col gap-space-2xs">
          <label className={labelClass} htmlFor="category">
            Category *
          </label>
          <select
            id="category"
            name="category"
            defaultValue={product?.category ?? "packaging"}
            className={inputClass}
          >
            <option value="packaging">Packaging</option>
            <option value="films">Films</option>
            <option value="cutlery">Cutlery</option>
          </select>
        </div>
        <div className="flex flex-col gap-space-2xs">
          <label className={labelClass} htmlFor="series_code">
            Series Code
          </label>
          <input
            id="series_code"
            name="series_code"
            defaultValue={product?.series_code ?? ""}
            className={inputClass}
          />
        </div>
      </div>
      <div className="flex flex-col gap-space-2xs">
        <label className={labelClass} htmlFor="application_grade">
          Application Grade Badge <span className="text-[11px]">(e.g. Food Service Grade)</span>
        </label>
        <input
          id="application_grade"
          name="application_grade"
          defaultValue={product?.application_grade ?? ""}
          className={inputClass}
        />
      </div>
      <div className="flex flex-col gap-space-2xs">
        <label className={labelClass} htmlFor="description">
          Description
        </label>
        <textarea
          id="description"
          name="description"
          rows={3}
          defaultValue={product?.description ?? ""}
          className={inputClass}
        />
      </div>
      <ImageUploadField
        name="image_url"
        label="Product Photo"
        defaultValue={product?.image_url}
        folder="products"
      />
      <div className="flex flex-col gap-space-2xs">
        <label className={labelClass} htmlFor="icon">
          Icon <span className="text-[11px]">(Material Symbols name, e.g. restaurant)</span>
        </label>
        <input id="icon" name="icon" defaultValue={product?.icon ?? ""} className={inputClass} />
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
        <div className="flex flex-col gap-space-2xs">
          <label className={labelClass} htmlFor="spec1_label">
            Spec 1 Label
          </label>
          <input
            id="spec1_label"
            name="spec1_label"
            defaultValue={product?.spec1_label ?? ""}
            className={inputClass}
          />
        </div>
        <div className="flex flex-col gap-space-2xs">
          <label className={labelClass} htmlFor="spec1_value">
            Spec 1 Value
          </label>
          <input
            id="spec1_value"
            name="spec1_value"
            defaultValue={product?.spec1_value ?? ""}
            className={inputClass}
          />
        </div>
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
        <div className="flex flex-col gap-space-2xs">
          <label className={labelClass} htmlFor="spec2_label">
            Spec 2 Label
          </label>
          <input
            id="spec2_label"
            name="spec2_label"
            defaultValue={product?.spec2_label ?? ""}
            className={inputClass}
          />
        </div>
        <div className="flex flex-col gap-space-2xs">
          <label className={labelClass} htmlFor="spec2_value">
            Spec 2 Value
          </label>
          <input
            id="spec2_value"
            name="spec2_value"
            defaultValue={product?.spec2_value ?? ""}
            className={inputClass}
          />
        </div>
      </div>
      <div className="flex flex-col gap-space-2xs">
        <label className={labelClass} htmlFor="display_order">
          Display Order <span className="text-[11px]">(lower shows first)</span>
        </label>
        <input
          id="display_order"
          name="display_order"
          type="number"
          defaultValue={product?.display_order ?? 0}
          className={inputClass}
        />
      </div>
      <label className="flex items-center gap-space-xs font-label-md text-label-md text-on-surface">
        <input
          type="checkbox"
          name="published"
          defaultChecked={product?.published ?? true}
          className="w-5 h-5 accent-primary"
        />
        Published (visible on the live site)
      </label>
      <button
        type="submit"
        className="w-fit bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md px-space-xl py-space-sm rounded-full transition-all"
      >
        {product ? "Save Changes" : "Create Product"}
      </button>
    </form>
  );
}
