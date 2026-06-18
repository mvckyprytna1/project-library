<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/auth.php';

require_login();

$pdo = db();
$pageTitle = 'Categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $name = trim((string)($_POST['name'] ?? ''));
    $color = trim((string)($_POST['color'] ?? '#60a5fa'));

    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
        $color = '#60a5fa';
    }

    if ($action === 'add') {
        if ($name === '') {
            set_flash('error', 'Nama kategori wajib diisi.');
        } else {
            $slug = unique_slug($pdo, 'categories', $name);
            $stmt = $pdo->prepare('INSERT INTO categories (name, slug, color) VALUES (?, ?, ?)');
            $stmt->execute([$name, $slug, $color]);
            set_flash('success', 'Kategori berhasil ditambahkan.');
        }

        redirect('categories.php');
    }

    if ($action === 'update' && $id > 0) {
        if ($name === '') {
            set_flash('error', 'Nama kategori wajib diisi.');
        } else {
            $slug = unique_slug($pdo, 'categories', $name, $id);
            $stmt = $pdo->prepare('UPDATE categories SET name = ?, slug = ?, color = ? WHERE id = ?');
            $stmt->execute([$name, $slug, $color, $id]);
            set_flash('success', 'Kategori berhasil diperbarui.');
        }

        redirect('categories.php');
    }

    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        set_flash('success', 'Kategori berhasil dihapus. Project terkait jadi tanpa kategori.');
        redirect('categories.php');
    }
}

$categories = $pdo->query(
    'SELECT c.*, COUNT(p.id) AS total_projects
     FROM categories c
     LEFT JOIN projects p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name ASC'
)->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>

<section class="grid" style="grid-template-columns: 380px 1fr;">
  <div class="card card-pad">
    <h2>Tambah Kategori</h2>
    <form method="post" class="grid">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">

      <div class="form-group">
        <label>Nama Kategori</label>
        <input class="input" name="name" required placeholder="Contoh: Dashboard">
      </div>

      <div class="form-group">
        <label>Warna Badge</label>
        <input class="input" type="color" name="color" value="#60a5fa">
      </div>

      <button class="btn" type="submit">Tambah</button>
    </form>
  </div>

  <div class="card card-pad">
    <h2>Daftar Kategori</h2>

    <?php if (!$categories): ?>
      <p class="help">Belum ada kategori.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead>
          <tr>
            <th>Warna</th>
            <th>Nama</th>
            <th>Slug</th>
            <th>Total Project</th>
            <th>Aksi</th>
          </tr>
          </thead>
          <tbody>
          <?php foreach ($categories as $category): ?>
            <tr>
              <td><span class="color-dot" style="background: <?= e($category['color']) ?>"></span></td>
              <td>
                <form method="post" style="display:grid; gap:8px;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="id" value="<?= e((string)$category['id']) ?>">
                  <input class="input" name="name" value="<?= e($category['name']) ?>">
                  <input class="input" type="color" name="color" value="<?= e($category['color']) ?>">
                  <button class="btn btn-secondary" type="submit">Update</button>
                </form>
              </td>
              <td><?= e($category['slug']) ?></td>
              <td><?= e((string)$category['total_projects']) ?></td>
              <td>
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= e((string)$category['id']) ?>">
                  <button class="btn btn-danger" type="submit" data-confirm="Hapus kategori ini? Project tidak ikut terhapus.">Hapus</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
