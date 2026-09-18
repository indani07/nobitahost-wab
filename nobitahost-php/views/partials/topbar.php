<?php
// ─────────────────────────────────────────────
//  Topbar  |  views/partials/topbar.php
// ─────────────────────────────────────────────
$s       = $s ?? getSettings();
$user    = $user ?? currentUser();
$title   = $title ?? ($s['panel_name'] ?? 'NobitaHost');
$isAdmin = isAdmin($user);
$transparentBar = ($s['transparent_bar'] ?? 'on') === 'on';
$blurBar        = ($s['blur_bar'] ?? 'on') === 'on';
?>
<header class="topbar <?= $transparentBar ? 'topbar-transparent' : '' ?> <?= $blurBar ? 'topbar-blur' : '' ?>">
  <div class="topbar-left">
    <button class="sidebar-open-btn" id="sidebarOpen" title="Open sidebar">☰</button>
    <h1 class="topbar-title"><?= h($title) ?></h1>
  </div>
  <div class="topbar-right">
    <?php if ($isAdmin): ?>
    <a href="index.php?page=admin" class="topbar-btn admin-badge" title="Admin Panel">⚡ Admin</a>
    <?php endif; ?>
    <a href="index.php?page=profile" class="topbar-avatar-link">
      <img src="<?= h(avatarUrl($user['profile_pic'] ?? '', $user['username'])) ?>"
           alt="avatar" class="topbar-avatar" onerror="this.src='public/img/avatar.svg'">
    </a>
  </div>
</header>
