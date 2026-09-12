import Link from "next/link";
import { createClient } from "@/lib/supabase/server";
import type { BlogPost } from "@/lib/types";
import { deletePost } from "@/app/admin/actions";

export default async function AdminBlogPage() {
  const supabase = await createClient();
  const { data } = await supabase
    .from("blog_posts")
    .select("*")
    .order("published_at", { ascending: false });

  const posts = (data as BlogPost[]) ?? [];

  return (
    <div className="flex flex-col gap-space-lg">
      <div className="flex items-center justify-between">
        <h1 className="font-headline-lg text-headline-lg text-on-surface">Blog Posts</h1>
        <Link
          href="/admin/blog/new"
          className="bg-primary text-on-primary font-label-md text-label-md px-space-lg py-space-sm rounded-full"
        >
          + New Post
        </Link>
      </div>

      <div className="flex flex-col gap-space-sm">
        {posts.length === 0 && (
          <p className="font-body-md text-on-surface-variant">No posts yet.</p>
        )}
        {posts.map((post) => (
          <div
            key={post.id}
            className="flex items-center gap-space-md p-space-md bg-surface-container-lowest rounded-xl shadow-sm"
          >
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src={post.image_url ?? "/images/cassavaforge-logo.png"}
              alt=""
              className="w-16 h-16 object-cover rounded-lg bg-surface-container flex-shrink-0"
            />
            <div className="flex-1 min-w-0">
              <p className="font-label-md text-label-md text-on-surface truncate">{post.title}</p>
              <p className="font-body-sm text-body-sm text-on-surface-variant">
                {new Date(post.published_at).toLocaleDateString()} {!post.published && "· draft"}
              </p>
            </div>
            <Link
              href={`/admin/blog/${post.id}`}
              className="font-label-md text-label-md text-primary px-space-sm py-space-xs"
            >
              Edit
            </Link>
            <form action={deletePost}>
              <input type="hidden" name="id" value={post.id} />
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
