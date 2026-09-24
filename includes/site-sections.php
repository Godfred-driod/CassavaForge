<?php
function original_slot(string $html, string $id, string $replacement): string
{
  $pattern = '/<[^>]+id="' . preg_quote($id, '/') . '"[^>]*>.*?<\/[^>]+>/is';
  return preg_replace($pattern, $replacement, $html, 1) ?? $html;
}

function original_product_cards(array $products): string
{
  if (!$products) {
    return '<p class="font-body-md text-body-md text-on-surface-variant col-span-full">Products will appear here once they are added from the admin dashboard.</p>';
  }
  $cards = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-layout-gutter" id="productsGrid">';
  foreach ($products as $product) {
    $cards .= '<article class="product-card motion-reveal group flex min-h-[34rem] w-full flex-col bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300" data-category="' . e((string) $product['category']) . '"><div class="relative aspect-square w-full overflow-hidden bg-surface-container"><img class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" src="' . e(asset_url($product['image_url'] ?? null)) . '" alt="' . e($product['name']) . '"><div class="absolute top-3 left-3 bg-surface/90 backdrop-blur-sm px-2.5 py-1 rounded-full"><span class="font-label-caps text-primary uppercase">' . e($product['application_grade'] ?? '') . '</span></div></div><div class="flex flex-grow flex-col justify-between p-space-xl"><div><div class="flex items-center justify-between mb-space-xs"><span class="font-label-caps text-on-surface-variant uppercase tracking-wider">' . e($product['series_code'] ? 'Series ' . $product['series_code'] : '') . '</span><span class="material-symbols-outlined text-primary text-[20px]">' . e($product['icon'] ?: 'eco') . '</span></div><h3 class="font-headline-md text-on-surface group-hover:text-primary transition-colors mb-space-xs">' . e($product['name']) . '</h3><p class="font-body-sm text-on-surface-variant">' . e($product['description'] ?? '') . '</p></div><div class="pt-space-lg"><a class="inline-flex items-center font-label-md text-primary" href="' . e(url('products/' . $product['slug'])) . '">Learn More <span class="material-symbols-outlined text-[18px] ml-1">arrow_forward</span></a></div></div></article>';
  }
  return $cards . '</div>';
}

function original_product_filter_pills(string $activeCategory): string
{
  $categories = ['' => 'All', 'packaging' => 'Packaging', 'films' => 'Films', 'cutlery' => 'Cutlery'];
  $pills = '<div class="flex flex-wrap items-center gap-2">';
  foreach ($categories as $value => $label) {
    $href = $value === '' ? url('products') : url('products?category=' . $value);
    $active = $activeCategory === $value;
    $classes = $active
      ? 'bg-primary text-on-primary font-label-md text-label-md px-4 py-1.5 rounded-full shadow-sm transition-all'
      : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high font-label-md text-label-md px-4 py-1.5 rounded-full transition-all';
    $pills .= '<a class="' . $classes . '" href="' . e($href) . '">' . e($label) . '</a>';
  }
  return $pills . '</div>';
}

function original_blog_filter_pills(string $activeCategory, array $categories): string
{
  $pills = '<div class="flex items-center gap-space-xs mt-8 overflow-x-auto pb-2 scrollbar-none">';
  $allActive = $activeCategory === '';
  $allClass = $allActive
    ? 'bg-surface text-primary font-label-md text-label-md px-4 py-1.5 rounded-full shadow-sm transition-all whitespace-nowrap'
    : 'bg-white/10 hover:bg-white/20 text-on-primary font-label-md text-label-md px-4 py-1.5 rounded-full backdrop-blur-sm transition-all whitespace-nowrap';
  $pills .= '<a class="' . $allClass . '" href="' . e(url('blog')) . '">All Articles</a>';
  foreach ($categories as $category) {
    $active = strcasecmp($activeCategory, $category) === 0;
    $classes = $active
      ? 'bg-surface text-primary font-label-md text-label-md px-4 py-1.5 rounded-full shadow-sm transition-all whitespace-nowrap'
      : 'bg-white/10 hover:bg-white/20 text-on-primary font-label-md text-label-md px-4 py-1.5 rounded-full backdrop-blur-sm transition-all whitespace-nowrap';
    $pills .= '<a class="' . $classes . '" href="' . e(url('blog?category=' . rawurlencode($category))) . '">' . e($category) . '</a>';
  }
  return $pills . '</div>';
}

