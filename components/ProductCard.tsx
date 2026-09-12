import type { Product } from "@/lib/types";
import Link from "next/link";
import { getProductImage } from "@/lib/image-fallbacks";

export default function ProductCard({ product }: { product: Product }) {
  return (
    <article
      className="product-card motion-reveal group flex min-h-[34rem] w-full flex-col bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 sm:min-h-0"
      data-category={product.category}
    >
      <div className="relative aspect-square w-full overflow-hidden bg-surface-container sm:aspect-[4/3]">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img
          className="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500 ease-out"
          alt={product.name}
          src={getProductImage(product)}
        />
        {product.application_grade && (
          <div className="absolute top-3 left-3 bg-surface/90 backdrop-blur-sm px-2.5 py-1 rounded-full">
            <span className="font-label-caps text-primary uppercase">
              {product.application_grade}
            </span>
          </div>
        )}
      </div>
      <div className="flex flex-grow flex-col justify-between p-space-xl sm:p-space-lg">
        <div>
          <div className="flex items-center justify-between mb-space-xs">
            {product.series_code && (
              <span className="font-label-caps text-on-surface-variant uppercase tracking-wider">
                Series {product.series_code}
              </span>
            )}
            {product.icon && (
              <span className="material-symbols-outlined text-primary text-[20px]">
                {product.icon}
              </span>
            )}
          </div>
          <h3 className="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors mb-space-xs sm:font-headline-sm sm:text-headline-sm">
            {product.name}
          </h3>
          {product.description && (
            <p className="font-body-sm text-body-sm text-on-surface-variant line-clamp-2">
              {product.description}
            </p>
          )}
          {(product.spec1_label || product.spec2_label) && (
            <div className="mt-space-md pt-space-sm bg-surface-container-low p-space-sm rounded-xl space-y-1">
              {product.spec1_label && (
                <div className="flex justify-between text-body-sm text-on-surface-variant">
                  <span>{product.spec1_label}:</span>
                  <span className="font-semibold text-on-surface">{product.spec1_value}</span>
                </div>
              )}
              {product.spec2_label && (
                <div className="flex justify-between text-body-sm text-on-surface-variant">
                  <span>{product.spec2_label}:</span>
                  <span className="font-semibold text-on-surface">{product.spec2_value}</span>
                </div>
              )}
            </div>
          )}
        </div>
        <div className="pt-space-lg">
          <Link
            className="inline-flex items-center font-label-md text-label-md text-primary group-hover:text-primary-container transition-colors"
            href={`/products/${product.slug}`}
            aria-label={`Learn more about ${product.name}`}
          >
            Learn More
            <span className="material-symbols-outlined text-[18px] ml-1 group-hover:translate-x-1 transition-transform">
              arrow_forward
            </span>
          </Link>
        </div>
      </div>
    </article>
  );
}
