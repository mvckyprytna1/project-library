<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config/app.php';
require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/helpers.php';
require dirname(__DIR__) . '/includes/auth.php';

logout_user();
redirect('auth/login.php');
