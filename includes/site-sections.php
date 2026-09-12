<?php
function original_slot(string $html, string $id, string $replacement): string
{
  $pattern = '/<[^>]+id="' . preg_quote($id, '/') . '"[^>]*>.*?<\/[^>]+>/is';
  return preg_replace($pattern, $replacement, $html, 1) ?? $html;
}

function original_product_cards(array $products): string
{
  $cards = '<div class="original-product-grid row g-4" id="productsGrid">';
  foreach ($products as $product) {
    $cards .= '<article class="product-card col-md-4" data-category="' . e((string) $product['category']) . '"><div class="original-card h-100 overflow-hidden"><div class="original-product-media"><img src="' . e(asset_url($product['image_url'] ?? null)) . '" alt="' . e($product['name']) . '"><span class="original-image-badge">' . e($product['application_grade'] ?? '') . '</span></div><div class="p-4 d-flex flex-column h-100"><div class="d-flex justify-content-between"><span class="original-label">' . e($product['series_code'] ? 'Series ' . $product['series_code'] : '') . '</span><span class="material-symbols-outlined text-success">' . e($product['icon'] ?: 'eco') . '</span></div><h3>' . e($product['name']) . '</h3><p class="text-secondary">' . e($product['description'] ?? '') . '</p><div class="mt-auto"><a class="original-action" href="' . e(url('products/' . $product['slug'])) . '">Learn More <span class="material-symbols-outlined">arrow_forward</span></a></div></div></div></article>';
  }
  return $cards . '</div>';
}

function original_blog_cards(array $posts): string
{
  $cards = '<div class="original-blog-grid row g-4">';
  foreach ($posts as $post) {
    $cards .= '<article class="col-md-6"><div class="original-card h-100 overflow-hidden"><div class="original-blog-media"><img src="' . e(asset_url($post['image_url'] ?? null)) . '" alt="' . e($post['title']) . '"><span class="original-image-badge">' . e($post['category'] ?? '') . '</span></div><div class="p-4"><div class="text-secondary small"><span class="material-symbols-outlined align-middle text-success">calendar_today</span> ' . e(date('M j, Y', strtotime((string) ($post['published_at'] ?? 'now')))) . ' &bull; ' . e((string) ($post['read_minutes'] ?? 3)) . ' min read</div><h3>' . e($post['title']) . '</h3><p class="text-secondary">' . e($post['excerpt'] ?? '') . '</p><a class="original-action" href="' . e(url('blog/' . $post['slug'])) . '">Read Article <span class="material-symbols-outlined">arrow_forward</span></a></div></div></article>';
  }
  return $cards . '</div>';
}

function original_contact_form(): string
{
  return '<form method="post" class="original-form"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><label for="full_name">Name</label><input id="full_name" name="full_name" maxlength="200" required><label for="email_address">Email</label><input id="email_address" type="email" name="email_address" maxlength="254" required><label for="message_body">Message</label><textarea id="message_body" name="message_body" maxlength="5000" rows="6" required></textarea><button class="original-button" type="submit">Send inquiry <span class="material-symbols-outlined">arrow_forward</span></button></form>';
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
    $products = safe_query(fn() => db()->query('SELECT * FROM products WHERE published = 1 ORDER BY display_order, name')->fetchAll(), []);
    $html = original_slot($html, 'product-filter-slot', original_product_cards($products));
    $html = original_slot($html, 'offset-calculator-slot', '');
    $html = original_slot($html, 'tech-specs-slot', '');
  }
  if ($page === 'blog') {
    $posts = safe_query(fn() => db()->query('SELECT * FROM blog_posts WHERE published = 1 ORDER BY published_at DESC')->fetchAll(), []);
    $html = original_slot($html, 'blog-list-slot', original_blog_cards($posts));
  }
  if ($page === 'contact') {
    $html = original_slot($html, 'contact-form-slot', original_contact_form());
    $html = original_slot($html, 'faq-slot', '');
  }
  echo $html;
}

