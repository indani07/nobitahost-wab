<?php
// ─────────────────────────────────────────────
//  NobitaHost — PHP Edition  |  index.php
//  Front Controller / Router
// ─────────────────────────────────────────────

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/mailer.php';

// Initialize DB (creates schema + seeds on first run)
getDB();

$s    = getSettings();
$user = currentUser();

// Maintenance mode
$page = $_GET['page'] ?? 'home';
if ($s['maintenance'] === 'on' && !isAdmin($user) && !in_array($page, ['login','logout','register','forgot','reset','verify-2fa'])) {
    include __DIR__ . '/views/maintenance.php';
    exit;
}

// Route map: page slug => [file_path, auth_required, admin_required]
$routes = [
    // Auth
    'login'        => ['pages/auth/login.php',     false, false],
    'register'     => ['pages/auth/register.php',  false, false],
    'logout'       => ['pages/auth/logout.php',    false, false],
    'forgot'       => ['pages/auth/forgot.php',    false, false],
    'reset'        => ['pages/auth/reset.php',     false, false],
    'verify-2fa'   => ['pages/auth/verify-2fa.php',false, false],

    // User pages
    'home'         => ['pages/user/home.php',         true, false],
    'profile'      => ['pages/user/profile.php',      true, false],
    'team'         => ['pages/user/team.php',          true, false],
    'about'        => ['pages/user/about.php',         true, false],
    'tutorials'    => ['pages/user/tutorials.php',     true, false],
    'projects'     => ['pages/user/projects.php',      true, false],
    'links'        => ['pages/user/links.php',         true, false],
    'activity'     => ['pages/user/activity.php',      true, false],
    'blog'         => ['pages/user/blog.php',          true, false],
    'blog-post'    => ['pages/user/blog-post.php',     true, false],
    'terms'        => ['pages/user/terms.php',         true, false],

    // Admin pages
    'admin'        => ['pages/admin/dashboard.php',   true, true],
    'admin-users'  => ['pages/admin/users.php',       true, true],
    'admin-settings' => ['pages/admin/settings.php', true, true],
    'admin-content'  => ['pages/admin/content.php',  true, true],
    'admin-tutorials'=> ['pages/admin/tutorials.php', true, true],
    'admin-blog'     => ['pages/admin/blog.php',      true, true],
    'admin-activity' => ['pages/admin/activity.php',  true, true],
    'admin-roles'    => ['pages/admin/roles.php',     true, true],
    'admin-links'    => ['pages/admin/links.php',     true, true],
    'admin-projects' => ['pages/admin/projects.php',  true, true],
];

// Default page: redirect logged-in users to home, others to login
if (!array_key_exists($page, $routes)) {
    if ($user) {
        redirect('index.php?page=home');
    } else {
        redirect('index.php?page=login');
    }
}

[$file, $needsAuth, $needsAdmin] = $routes[$page];

// Auth guards
if ($needsAuth && !$user) {
    $next = urlencode($_SERVER['REQUEST_URI'] ?? '');
    redirect("index.php?page=login&next={$next}");
}
if ($needsAdmin && $user && !isAdmin($user)) {
    redirect('index.php?page=home');
}
// Redirect logged-in users away from auth pages
if (!$needsAuth && $user && in_array($page, ['login','register'])) {
    redirect('index.php?page=home');
}

$filePath = __DIR__ . '/' . $file;
if (!file_exists($filePath)) {
    http_response_code(404);
    echo "<h1>Page not found</h1><p>File: {$filePath}</p>";
    exit;
}

include $filePath;
