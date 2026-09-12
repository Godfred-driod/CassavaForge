import { notFound } from "next/navigation";
import BlogForm from "@/components/admin/BlogForm";
import { createClient } from "@/lib/supabase/server";
import { updatePost } from "@/app/admin/actions";
import type { BlogPost } from "@/lib/types";

export default async function EditBlogPostPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const supabase = await createClient();
  const { data } = await supabase.from("blog_posts").select("*").eq("id", id).single();

  if (!data) notFound();

  const updateWithId = updatePost.bind(null, id);

  return (
    <div className="flex flex-col gap-space-lg">
      <h1 className="font-headline-lg text-headline-lg text-on-surface">Edit Blog Post</h1>
      <BlogForm action={updateWithId} post={data as BlogPost} />
    </div>
  );
}
