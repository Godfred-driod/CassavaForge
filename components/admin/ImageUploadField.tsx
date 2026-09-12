"use client";

import { useState } from "react";
import { createClient } from "@/lib/supabase/client";

export default function ImageUploadField({
  name,
  label,
  defaultValue,
  folder,
}: {
  name: string;
  label: string;
  defaultValue?: string | null;
  folder: string;
}) {
  const [url, setUrl] = useState(defaultValue ?? "");
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function handleFile(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return;

    const allowedTypes: Record<string, string> = {
      "image/jpeg": "jpg",
      "image/png": "png",
      "image/webp": "webp",
      "image/gif": "gif",
    };
    const extension = allowedTypes[file.type];

    if (!extension) {
      setError("Please choose an image file.");
      e.target.value = "";
      return;
    }

    const MAX_BYTES = 5 * 1024 * 1024; // 5MB
    if (file.size > MAX_BYTES) {
      setError("Image is too large — please choose one under 5MB.");
      e.target.value = "";
      return;
    }

    setUploading(true);
    setError(null);

    const supabase = createClient();
    const path = `${folder}/${crypto.randomUUID()}.${extension}`;

    const { error: uploadError } = await supabase.storage.from("media").upload(path, file, {
      cacheControl: "3600",
      upsert: false,
    });

    if (uploadError) {
      setError(uploadError.message);
      setUploading(false);
      return;
    }

    const { data } = supabase.storage.from("media").getPublicUrl(path);
    setUrl(data.publicUrl);
    setUploading(false);
  }

  return (
    <div className="flex flex-col gap-space-2xs">
      <label className="font-label-md text-label-md text-on-surface-variant">{label}</label>
      {url && (
        // eslint-disable-next-line @next/next/no-img-element
        <img
          src={url}
          alt=""
          className="w-full max-w-xs aspect-video object-cover rounded-lg bg-surface-container"
        />
      )}
      <input type="hidden" name={name} value={url} />
      <input
        type="file"
        accept="image/*"
        onChange={handleFile}
        className="font-body-sm text-body-sm text-on-surface-variant file:mr-3 file:py-space-xs file:px-space-md file:rounded-full file:border-0 file:bg-primary-container file:text-on-primary file:font-label-md"
      />
      {uploading && <p className="font-body-sm text-body-sm text-on-surface-variant">Uploading…</p>}
      {error && <p className="font-body-sm text-body-sm text-error">{error}</p>}
      <p className="font-body-sm text-[11px] text-on-surface-variant">
        Or leave the upload alone and this keeps its current image.
      </p>
    </div>
  );
}
