<?php
// ─────────────────────────────────────────────
//  NobitaHost — PHP Edition  |  settings.php
// ─────────────────────────────────────────────

require_once __DIR__ . '/db.php';

/** Return all settings as associative array (merged with defaults) */
function getSettings(): array {
    $db      = getDB();
    $rows    = $db->query("SELECT key, value FROM settings")->fetchAll();
    $stored  = [];
    foreach ($rows as $r) $stored[$r['key']] = $r['value'];
    return array_merge(getDefaultSettings(), $stored);
}

/** Save a single setting key-value pair */
function saveSetting(string $key, string $value): void {
    $db = getDB();
    $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)")
       ->execute([$key, $value]);
}

/** Save multiple settings at once */
function saveSettings(array $data): void {
    foreach ($data as $k => $v) {
        saveSetting($k, (string)$v);
    }
}

/** Resolve background CSS values from settings */
function resolveBackground(array $s): array {
    $type = $s['background_type'] ?? 'image';
    $url  = $s['background_url']  ?? '';
    return [
        'type'    => $type,
        'url'     => $url,
        'overlay' => ($s['background_overlay'] ?? 'on') === 'on',
    ];
}

/** Get content page by slug */
function getContentPage(string $slug): ?array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM content_pages WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

/** Save content page */
function saveContentPage(string $slug, string $title, string $content, int $userId): void {
    $db = getDB();
    $db->prepare("INSERT OR REPLACE INTO content_pages (slug, title, content, updated_at, updated_by)
                  VALUES (?, ?, ?, datetime('now'), ?)")
       ->execute([$slug, $title, $content, $userId]);
}

/** Handle file upload for a setting (logo, favicon, background) */
function handleSettingUpload(string $fileKey, string $destName): ?string {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file    = $_FILES[$fileKey];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','svg','webp','mp4','webm'];
    if (!in_array($ext, $allowed)) return null;
    $filename = $destName . '.' . $ext;
    $dest     = UPLOAD_DIR . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'uploads/' . $filename;
    }
    return null;
}
