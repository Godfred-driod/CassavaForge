-- CassavaForge database schema (reference copy).
-- This has already been applied to the live Supabase project — you don't
-- need to run this yourself. Keep it here as documentation / for setting up
-- a second environment (e.g. staging) later.

create table if not exists contact_messages (
  id uuid primary key default gen_random_uuid(),
  full_name text not null,
  email_address text not null,
  message_body text not null,
  created_at timestamptz not null default now(),
  read boolean not null default false
);

create table if not exists site_visitors (
  visitor_id uuid primary key,
  first_seen timestamptz not null default now()
);

create table if not exists products (
  id uuid primary key default gen_random_uuid(),
  slug text not null unique,
  name text not null,
  series_code text,
  category text not null check (category in ('packaging', 'films', 'cutlery')),
  application_grade text,
  description text,
  image_url text,
  icon text,
  spec1_label text,
  spec1_value text,
  spec2_label text,
  spec2_value text,
  display_order int not null default 0,
  published boolean not null default true,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists blog_posts (
  id uuid primary key default gen_random_uuid(),
  slug text not null unique,
  title text not null,
  excerpt text,
  content text,
  image_url text,
  author text,
  category text,
  read_minutes int,
  published boolean not null default true,
  published_at timestamptz not null default now(),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create or replace function set_updated_at()
returns trigger as $$
begin
  new.updated_at = now();
  return new;
end;
$$ language plpgsql;

drop trigger if exists products_set_updated_at on products;
create trigger products_set_updated_at
  before update on products
  for each row execute function set_updated_at();

drop trigger if exists blog_posts_set_updated_at on blog_posts;
create trigger blog_posts_set_updated_at
  before update on blog_posts
  for each row execute function set_updated_at();

alter table contact_messages enable row level security;
alter table site_visitors enable row level security;
alter table products enable row level security;
alter table blog_posts enable row level security;

create policy "Anyone can submit a contact message"
  on contact_messages for insert
  to anon
  with check (true);

create policy "Anyone can record a visitor session"
  on site_visitors for insert
  to anon
  with check (visitor_id is not null);

create policy "Anyone can read published products"
  on products for select
  to anon
  using (published = true);

create policy "Anyone can read published blog posts"
  on blog_posts for select
  to anon
  using (published = true);
