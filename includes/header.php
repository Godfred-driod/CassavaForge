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
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
  <div class="container py-2">
    <a class="navbar-brand d-flex align-items-center gap-2 fw-bold sans text-dark" href="<?= e(url('')) ?>"><img src="<?= e(url('assets/images/cassavaforge-logo.png')) ?>" alt="CassavaForge Logo" height="32"><span>CassavaForge</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav">
      <div class="navbar-nav ms-auto align-items-lg-center gap-lg-3 sans">
        <?php foreach (['about' => 'About', 'products' => 'Products', 'impact' => 'Impact', 'blog' => 'Blog'] as $path => $label): ?>
          <a class="nav-link <?= $currentPath === $path ? 'active' : '' ?>" aria-current="<?= $currentPath === $path ? 'page' : 'false' ?>" href="<?= e(url($path)) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
        <a class="btn btn-success rounded-pill px-4" href="<?= e(url('contact')) ?>">Get in Touch</a><a class="nav-link" href="<?= e(url('admin/login')) ?>" aria-label="Admin login">&#128100;</a>
      </div>
    </div>
  </div>
</nav>
<main class="pt-5 pb-4">
