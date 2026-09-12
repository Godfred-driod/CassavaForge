"use server";

import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { requireAdmin } from "@/lib/admin-auth";

export async function signOut() {
  const { supabase } = await requireAdmin();
  await supabase.auth.signOut();
  redirect("/admin/login");
}

// ---------- Products ----------

function readText(formData: FormData, field: string, maxLength: number, required = false) {
  const value = String(formData.get(field) ?? "").trim();
  if (required && !value) throw new Error(`${field} is required`);
  if (value.length > maxLength) throw new Error(`${field} is too long`);
  return value;
}

function readNullableText(formData: FormData, field: string, maxLength: number) {
  return readText(formData, field, maxLength) || null;
}

function readSlug(formData: FormData) {
  const slug = readText(formData, "slug", 100, true);
  if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) {
    throw new Error("Slug must use lowercase letters, numbers, and single hyphens");
  }
  return slug;
}

function readImageUrl(formData: FormData, field: string) {
  const imageUrl = readNullableText(formData, field, 2048);
  if (imageUrl && !imageUrl.startsWith("/") && !imageUrl.startsWith("https://")) {
    throw new Error("Image URL must be a local path or HTTPS URL");
  }
  return imageUrl;
}

function readProductFields(formData: FormData) {
  const category = readText(formData, "category", 20, true);
  if (!(["packaging", "films", "cutlery"] as const).includes(category as never)) {
    throw new Error("Invalid product category");
  }

  const displayOrder = Number(readText(formData, "display_order", 10));
  if (!Number.isInteger(displayOrder) || displayOrder < 0 || displayOrder > 100000) {
    throw new Error("Display order must be a non-negative integer");
  }

  return {
    name: readText(formData, "name", 200, true),
    slug: readSlug(formData),
    series_code: readNullableText(formData, "series_code", 50),
    category,
    application_grade: readNullableText(formData, "application_grade", 100),
    description: readNullableText(formData, "description", 2000),
    image_url: readImageUrl(formData, "image_url"),
    icon: readNullableText(formData, "icon", 50),
    spec1_label: readNullableText(formData, "spec1_label", 100),
    spec1_value: readNullableText(formData, "spec1_value", 200),
    spec2_label: readNullableText(formData, "spec2_label", 100),
    spec2_value: readNullableText(formData, "spec2_value", 200),
    display_order: displayOrder,
    published: formData.get("published") === "on",
  };
}

export async function createProduct(formData: FormData) {
  const { supabase } = await requireAdmin();
  const fields = readProductFields(formData);
  const { error } = await supabase.from("products").insert(fields);
  if (error) throw new Error(error.message);
  revalidatePath("/admin/products");
  revalidatePath("/products");
  redirect("/admin/products");
}

export async function updateProduct(id: string, formData: FormData) {
  const { supabase } = await requireAdmin();
  const fields = readProductFields(formData);
  const { error } = await supabase.from("products").update(fields).eq("id", id);
  if (error) throw new Error(error.message);
  revalidatePath("/admin/products");
  revalidatePath("/products");
  redirect("/admin/products");
}

export async function deleteProduct(formData: FormData) {
  const id = String(formData.get("id"));
  const { supabase } = await requireAdmin();
  const { error } = await supabase.from("products").delete().eq("id", id);
  if (error) throw new Error(error.message);
  revalidatePath("/admin/products");
  revalidatePath("/products");
}

// ---------- Blog posts ----------

function readPostFields(formData: FormData) {
  const readMinutesValue = readText(formData, "read_minutes", 3);
  const readMinutes = readMinutesValue ? Number(readMinutesValue) : null;
  if (readMinutes !== null && (!Number.isInteger(readMinutes) || readMinutes < 1 || readMinutes > 120)) {
    throw new Error("Read minutes must be an integer between 1 and 120");
  }

  return {
    title: readText(formData, "title", 200, true),
    slug: readSlug(formData),
    category: readNullableText(formData, "category", 80),
    excerpt: readNullableText(formData, "excerpt", 2000),
    content: readNullableText(formData, "content", 50000),
    image_url: readImageUrl(formData, "image_url"),
    author: readNullableText(formData, "author", 200),
    read_minutes: readMinutes,
    published: formData.get("published") === "on",
  };
}

export async function createPost(formData: FormData) {
  const { supabase } = await requireAdmin();
  const fields = readPostFields(formData);
  const { error } = await supabase.from("blog_posts").insert(fields);
  if (error) throw new Error(error.message);
  revalidatePath("/admin/blog");
  revalidatePath("/blog");
  redirect("/admin/blog");
}

export async function updatePost(id: string, formData: FormData) {
  const { supabase } = await requireAdmin();
  const fields = readPostFields(formData);
  const { error } = await supabase.from("blog_posts").update(fields).eq("id", id);
  if (error) throw new Error(error.message);
  revalidatePath("/admin/blog");
  revalidatePath("/blog");
  redirect("/admin/blog");
}

export async function deletePost(formData: FormData) {
  const id = String(formData.get("id"));
  const { supabase } = await requireAdmin();
  const { error } = await supabase.from("blog_posts").delete().eq("id", id);
  if (error) throw new Error(error.message);
  revalidatePath("/admin/blog");
  revalidatePath("/blog");
}

// ---------- Contact messages ----------

export async function markMessageRead(formData: FormData) {
  const id = String(formData.get("id"));
  const read = formData.get("read") === "true";
  const { supabase } = await requireAdmin();
  const { error } = await supabase.from("contact_messages").update({ read }).eq("id", id);
  if (error) throw new Error(error.message);
  revalidatePath("/admin/messages");
}

export async function deleteMessage(formData: FormData) {
  const id = String(formData.get("id"));
  const { supabase } = await requireAdmin();
  const { error } = await supabase.from("contact_messages").delete().eq("id", id);
  if (error) throw new Error(error.message);
  revalidatePath("/admin/messages");
}
