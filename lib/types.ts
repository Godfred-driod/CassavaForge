export type Product = {
  id: string;
  slug: string;
  name: string;
  series_code: string | null;
  category: "packaging" | "films" | "cutlery";
  application_grade: string | null;
  description: string | null;
  image_url: string | null;
  icon: string | null;
  spec1_label: string | null;
  spec1_value: string | null;
  spec2_label: string | null;
  spec2_value: string | null;
  display_order: number;
  published: boolean;
};

export type BlogPost = {
  id: string;
  slug: string;
  title: string;
  excerpt: string | null;
  content: string | null;
  image_url: string | null;
  author: string | null;
  category: string | null;
  read_minutes: number | null;
  published: boolean;
  published_at: string;
};
