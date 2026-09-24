<?php
$pageTitle = $pageTitle ?? 'Workspace';
$currentPath = (string) ($GLOBALS['currentPath'] ?? '');
$GLOBALS['currentPath'] = $currentPath;
$meta = page_meta($pageTitle, $currentPath);
$staffUser = current_user();
$adminNav = [
    ['admin', 'Dashboard', 'dashboard'],
    ['admin/orders', 'Orders', 'receipt_long'],
    ['admin/products', 'Products', 'inventory_2'],
    ['admin/blog', 'Journal', 'article'],
    ['admin/messages', 'Messages', 'mail'],
    ['admin/landers', 'Traffic', 'monitoring'],
];
if (($staffUser['role'] ?? '') === 'owner') {
    $adminNav[] = ['admin/people', 'People', 'group'];
}
$adminNav[] = ['admin/account', 'Account', 'manage_accounts'];
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($meta['title']) ?></title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="icon" href="<?= e(url('assets/images/cassavaforge-logo.png')) ?>" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/touchups.css')) ?>">
</head>
<body class="admin-body">
<a class="visually-hidden-focusable skip-link" href="#main-content">Skip to content</a>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <a class="admin-brand" href="<?= e(url('admin')) ?>">
      <img alt="" src="<?= e(url('assets/images/cassavaforge-logo.png')) ?>">
      <span>Workspace</span>
    </a>
    <nav class="admin-nav" aria-label="Workspace">
      <?php foreach ($adminNav as $item):
          $active = $item[0] === 'admin'
              ? $currentPath === 'admin'
              : ($currentPath === $item[0] || str_starts_with($currentPath, $item[0] . '/'));
      ?>
        <a href="<?= e(url($item[0])) ?>"<?= $active ? ' aria-current="page"' : '' ?> class="admin-nav-link<?= $active ? ' is-active' : '' ?>">
          <span class="material-symbols-outlined"><?= e($item[2]) ?></span>
          <?= e($item[1]) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-foot">
      <a class="admin-nav-link" href="<?= e(url('')) ?>"><span class="material-symbols-outlined">public</span> View site</a>
      <a class="admin-nav-link" href="<?= e(url('logout')) ?>"><span class="material-symbols-outlined">logout</span> Sign out</a>
    </div>
  </aside>
  <div class="admin-frame">
    <header class="admin-topbar">
      <strong><?= e($pageTitle) ?></strong>
      <a class="admin-topbar-user" href="<?= e(url('admin/account')) ?>"><?= e((string) ($staffUser['email'] ?? '')) ?></a>
    </header>
    <main id="main-content" class="admin-main">
