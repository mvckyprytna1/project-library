<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/auth.php';

require_login();

$pdo = db();
$pageTitle = 'Tambah Project';

$categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

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
            $thumbnail = upload_thumbnail($_FILES['thumbnail'] ?? []);

            $slug = unique_slug($pdo, 'projects', $name);

            $stmt = $pdo->prepare(
                'INSERT INTO projects
                (category_id, name, slug, description, status, tech_stack, hosting_platform, github_url, live_url, thumbnail, notes, is_favorite, is_public)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
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
            ]);

            set_flash('success', 'Project berhasil ditambahkan.');
            redirect('projects.php');
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

    <div class="form-group">
      <label>Nama Project *</label>
      <input class="input" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Kategori</label>
      <select class="select" name="category_id">
        <option value="0">Tanpa kategori</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= e((string)$cat['id']) ?>" <?= (int)($_POST['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
            <?= e($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Status</label>
      <select class="select" name="status">
        <?php foreach (status_options() as $key => $label): ?>
          <option value="<?= e($key) ?>" <?= ($_POST['status'] ?? 'draft') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Hosting Platform</label>
      <input class="input" name="hosting_platform" placeholder="Vercel, cPanel, Netlify, VPS..." value="<?= e($_POST['hosting_platform'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Tech Stack</label>
      <input class="input" name="tech_stack" placeholder="PHP, MySQL, JS, CSS..." value="<?= e($_POST['tech_stack'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Thumbnail / Screenshot</label>
      <input class="input" type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp" data-preview-input="#thumbPreview">
      <p class="help">Format jpg, jpeg, png, webp. Maksimal 2MB.</p>
      <img id="thumbPreview" class="preview-img hidden" alt="Preview thumbnail">
    </div>

    <div class="form-group">
      <label>Link GitHub</label>
      <input class="input" type="url" name="github_url" placeholder="https://github.com/..." value="<?= e($_POST['github_url'] ?? '') ?>">
    </div>

    <div class="form-group">
      <label>Link Live Demo</label>
      <input class="input" type="url" name="live_url" placeholder="https://..." value="<?= e($_POST['live_url'] ?? '') ?>">
    </div>

    <div class="form-group full-row">
      <label>Deskripsi</label>
      <textarea class="textarea" name="description" placeholder="Jelaskan project ini..."><?= e($_POST['description'] ?? '') ?></textarea>
    </div>

    <div class="form-group full-row">
      <label>Catatan Pribadi</label>
      <textarea class="textarea" name="notes" placeholder="Bug, ide update, akses, catatan internal..."><?= e($_POST['notes'] ?? '') ?></textarea>
      <p class="help">Catatan ini tidak tampil di public.php.</p>
    </div>

    <div class="form-group full-row">
      <label>Mode</label>
      <div class="switch-row">
        <label class="check"><input type="checkbox" name="is_favorite" <?= checked(isset($_POST['is_favorite'])) ?>> Favorite</label>
        <label class="check"><input type="checkbox" name="is_public" <?= checked(isset($_POST['is_public'])) ?>> Public</label>
      </div>
    </div>

    <div class="actions full-row">
      <button class="btn" type="submit">Simpan Project</button>
      <a class="btn btn-ghost" href="<?= e(url('projects.php')) ?>">Batal</a>
    </div>
  </form>
</section>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
