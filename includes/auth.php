<?php
declare(strict_types=1);

function auth_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;

    if ($user !== null) {
        return $user;
    }

    $stmt = db()->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;

    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }

    return $user;
}

function is_logged_in(): bool
{
    return auth_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('auth/login.php');
    }
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool)$params['secure'],
            (bool)$params['httponly']
        );
    }

    session_destroy();
}
