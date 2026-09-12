import Link from "next/link";
import { notFound } from "next/navigation";
import type { BlogPost } from "@/lib/types";
import { supabase } from "@/lib/supabase";

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric",
  });
}

export default async function BlogArticlePage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const { data } = await supabase
    .from("blog_posts")
    .select("*")
    .eq("slug", slug)
    .eq("published", true)
    .single();

  if (!data) notFound();

  const post = data as BlogPost;
  const paragraphs = (post.content ?? post.excerpt ?? "").split(/\r?\n\s*\r?\n/);

  return (
    <article className="w-full bg-surface">
      <header className="w-full bg-primary text-on-primary">
        <div className="mx-auto max-w-[900px] px-layout-margin-mobile py-space-3xl lg:px-layout-margin-desktop lg:py-space-4xl">
          <Link
            href="/blog"
            className="font-label-md text-label-md text-secondary-fixed transition-colors hover:text-on-primary"
          >
            Back to Blog
          </Link>
          {post.category && (
            <span className="mt-space-xl block font-label-caps text-label-caps uppercase tracking-wider text-secondary-fixed">
              {post.category}
            </span>
          )}
          <h1 className="mt-space-xs font-headline-xl text-headline-xl text-on-primary">
            {post.title}
          </h1>
          <div className="mt-space-md flex flex-wrap gap-x-space-sm gap-y-space-2xs font-body-sm text-body-sm text-on-primary-container">
            <span>{formatDate(post.published_at)}</span>
            {post.read_minutes && <span>{post.read_minutes} min read</span>}
            {post.author && <span>By {post.author}</span>}
          </div>
        </div>
      </header>

      <div className="mx-auto grid max-w-[1100px] grid-cols-1 gap-space-2xl px-layout-margin-mobile py-space-3xl lg:grid-cols-12 lg:gap-layout-gutter lg:px-layout-margin-desktop lg:py-space-4xl">
        <div className="lg:col-span-8">
          {post.image_url && (
            <div className="motion-reveal mb-space-2xl overflow-hidden rounded-2xl bg-surface-container">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img className="aspect-[16/10] h-full w-full object-cover" src={post.image_url} alt={post.title} />
            </div>
          )}
          <div className="flex flex-col gap-space-lg">
            {paragraphs.map((paragraph, index) => (
              <p key={`${post.id}-${index}`} className="whitespace-pre-line font-body-lg text-body-lg text-on-surface-variant">
                {paragraph}
              </p>
            ))}
          </div>
        </div>

        <aside className="h-fit rounded-2xl bg-surface-container-low p-space-lg lg:col-span-4">
          <span className="font-label-caps text-label-caps uppercase tracking-wider text-primary">
            Continue exploring
          </span>
          <h2 className="mt-space-xs font-headline-md text-headline-md text-on-surface">
            Building better materials takes a conversation.
          </h2>
          <p className="mt-space-sm font-body-md text-body-md text-on-surface-variant">
            Talk with the CassavaForge team about applications, partnerships, or pilot opportunities.
          </p>
          <Link
            href="/contact"
            className="mt-space-lg inline-flex items-center rounded-full bg-primary px-space-lg py-space-sm font-label-md text-label-md text-on-primary transition-colors hover:bg-primary-container"
          >
            Contact the team
            <span className="material-symbols-outlined ml-space-xs text-[18px]">arrow_forward</span>
          </Link>
        </aside>
      </div>
    </article>
  );
}