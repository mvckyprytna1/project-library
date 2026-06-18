<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
| Ganti sesuai database cPanel kamu.
| Jangan taruh password database di repo publik.
*/
define('DB_HOST', 'localhost');
define('DB_NAME', 'vicj7142_project_library');
define('DB_USER', 'vicj7142_user_project_library');
define('DB_PASS', 'supriyanto');
define('DB_CHARSET', 'utf8mb4');

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        exit('Database connection failed. Cek config/database.php dan pastikan database sudah dibuat.');
    }

    return $pdo;
}
