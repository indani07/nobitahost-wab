<?php
// Team Page
$user   = requireLogin();
$pageId = 'team';
$title  = 'Team';
$db     = getDB();

// Sub-users (users whose owner_id = current user, or if admin — all sub-users)
if (isAdmin($user)) {
    $members = $db->query("SELECT u.*, owner.username as owner_name FROM users u LEFT JOIN users owner ON owner.id=u.owner_id WHERE u.id != {$user['id']} ORDER BY u.created_at DESC")->fetchAll();
} else {
    $stmt = $db->prepare("SELECT * FROM users WHERE owner_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $members = $stmt->fetchAll();
}

ob_start();
?>
<div class="team-page">
  <div class="section-header">
    <h2 class="section-title">👥 Team Members</h2>
    <?php if ($user['role'] === 'admin'): ?>
    <a href="index.php?page=admin-users" class="btn btn-primary">+ Add User</a>
    <?php endif; ?>
  </div>

  <?php if (empty($members)): ?>
  <div class="card empty-state">
    <span class="empty-icon">👥</span>
    <h3>No team members yet</h3>
    <p>Team members will appear here.</p>
  </div>
  <?php else: ?>
  <div class="team-grid">
    <?php foreach ($members as $member): ?>
    <div class="team-card card">
      <img src="<?= h(avatarUrl($member['profile_pic'], $member['username'])) ?>"
           alt="avatar" class="team-avatar" onerror="this.src='public/img/avatar.svg'">
      <div class="team-info">
        <h4 class="team-username"><?= h($member['username']) ?></h4>
        <span class="team-role badge badge-<?= h($member['role']) ?>"><?= ucfirst(h($member['role'])) ?></span>
        <span class="team-status badge badge-<?= $member['status'] === 'active' ? 'success' : 'danger' ?>">
          <?= ucfirst(h($member['status'])) ?>
        </span>
        <?php if ($member['bio']): ?>
        <p class="team-bio"><?= h(mb_strimwidth($member['bio'], 0, 80, '...')) ?></p>
        <?php endif; ?>
        <span class="team-joined">📅 <?= date('M Y', strtotime($member['created_at'])) ?></span>
        <?php if ($member['last_login']): ?>
        <span class="team-last">🕐 <?= timeAgo($member['last_login']) ?></span>
        <?php endif; ?>
        <?php if (isAdmin($user) && $member['owner_name']): ?>
        <span class="team-owner">👤 Owner: <?= h($member['owner_name']) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
