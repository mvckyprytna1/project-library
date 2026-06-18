<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/helpers.php';

$pdo = db();

$stmt = $pdo->query(
    'SELECT p.*, c.name AS category_name, c.color AS category_color
     FROM projects p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.is_public = 1
     ORDER BY p.created_at DESC'
);
$projects = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Public Portfolio - <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="public-page">
  <header class="public-header">
    <div class="brand">
      <div class="brand-logo">VP</div>
      <div>
        <strong>Vicky Project Library</strong>
        <span>Public Portfolio</span>
      </div>
    </div>

    <a class="btn btn-secondary" href="<?= e(url('auth/login.php')) ?>">Owner Login</a>
  </header>

  <section class="public-hero">
    <p class="eyebrow">Public Project Collection</p>
    <h1>Project yang sudah siap ditunjukkan ke dunia.</h1>
    <p>Halaman ini hanya menampilkan project berstatus public. Catatan pribadi, timeline internal, dan hal-hal yang biasanya bikin pemilik project malu tidak ditampilkan.</p>
  </section>

  <main class="public-container">
    <?php if (!$projects): ?>
      <div class="card empty-state">Belum ada project public.</div>
    <?php else: ?>
      <section class="grid project-grid">
        <?php foreach ($projects as $project): ?>
          <article class="card project-card">
            <img class="project-thumb" src="<?= e(thumbnail_url($project['thumbnail'])) ?>" alt="<?= e($project['name']) ?>">
            <div class="project-body">
              <h3><?= e($project['name']) ?></h3>
              <p class="project-desc"><?= e(excerpt($project['description'], 180)) ?></p>

              <div class="project-meta">
                <span class="<?= e(status_class($project['status'])) ?>"><?= e(status_label($project['status'])) ?></span>
                <?php if ($project['category_name']): ?>
                  <span class="badge">
                    <span class="color-dot" style="background: <?= e($project['category_color']) ?>"></span>
                    <?= e($project['category_name']) ?>
                  </span>
                <?php endif; ?>
                <?php if ($project['tech_stack']): ?><span class="badge"><?= e($project['tech_stack']) ?></span><?php endif; ?>
              </div>

              <div class="actions">
                <?php if ($project['live_url']): ?>
                  <a class="btn" target="_blank" rel="noopener" href="<?= e($project['live_url']) ?>">Live Demo</a>
                <?php endif; ?>
                <?php if ($project['github_url']): ?>
                  <a class="btn btn-secondary" target="_blank" rel="noopener" href="<?= e($project['github_url']) ?>">GitHub</a>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
