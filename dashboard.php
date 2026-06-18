<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/auth.php';

require_login();

$pdo = db();
$pageTitle = 'Dashboard';

$totalProjects = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();

$statusCounts = [
    'live' => 0,
    'draft' => 0,
    'development' => 0,
    'bug' => 0,
    'archived' => 0,
];

$stmt = $pdo->query('SELECT status, COUNT(*) AS total FROM projects GROUP BY status');
foreach ($stmt->fetchAll() as $row) {
    $statusCounts[$row['status']] = (int)$row['total'];
}

$favorites = $pdo->query(
    'SELECT p.*, c.name AS category_name
     FROM projects p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.is_favorite = 1
     ORDER BY p.updated_at DESC, p.created_at DESC
     LIMIT 6'
)->fetchAll();

$latest = $pdo->query(
    'SELECT p.*, c.name AS category_name
     FROM projects p
     LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY p.created_at DESC
     LIMIT 6'
)->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>

<section class="grid stats-grid">
  <div class="card stat-card"><span>Total Project</span><strong><?= e((string)$totalProjects) ?></strong></div>
  <div class="card stat-card"><span>Live</span><strong><?= e((string)$statusCounts['live']) ?></strong></div>
  <div class="card stat-card"><span>Draft</span><strong><?= e((string)$statusCounts['draft']) ?></strong></div>
  <div class="card stat-card"><span>Development</span><strong><?= e((string)$statusCounts['development']) ?></strong></div>
  <div class="card stat-card"><span>Bug/Error</span><strong><?= e((string)$statusCounts['bug']) ?></strong></div>
  <div class="card stat-card"><span>Archived</span><strong><?= e((string)$statusCounts['archived']) ?></strong></div>
</section>

<div class="section-head">
  <div>
    <h2>Project Favorit</h2>
    <p>Project penting yang kamu tandai bintang.</p>
  </div>
  <a class="btn btn-secondary" href="<?= e(url('projects.php?favorite=1')) ?>">Lihat Semua</a>
</div>

<?php if (!$favorites): ?>
  <div class="card empty-state">Belum ada project favorit. Kasih bintang dulu, biar hidup dashboard ini punya tujuan.</div>
<?php else: ?>
  <section class="grid project-grid">
    <?php foreach ($favorites as $project): ?>
      <article class="card project-card">
        <img class="project-thumb" src="<?= e(thumbnail_url($project['thumbnail'])) ?>" alt="<?= e($project['name']) ?>">
        <div class="project-body">
          <div class="project-title-row">
            <h3><?= e($project['name']) ?></h3>
            <span>⭐</span>
          </div>
          <p class="project-desc"><?= e(excerpt($project['description'])) ?></p>
          <div class="project-meta">
            <span class="<?= e(status_class($project['status'])) ?>"><?= e(status_label($project['status'])) ?></span>
            <?php if ($project['category_name']): ?><span class="badge"><?= e($project['category_name']) ?></span><?php endif; ?>
            <?php if ((int)$project['is_public'] === 1): ?><span class="badge badge-public">Public</span><?php endif; ?>
          </div>
          <div class="actions">
            <a class="btn btn-secondary" href="<?= e(url('project-detail.php?id=' . $project['id'])) ?>">Detail</a>
            <a class="btn btn-ghost" href="<?= e(url('project-edit.php?id=' . $project['id'])) ?>">Edit</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
<?php endif; ?>

<div class="section-head">
  <div>
    <h2>Project Terbaru</h2>
    <p>Yang terakhir kamu masukin ke library.</p>
  </div>
  <a class="btn" href="<?= e(url('project-add.php')) ?>">Tambah Project</a>
</div>

<?php if (!$latest): ?>
  <div class="card empty-state">Belum ada project. Tambahkan satu, biar dashboard ini nggak cuma jadi dekorasi gelap.</div>
<?php else: ?>
  <section class="grid project-grid">
    <?php foreach ($latest as $project): ?>
      <article class="card project-card">
        <img class="project-thumb" src="<?= e(thumbnail_url($project['thumbnail'])) ?>" alt="<?= e($project['name']) ?>">
        <div class="project-body">
          <h3><?= e($project['name']) ?></h3>
          <p class="project-desc"><?= e(excerpt($project['description'])) ?></p>
          <div class="project-meta">
            <span class="<?= e(status_class($project['status'])) ?>"><?= e(status_label($project['status'])) ?></span>
            <?php if ($project['category_name']): ?><span class="badge"><?= e($project['category_name']) ?></span><?php endif; ?>
            <?php if ((int)$project['is_favorite'] === 1): ?><span class="badge badge-favorite">Favorite</span><?php endif; ?>
          </div>
          <div class="actions">
            <a class="btn btn-secondary" href="<?= e(url('project-detail.php?id=' . $project['id'])) ?>">Detail</a>
            <a class="btn btn-ghost" href="<?= e(url('project-edit.php?id=' . $project['id'])) ?>">Edit</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
