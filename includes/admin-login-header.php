<?php
$pageTitle = $pageTitle ?? 'Admin sign in';
$currentPath = (string) ($GLOBALS['currentPath'] ?? 'admin/login');
$GLOBALS['currentPath'] = $currentPath;
$meta = page_meta($pageTitle, $currentPath);
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
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/touchups.css')) ?>">
</head>
<body class="admin-login-body">
<a class="visually-hidden-focusable skip-link" href="#main-content">Skip to content</a>
<main id="main-content" class="admin-login">
  <div class="admin-login-card">
    <a class="admin-login-brand" href="<?= e(url('')) ?>">
      <img alt="CassavaForge" src="<?= e(url('assets/images/cassavaforge-logo.png')) ?>">
    </a>
    <p class="eyebrow mb-2"><?= e((string) ($GLOBALS['loginEyebrow'] ?? 'Private workspace')) ?></p>
    <h1 class="h3 mb-4"><?= e((string) ($GLOBALS['loginHeading'] ?? 'Sign in')) ?></h1>
