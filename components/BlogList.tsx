import { supabase } from "@/lib/supabase";
import type { BlogPost } from "@/lib/types";
import BlogCard from "./BlogCard";

export default async function BlogList() {
  const { data, error } = await supabase
    .from("blog_posts")
    .select("*")
    .order("published_at", { ascending: false });

  if (error) {
    console.error("Failed to load blog posts:", error.message);
  }

  const posts = (data as BlogPost[]) ?? [];

  if (posts.length === 0) {
    return (
      <p className="font-body-md text-on-surface-variant py-space-xl text-center">
        No posts yet — check back soon.
      </p>
    );
  }

  return (
    <div
      className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-layout-gutter"
      id="articles-grid"
    >
      {posts.map((post) => (
        <BlogCard key={post.id} post={post} />
      ))}
    </div>
  );
}
