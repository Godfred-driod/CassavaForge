import Link from "next/link";
import { notFound } from "next/navigation";
import type { Product } from "@/lib/types";
import { supabase } from "@/lib/supabase";

const MATERIAL_GUIDANCE: Record<
  Product["category"],
  { eyebrow: string; summary: string; bullets: string[] }
> = {
  packaging: {
    eyebrow: "Rigid packaging profile",
    summary: "Form-ready material thinking for food service and protective packaging applications.",
    bullets: [
      "Designed around cassava-derived feedstock and lower-impact end-of-life goals.",
      "Suitable for teams evaluating thermoforming or molded packaging workflows.",
      "Balance stiffness, moisture management, and responsible material recovery in testing.",
    ],
  },
  films: {
    eyebrow: "Flexible film profile",
    summary: "A flexible material direction for teams exploring lightweight packaging and agricultural film formats.",
    bullets: [
      "Evaluate seal behavior, tensile performance, and moisture barrier needs together.",
      "Designed for development programs that need a plant-derived alternative to conventional films.",
      "Pilot with real humidity, temperature, and shelf-life conditions before scale-up.",
    ],
  },
  cutlery: {
    eyebrow: "Molded cutlery profile",
    summary: "Molded application development for food-service formats where function and responsible disposal matter.",
    bullets: [
      "Assess rigidity, heat exposure, and user comfort in the intended serving environment.",
      "A practical direction for replacing selected single-use petroleum-based items.",
      "Validate compostability claims against the standards and facilities relevant to your market.",
    ],
  },
};

export default async function ProductDetailPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const { data } = await supabase
    .from("products")
    .select("*")
    .eq("slug", slug)
    .eq("published", true)
    .single();

  if (!data) notFound();

  const product = data as Product;
  const guidance = MATERIAL_GUIDANCE[product.category];

  return (
    <div className="w-full bg-surface">
      <section className="w-full bg-surface-container-low py-space-3xl lg:py-space-4xl">
        <div className="mx-auto grid max-w-[1240px] grid-cols-1 items-center gap-layout-gutter px-layout-margin-mobile lg:grid-cols-12 lg:px-layout-margin-desktop">
          <div className="motion-reveal order-2 flex flex-col gap-space-md lg:order-1 lg:col-span-6">
            <Link
              href="/products"
              className="font-label-md text-label-md text-primary transition-colors hover:text-primary-container"
            >
              Back to products
            </Link>
            <span className="font-label-caps text-label-caps uppercase tracking-wider text-primary">
              {guidance.eyebrow}
            </span>
            <h1 className="font-headline-xl text-headline-xl text-on-surface">
              {product.name}
            </h1>
            {product.description && (
              <p className="max-w-2xl font-body-lg text-body-lg text-on-surface-variant">
                {product.description}
              </p>
            )}
            <p className="max-w-2xl font-body-md text-body-md text-on-surface-variant">
              {guidance.summary}
            </p>
            <Link
              href="/contact"
              className="inline-flex w-fit items-center justify-center rounded-full bg-primary px-space-xl py-space-sm font-label-md text-label-md text-on-primary transition-colors hover:bg-primary-container"
            >
              Discuss this material
            </Link>
          </div>

          <div className="motion-reveal order-1 overflow-hidden rounded-3xl bg-surface-container shadow-xl lg:order-2 lg:col-span-6">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              className="aspect-[4/3] h-full w-full object-cover transition-transform duration-700 hover:scale-105"
              src={product.image_url ?? "/images/cassavaforge-logo.png"}
              alt={product.name}
            />
          </div>
        </div>
      </section>

      <section className="w-full bg-surface py-space-3xl">
        <div className="mx-auto grid max-w-[1240px] grid-cols-1 gap-layout-gutter px-layout-margin-mobile lg:grid-cols-12 lg:px-layout-margin-desktop">
          <div className="motion-reveal lg:col-span-7">
            <span className="font-label-caps text-label-caps uppercase tracking-wider text-primary">
              Material development notes
            </span>
            <h2 className="mt-space-xs font-headline-xl text-headline-xl text-on-surface">
              Built for better conversations between material and machine.
            </h2>
            <ul className="mt-space-lg flex flex-col gap-space-md">
              {guidance.bullets.map((bullet) => (
                <li key={bullet} className="flex gap-space-sm font-body-md text-body-md text-on-surface-variant">
                  <span className="material-symbols-outlined text-primary">check_circle</span>
                  <span>{bullet}</span>
                </li>
              ))}
            </ul>
          </div>

          <div className="motion-reveal rounded-2xl bg-surface-container-low p-space-xl lg:col-span-5">
            <h2 className="font-headline-md text-headline-md text-on-surface">Technical snapshot</h2>
            <dl className="mt-space-md flex flex-col divide-y divide-outline-variant/40">
              {product.series_code && (
                <div className="flex items-center justify-between gap-space-md py-space-sm">
                  <dt className="font-body-sm text-body-sm text-on-surface-variant">Series</dt>
                  <dd className="font-label-md text-label-md text-on-surface">{product.series_code}</dd>
                </div>
              )}
              {product.application_grade && (
                <div className="flex items-center justify-between gap-space-md py-space-sm">
                  <dt className="font-body-sm text-body-sm text-on-surface-variant">Application grade</dt>
                  <dd className="font-label-md text-label-md text-on-surface">{product.application_grade}</dd>
                </div>
              )}
              {product.spec1_label && (
                <div className="flex items-center justify-between gap-space-md py-space-sm">
                  <dt className="font-body-sm text-body-sm text-on-surface-variant">{product.spec1_label}</dt>
                  <dd className="font-label-md text-label-md text-on-surface">{product.spec1_value}</dd>
                </div>
              )}
              {product.spec2_label && (
                <div className="flex items-center justify-between gap-space-md py-space-sm">
                  <dt className="font-body-sm text-body-sm text-on-surface-variant">{product.spec2_label}</dt>
                  <dd className="font-label-md text-label-md text-on-surface">{product.spec2_value}</dd>
                </div>
              )}
            </dl>
          </div>
        </div>
      </section>
    </div>
  );
}