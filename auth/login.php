<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config/app.php';
require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/helpers.php';
require dirname(__DIR__) . '/includes/csrf.php';
require dirname(__DIR__) . '/includes/auth.php';

$pdo = db();

try {
    $ownerCount = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
} catch (Throwable $e) {
    $ownerCount = 0;
}

if ($ownerCount === 0) {
    redirect('install.php');
}

if (is_logged_in()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        login_user($user);
        set_flash('success', 'Login berhasil. Selamat datang di library project kamu.');
        redirect('dashboard.php');
    }

    $error = 'Email atau password salah.';
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="auth-page">
  <div class="auth-wrap">
    <form class="card auth-card" method="post">
      <div class="auth-logo">VP</div>
      <p class="eyebrow">Owner Login</p>
      <h1>Masuk Dashboard</h1>
      <p class="help">Akses project vault pribadi kamu.</p>

      <?php if ($flash = get_flash()): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <?= csrf_field() ?>

      <div class="form-group">
        <label>Email</label>
        <input class="input" type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Password</label>
        <input class="input" type="password" name="password" required>
      </div>

      <div class="actions">
        <button class="btn full" type="submit">Login</button>
      </div>
    </form>
  </div>
</body>
</html>
