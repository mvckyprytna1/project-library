<?php $path = current_path(); ?>
<aside class="sidebar" data-sidebar>
  <div class="brand">
    <div class="brand-logo">VP</div>
    <div>
      <strong>Vicky Library</strong>
      <span>Project Vault</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <a class="<?= e(is_active('dashboard.php')) ?>" href="<?= e(url('dashboard.php')) ?>">
      <span>⌁</span> Dashboard
    </a>
    <a class="<?= e(is_active('projects.php')) ?>" href="<?= e(url('projects.php')) ?>">
      <span>▦</span> Projects
    </a>
    <a class="<?= e(is_active('project-add.php')) ?>" href="<?= e(url('project-add.php')) ?>">
      <span>＋</span> Add Project
    </a>
    <a class="<?= e(is_active('categories.php')) ?>" href="<?= e(url('categories.php')) ?>">
      <span>◈</span> Categories
    </a>
    <a class="<?= e(is_active('settings.php')) ?>" href="<?= e(url('settings.php')) ?>">
      <span>⚙</span> Settings
    </a>
    <a href="<?= e(url('public.php')) ?>" target="_blank" rel="noopener">
      <span>↗</span> Public Portfolio
    </a>
  </nav>

  <div class="sidebar-footer">
    <a class="btn btn-ghost full" href="<?= e(url('auth/logout.php')) ?>">Logout</a>
  </div>
</aside>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>
