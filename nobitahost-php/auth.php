<?php
// ─────────────────────────────────────────────
//  NobitaHost — PHP Edition  |  auth.php
// ─────────────────────────────────────────────

require_once __DIR__ . '/db.php';

/** Return current logged-in user array or null */
function currentUser(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if (!$u || $u['status'] !== 'active') {
        session_destroy();
        return null;
    }
    return $u;
}

/** Require any logged-in user, redirect to login otherwise */
function requireLogin(): array {
    $user = currentUser();
    if (!$user) {
        $next = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header("Location: index.php?page=login&next={$next}");
        exit;
    }
    return $user;
}

/** Require admin role */
function requireAdmin(): array {
    $user = requireLogin();
    if (!in_array($user['role'], ['admin'])) {
        header('Location: index.php?page=home');
        exit;
    }
    return $user;
}

/** Require admin OR owner (team lead) */
function requireAdminOrOwner(): array {
    $user = requireLogin();
    if (!in_array($user['role'], ['admin', 'owner'])) {
        header('Location: index.php?page=home');
        exit;
    }
    return $user;
}

/** Log the user in (sets session) */
function loginUser(array $user, string $ip = ''): void {
    $_SESSION['user_id'] = $user['id'];
    // Update last_login and last_ip
    $db = getDB();
    $db->prepare("UPDATE users SET last_login = datetime('now'), last_ip = ? WHERE id = ?")
       ->execute([$ip, $user['id']]);
}

/** Log the user out */
function logoutUser(): void {
    session_destroy();
}

/** Log an activity action */
function logActivity(int $userId, string $username, string $action, string $ip = ''): void {
    $db = getDB();
    $db->prepare("INSERT INTO activity_log (user_id,username,action,ip) VALUES (?,?,?,?)")
       ->execute([$userId, $username, $action, $ip]);
}

/** Get client IP */
function clientIp(): string {
    $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($forwarded) {
        return trim(explode(',', $forwarded)[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

/** Check if user is admin */
function isAdmin(?array $user): bool {
    return $user && $user['role'] === 'admin';
}

/** Flash message helpers */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
