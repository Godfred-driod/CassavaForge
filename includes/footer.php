</main>
<?php
$currentPath = $GLOBALS['currentPath'] ?? '';
$socials = [
    ['LinkedIn', 'https://www.linkedin.com/company/139313981', 'M4.98 3.5a2.25 2.25 0 1 1 0 4.5 2.25 2.25 0 0 1 0-4.5ZM3 9h4v12H3V9Zm6 0h3.83v1.64h.05c.53-1 1.83-2.06 3.77-2.06C20.68 8.58 22 11 22 14.15V21h-4v-6.08c0-1.45-.03-3.32-2.02-3.32-2.02 0-2.33 1.58-2.33 3.21V21H9V9Z'],
];
?>
<footer class="w-full bg-primary text-on-primary mt-auto">
  <div class="max-w-[1240px] mx-auto px-layout-margin-mobile lg:px-layout-margin-desktop pt-space-3xl pb-space-2xl">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-layout-gutter pb-space-2xl">
      <div class="md:col-span-5 flex flex-col gap-space-md"><div class="flex items-center gap-space-sm"><img alt="CassavaForge" class="site-logo h-8 w-auto object-contain brightness-0 invert" src="<?= e(url('assets/images/cassavaforge-logo.png')) ?>"><span class="site-wordmark font-headline-sm text-headline-sm tracking-tight text-on-primary">CassavaForge</span></div><p class="font-body-md text-body-md text-on-primary-container max-w-sm">Sustainable Materials. A Brighter Future.</p></div>
      <div class="md:col-span-4 flex flex-col gap-space-sm"><span class="font-label-caps text-label-caps text-secondary-fixed tracking-wider uppercase">Quick Links</span><nav class="flex flex-col gap-space-xs"><?php foreach (['' => 'Home', 'about' => 'About', 'products' => 'Products', 'impact' => 'Impact', 'blog' => 'Blog', 'contact' => 'Contact', 'privacy' => 'Privacy'] as $link => $label): ?><a class="font-body-sm text-body-sm text-on-primary-container hover:text-on-primary transition-colors" href="<?= e(url($link)) ?>"><?= e($label) ?></a><?php endforeach; ?></nav></div>
      <div class="md:col-span-3 flex flex-col gap-space-sm"><span class="font-label-caps text-label-caps text-secondary-fixed tracking-wider uppercase">Connect With Us</span><div class="flex items-center gap-space-sm"><?php foreach ($socials as $social): ?><a aria-label="<?= e($social[0]) ?>" class="w-9 h-9 rounded-full bg-on-primary-fixed-variant/40 hover:bg-on-primary-fixed-variant flex items-center justify-center transition-colors" href="<?= e($social[1]) ?>"><svg aria-hidden="true" class="h-5 w-5 fill-current text-on-primary" viewBox="0 0 24 24"><path d="<?= svg_path($social[2]) ?>"></path></svg></a><?php endforeach; ?></div></div>
    </div>
    <div class="pt-space-lg flex flex-col sm:flex-row items-center justify-between gap-space-md font-body-sm text-body-sm text-on-primary-container"><p>&copy; 2026 CassavaForge. All rights reserved.</p><p>First-party visit data is described in our <a class="text-secondary-fixed" href="<?= e(url('privacy')) ?>">privacy note</a>.</p></div>
  </div>
</footer>
<nav class="mobile-bottom-nav md:hidden fixed bottom-0 inset-x-0 z-50 bg-surface/95 backdrop-blur-xl border-t border-black/5" aria-label="Primary">
  <div class="mobile-bottom-nav-inner flex items-stretch justify-between gap-0 px-1 safe-bottom">
    <?php foreach ([['','Home','eco'],['about','About','science'],['products','Products','inventory_2'],['impact','Impact','compost'],['blog','Blog','article'],['contact','Contact','contact_support']] as $tab): ?>
      <a href="<?= e(url($tab[0])) ?>" aria-current="<?= $currentPath === $tab[0] ? 'page' : 'false' ?>" class="mobile-bottom-nav-link <?= $currentPath === $tab[0] ? 'is-active' : '' ?>">
        <span class="material-symbols-outlined" aria-hidden="true"><?= e($tab[2]) ?></span>
        <span class="mobile-bottom-nav-label"><?= e($tab[1]) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
