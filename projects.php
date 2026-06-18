<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/auth.php';

require_login();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare('SELECT thumbnail FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $project = $stmt->fetch();

        if ($project) {
            delete_thumbnail($project['thumbnail']);
            $delete = $pdo->prepare('DELETE FROM projects WHERE id = ?');
            $delete->execute([$id]);
            set_flash('success', 'Project berhasil dihapus.');
        }

        redirect('projects.php');
    }
}

$pageTitle = 'Projects';

$categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

$q = trim((string)($_GET['q'] ?? ''));
$category = (int)($_GET['category'] ?? 0);
$status = trim((string)($_GET['status'] ?? ''));
$favorite = trim((string)($_GET['favorite'] ?? ''));
$sort = trim((string)($_GET['sort'] ?? 'newest'));

$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ? OR p.tech_stack LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}

if ($category > 0) {
    $where[] = 'p.category_id = ?';
    $params[] = $category;
}

if ($status !== '' && array_key_exists($status, status_options())) {
    $where[] = 'p.status = ?';
    $params[] = $status;
}

if ($favorite === '1') {
    $where[] = 'p.is_favorite = 1';
}

$orderBy = $sort === 'oldest' ? 'p.created_at ASC' : 'p.created_at DESC';

$sql = 'SELECT p.*, c.name AS category_name
        FROM projects p
        LEFT JOIN categories c ON c.id = p.category_id';

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY ' . $orderBy;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>

<section class="card card-pad filter-card">
  <form class="filter-form" method="get">
    <div class="form-group">
      <label>Cari Project</label>
      <input class="input" name="q" placeholder="Nama, deskripsi, tech stack..." value="<?= e($q) ?>">
    </div>

    <div class="form-group">
      <label>Kategori</label>
      <select class="select" name="category">
        <option value="0">Semua kategori</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= e((string)$cat['id']) ?>" <?= $category === (int)$cat['id'] ? 'selected' : '' ?>>
            <?= e($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Status</label>
      <select class="select" name="status">
        <option value="">Semua status</option>
        <?php foreach (status_options() as $key => $label): ?>
          <option value="<?= e($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Favorite</label>
      <select class="select" name="favorite">
        <option value="">Semua</option>
        <option value="1" <?= $favorite === '1' ? 'selected' : '' ?>>Favorite</option>
      </select>
    </div>

    <div class="form-group">
      <label>Sorting</label>
      <select class="select" name="sort">
        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
        <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Terlama</option>
      </select>
    </div>

    <button class="btn" type="submit">Filter</button>
  </form>
</section>

<div class="section-head">
  <div>
    <h2>Daftar Project</h2>
    <p><?= e((string)count($projects)) ?> project ditemukan.</p>
  </div>
  <a class="btn" href="<?= e(url('project-add.php')) ?>">Tambah Project</a>
</div>

<?php if (!$projects): ?>
  <div class="card empty-state">Project tidak ditemukan. Filtermu mungkin terlalu niat.</div>
<?php else: ?>
  <section class="grid project-grid">
    <?php foreach ($projects as $project): ?>
      <article class="card project-card">
        <img class="project-thumb" src="<?= e(thumbnail_url($project['thumbnail'])) ?>" alt="<?= e($project['name']) ?>">
        <div class="project-body">
          <div class="project-title-row">
            <h3><?= e($project['name']) ?></h3>
            <?php if ((int)$project['is_favorite'] === 1): ?><span>⭐</span><?php endif; ?>
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
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= e((string)$project['id']) ?>">
              <button class="btn btn-danger" type="submit" data-confirm="Hapus project ini?">Hapus</button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
