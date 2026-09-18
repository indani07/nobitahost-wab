<?php
// ─────────────────────────────────────────────
//  Home  |  pages/user/home.php
// ─────────────────────────────────────────────
$user   = requireLogin();
$pageId = 'home';
$title  = 'Home';
$db     = getDB();

// Fetch data for the home dashboard
$links     = $db->query("SELECT * FROM links ORDER BY sort_order, id LIMIT 12")->fetchAll();
$projects  = $db->query("SELECT * FROM projects ORDER BY sort_order, id LIMIT 6")->fetchAll();
$tutorials = $db->query("SELECT * FROM tutorials ORDER BY created_at DESC LIMIT 6")->fetchAll();
$blogs     = $db->query("SELECT bp.*, u.username as author FROM blog_posts bp LEFT JOIN users u ON u.id=bp.author_id WHERE bp.published=1 ORDER BY bp.created_at DESC LIMIT 4")->fetchAll();

// Script settings
$scriptEnabled = ($s['script_enabled'] ?? 'on') === 'on';
$scriptBadge   = $s['script_badge']   ?? 'Install Script';
$scriptDesc    = $s['script_description'] ?? '';
$scriptCmd     = $s['script_command'] ?? '';

// Discord/YouTube widgets
$discordEnabled  = ($s['discord_enabled'] ?? 'on') === 'on' && !empty($s['discord_server_id']);
$youtubeEnabled  = ($s['youtube_enabled'] ?? 'on') === 'on' && !empty($s['youtube_channel']);
$discordServerId = h($s['discord_server_id'] ?? '');
$discordTheme    = h($s['discord_theme'] ?? 'dark');
$youtubeChannel  = h($s['youtube_channel'] ?? '');

ob_start();
?>
<div class="page-home">
  <!-- Welcome Banner -->
  <div class="welcome-banner card">
    <div class="welcome-text">
      <h2>Welcome back, <span class="accent"><?= h($user['username']) ?></span> 👋</h2>
      <p><?= h($s['panel_tagline'] ?? 'Your hosting control center') ?></p>
    </div>
    <img src="<?= h(avatarUrl($user['profile_pic'] ?? '', $user['username'])) ?>"
         alt="avatar" class="welcome-avatar" onerror="this.src='public/img/avatar.svg'">
  </div>

  <!-- Quick Links -->
  <?php if ($links): ?>
  <section class="section">
    <h3 class="section-title">🔗 Quick Links</h3>
    <div class="links-grid">
      <?php foreach ($links as $link): ?>
      <a href="<?= h($link['url']) ?>" target="_blank" rel="noopener" class="link-card card">
        <span class="link-icon"><?= h($link['icon']) ?></span>
        <span class="link-title"><?= h($link['title']) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Install Script -->
  <?php if ($scriptEnabled && $scriptCmd): ?>
  <section class="section">
    <div class="script-card card">
      <div class="script-header">
        <span class="script-badge"><?= h($scriptBadge) ?></span>
        <p class="script-desc"><?= h($scriptDesc) ?></p>
      </div>
      <div class="script-body">
        <code id="scriptCmd" class="script-code"><?= h($scriptCmd) ?></code>
        <button class="btn btn-sm btn-outline" onclick="copyScript()">📋 Copy</button>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <div class="home-cols">
    <!-- Projects -->
    <?php if ($projects): ?>
    <section class="section col-2">
      <div class="section-header">
        <h3 class="section-title">🚀 Projects</h3>
        <a href="index.php?page=projects" class="btn btn-sm btn-ghost">View All →</a>
      </div>
      <div class="projects-grid">
        <?php foreach ($projects as $proj): ?>
        <div class="project-card card">
          <span class="project-icon"><?= h($proj['icon']) ?></span>
          <h4 class="project-name"><?= h($proj['name']) ?></h4>
          <p class="project-desc"><?= h($proj['description']) ?></p>
          <?php if ($proj['url']): ?>
          <a href="<?= h($proj['url']) ?>" target="_blank" class="btn btn-sm btn-primary"><?= h($proj['button']) ?></a>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- Tutorials -->
    <?php if ($tutorials): ?>
    <section class="section col-2">
      <div class="section-header">
        <h3 class="section-title">📚 Tutorials</h3>
        <a href="index.php?page=tutorials" class="btn btn-sm btn-ghost">View All →</a>
      </div>
      <div class="tutorials-list">
        <?php foreach ($tutorials as $tut): ?>
        <div class="tutorial-item card">
          <div class="tut-thumb">
            <?php $vid = youtubeId($tut['video_url']); ?>
            <?php if ($vid): ?>
            <img src="https://img.youtube.com/vi/<?= h($vid) ?>/mqdefault.jpg" alt="<?= h($tut['title']) ?>" loading="lazy">
            <?php else: ?>
            <div class="tut-thumb-placeholder">🎬</div>
            <?php endif; ?>
          </div>
          <div class="tut-info">
            <h5><?= h($tut['title']) ?></h5>
            <p><?= h(mb_strimwidth($tut['description'], 0, 80, '...')) ?></p>
            <?php if ($tut['video_url']): ?>
            <a href="<?= h($tut['video_url']) ?>" target="_blank" class="btn btn-xs btn-primary">▶ Watch</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </div>

  <!-- Discord Widget -->
  <?php if ($discordEnabled): ?>
  <section class="section">
    <h3 class="section-title">💬 Discord Server</h3>
    <iframe src="https://discord.com/widget?id=<?= $discordServerId ?>&theme=<?= $discordTheme ?>"
            width="350" height="500" allowtransparency="true" frameborder="0"
            sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"
            class="discord-widget"></iframe>
  </section>
  <?php endif; ?>

  <!-- Blog Posts -->
  <?php if ($blogs): ?>
  <section class="section">
    <div class="section-header">
      <h3 class="section-title">📝 Latest Blog Posts</h3>
      <a href="index.php?page=blog" class="btn btn-sm btn-ghost">View All →</a>
    </div>
    <div class="blog-grid">
      <?php foreach ($blogs as $post): ?>
      <a href="index.php?page=blog-post&slug=<?= h($post['slug']) ?>" class="blog-card card">
        <?php if ($post['thumbnail']): ?>
        <img src="<?= h($post['thumbnail']) ?>" alt="<?= h($post['title']) ?>" class="blog-thumb" loading="lazy">
        <?php endif; ?>
        <div class="blog-info">
          <h4><?= h($post['title']) ?></h4>
          <p><?= h(mb_strimwidth($post['excerpt'], 0, 100, '...')) ?></p>
          <span class="blog-meta"><?= h($post['author'] ?? 'Admin') ?> · <?= timeAgo($post['created_at']) ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</div>

<script>
function copyScript() {
  const code = document.getElementById('scriptCmd').textContent;
  navigator.clipboard.writeText(code).then(() => showToast('Copied!', 'success'));
}
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
