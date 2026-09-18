<?php
// Activity Page
$user   = requireLogin();
$pageId = 'activity';
$title  = 'Activity';
$db     = getDB();
$stmt   = $db->prepare("SELECT * FROM activity_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 100");
$stmt->execute([$user['id']]);
$logs   = $stmt->fetchAll();

ob_start();
?>
<div class="activity-page">
  <h2 class="section-title">📊 My Activity</h2>
  <?php if (empty($logs)): ?>
  <div class="card empty-state">
    <span class="empty-icon">📊</span>
    <h3>No activity yet</h3>
  </div>
  <?php else: ?>
  <div class="card">
    <div class="activity-list">
      <?php foreach ($logs as $log): ?>
      <div class="activity-item">
        <div class="activity-dot"></div>
        <div class="activity-info">
          <span class="activity-action"><?= h($log['action']) ?></span>
          <span class="activity-meta">
            📅 <?= date('M j, Y H:i', strtotime($log['created_at'])) ?>
            <?php if ($log['ip']): ?> · 🌐 <?= h($log['ip']) ?><?php endif; ?>
          </span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
