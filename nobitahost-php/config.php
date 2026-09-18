<?php
// ─────────────────────────────────────────────
//  NobitaHost — PHP Edition  |  config.php
// ─────────────────────────────────────────────

define('APP_ROOT',   __DIR__);
define('DATA_DIR',   APP_ROOT . '/data');
define('UPLOAD_DIR', APP_ROOT . '/public/uploads');
define('DB_PATH',    DATA_DIR . '/nobitahost.db');
define('APP_VERSION', '1.0.0');

// Create required directories
foreach ([DATA_DIR, UPLOAD_DIR] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// Timezone
date_default_timezone_set('UTC');

// Error display (turn off in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);
