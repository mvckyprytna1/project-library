<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/auth.php';

require_login();

$pdo = db();
$user = auth_user();
$pageTitle = 'Settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));

        if ($name === '' || $email === '') {
            set_flash('error', 'Nama dan email wajib diisi.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Format email tidak valid.');
        } else {
            $check = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $check->execute([$email, $user['id']]);

            if ($check->fetch()) {
                set_flash('error', 'Email sudah dipakai.');
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
                $stmt->execute([$name, $email, $user['id']]);
                unset($_SESSION['user_cache']);
                set_flash('success', 'Profil berhasil diperbarui.');
            }
        }

        redirect('settings.php');
    }

    if ($action === 'password') {
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $passwordHash = (string)$stmt->fetchColumn();

        if (!password_verify($currentPassword, $passwordHash)) {
            set_flash('error', 'Password lama salah.');
        } elseif (strlen($newPassword) < 8) {
            set_flash('error', 'Password baru minimal 8 karakter.');
        } elseif ($newPassword !== $confirmPassword) {
            set_flash('error', 'Konfirmasi password baru tidak sama.');
        } else {
            $update = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $update->execute([password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);
            set_flash('success', 'Password berhasil diganti.');
        }

        redirect('settings.php');
    }
}

require __DIR__ . '/includes/layout_top.php';
?>

<section class="grid" style="grid-template-columns: 1fr 1fr;">
  <div class="card card-pad">
    <h2>Profil Owner</h2>

    <form method="post" class="grid">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="profile">

      <div class="form-group">
        <label>Nama</label>
        <input class="input" name="name" required value="<?= e($user['name']) ?>">
      </div>

      <div class="form-group">
        <label>Email</label>
        <input class="input" type="email" name="email" required value="<?= e($user['email']) ?>">
      </div>

      <button class="btn" type="submit">Simpan Profil</button>
    </form>
  </div>

  <div class="card card-pad">
    <h2>Ganti Password</h2>

    <form method="post" class="grid">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password">

      <div class="form-group">
        <label>Password Lama</label>
        <input class="input" type="password" name="current_password" required>
      </div>

      <div class="form-group">
        <label>Password Baru</label>
        <input class="input" type="password" name="new_password" minlength="8" required>
      </div>

      <div class="form-group">
        <label>Konfirmasi Password Baru</label>
        <input class="input" type="password" name="confirm_password" minlength="8" required>
      </div>

      <button class="btn" type="submit">Ganti Password</button>
    </form>
  </div>
</section>

<section class="card card-pad" style="margin-top:18px;">
  <h2>Konfigurasi Deployment</h2>
  <div class="info-list">
    <div><span>APP_BASE_URL</span><strong><?= e(APP_BASE_URL === '' ? '(root domain)' : APP_BASE_URL) ?></strong></div>
    <div><span>Upload Path</span><strong><?= e(UPLOAD_PATH) ?></strong></div>
    <div><span>Max Upload</span><strong>2MB</strong></div>
  </div>
</section>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
