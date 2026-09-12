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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container py-2">
    <a class="navbar-brand fw-bold sans" href="<?= e(url('')) ?>">CASSAVAFORGE</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav">
      <div class="navbar-nav ms-auto gap-lg-3 sans">
        <?php foreach (['about' => 'About', 'products' => 'Products', 'impact' => 'Impact', 'blog' => 'Journal', 'contact' => 'Contact'] as $path => $label): ?>
          <a class="nav-link <?= $currentPath === $path ? 'active' : '' ?>" href="<?= e(url($path)) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</nav>
<main>
