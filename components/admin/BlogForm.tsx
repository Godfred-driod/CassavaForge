import ImageUploadField from "./ImageUploadField";
import type { BlogPost } from "@/lib/types";

const inputClass =
  "w-full bg-surface-container-low text-on-surface font-body-md text-body-md px-space-md py-space-sm rounded-lg focus:outline-none focus:shadow-[0_0_0_2px_#00652c] transition-all";
const labelClass = "font-label-md text-label-md text-on-surface-variant";

export default function BlogForm({
  action,
  post,
}: {
  action: (formData: FormData) => void;
  post?: BlogPost;
}) {
  return (
    <form action={action} className="flex flex-col gap-space-md max-w-xl">
      <div className="flex flex-col gap-space-2xs">
        <label className={labelClass} htmlFor="title">
          Title *
        </label>
        <input id="title" name="title" required defaultValue={post?.title} className={inputClass} />
      </div>
      <div className="flex flex-col gap-space-2xs">
        <label className={labelClass} htmlFor="slug">
          Slug * <span className="text-[11px]">(unique, used in the URL)</span>
        </label>
        <input id="slug" name="slug" required defaultValue={post?.slug} className={inputClass} />
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
        <div className="flex flex-col gap-space-2xs">
          <label className={labelClass} htmlFor="category">
            Category Tag
          </label>
          <input
            id="category"
            name="category"
            defaultValue={post?.category ?? ""}
            className={inputClass}
          />
        </div>
        <div className="flex flex-col gap-space-2xs">
          <label className={labelClass} htmlFor="read_minutes">
            Read Minutes
          </label>
          <input
            id="read_minutes"
            name="read_minutes"
            type="number"
            defaultValue={post?.read_minutes ?? ""}
            className={inputClass}
          />
        </div>
      </div>
      <div className="flex flex-col gap-space-2xs">
        <label className={labelClass} htmlFor="excerpt">
          Excerpt <span className="text-[11px]">(short summary shown on the card)</span>
        </label>
        <textarea
          id="excerpt"
          name="excerpt"
          rows={2}
          defaultValue={post?.excerpt ?? ""}
          className={inputClass}
        />
      </div>
      <div className="flex flex-col gap-space-2xs">
        <label className={labelClass} htmlFor="content">
          Full Content
        </label>
        <textarea
          id="content"
          name="content"
          rows={8}
          defaultValue={post?.content ?? ""}
          className={inputClass}
        />
      </div>
      <ImageUploadField
        name="image_url"
        label="Cover Photo"
        defaultValue={post?.image_url}
        folder="blog"
      />
      <div className="flex flex-col gap-space-2xs">
        <label className={labelClass} htmlFor="author">
          Author
        </label>
        <input
          id="author"
          name="author"
          defaultValue={post?.author ?? "CassavaForge Team"}
          className={inputClass}
        />
      </div>
      <label className="flex items-center gap-space-xs font-label-md text-label-md text-on-surface">
        <input
          type="checkbox"
          name="published"
          defaultChecked={post?.published ?? true}
          className="w-5 h-5 accent-primary"
        />
        Published (visible on the live site)
      </label>
      <button
        type="submit"
        className="w-fit bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md px-space-xl py-space-sm rounded-full transition-all"
      >
        {post ? "Save Changes" : "Publish Post"}
      </button>
    </form>
  );
}