function render_collaboration_sections(): void
{
    ?>
    <section class="section bg-light" aria-labelledby="collaboration-path-heading">
      <div class="container">
        <div class="row align-items-end g-4"><div class="col-lg-5"><span class="eyebrow">From conversation to pilot</span><h2 id="collaboration-path-heading" class="display-5 mt-2">A practical path for ambitious material ideas.</h2></div><p class="lead col-lg-7">Good biomaterials work is collaborative. Start with the problem, test the right variables, and make decisions with evidence from the application that matters.</p></div>
        <div class="row g-4 mt-4">
          <?php foreach ([['01','Bring the use case','Share the format, performance targets, volumes, and end-of-life requirements your team is solving for.'],['02','Test the material fit','Align on the right product direction, then evaluate processing, durability, barrier needs, and real-world conditions.'],['03','Build the pilot','Move from promising formulation to a measured pilot with the people and equipment needed for responsible scale-up.']] as $step): ?><div class="col-md-4"><article class="border-top border-3 border-success pt-3"><span class="eyebrow"><?= e($step[0]) ?></span><h3 class="h4 mt-2"><?= e($step[1]) ?></h3><p><?= e($step[2]) ?></p></article></div><?php endforeach; ?>
        </div><a class="btn btn-primary sans mt-4" href="<?= e(url('contact')) ?>">Talk through your use case <span aria-hidden="true">&rarr;</span></a>
      </div>
    </section>
    <section class="section hero" aria-labelledby="investor-partner-heading"><div class="container"><div class="row align-items-end g-5"><div class="col-lg-7"><span class="eyebrow">Open for collaboration</span><h2 id="investor-partner-heading" class="display-5 mt-2">Help build the next generation of useful, lower-impact materials.</h2><p class="lead">We are looking for aligned investors and hands-on partners who can help move cassava bioplastics from promising material science into dependable everyday applications.</p></div><div class="col-lg-5 d-flex flex-column gap-2"><a class="btn btn-light sans" href="<?= e(url('contact')) ?>">Start a conversation <span aria-hidden="true">&rarr;</span></a><a class="btn btn-outline-light sans" href="<?= e(url('products')) ?>">Explore our material focus</a></div></div><div class="row g-4 mt-5"><?php foreach ([['Invest in material change','Support the scale-up of cassava-based biomaterials, pilot manufacturing, and the infrastructure needed for responsible growth.'],['Partner for real-world pilots','Bring packaging, film, or molded-product requirements to a technical partnership built around measurable performance.'],['Connect the value chain','Join a network of growers, converters, brands, and researchers working toward practical alternatives to conventional plastics.']] as $item): ?><div class="col-md-4 border-top border-light pt-3"><h3 class="h4"><?= e($item[0]) ?></h3><p><?= e($item[1]) ?></p></div><?php endforeach; ?></div></div></section>
    <?php
}

function render_faq(): void
{
    $faqs = [['Can we request trial bioplastic sample rolls?','Yes. We provide 5kg trial pellets and 100-meter sample reels for test extrusion on standard blow-molding and heat-sealing machinery.'],['How long does home composting take?','Under normal backyard composting conditions (humidity >50%, soil microbes), our film decomposes within 90 to 180 days with no toxic residue.'],['Do you export pellets globally?','We ship directly via Tema Port to Europe, North America, and across the African Continental Free Trade Area (AfCFTA) with full Phytosanitary certification.']];
    ?><section class="container py-3" aria-labelledby="faq-heading"><div class="card p-4"><h3 id="faq-heading" class="h4">&#10067; Quick Procurement Answers</h3><?php foreach ($faqs as $index => $faq): ?><div class="accordion-item bg-light mt-2"><h2 class="accordion-header"><button class="accordion-button collapsed sans" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $index ?>"><?= e($faq[0]) ?></button></h2><div id="faq<?= $index ?>" class="accordion-collapse collapse"><div class="accordion-body"><?= e($faq[1]) ?></div></div></div><?php endforeach; ?></div></section><?php
}

function render_offset_calculator(): void
{
    ?><section class="container mb-4"><div class="card p-4 bg-light"><div class="d-flex justify-content-between"><h3 class="h4">&#128202; Plastic Offset Calculator</h3><span class="badge text-bg-success sans">Interactive</span></div><p>Estimate your enterprise CO2 and petro-plastic reduction by switching to CassavaForge starch polymers:</p><label class="form-label sans d-flex justify-content-between" for="volumeSlider"><span>Annual Packaging Volume:</span><strong id="volumeValue">25,000 kg</strong></label><input class="form-range" id="volumeSlider" type="range" min="5000" max="100000" step="5000" value="25000"><div class="row g-2 text-center mt-2"><div class="col-6"><div class="bg-white p-3 rounded"><strong id="co2Value" class="text-success">42.5 Tons</strong><small class="d-block sans">CO2 Offset</small></div></div><div class="col-6"><div class="bg-white p-3 rounded"><strong id="petroValue" class="text-success">18,750 kg</strong><small class="d-block sans">Petro Plastic Replaced</small></div></div></div><small>Directional planning estimate based on current internal assumptions, not a verified lifecycle assessment.</small></div></section><script>document.getElementById('volumeSlider')?.addEventListener('input',function(){const v=Number(this.value);document.getElementById('volumeValue').textContent=v.toLocaleString()+' kg';document.getElementById('co2Value').textContent=(v*.0017).toLocaleString(undefined,{maximumFractionDigits:1})+' Tons';document.getElementById('petroValue').textContent=Math.round(v*.75).toLocaleString()+' kg';});</script><?php
}

