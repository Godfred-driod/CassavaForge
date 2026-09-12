# CassavaForge — Frontend

This is your CassavaForge design (from Stitch) rebuilt as a real, working Next.js site —
same layout, same colors, same copy, but now it's actual code you can run, deploy, and
wire up to a backend.

## Running it locally

```bash
npm install
npm run dev
```

Then open http://localhost:3000

## What's real right now

- **All 6 pages** (Home, About, Products, Impact, Blog, Contact) are live routes with
  working navigation — the fake `data-path` prototype links from Stitch have been
  converted into real Next.js links.
- **Responsive nav**: your desktop header + a mobile bottom tab bar (pulled from your
  mobile design), both with correct "active page" highlighting.
- **The Contact form actually works**: it's a real React form (not a static mockup) that
  submits to `/api/contact`. Right now that route just validates the input and logs it —
  see the `TODO` in `app/api/contact/route.ts` for where to plug in a database and/or an
  email notification.
- **Colors, type scale, spacing** all come from your Stitch design system (`tailwind.config.ts`),
  compiled properly with Tailwind — not the CDN script Stitch uses for previews.

## What's still a placeholder / needs your attention

- **Images** still point to Stitch's temporary preview URLs
  (`lh3.googleusercontent.com/...`). These can expire. Swap them for real, permanently
  hosted images (e.g. in `/public` or a CDN/storage bucket) before launch.
- **The "Request Material Samples" form** on the Products page is still static markup —
  same pattern as the Contact form, just not wired up yet. Say the word and I'll connect
  it the same way.
- **Blog posts and product specs** are currently the placeholder copy from the design.
  Once we set up the database, these become real, editable content instead of hardcoded text.

## Project structure

```
app/
  layout.tsx        — shared shell: fonts, Header, Footer, bottom nav
  page.tsx           — Home
  about/, products/, impact/, blog/, contact/
  api/contact/       — form submission endpoint (stub, ready for a real DB)
components/
  Header.tsx         — desktop nav, active-route aware
  BottomNav.tsx       — mobile tab bar, active-route aware
  Footer.tsx / StitchHtml.tsx — renders the extracted design content
  ContactForm.tsx     — the working contact form
  generated/          — raw content pulled from your Stitch export
```

## Admin panel

CassavaForge now has its own branded admin panel at `/admin` — no need to touch
Supabase's dashboard directly for day-to-day content updates.

**First-time setup (do this once):** you need to create your own login. In the
Supabase dashboard for this project: **Authentication → Users → Add User**,
enter your email + a password, and check "Auto Confirm User." That's your
admin login — Claude never sees or sets this password. Add that email to the
comma-separated `ADMIN_EMAILS` environment variable, or set the user's
Supabase `app_metadata.role` to `admin`, before opening the admin panel.

### Visitor analytics setup

The admin dashboard includes **Unique Visits**, counted as anonymous browser
sessions. It stores only a random session ID; it does not store names, email
addresses, IP addresses, or page history. To enable it:

1. Run the `site_visitors` section of `supabase/schema.sql` in the Supabase SQL editor.
2. Add the Supabase project's server-only service-role key as `SUPABASE_SERVICE_ROLE_KEY` in `.env.local` and your deployment environment.
3. Restart the app and open `/admin`.

The number is an estimate of browser sessions, not an exact count of individual people.

Then visit `/admin` on your site (e.g. `http://localhost:3000/admin`), sign
in, and you'll find:
- **Dashboard** — quick counts of products, posts, and unread messages.
- **Products** — add, edit, delete, and publish/unpublish products, including
  uploading a new photo directly (no need to touch code or the images folder).
- **Blog Posts** — same, for blog posts.
- **Messages** — every contact form submission, with mark read/unread and delete.

## Next step: the backend

This is built on the stack we discussed — Next.js + Postgres (Supabase) — specifically
so the next move is straightforward: add a database for products, blog posts, and
contact submissions, then swap the placeholder content for real data.
