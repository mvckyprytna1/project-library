<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| App Configuration
|--------------------------------------------------------------------------
| APP_BASE_URL:
| - Pakai '' kalau app ada di root domain/subdomain.
| - Pakai '/nama-folder' kalau app ada di subfolder.
*/
define('APP_NAME', 'Vicky Project Library');
define('APP_BASE_URL', 'http://project.vickydisini.my.id/');
define('APP_TIMEZONE', 'Asia/Jakarta');

define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/projects/');
define('UPLOAD_URL', 'uploads/projects/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB

define('ALLOWED_IMAGE_MIME', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
]);

date_default_timezone_set(APP_TIMEZONE);

if (session_status() === PHP_SESSION_NONE) {
    session_name('vicky_project_library_session');
    session_start();
}
