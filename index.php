<?php
declare(strict_types=1);

require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

redirect('auth/login.php');