function render_specs_modal(): void
{
    ?><button class="btn btn-outline-dark sans" data-bs-toggle="modal" data-bs-target="#specModal">&#128300; Technical Spec Sheet</button><div class="modal fade" id="specModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h3 class="modal-title">Cassava Resin Specification Summary</h3><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>Preliminary material property comparison between CassavaForge Native Resin CF-100 and standard low-density polyethylene (LDPE).</p><div class="table-responsive"><table class="table"><thead><tr><th>Parameter</th><th class="text-success">CassavaForge CF-100</th><th>Standard LDPE</th></tr></thead><tbody><?php foreach ([['Feedstock Source','Manihot esculenta (Cassava)','Petroleum Naphtha'],['Tensile Modulus (MPa)','280 - 450','200 - 400'],['Elongation at Break (%)','320 - 480%','400 - 600%'],['Industrial Compost','< 90 days','Non-degradable (400+ yrs)'],['Marine Toxicity','Non-toxic, bio-assimilable','Microplastic hazardous']] as $row): ?><tr><th><?= e($row[0]) ?></th><td class="text-success"><?= e($row[1]) ?></td><td><?= e($row[2]) ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="modal-footer"><button class="btn btn-success sans" data-bs-dismiss="modal">Close Window</button></div></div></div></div><?php
}

function render_about_sections(): void
{
  ?><section class="section"><div class="container"><div class="row g-4"><div class="col-md-4"><div class="card p-4 h-100"><h3 class="h4">Our Story</h3><p>Starch content yield &gt; 85%</p><p>Supports rural livelihoods</p></div></div><div class="col-md-4"><div class="card p-4 h-100"><h3 class="h4">Our Vision</h3><p>Sourced sustainably from certified fair-trade agro-cooperatives.</p><p>Non-Toxic Starch Extraction</p></div></div><div class="col-md-4"><div class="card p-4 h-100"><h3 class="h4">Our Mission</h3><p>Zero hazardous chemical solvents; water-recirculating milling process.</p><p>Compounded into drop-in bio-pellets compatible with existing plastic machinery.</p></div></div></div><div class="row g-5 mt-4"><div class="col-md-6"><h2>Why Cassava?</h2><p>Cassava is a resilient crop with the potential to support rural livelihoods while providing a dependable renewable feedstock for modern materials.</p></div><div class="col-md-6"><h2>From Soil to Solution</h2><p>Our process degrades into nutrient-dense biomass that enriches farmland compost, connecting material performance back to the communities that grow it.</p></div></div></div></section><section class="section bg-light"><div class="container"><h3>Join the Biopolymer Revolution</h3><p>Discover how our cassava resin blends can seamlessly integrate into your packaging supply chain.</p><a class="btn btn-primary sans" href="<?= e(url('products')) ?>">Explore Materials Catalog</a></div></section><?php
}

function render_impact_sections(): void
{
  ?><section class="section"><div class="container"><h2>The Core Pillars of Our Impact</h2><div class="row g-4 mt-2"><?php foreach ([['Less Plastic Waste','Reducing pollution at the source','Target: 40k Tons Displaced'],['Lower Emissions','Turning waste into opportunity','Degrades naturally in soil within 180 days'],['Stronger Communities','Creating value for local farmers','100% locally sourced regenerative harvest'],['Circular Economy','Closing the loop responsibly','Non-toxic to aquatic and terrestrial life']] as $pillar): ?><div class="col-md-3"><div class="card p-4 h-100"><h3 class="h5"><?= e($pillar[0]) ?></h3><p><?= e($pillar[1]) ?></p><strong class="text-success"><?= e($pillar[2]) ?></strong></div></div><?php endforeach; ?></div><div class="row g-5 mt-5"><div class="col-md-6"><h2>A Cleaner Planet Starts with Better Choices</h2><p>Traceable farm-to-product blockchain ledger.</p><p>Rigorous standards, verifiable traceability.</p></div><div class="col-md-6"><h3>Rigorous standards, verifiable traceability.</h3><p>Certified compliant with ASTM D6400 and EN 13432 industrial and home compostability protocols.</p></div></div><a class="btn btn-primary sans mt-4" href="<?= e(url('contact')) ?>">Partner With CassavaForge</a><p>Transform your enterprise supply chain with regenerative polymers.</p></div></section><?php
}

function render_contact_sections(): void
{
  ?><section class="section bg-light"><div class="container"><div class="row g-4"><div class="col-md-4"><h3 class="h5">Inquiries &amp; Partnerships</h3><p>Enterprise ISO-Certified Facility</p><p>Zero toxic additives or residual microplastics</p></div><div class="col-md-4"><h3 class="h5">Our Processing Nodes</h3><p>Tema Industrial Free Zone</p><p>Manufacturing &amp; Logistics</p></div><div class="col-md-4"><h3 class="h5">How we help</h3><p>Starch tensile optimization</p><p>Fair-price harvest contracts</p><p>Dedicated sample dispatch</p></div></div></div></section><?php
}
