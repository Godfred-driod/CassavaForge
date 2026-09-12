import type { BlogPost } from "@/lib/types";
import Link from "next/link";

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
}

export default function BlogCard({ post }: { post: BlogPost }) {
  return (
    <article className="group flex flex-col bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden">
      <div className="relative w-full aspect-[16/10] overflow-hidden bg-surface-container">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img
          className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          alt={post.title}
          src={post.image_url ?? "/images/cassavaforge-logo.png"}
        />
        {post.category && (
          <div className="absolute top-3 left-3">
            <span className="bg-surface/90 backdrop-blur-md font-label-caps text-label-caps text-primary px-3 py-1 rounded-full uppercase tracking-wider font-bold shadow-sm">
              {post.category}
            </span>
          </div>
        )}
      </div>
      <div className="p-space-lg flex flex-col flex-1 justify-between gap-space-md">
        <div className="flex flex-col gap-space-xs">
          <div className="flex items-center gap-space-2xs text-on-surface-variant font-body-sm text-body-sm">
            <span className="material-symbols-outlined text-[15px] text-primary">calendar_today</span>
            <span>{formatDate(post.published_at)}</span>
            {post.read_minutes && (
              <>
                <span className="mx-1 font-bold text-outline-variant">•</span>
                <span>{post.read_minutes} min read</span>
              </>
            )}
          </div>
          <h3 className="font-headline-sm text-headline-sm text-on-surface group-hover:text-primary transition-colors leading-snug">
            {post.title}
          </h3>
          {post.excerpt && (
            <p className="font-body-md text-body-md text-on-surface-variant line-clamp-2">
              {post.excerpt}
            </p>
          )}
        </div>
        <Link
          className="inline-flex items-center font-label-md text-label-md text-primary group-hover:text-primary-container transition-colors"
          href={`/blog/${post.slug}`}
          aria-label={`Read article: ${post.title}`}
        >
          Read Article
          <span className="material-symbols-outlined text-[18px] ml-1 group-hover:translate-x-1 transition-transform">
            arrow_forward
          </span>
        </Link>
      </div>
    </article>
  );
}
