<?php
// ─────────────────────────────────────────────
//  Sidebar  |  views/partials/sidebar.php
// ─────────────────────────────────────────────
$s         = $s ?? getSettings();
$user      = $user ?? currentUser();
$panelName = h($s['panel_name'] ?? 'NobitaHost');
$logoType  = $s['logo_type'] ?? 'emoji';
$logoEmoji = h($s['logo_emoji'] ?? '🔷');
$logoUrl   = h($s['logo_url'] ?? '');
$isAdmin   = isAdmin($user);
$pageId    = $pageId ?? '';

$navItems = [
    ['icon'=>'🏠','label'=>'Home',      'page'=>'home'],
    ['icon'=>'👥','label'=>'Team',       'page'=>'team'],
    ['icon'=>'📚','label'=>'Tutorials',  'page'=>'tutorials'],
    ['icon'=>'🔗','label'=>'Links',      'page'=>'links'],
    ['icon'=>'🚀','label'=>'Projects',   'page'=>'projects'],
    ['icon'=>'📝','label'=>'Blog',       'page'=>'blog'],
    ['icon'=>'📊','label'=>'Activity',   'page'=>'activity'],
    ['icon'=>'ℹ️', 'label'=>'About',     'page'=>'about'],
    ['icon'=>'📜','label'=>'Terms',      'page'=>'terms'],
];
$adminItems = [
    ['icon'=>'⚡','label'=>'Dashboard',  'page'=>'admin'],
    ['icon'=>'👤','label'=>'Users',      'page'=>'admin-users'],
    ['icon'=>'⚙️', 'label'=>'Settings',  'page'=>'admin-settings'],
    ['icon'=>'📄','label'=>'Content',    'page'=>'admin-content'],
    ['icon'=>'🎥','label'=>'Tutorials',  'page'=>'admin-tutorials'],
    ['icon'=>'✏️', 'label'=>'Blog',      'page'=>'admin-blog'],
    ['icon'=>'🔗','label'=>'Links',      'page'=>'admin-links'],
    ['icon'=>'🚀','label'=>'Projects',   'page'=>'admin-projects'],
    ['icon'=>'📋','label'=>'Activity',   'page'=>'admin-activity'],
];
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <a href="index.php?page=home" class="sidebar-brand">
      <?php if ($logoType === 'image' && $logoUrl): ?>
        <img src="<?= $logoUrl ?>" alt="logo" class="brand-logo-img">
      <?php else: ?>
        <span class="brand-logo-emoji"><?= $logoEmoji ?></span>
      <?php endif; ?>
      <span class="brand-name"><?= $panelName ?></span>
    </a>
    <button class="sidebar-toggle" id="sidebarClose" title="Close sidebar">✕</button>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section">
      <span class="nav-section-label">Navigation</span>
      <?php foreach ($navItems as $item): ?>
      <a href="index.php?page=<?= $item['page'] ?>"
         class="nav-item <?= ($pageId === $item['page']) ? 'active' : '' ?>">
        <span class="nav-icon"><?= $item['icon'] ?></span>
        <span class="nav-label"><?= $item['label'] ?></span>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if ($isAdmin): ?>
    <div class="nav-section nav-admin">
      <span class="nav-section-label">Admin</span>
      <?php foreach ($adminItems as $item): ?>
      <a href="index.php?page=<?= $item['page'] ?>"
         class="nav-item <?= ($pageId === $item['page']) ? 'active' : '' ?>">
        <span class="nav-icon"><?= $item['icon'] ?></span>
        <span class="nav-label"><?= $item['label'] ?></span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <a href="index.php?page=profile" class="sidebar-user">
      <img src="<?= h(avatarUrl($user['profile_pic'] ?? '', $user['username'])) ?>"
           alt="avatar" class="sidebar-avatar" onerror="this.src='public/img/avatar.svg'">
      <div class="sidebar-user-info">
        <span class="sidebar-username"><?= h($user['username']) ?></span>
        <span class="sidebar-role <?= h($user['role']) ?>"><?= ucfirst(h($user['role'])) ?></span>
      </div>
    </a>
    <a href="index.php?page=logout" class="sidebar-logout" title="Logout">⏻</a>
  </div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
