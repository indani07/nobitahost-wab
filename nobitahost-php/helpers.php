<?php
// ─────────────────────────────────────────────
//  NobitaHost — PHP Edition  |  helpers.php
// ─────────────────────────────────────────────

/** Sanitize output for HTML */
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** Generate a URL-safe slug */
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/** Generate a unique slug (checks DB table) */
function uniqueSlug(string $text, string $table, string $col = 'slug', int $excludeId = 0): string {
    $db   = getDB();
    $base = slugify($text);
    $slug = $base;
    $i    = 1;
    while (true) {
        $sql  = "SELECT id FROM {$table} WHERE {$col} = ? AND id != ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$slug, $excludeId]);
        if (!$stmt->fetch()) break;
        $slug = $base . '-' . (++$i);
    }
    return $slug;
}

/** Generate a cryptographically secure token */
function generateToken(int $bytes = 32): string {
    return bin2hex(random_bytes($bytes));
}

/** Basic markdown to HTML (paragraphs + headings + bold + italic + links) */
function markdownToHtml(string $md): string {
    $html = htmlspecialchars($md, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Code blocks
    $html = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $html);
    // Headings
    $html = preg_replace('/^######\s(.+)$/m', '<h6>$1</h6>', $html);
    $html = preg_replace('/^#####\s(.+)$/m',  '<h5>$1</h5>', $html);
    $html = preg_replace('/^####\s(.+)$/m',   '<h4>$1</h4>', $html);
    $html = preg_replace('/^###\s(.+)$/m',    '<h3>$1</h3>', $html);
    $html = preg_replace('/^##\s(.+)$/m',     '<h2>$1</h2>', $html);
    $html = preg_replace('/^#\s(.+)$/m',      '<h1>$1</h1>', $html);
    // Bold / Italic
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.+?)\*/',     '<em>$1</em>',     $html);
    // Links
    $html = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $html);
    // Inline code
    $html = preg_replace('/`(.+?)`/', '<code>$1</code>', $html);
    // Unordered lists
    $html = preg_replace('/^\-\s(.+)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
    // Paragraphs
    $lines  = explode("\n\n", $html);
    $result = [];
    foreach ($lines as $block) {
        $block = trim($block);
        if (!$block) continue;
        if (preg_match('/^<(h[1-6]|ul|ol|pre|blockquote)/', $block)) {
            $result[] = $block;
        } else {
            $result[] = '<p>' . nl2br($block) . '</p>';
        }
    }
    return implode("\n", $result);
}

/** Extract YouTube video ID from various URL formats */
function youtubeId(string $url): string {
    preg_match('/(?:v=|youtu\.be\/|embed\/)([a-zA-Z0-9_-]{11})/', $url, $m);
    return $m[1] ?? '';
}

/** Format a datetime string for display */
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff/60) . 'm ago';
    if ($diff < 86400) return floor($diff/3600) . 'h ago';
    if ($diff < 604800) return floor($diff/86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}

/** Validate email */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/** Validate username (alphanumeric + underscore, 3-30 chars) */
function isValidUsername(string $u): bool {
    return (bool) preg_match('/^[a-zA-Z0-9_]{3,30}$/', $u);
}

/** Get avatar URL with fallback */
function avatarUrl(?string $profilePic, string $username): string {
    if ($profilePic && file_exists(APP_ROOT . '/public/' . $profilePic)) {
        return 'public/' . $profilePic;
    }
    return 'public/img/avatar.svg';
}

/** Redirect helper */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/** Get base URL */
function baseUrl(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $script = rtrim($script, '/');
    return $scheme . '://' . $host . $script;
}
