import BlogForm from "@/components/admin/BlogForm";
import { createPost } from "@/app/admin/actions";

export default function NewBlogPostPage() {
  return (
    <div className="flex flex-col gap-space-lg">
      <h1 className="font-headline-lg text-headline-lg text-on-surface">New Blog Post</h1>
      <BlogForm action={createPost} />
    </div>
  );
}
