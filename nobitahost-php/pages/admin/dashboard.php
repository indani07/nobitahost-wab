<?php
// Admin Dashboard
$user   = requireAdmin();
$pageId = 'admin';
$title  = 'Admin Dashboard';
$db     = getDB();

$totalUsers    = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalTutorials= $db->query("SELECT COUNT(*) FROM tutorials")->fetchColumn();
$totalLinks    = $db->query("SELECT COUNT(*) FROM links")->fetchColumn();
$totalProjects = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$totalBlog     = $db->query("SELECT COUNT(*) FROM blog_posts WHERE published=1")->fetchColumn();
$recentUsers   = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentActivity= $db->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 10")->fetchAll();

ob_start();
?>
<div class="admin-dashboard">
  <h2 class="section-title">⚡ Admin Dashboard</h2>

  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="stat-card card">
      <span class="stat-icon">👤</span>
      <div class="stat-info">
        <span class="stat-num"><?= $totalUsers ?></span>
        <span class="stat-label">Total Users</span>
      </div>
    </div>
    <div class="stat-card card">
      <span class="stat-icon">📚</span>
      <div class="stat-info">
        <span class="stat-num"><?= $totalTutorials ?></span>
        <span class="stat-label">Tutorials</span>
      </div>
    </div>
    <div class="stat-card card">
      <span class="stat-icon">🔗</span>
      <div class="stat-info">
        <span class="stat-num"><?= $totalLinks ?></span>
        <span class="stat-label">Links</span>
      </div>
    </div>
    <div class="stat-card card">
      <span class="stat-icon">🚀</span>
      <div class="stat-info">
        <span class="stat-num"><?= $totalProjects ?></span>
        <span class="stat-label">Projects</span>
      </div>
    </div>
    <div class="stat-card card">
      <span class="stat-icon">📝</span>
      <div class="stat-info">
        <span class="stat-num"><?= $totalBlog ?></span>
        <span class="stat-label">Blog Posts</span>
      </div>
    </div>
  </div>

  <!-- Admin Quick Actions -->
  <div class="quick-actions card">
    <h3 class="card-title">Quick Actions</h3>
    <div class="action-grid">
      <a href="index.php?page=admin-settings" class="action-btn card">⚙️ Settings</a>
      <a href="index.php?page=admin-users" class="action-btn card">👤 Users</a>
      <a href="index.php?page=admin-content" class="action-btn card">📄 Content</a>
      <a href="index.php?page=admin-tutorials" class="action-btn card">📚 Tutorials</a>
      <a href="index.php?page=admin-blog" class="action-btn card">📝 Blog</a>
      <a href="index.php?page=admin-links" class="action-btn card">🔗 Links</a>
      <a href="index.php?page=admin-projects" class="action-btn card">🚀 Projects</a>
      <a href="index.php?page=admin-activity" class="action-btn card">📋 Activity</a>
    </div>
  </div>

  <div class="admin-cols">
    <!-- Recent Users -->
    <div class="card">
      <div class="card-header-row">
        <h3 class="card-title">Recent Users</h3>
        <a href="index.php?page=admin-users" class="btn btn-sm btn-ghost">View All →</a>
      </div>
      <div class="user-list">
        <?php foreach ($recentUsers as $u): ?>
        <div class="user-list-item">
          <img src="<?= h(avatarUrl($u['profile_pic'], $u['username'])) ?>"
               alt="avatar" class="user-list-avatar" onerror="this.src='public/img/avatar.svg'">
          <div class="user-list-info">
            <span class="user-list-name"><?= h($u['username']) ?></span>
            <span class="badge badge-<?= h($u['role']) ?>"><?= ucfirst(h($u['role'])) ?></span>
            <span class="badge badge-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>"><?= ucfirst(h($u['status'])) ?></span>
          </div>
          <span class="user-list-date"><?= timeAgo($u['created_at']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
      <div class="card-header-row">
        <h3 class="card-title">Recent Activity</h3>
        <a href="index.php?page=admin-activity" class="btn btn-sm btn-ghost">View All →</a>
      </div>
      <div class="activity-list">
        <?php foreach ($recentActivity as $log): ?>
        <div class="activity-item">
          <div class="activity-dot"></div>
          <div class="activity-info">
            <span class="activity-user"><?= h($log['username']) ?></span>
            <span class="activity-action"><?= h($log['action']) ?></span>
            <span class="activity-time"><?= timeAgo($log['created_at']) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
