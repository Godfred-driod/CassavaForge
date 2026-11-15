<?php
$pageTitle = $pageTitle ?? 'CassavaForge';
$currentPath = (string) ($GLOBALS['currentPath'] ?? '');
if ($currentPath === '') {
    $currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');
    $currentPath = preg_replace('#^(php-app|cassavaforge)/?#', '', $currentPath);
}
$GLOBALS['currentPath'] = $currentPath;
$meta = page_meta($pageTitle, $currentPath);
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($meta['title']) ?></title>
  <meta name="description" content="<?= e($meta['description']) ?>">
  <link rel="canonical" href="<?= e($meta['canonical']) ?>">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e($meta['title']) ?>">
  <meta property="og:description" content="<?= e($meta['description']) ?>">
  <meta property="og:url" content="<?= e($meta['canonical']) ?>">
  <meta property="og:image" content="<?= e($meta['image']) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <link rel="icon" href="<?= e(url('assets/images/cassavaforge-logo.png')) ?>" type="image/png">
  <link rel="apple-touch-icon" href="<?= e(url('assets/images/cassavaforge-logo.png')) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/original.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/touchups.css')) ?>">
</head>
<body class="page-<?= e($currentPath ?: 'home') ?>">
<a class="visually-hidden-focusable skip-link" href="#main-content">Skip to content</a>
<header class="site-header fixed top-0 left-0 right-0 z-50 bg-surface/85 backdrop-blur-xl shadow-sm">
  <div class="site-header-inner h-16 md:h-20 max-w-[1240px] mx-auto px-layout-margin-mobile lg:px-layout-margin-desktop flex items-center justify-between gap-3 md:gap-space-lg">
    <a href="<?= e(url('')) ?>" class="site-brand flex min-w-0 items-center gap-2 md:gap-space-sm">
      <img alt="CassavaForge" class="site-logo h-8 w-auto shrink-0 object-contain" src="<?= e(url('assets/images/cassavaforge-logo.png')) ?>">
      <span class="site-wordmark truncate font-headline-sm text-headline-sm tracking-tight text-on-surface">CassavaForge</span>
    </a>
    <nav class="hidden md:flex items-center gap-space-xl">
      <?php foreach (['' => 'Home', 'about' => 'About', 'products' => 'Products', 'impact' => 'Impact', 'blog' => 'Blog'] as $path => $label): ?><a href="<?= e(url($path)) ?>" aria-current="<?= $currentPath === $path ? 'page' : 'false' ?>" class="<?= $currentPath === $path ? 'transition-colors text-primary font-bold' : 'font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors' ?>"><?= e($label) ?></a><?php endforeach; ?>
    </nav>
    <div class="site-header-actions flex shrink-0 items-center gap-1.5 md:gap-space-sm">
      <?php
        $headerUser = function_exists('current_user') ? current_user() : null;
        if ($headerUser && in_array((string) $headerUser['role'], ['owner', 'staff'], true)):
      ?>
        <a href="<?= e(url('admin')) ?>" class="site-header-account font-label-md text-label-md text-on-surface-variant hover:text-primary" aria-label="Workspace">
          <span class="material-symbols-outlined md:hidden" aria-hidden="true">dashboard</span>
          <span class="site-header-account-label">Workspace</span>
        </a>
      <?php elseif ($headerUser && ($headerUser['role'] ?? '') === 'partner'): ?>
        <a href="<?= e(url('portal')) ?>" class="site-header-account font-label-md text-label-md text-on-surface-variant hover:text-primary" aria-label="Portal">
          <span class="material-symbols-outlined md:hidden" aria-hidden="true">account_circle</span>
          <span class="site-header-account-label">Portal</span>
        </a>
        <a href="<?= e(url('logout')) ?>" class="site-header-account site-header-account-secondary font-label-md text-label-md text-on-surface-variant hover:text-primary" aria-label="Sign out">
          <span class="material-symbols-outlined md:hidden" aria-hidden="true">logout</span>
          <span class="site-header-account-label">Sign out</span>
        </a>
      <?php else: ?>
        <a href="<?= e(url('signin')) ?>" class="site-header-account font-label-md text-label-md text-on-surface-variant hover:text-primary" aria-label="Sign in">
          <span class="material-symbols-outlined md:hidden" aria-hidden="true">person</span>
          <span class="site-header-account-label">Sign in</span>
        </a>
      <?php endif; ?>
      <a href="<?= e(url('contact')) ?>" class="site-header-cta inline-flex items-center justify-center bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md rounded-full transition-all">
        <span class="site-header-cta-long">Get in Touch</span>
        <span class="site-header-cta-short">Contact</span>
      </a>
    </div>
  </div>
</header>
<main id="main-content" class="w-full pt-16 md:pt-20 pb-20 md:pb-0 bg-surface min-h-[calc(100vh-20rem)]">