function original_blog_cards(array $posts): string
{
  if (!$posts) {
    return '<p class="font-body-md text-body-md text-on-surface-variant col-span-full">No published notes in this category yet.</p>';
  }
  $cards = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-layout-gutter" id="articles-grid">';
  foreach ($posts as $post) {
    $cards .= '<article class="group flex flex-col bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden" data-card-category="' . e($post['category'] ?? '') . '"><div class="relative w-full aspect-[16/10] overflow-hidden bg-surface-container"><img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="' . e(asset_url($post['image_url'] ?? null)) . '" alt="' . e($post['title']) . '"><div class="absolute top-3 left-3"><span class="bg-surface/90 backdrop-blur-md font-label-caps text-primary px-3 py-1 rounded-full uppercase tracking-wider font-bold shadow-sm">' . e($post['category'] ?? '') . '</span></div></div><div class="p-space-lg flex flex-col flex-1 justify-between gap-space-md"><div><div class="flex items-center gap-space-2xs text-on-surface-variant font-body-sm"><span class="material-symbols-outlined text-[15px] text-primary">calendar_today</span><span>' . e(date('M j, Y', strtotime((string) ($post['published_at'] ?? 'now')))) . '</span><span class="mx-1 font-bold">&bull;</span><span>' . e((string) ($post['read_minutes'] ?? 3)) . ' min read</span></div><h3 class="font-headline-sm text-on-surface group-hover:text-primary transition-colors leading-snug">' . e($post['title']) . '</h3><p class="font-body-md text-on-surface-variant">' . e($post['excerpt'] ?? '') . '</p></div><a class="inline-flex items-center font-label-md text-primary" href="' . e(url('blog/' . $post['slug'])) . '">Read Article <span class="material-symbols-outlined text-[18px] ml-1">arrow_forward</span></a></div></article>';
  }
  return $cards . '</div>';
}

function original_contact_form(): string
{
  $flash = take_flash();
  $alert = '';
  if ($flash) {
    $class = $flash['type'] === 'error' ? 'original-alert original-alert-error' : 'original-alert original-alert-success';
    $alert = '<div class="' . $class . '" role="status">' . e($flash['message']) . '</div>';
  }
  $topics = inquiry_topics();
  $selected = (string) ($_GET['request'] ?? $_GET['inquiry_type'] ?? 'general');
  $selected = isset($topics[$selected]) ? $selected : 'general';
  $prefill = $selected === 'sample'
    ? 'I would like to request a material sample for a converting trial.'
    : '';
  $partner = function_exists('current_user') ? current_user() : null;
  $nameValue = ($partner && ($partner['role'] ?? '') === 'partner') ? (string) $partner['full_name'] : '';
  $emailValue = ($partner && ($partner['role'] ?? '') === 'partner') ? (string) $partner['email'] : '';
  $note = ($partner && ($partner['role'] ?? '') === 'partner')
    ? '<p class="font-body-sm text-on-surface-variant mb-2">This will create a tracked request in your portal.</p>'
    : '<p class="font-body-sm text-on-surface-variant mb-2"><a href="' . e(url('portal/register')) . '">Create an account</a> to track samples and trials.</p>';
  $select = '<label for="inquiry_type">Topic</label><select id="inquiry_type" name="inquiry_type">';
  foreach ($topics as $value => $label) {
    $select .= '<option value="' . e($value) . '"' . ($value === $selected ? ' selected' : '') . '>' . e($label) . '</option>';
  }
  $select .= '</select>';
  return $alert . $note . '<form method="post" class="original-form"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><label for="full_name">Name</label><input id="full_name" name="full_name" maxlength="200" required value="' . e($nameValue) . '"><label for="email_address">Email</label><input id="email_address" type="email" name="email_address" maxlength="254" required value="' . e($emailValue) . '">' . $select . '<label for="message_body">Message</label><textarea id="message_body" name="message_body" maxlength="5000" rows="6" required>' . e($prefill) . '</textarea><button class="original-button" type="submit">Send inquiry <span class="material-symbols-outlined">arrow_forward</span></button></form>';
}

