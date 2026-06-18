<?php
$user = auth_user();
$pageTitle = $pageTitle ?? APP_NAME;
$searchValue = $_GET['q'] ?? '';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> - <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<div class="app-shell">
  <?php require dirname(__DIR__) . '/includes/sidebar.php'; ?>

  <main class="main-content">
    <header class="topbar">
      <button class="mobile-menu-btn" type="button" data-sidebar-toggle aria-label="Buka menu">
        ☰
      </button>

      <div>
        <p class="eyebrow">Dashboard Pribadi</p>
        <h1><?= e($pageTitle) ?></h1>
      </div>

      <form class="topbar-search" action="<?= e(url('projects.php')) ?>" method="get">
        <input type="search" name="q" placeholder="Cari project..." value="<?= e((string)$searchValue) ?>">
      </form>

      <div class="topbar-user">
        <span><?= e($user['name'] ?? 'Owner') ?></span>
        <small><?= e($user['email'] ?? '') ?></small>
      </div>
    </header>

    <?php if ($flash = get_flash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?>">
        <?= e($flash['message']) ?>
      </div>
    <?php endif; ?>
