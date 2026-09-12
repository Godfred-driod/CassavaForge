import type { BlogPost, Product } from "@/lib/types";

const PRODUCT_IMAGES: Record<Product["category"], string> = {
  packaging: "/images/products-clean-minimal-studio-arrangement-of-transparent-3.jpg",
  films: "/images/products-overhead-studio-capture-of-natural-beige-2.jpg",
  cutlery: "/images/products-editorial-product-photography-of-organic-cassava-4.jpg",
};

const DEFAULT_BLOG_IMAGE = "/images/blog-a-vivid-vibrant-green-young-plant-3.jpg";

export function getProductImage(product: Pick<Product, "category" | "image_url">) {
  return product.image_url || PRODUCT_IMAGES[product.category];
}

export function getBlogImage(post: Pick<BlogPost, "category" | "slug" | "image_url">) {
  if (post.image_url) return post.image_url;

  const context = `${post.category ?? ""} ${post.slug}`.toLowerCase();

  if (/(farm|farmer|community|impact|grower)/.test(context)) {
    return "/images/blog-smiling-african-smallholder-cassava-farmers-standing-4.jpg";
  }

  if (/(research|science|innovation|technology|lab)/.test(context)) {
    return "/images/blog-modern-biotechnology-laboratory-glassware-clear-glass-2.jpg";
  }

  if (/(material|packaging|design|product)/.test(context)) {
    return "/images/blog-minimalist-product-design-showcase-featuring-eco-5.jpg";
  }

  return DEFAULT_BLOG_IMAGE;
}