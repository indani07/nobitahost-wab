<?php
// ─────────────────────────────────────────────
//  NobitaHost — PHP Edition  |  db.php
// ─────────────────────────────────────────────

require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA journal_mode=WAL');
        $pdo->exec('PRAGMA foreign_keys=ON');
        initSchema($pdo);
        seedData($pdo);
    }
    return $pdo;
}

function initSchema(PDO $db): void {
    $db->exec("
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT UNIQUE NOT NULL,
      email TEXT UNIQUE NOT NULL,
      password TEXT NOT NULL,
      role TEXT NOT NULL DEFAULT 'user',
      status TEXT NOT NULL DEFAULT 'active',
      owner_id INTEGER DEFAULT NULL,
      profile_pic TEXT DEFAULT '',
      bio TEXT DEFAULT '',
      two_factor_enabled INTEGER NOT NULL DEFAULT 0,
      two_factor_secret TEXT DEFAULT NULL,
      is_demo INTEGER NOT NULL DEFAULT 0,
      last_login TEXT,
      last_ip TEXT DEFAULT '',
      created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS settings (
      key TEXT PRIMARY KEY,
      value TEXT
    );

    CREATE TABLE IF NOT EXISTS content_pages (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      slug TEXT UNIQUE NOT NULL,
      title TEXT NOT NULL,
      content TEXT DEFAULT '',
      updated_at TEXT,
      updated_by INTEGER
    );

    CREATE TABLE IF NOT EXISTS roles (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT UNIQUE NOT NULL,
      color TEXT NOT NULL DEFAULT '#3b82f6',
      sort_order INTEGER NOT NULL DEFAULT 0
    );

    CREATE TABLE IF NOT EXISTS tutorials (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      title TEXT NOT NULL,
      description TEXT DEFAULT '',
      video_url TEXT DEFAULT '',
      thumbnail TEXT DEFAULT '',
      author_id INTEGER,
      created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS links (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      title TEXT NOT NULL,
      url TEXT DEFAULT '',
      icon TEXT DEFAULT '🔗',
      sort_order INTEGER NOT NULL DEFAULT 0,
      created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS projects (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      description TEXT DEFAULT '',
      url TEXT DEFAULT '',
      button TEXT DEFAULT 'View Project',
      icon TEXT DEFAULT '🚀',
      sort_order INTEGER NOT NULL DEFAULT 0,
      created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS blog_posts (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      slug TEXT UNIQUE NOT NULL,
      title TEXT NOT NULL,
      content TEXT DEFAULT '',
      excerpt TEXT DEFAULT '',
      thumbnail TEXT DEFAULT '',
      author_id INTEGER,
      published INTEGER NOT NULL DEFAULT 0,
      created_at TEXT NOT NULL DEFAULT (datetime('now')),
      updated_at TEXT
    );

    CREATE TABLE IF NOT EXISTS activity_log (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER,
      username TEXT DEFAULT '',
      action TEXT NOT NULL,
      ip TEXT DEFAULT '',
      created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS password_resets (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      email TEXT NOT NULL,
      token TEXT NOT NULL,
      expires_at TEXT NOT NULL,
      used INTEGER NOT NULL DEFAULT 0,
      created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS plans (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      description TEXT DEFAULT '',
      price REAL NOT NULL DEFAULT 0,
      billing_cycle TEXT DEFAULT 'monthly',
      features TEXT DEFAULT '',
      is_active INTEGER NOT NULL DEFAULT 1,
      sort_order INTEGER NOT NULL DEFAULT 0,
      created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );
    ");
}

function seedData(PDO $db): void {
    // Seed admin user
    $adminExists = $db->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetch();
    if (!$adminExists) {
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $db->prepare("INSERT OR IGNORE INTO users (username,email,password,role,status) VALUES (?,?,?,?,?)")
           ->execute(['admin','admin@nobitahost.local',$hash,'admin','active']);
    }

    // Seed default settings
    $defaults = getDefaultSettings();
    $stmt = $db->prepare("INSERT OR IGNORE INTO settings (key,value) VALUES (?,?)");
    foreach ($defaults as $k => $v) {
        $stmt->execute([$k, $v]);
    }

    // Seed default roles
    $rolesExist = $db->query("SELECT id FROM roles LIMIT 1")->fetch();
    if (!$rolesExist) {
        $roles = [
            ['Admin',     '#ef4444', 0],
            ['Moderator', '#f59e0b', 1],
            ['User',      '#3b82f6', 2],
            ['VIP',       '#8b5cf6', 3],
        ];
        $rs = $db->prepare("INSERT OR IGNORE INTO roles (name,color,sort_order) VALUES (?,?,?)");
        foreach ($roles as $r) $rs->execute($r);
    }

    // Seed content pages
    $pages = [
        ['about',  'About Us',        '<h2>About NobitaHost</h2><p>We provide premium hosting solutions.</p>'],
        ['terms',  'Terms of Service', '<h2>Terms of Service</h2><p>By using this service you agree to our terms.</p>'],
        ['footer', 'Footer',           'NobitaHost &copy; 2025 · All rights reserved.'],
        ['navbar', 'Navbar Brand',     'NobitaHost'],
        ['hub',    'Hub Description',  'Your one-stop hosting control panel.'],
    ];
    $cs = $db->prepare("INSERT OR IGNORE INTO content_pages (slug,title,content) VALUES (?,?,?)");
    foreach ($pages as $p) $cs->execute($p);

    // Seed demo tutorials
    $tutExists = $db->query("SELECT id FROM tutorials LIMIT 1")->fetch();
    if (!$tutExists) {
        $admin = $db->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetch();
        $aid = $admin ? $admin['id'] : 1;
        $tuts = [
            ['Getting Started with NobitaHost', 'Learn how to set up your hosting panel from scratch.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '', $aid],
            ['Installing Pterodactyl Panel',    'Step-by-step guide to install Pterodactyl.',           'https://www.youtube.com/watch?v=dQw4w9WgXcQ', '', $aid],
        ];
        $ts = $db->prepare("INSERT INTO tutorials (title,description,video_url,thumbnail,author_id) VALUES (?,?,?,?,?)");
        foreach ($tuts as $t) $ts->execute($t);
    }

    // Seed demo links
    $linkExists = $db->query("SELECT id FROM links LIMIT 1")->fetch();
    if (!$linkExists) {
        $links = [
            ['Discord',  'https://discord.gg', '💬', 0],
            ['GitHub',   'https://github.com',  '🐱', 1],
            ['Telegram', 'https://t.me',        '✈️', 2],
        ];
        $ls = $db->prepare("INSERT INTO links (title,url,icon,sort_order) VALUES (?,?,?,?)");
        foreach ($links as $l) $ls->execute($l);
    }

    // Seed demo projects
    $projExists = $db->query("SELECT id FROM projects LIMIT 1")->fetch();
    if (!$projExists) {
        $projs = [
            ['NobitaHost Panel',  'Full-featured hosting panel.',   'https://github.com', 'View Project', '🖥️', 0],
            ['Bot Manager',       'Discord bot management system.', 'https://github.com', 'View Project', '🤖', 1],
        ];
        $ps = $db->prepare("INSERT INTO projects (name,description,url,button,icon,sort_order) VALUES (?,?,?,?,?,?)");
        foreach ($projs as $p) $ps->execute($p);
    }
}

function getDefaultSettings(): array {
    return [
        'panel_name'          => 'NobitaHost',
        'panel_tagline'       => 'Full-Featured Hosting Panel',
        'logo_type'           => 'emoji',
        'logo_url'            => '',
        'logo_emoji'          => '🔷',
        'favicon_url'         => '',
        'background_type'     => 'image',
        'background_url'      => '',
        'background_source'   => 'none',
        'background_overlay'  => 'on',
        'panel_blur'          => '16',
        'transparency'        => '100',
        'theme'               => 'dark',
        'wallpaper_favs'      => '',
        'music_type'          => 'none',
        'music_url'           => '',
        'music_volume'        => '40',
        'transparent_bar'     => 'on',
        'blur_bar'            => 'on',
        'card_radius'         => '16',
        'accent_color'        => '#0044ff',
        'register_open'       => 'on',
        'maintenance'         => 'off',
        'script_enabled'      => 'on',
        'script_badge'        => 'All In One CMD',
        'script_description'  => 'Execute the master script to install all dependencies instantly.',
        'script_command'      => 'bash <(curl -s https://example.com/install.sh)',
        'discord_enabled'     => 'on',
        'discord_server_id'   => '',
        'discord_channel'     => '',
        'discord_theme'       => 'dark',
        'youtube_enabled'     => 'on',
        'youtube_channel'     => '',
        'youtube_api_key'     => '',
        'instagram_handle'    => '',
        'github_username'     => '',
        'github_token'        => '',
        'smtp_host'           => '',
        'smtp_port'           => '587',
        'smtp_secure'         => 'false',
        'smtp_user'           => '',
        'smtp_pass'           => '',
        'mail_from'           => 'NobitaHost <noreply@nobitahost.local>',
        'mail_enabled'        => 'off',
        'plans_enabled'       => 'off',
    ];
}
