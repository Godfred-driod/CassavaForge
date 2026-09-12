</main>
<?php
$socials = [
    ['LinkedIn', 'https://www.linkedin.com/company/139313981', 'M4.98 3.5a2.25 2.25 0 1 1 0 4.5 2.25 2.25 0 0 1 0-4.5ZM3 9h4v12H3V9Zm6 0h3.83v1.64h.05c.53-1 1.83-2.06 3.77-2.06C20.68 8.58 22 11 22 14.15V21h-4v-6.08c0-1.45-.03-3.32-2.02-3.32-2.02 0-2.33 1.58-2.33 3.21V21H9V9Z'],
    ['X', '#', 'M18.244 2H21.5l-7.11 8.13L22.75 22h-6.59l-5.16-6.75L5.1 22H1.84l7.61-8.7L1.25 2h6.76l4.66 6.16L18.244 2Zm-1.15 17.9h1.8L7 4h-1.93l12.02 15.9Z'],
    ['Instagram', '#', 'M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm5-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z'],
    ['YouTube', '#', 'M21.58 7.19a2.75 2.75 0 0 0-1.94-1.95C17.93 4.75 12 4.75 12 4.75s-5.93 0-7.64.49a2.75 2.75 0 0 0-1.94 1.95C1.93 8.9 1.93 12 1.93 12s0 3.1.49 4.81a2.75 2.75 0 0 0 1.94 1.95c1.71.49 7.64.49 7.64.49s5.93 0 7.64-.49a2.75 2.75 0 0 0 1.94-1.95c.49-1.71.49-4.81.49-4.81s0-3.1-.49-4.81ZM10 15.5v-7l6 3.5-6 3.5Z'],
];
?>
<footer class="w-full bg-primary text-on-primary mt-auto">
  <div class="max-w-[1240px] mx-auto px-layout-margin-mobile lg:px-layout-margin-desktop pt-space-3xl pb-space-2xl">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-layout-gutter pb-space-2xl">
      <div class="md:col-span-5 flex flex-col gap-space-md"><div class="flex items-center gap-space-sm"><img alt="CassavaForge Logo" class="h-8 w-auto object-contain brightness-0 invert" src="<?= e(url('assets/images/cassavaforge-logo.png')) ?>"><span class="font-headline-sm text-headline-sm tracking-tight text-on-primary">CassavaForge</span></div><p class="font-body-md text-body-md text-on-primary-container max-w-sm">Sustainable Materials. A Brighter Future.</p></div>
      <div class="md:col-span-4 flex flex-col gap-space-sm"><span class="font-label-caps text-label-caps text-secondary-fixed tracking-wider uppercase">Quick Links</span><nav class="flex flex-col gap-space-xs"><?php foreach (['' => 'Home', 'about' => 'About', 'products' => 'Products', 'impact' => 'Impact', 'blog' => 'Blog'] as $link => $label): ?><a class="font-body-sm text-body-sm text-on-primary-container hover:text-on-primary transition-colors" href="<?= e(url($link)) ?>"><?= e($label) ?></a><?php endforeach; ?></nav></div>
      <div class="md:col-span-3 flex flex-col gap-space-sm"><span class="font-label-caps text-label-caps text-secondary-fixed tracking-wider uppercase">Connect With Us</span><div class="flex items-center gap-space-sm"><?php foreach ($socials as $social): ?><a aria-label="<?= e($social[0]) ?>" class="w-9 h-9 rounded-full bg-on-primary-fixed-variant/40 hover:bg-on-primary-fixed-variant flex items-center justify-center transition-colors" href="<?= e($social[1]) ?>"><svg aria-hidden="true" class="h-5 w-5 fill-current text-on-primary" viewBox="0 0 24 24"><path d="<?= svg_path($social[2]) ?>"></path></svg></a><?php endforeach; ?></div></div>
    </div>
    <div class="pt-space-lg flex flex-col sm:flex-row items-center justify-between gap-space-md font-body-sm text-body-sm text-on-primary-container"><p>&copy; 2025 CassavaForge. All rights reserved.</p><p class="font-label-caps text-label-caps tracking-widest text-tertiary-fixed">BIO-PLASTICS REDEFINED</p></div>
  </div>
</footer>
<nav class="mobile-bottom-nav md:hidden fixed bottom-0 inset-x-0 z-50 bg-surface/85 backdrop-blur-xl shadow-[0_-2px_12px_rgba(0,0,0,0.04)]"><div class="flex items-center justify-around h-16 px-space-xs"><?php foreach ([['','Home','eco'],['about','About','science'],['products','Products','inventory_2'],['impact','Impact','compost'],['blog','Blog','article'],['contact','Contact','contact_support']] as $tab): ?><a href="<?= e(url($tab[0])) ?>" aria-current="<?= $currentPath === $tab[0] ? 'page' : 'false' ?>" class="flex flex-col items-center justify-center w-12 min-h-[44px] <?= $currentPath === $tab[0] ? 'text-primary font-bold' : 'text-on-surface-variant' ?>"><span class="material-symbols-outlined text-[22px]"><?= e($tab[2]) ?></span><span class="font-label-caps text-[10px] leading-tight"><?= e($tab[1]) ?></span></a><?php endforeach; ?></div></nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