function render_original_page(string $page): void
{
  $html = original_page_html($page);
  if ($page === 'home') {
    echo $html;
    render_collaboration_sections();
    return;
  }
  if ($page === 'products') {
    $validCategories = ['packaging', 'films', 'cutlery'];
    $category = trim((string) ($_GET['category'] ?? ''));
    $category = in_array($category, $validCategories, true) ? $category : '';
    $products = safe_query(function () use ($category): array {
      if ($category !== '') {
        $stmt = db()->prepare('SELECT * FROM products WHERE published = 1 AND category = ? ORDER BY display_order, name');
        $stmt->execute([$category]);
        return $stmt->fetchAll();
      }
      return db()->query('SELECT * FROM products WHERE published = 1 ORDER BY display_order, name')->fetchAll();
    }, []);
    $html = original_slot($html, 'product-filter-slot', original_product_filter_pills($category));
    $html = original_slot($html, 'products-grid-slot', original_product_cards($products));
    $html = original_slot($html, 'offset-calculator-slot', '');
    $html = original_slot($html, 'tech-specs-slot', render_specs_button());
    $html = str_replace('<button class="w-full bg-secondary-container', '<a href="' . e(url('contact?request=sample')) . '" class="w-full inline-flex items-center justify-center bg-secondary-container', $html);
    $html = preg_replace('/(Request Test Kit\s*)<\/button>/s', '$1</a>', $html) ?? $html;
  }
  if ($page === 'blog') {
    $categories = safe_query(fn() => db()->query("SELECT DISTINCT category FROM blog_posts WHERE published = 1 AND category IS NOT NULL AND category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN), []);
    $requested = trim((string) ($_GET['category'] ?? ''));
    $category = in_array($requested, $categories, true) ? $requested : '';
    $posts = safe_query(function () use ($category): array {
      if ($category !== '') {
        $stmt = db()->prepare('SELECT * FROM blog_posts WHERE published = 1 AND category = ? ORDER BY published_at DESC');
        $stmt->execute([$category]);
        return $stmt->fetchAll();
      }
      return db()->query('SELECT * FROM blog_posts WHERE published = 1 ORDER BY published_at DESC')->fetchAll();
    }, []);
    $html = original_slot($html, 'blog-filter-slot', original_blog_filter_pills($category, $categories));
    $html = original_slot($html, 'blog-list-slot', original_blog_cards($posts));
  }
  if ($page === 'contact') {
    $html = original_slot($html, 'contact-form-slot', original_contact_form());
    $html = original_slot($html, 'faq-slot', '');
  }
  echo $html;
}

function render_specs_button(): string
{
  return '<button class="inline-flex items-center justify-center bg-surface-container text-on-surface hover:bg-surface-container-highest font-label-md text-label-md px-space-lg py-space-sm rounded-full transition-all" type="button" id="specModalOpenBtn"><span class="material-symbols-outlined text-[18px] mr-1.5 text-primary">science</span>Working spec sheet</button>'
  . '<div class="modal" id="specModal" tabindex="-1" aria-hidden="true" style="display:none;"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h3 class="modal-title">Preliminary resin comparison</h3><button type="button" class="btn-close" id="specModalCloseX"></button></div><div class="modal-body"><p>Internal working figures for CassavaForge CF-100 versus typical LDPE. This is not a certified test report.</p><div class="table-responsive"><table class="table"><thead><tr><th>Parameter</th><th class="text-success">CassavaForge CF-100 (working)</th><th>Typical LDPE</th></tr></thead><tbody><tr><th>Feedstock Source</th><td>Manihot esculenta (Cassava)</td><td>Petroleum naphtha</td></tr><tr><th>Tensile Modulus (MPa)</th><td>To be lab-verified</td><td>200 - 400</td></tr><tr><th>Industrial compost</th><td>Design target</td><td>Not compostable</td></tr><tr><th>End of life</th><td>Designed to compost, not persist</td><td>Persistent plastic</td></tr></tbody></table></div></div><div class="modal-footer"><button class="btn btn-success" id="specModalCloseBtn">Close</button></div></div></div></div>'
  . '<script>(function(){function q(id){return document.getElementById(id);}var modal=q("specModal");if(!modal)return;document.body.appendChild(modal);function openModal(){modal.style.display="block";modal.classList.add("show");modal.removeAttribute("aria-hidden");document.body.classList.add("modal-open");var b=document.createElement("div");b.className="modal-backdrop fade show";b.id="specModalBackdrop";document.body.appendChild(b);}function closeModal(){modal.style.display="none";modal.classList.remove("show");modal.setAttribute("aria-hidden","true");document.body.classList.remove("modal-open");var b=q("specModalBackdrop");if(b)b.remove();}var openBtn=q("specModalOpenBtn");if(openBtn)openBtn.addEventListener("click",openModal);["specModalCloseX","specModalCloseBtn"].forEach(function(id){var el=q(id);if(el)el.addEventListener("click",closeModal);});modal.addEventListener("click",function(e){if(e.target===modal)closeModal();});document.addEventListener("keydown",function(e){if(e.key==="Escape")closeModal();});})();</script>';
}

function render_blog_article(string $slug): void
{
  $stmt = db()->prepare('SELECT * FROM blog_posts WHERE slug = ? AND published = 1 LIMIT 1');
  $stmt->execute([$slug]);
  $post = $stmt->fetch();
  if (!$post) {
    http_response_code(404);
    render_not_found('That article is not published.');
    return;
  }
  $paragraphs = preg_split('/\r?\n\s*\r?\n/', (string) ($post['content'] ?: $post['excerpt'])) ?: [];
  ?><article class="w-full bg-surface"><header class="w-full bg-primary text-on-primary"><div class="mx-auto max-w-[900px] px-layout-margin-mobile py-space-3xl lg:px-layout-margin-desktop lg:py-space-4xl"><a href="<?= e(url('blog')) ?>" class="font-label-md text-label-md text-secondary-fixed">Back to Blog</a><span class="mt-space-xl block font-label-caps text-label-caps uppercase tracking-wider text-secondary-fixed"><?= e($post['category'] ?? '') ?></span><h1 class="mt-space-xs font-headline-xl text-headline-xl text-on-primary"><?= e($post['title']) ?></h1><div class="mt-space-md flex flex-wrap gap-x-space-sm gap-y-space-2xs font-body-sm text-body-sm text-on-primary-container"><span><?= e(date('F j, Y', strtotime((string) $post['published_at']))) ?></span><span><?= e((string) ($post['read_minutes'] ?? 3)) ?> min read</span><span>By <?= e($post['author'] ?? 'CassavaForge') ?></span></div></div></header><div class="mx-auto grid max-w-[1100px] grid-cols-1 gap-space-2xl px-layout-margin-mobile py-space-3xl lg:grid-cols-12 lg:gap-layout-gutter lg:px-layout-margin-desktop lg:py-space-4xl"><div class="lg:col-span-8"><?php if (!empty($post['image_url'])): ?><div class="motion-reveal mb-space-2xl overflow-hidden rounded-2xl bg-surface-container"><img class="aspect-[16/10] h-full w-full object-cover" src="<?= e(asset_url($post['image_url'])) ?>" alt="<?= e($post['title']) ?>"></div><?php endif; ?><div class="flex flex-col gap-space-lg"><?php foreach ($paragraphs as $paragraph): ?><p class="whitespace-pre-line font-body-lg text-body-lg text-on-surface-variant"><?= e($paragraph) ?></p><?php endforeach; ?></div></div><aside class="h-fit rounded-2xl bg-surface-container-low p-space-lg lg:col-span-4"><span class="font-label-caps text-label-caps uppercase tracking-wider text-primary">Continue exploring</span><h2 class="mt-space-xs font-headline-md text-headline-md text-on-surface">Building better materials takes a conversation.</h2><p class="mt-space-sm font-body-md text-body-md text-on-surface-variant">Talk with the CassavaForge team about applications, partnerships, or pilot opportunities.</p><a class="mt-space-lg inline-flex items-center rounded-full bg-primary px-space-lg py-space-sm font-label-md text-label-md text-on-primary" href="<?= e(url('contact')) ?>">Contact the team <span class="material-symbols-outlined ml-space-xs text-[18px]">arrow_forward</span></a></aside></div></article><?php
}

function render_product_page(string $slug): void
{
  $stmt = db()->prepare('SELECT * FROM products WHERE slug = ? AND published = 1 LIMIT 1');
  $stmt->execute([$slug]);
  $product = $stmt->fetch();
  if (!$product) {
    http_response_code(404);
    render_start('Material not found');
    render_not_found('That material is not in the catalog.');
    render_end();
    return;
  }
  render_start((string) $product['name']);
  ?>
  <article class="w-full bg-surface">
    <div class="mx-auto max-w-[1240px] px-layout-margin-mobile py-space-3xl lg:px-layout-margin-desktop lg:py-space-4xl">
      <a href="<?= e(url('products')) ?>" class="font-label-md text-label-md text-primary">Back to products</a>
      <div class="mt-space-xl grid grid-cols-1 gap-space-2xl lg:grid-cols-12 lg:gap-layout-gutter lg:items-start">
        <div class="overflow-hidden rounded-2xl bg-surface-container lg:col-span-6">
          <img class="aspect-square h-full w-full object-cover" src="<?= e(asset_url($product['image_url'] ?? null)) ?>" alt="<?= e($product['name']) ?>">
        </div>
        <div class="flex flex-col gap-space-md lg:col-span-6">
          <div class="flex flex-wrap items-center gap-space-sm">
            <span class="rounded-full bg-surface-container-low px-3 py-1 font-label-caps text-label-caps uppercase tracking-wider text-primary"><?= e($product['category']) ?></span>
            <?php if (!empty($product['series_code'])): ?><span class="font-label-caps text-label-caps uppercase tracking-wider text-on-surface-variant">Series <?= e($product['series_code']) ?></span><?php endif; ?>
          </div>
          <h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tight"><?= e($product['name']) ?></h1>
          <?php if (!empty($product['application_grade'])): ?><p class="font-label-md text-label-md text-primary"><?= e($product['application_grade']) ?></p><?php endif; ?>
          <p class="font-body-lg text-body-lg text-on-surface-variant"><?= e($product['description']) ?></p>
          <?php if (!empty($product['spec1_label']) || !empty($product['spec2_label'])): ?>
            <dl class="mt-space-sm grid grid-cols-1 gap-space-sm sm:grid-cols-2">
              <?php if (!empty($product['spec1_label'])): ?>
                <div class="rounded-xl bg-surface-container-low p-space-md"><dt class="font-label-caps text-label-caps uppercase tracking-wider text-on-surface-variant"><?= e($product['spec1_label']) ?></dt><dd class="mt-space-xs font-headline-sm text-headline-sm text-on-surface"><?= e($product['spec1_value']) ?></dd></div>
              <?php endif; ?>
              <?php if (!empty($product['spec2_label'])): ?>
                <div class="rounded-xl bg-surface-container-low p-space-md"><dt class="font-label-caps text-label-caps uppercase tracking-wider text-on-surface-variant"><?= e($product['spec2_label']) ?></dt><dd class="mt-space-xs font-headline-sm text-headline-sm text-on-surface"><?= e($product['spec2_value']) ?></dd></div>
              <?php endif; ?>
            </dl>
          <?php endif; ?>
          <p class="font-body-sm text-body-sm text-on-surface-variant">Performance figures are confirmed in a converting trial. Ask for the current working sheet for this grade.</p>
          <div class="mt-space-md flex flex-wrap gap-space-sm">
            <a class="inline-flex items-center rounded-full bg-primary px-space-xl py-space-sm font-label-md text-label-md text-on-primary" href="<?= e(url('contact?request=sample')) ?>">Request a sample <span class="material-symbols-outlined ml-space-xs text-[18px]">arrow_forward</span></a>
            <a class="inline-flex items-center rounded-full bg-surface-container px-space-xl py-space-sm font-label-md text-label-md text-on-surface" href="<?= e(url('products')) ?>">See all materials</a>
          </div>
        </div>
      </div>
    </div>
  </article>
  <?php
  render_end();
}

function render_privacy(): void
{
  ?>
  <article class="w-full bg-surface">
    <div class="mx-auto max-w-[760px] px-layout-margin-mobile py-space-4xl lg:px-layout-margin-desktop">
      <span class="font-label-caps text-label-caps uppercase tracking-wider text-primary">Legal</span>
      <h1 class="mt-space-xs font-headline-xl text-headline-xl text-on-surface">Privacy note</h1>
      <p class="mt-space-md font-body-lg text-body-lg text-on-surface-variant">CassavaForge collects only what is needed to answer inquiries and understand which public pages people visit. We do not sell personal data or run third-party advertising trackers on this site.</p>
      <h2 class="mt-space-2xl font-headline-md text-headline-md text-on-surface">Contact messages</h2>
      <p class="mt-space-sm font-body-md text-body-md text-on-surface-variant">When you send the contact form, we store your name, email, inquiry type, and message so the team can reply. A copy may also be emailed to the site administrator. We keep messages until they have been handled, then delete them from the inbox.</p>
      <h2 class="mt-space-2xl font-headline-md text-headline-md text-on-surface">Visit data</h2>
      <p class="mt-space-sm font-body-md text-body-md text-on-surface-variant">Public pages set a first-party cookie named <code>cf_visitor</code> so repeat visits can be counted without identifying you by name. We store the page path, a truncated browser user agent, and the referring site when a browser sends one. Admin pages, sitemap, and robots files are not counted.</p>
      <h2 class="mt-space-2xl font-headline-md text-headline-md text-on-surface">Cookies</h2>
      <p class="mt-space-sm font-body-md text-body-md text-on-surface-variant">The visitor cookie lasts up to one year and is HttpOnly. A session cookie is used only while you are signed in to the private workspace. You can block cookies in your browser; the public site still works, but visit counts will be less accurate.</p>
      <h2 class="mt-space-2xl font-headline-md text-headline-md text-on-surface">Contact</h2>
      <p class="mt-space-sm font-body-md text-body-md text-on-surface-variant">Questions about this note can be sent through the <a class="text-primary" href="<?= e(url('contact')) ?>">contact form</a>.</p>
    </div>
  </article>
  <?php
}

function render_not_found(string $message = 'That page is not here.'): void
{
  ?>
  <section class="w-full bg-surface">
    <div class="mx-auto max-w-[720px] px-layout-margin-mobile py-space-4xl lg:px-layout-margin-desktop">
      <span class="font-label-caps text-label-caps uppercase tracking-wider text-primary">404</span>
      <h1 class="mt-space-xs font-headline-xl text-headline-xl text-on-surface">Page not found.</h1>
      <p class="mt-space-sm font-body-lg text-body-lg text-on-surface-variant"><?= e($message) ?></p>
      <div class="mt-space-xl flex flex-wrap gap-space-sm">
        <a class="inline-flex items-center rounded-full bg-primary px-space-xl py-space-sm font-label-md text-label-md text-on-primary" href="<?= e(url('')) ?>">Home</a>
        <a class="inline-flex items-center rounded-full bg-surface-container px-space-xl py-space-sm font-label-md text-label-md text-on-surface" href="<?= e(url('products')) ?>">Products</a>
        <a class="inline-flex items-center rounded-full bg-surface-container px-space-xl py-space-sm font-label-md text-label-md text-on-surface" href="<?= e(url('contact')) ?>">Contact</a>
      </div>
    </div>
  </section>
  <?php
}

function render_collaboration_sections(): void
{
    ?>
  <section class="w-full bg-surface-container-low" aria-labelledby="collaboration-path-heading"><div class="mx-auto max-w-[1240px] px-layout-margin-mobile py-space-3xl lg:px-layout-margin-desktop lg:py-space-4xl"><div class="grid grid-cols-1 gap-space-xl lg:grid-cols-12 lg:items-end lg:gap-layout-gutter"><div class="motion-reveal lg:col-span-5"><span class="font-label-caps text-label-caps uppercase tracking-wider text-primary">From conversation to pilot</span><h2 id="collaboration-path-heading" class="mt-space-xs font-headline-xl text-headline-xl text-on-surface">A practical path for ambitious material ideas.</h2></div><p class="motion-reveal max-w-2xl font-body-lg text-body-lg text-on-surface-variant lg:col-span-7">Good biomaterials work is collaborative. Start with the problem, test the right variables, and make decisions with evidence from the application that matters.</p></div><div class="mt-space-2xl grid grid-cols-1 gap-space-lg md:grid-cols-3 md:gap-space-md"><?php foreach ([['01','Bring the use case','Share the format, performance targets, volumes, and end-of-life requirements your team is solving for.'],['02','Test the material fit','Align on the right product direction, then evaluate processing, durability, barrier needs, and real-world conditions.'],['03','Build the pilot','Move from promising formulation to a measured pilot with the people and equipment needed for responsible scale-up.']] as $index => $step): ?><article class="motion-reveal border-t-2 border-primary pt-space-md" style="animation-delay: <?= $index * 100 + 100 ?>ms"><span class="font-label-caps text-label-caps text-primary"><?= e($step[0]) ?></span><h3 class="mt-space-sm font-headline-sm text-headline-sm text-on-surface"><?= e($step[1]) ?></h3><p class="mt-space-xs font-body-md text-body-md text-on-surface-variant"><?= e($step[2]) ?></p></article><?php endforeach; ?></div><a class="motion-reveal mt-space-2xl inline-flex items-center rounded-full bg-primary px-space-xl py-space-sm font-label-md text-label-md text-on-primary" href="<?= e(url('contact')) ?>">Talk through your use case <span class="material-symbols-outlined ml-space-xs text-[18px]">arrow_forward</span></a></div></section>
    <section class="w-full overflow-hidden bg-primary text-on-primary" aria-labelledby="investor-partner-heading"><div class="mx-auto max-w-[1240px] px-layout-margin-mobile py-space-3xl lg:px-layout-margin-desktop lg:py-space-4xl"><div class="grid grid-cols-1 items-end gap-space-2xl lg:grid-cols-12 lg:gap-layout-gutter"><div class="motion-reveal lg:col-span-7"><span class="font-label-caps text-label-caps uppercase tracking-wider text-secondary-fixed">Open for collaboration</span><h2 id="investor-partner-heading" class="mt-space-xs max-w-3xl font-headline-xl text-headline-xl">Help build the next generation of useful, lower-impact materials.</h2><p class="mt-space-md max-w-2xl font-body-lg text-body-lg text-on-primary-container">We are looking for aligned investors and hands-on partners who can help move cassava bioplastics from promising material science into dependable everyday applications.</p></div><div class="motion-reveal flex flex-col gap-space-sm lg:col-span-5 lg:items-end"><a class="inline-flex w-full items-center justify-center rounded-full bg-secondary-container px-space-xl py-space-sm font-label-md text-label-md text-on-secondary-container transition-colors hover:bg-secondary-fixed lg:w-auto" href="<?= e(url('contact')) ?>">Start a conversation <span class="material-symbols-outlined ml-space-xs text-[18px]">arrow_forward</span></a><a class="inline-flex w-full items-center justify-center rounded-full border border-on-primary/40 px-space-xl py-space-sm font-label-md text-label-md text-on-primary transition-colors hover:bg-on-primary/10 lg:w-auto" href="<?= e(url('products')) ?>">Explore our material focus</a></div></div><div class="mt-space-3xl grid grid-cols-1 gap-space-md md:grid-cols-3"><?php foreach ([['trending_up','Invest in material change','Support the scale-up of cassava-based biomaterials, pilot manufacturing, and the infrastructure needed for responsible growth.'],['handshake','Partner for real-world pilots','Bring packaging, film, or molded-product requirements to a technical partnership built around measurable performance.'],['hub','Connect the value chain','Join a network of growers, converters, brands, and researchers working toward practical alternatives to conventional plastics.']] as $item): ?><div class="motion-reveal border-t border-on-primary/25 pt-space-md"><span class="material-symbols-outlined text-secondary-fixed text-[24px]"><?= e($item[0]) ?></span><h3 class="mt-space-sm font-headline-sm text-headline-sm text-on-primary"><?= e($item[1]) ?></h3><p class="mt-space-xs font-body-sm text-body-sm text-on-primary-container"><?= e($item[2]) ?></p></div><?php endforeach; ?></div></div></section>
    <?php
}
