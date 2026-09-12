<?php
$pageTitle = $pageTitle ?? 'CassavaForge';
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');
$currentPath = preg_replace('#^php-app/?#', '', $currentPath);
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | CassavaForge</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/original.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/touchups.css')) ?>">
</head>
<body class="page-<?= e($currentPath ?: 'home') ?>">
<header class="fixed top-0 left-0 right-0 z-50 bg-surface/85 backdrop-blur-xl shadow-sm">
  <div class="h-20 max-w-[1240px] mx-auto px-layout-margin-mobile lg:px-layout-margin-desktop flex items-center justify-between gap-space-lg">
    <a href="<?= e(url('')) ?>" class="flex items-center gap-space-sm"><img alt="CassavaForge Logo" class="h-8 w-auto object-contain" src="<?= e(url('assets/images/cassavaforge-logo.png')) ?>"><span class="font-headline-sm text-headline-sm tracking-tight text-on-surface">CassavaForge</span></a>
    <nav class="hidden md:flex items-center gap-space-xl">
      <?php foreach (['' => 'Home', 'about' => 'About', 'products' => 'Products', 'impact' => 'Impact', 'blog' => 'Blog'] as $path => $label): ?><a href="<?= e(url($path)) ?>" aria-current="<?= $currentPath === $path ? 'page' : 'false' ?>" class="<?= $currentPath === $path ? 'transition-colors text-primary font-bold' : 'font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors' ?>"><?= e($label) ?></a><?php endforeach; ?>
    </nav>
    <div class="flex items-center gap-space-md"><a href="<?= e(url('contact')) ?>" class="hidden sm:inline-flex items-center justify-center bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md px-space-lg py-space-xs rounded-full transition-all">Get in Touch</a><a href="<?= e(url('admin/login')) ?>" aria-label="Admin login" class="w-8 h-8 rounded-full bg-primary flex items-center justify-center shadow-sm"><span class="material-symbols-outlined text-on-primary text-[18px]">person</span></a></div>
  </div>
</header>
<main class="w-full pt-20 pb-16 md:pb-0 bg-surface min-h-[calc(100vh-20rem)]">
