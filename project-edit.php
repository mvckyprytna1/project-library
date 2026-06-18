<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/auth.php';

require_login();

$pdo = db();
$pageTitle = 'Edit Project';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    set_flash('error', 'Project tidak ditemukan.');
    redirect('projects.php');
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? 'update';

    if ($action === 'delete') {
        delete_thumbnail($project['thumbnail']);
        $delete = $pdo->prepare('DELETE FROM projects WHERE id = ?');
        $delete->execute([$id]);

        set_flash('success', 'Project berhasil dihapus.');
        redirect('projects.php');
    }

    $name = trim((string)($_POST['name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $status = trim((string)($_POST['status'] ?? 'draft'));
    $techStack = trim((string)($_POST['tech_stack'] ?? ''));
    $hostingPlatform = trim((string)($_POST['hosting_platform'] ?? ''));
    $githubUrl = normalize_url($_POST['github_url'] ?? '');
    $liveUrl = normalize_url($_POST['live_url'] ?? '');
    $notes = trim((string)($_POST['notes'] ?? ''));
    $isFavorite = isset($_POST['is_favorite']) ? 1 : 0;
    $isPublic = isset($_POST['is_public']) ? 1 : 0;

    if ($name === '') {
        $error = 'Nama project wajib diisi.';
    } elseif (!array_key_exists($status, status_options())) {
        $error = 'Status project tidak valid.';
    } elseif (!is_valid_url($githubUrl) || !is_valid_url($liveUrl)) {
        $error = 'Link GitHub atau Live Demo harus berupa URL valid.';
    } else {
        try {
            $thumbnail = upload_thumbnail($_FILES['thumbnail'] ?? [], $project['thumbnail']);
            $slug = unique_slug($pdo, 'projects', $name, $id);

            $update = $pdo->prepare(
                'UPDATE projects
                 SET category_id = ?, name = ?, slug = ?, description = ?, status = ?, tech_stack = ?,
                     hosting_platform = ?, github_url = ?, live_url = ?, thumbnail = ?, notes = ?,
                     is_favorite = ?, is_public = ?
                 WHERE id = ?'
            );

            $update->execute([
                $categoryId > 0 ? $categoryId : null,
                $name,
                $slug,
                $description !== '' ? $description : null,
                $status,
                $techStack !== '' ? $techStack : null,
                $hostingPlatform !== '' ? $hostingPlatform : null,
                $githubUrl,
                $liveUrl,
                $thumbnail,
                $notes !== '' ? $notes : null,
                $isFavorite,
                $isPublic,
                $id,
            ]);

            set_flash('success', 'Project berhasil diperbarui.');
            redirect('project-detail.php?id=' . $id);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

require __DIR__ . '/includes/layout_top.php';
?>

<section class="card card-pad">
  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update">

    <div class="form-group">
      <label>Nama Project *</label>
      <input class="input" name="name" required value="<?= e($_POST['name'] ?? $project['name']) ?>">
    </div>

    <div class="form-group">
      <label>Kategori</label>
      <select class="select" name="category_id">
        <option value="0">Tanpa kategori</option>
        <?php foreach ($categories as $cat): ?>
          <?php $selectedCat = (int)($_POST['category_id'] ?? $project['category_id']); ?>
          <option value="<?= e((string)$cat['id']) ?>" <?= $selectedCat === (int)$cat['id'] ? 'selected' : '' ?>>
            <?= e($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Status</label>
      <select class="select" name="status">
        <?php foreach (status_options() as $key => $label): ?>
          <option value="<?= e($key) ?>" <?= ($_POST['status'] ?? $project['status']) === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Hosting Platform</label>
      <input class="input" name="hosting_platform" value="<?= e($_POST['hosting_platform'] ?? $project['hosting_platform']) ?>">
    </div>

    <div class="form-group">
      <label>Tech Stack</label>
      <input class="input" name="tech_stack" value="<?= e($_POST['tech_stack'] ?? $project['tech_stack']) ?>">
    </div>

    <div class="form-group">
      <label>Thumbnail / Screenshot</label>
      <img class="preview-img" src="<?= e(thumbnail_url($project['thumbnail'])) ?>" alt="<?= e($project['name']) ?>">
      <input class="input" type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp" data-preview-input="#thumbPreview">
      <img id="thumbPreview" class="preview-img hidden" alt="Preview thumbnail baru">
      <p class="help">Kosongkan kalau tidak ingin mengganti thumbnail.</p>
    </div>

    <div class="form-group">
      <label>Link GitHub</label>
      <input class="input" type="url" name="github_url" value="<?= e($_POST['github_url'] ?? $project['github_url']) ?>">
    </div>

    <div class="form-group">
      <label>Link Live Demo</label>
      <input class="input" type="url" name="live_url" value="<?= e($_POST['live_url'] ?? $project['live_url']) ?>">
    </div>

    <div class="form-group full-row">
      <label>Deskripsi</label>
      <textarea class="textarea" name="description"><?= e($_POST['description'] ?? $project['description']) ?></textarea>
    </div>

    <div class="form-group full-row">
      <label>Catatan Pribadi</label>
      <textarea class="textarea" name="notes"><?= e($_POST['notes'] ?? $project['notes']) ?></textarea>
      <p class="help">Catatan ini tidak tampil di public.php.</p>
    </div>

    <div class="form-group full-row">
      <label>Mode</label>
      <div class="switch-row">
        <label class="check">
          <input type="checkbox" name="is_favorite" <?= checked(isset($_POST['is_favorite']) || (!$_POST && (int)$project['is_favorite'] === 1)) ?>>
          Favorite
        </label>
        <label class="check">
          <input type="checkbox" name="is_public" <?= checked(isset($_POST['is_public']) || (!$_POST && (int)$project['is_public'] === 1)) ?>>
          Public
        </label>
      </div>
    </div>

    <div class="actions full-row">
      <button class="btn" type="submit">Simpan Perubahan</button>
      <a class="btn btn-ghost" href="<?= e(url('project-detail.php?id=' . $project['id'])) ?>">Batal</a>
    </div>
  </form>

  <form method="post" class="actions">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="delete">
    <button class="btn btn-danger" type="submit" data-confirm="Hapus project ini secara permanen?">Hapus Project</button>
  </form>
</section>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
