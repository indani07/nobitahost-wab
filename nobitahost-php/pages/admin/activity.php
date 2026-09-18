<?php
// Admin Activity Log
$user   = requireAdmin();
$pageId = 'admin-activity';
$title  = 'Activity Log';
$db     = getDB();

$perPage = 50;
$pg      = max(1, (int)($_GET['p'] ?? 1));
$offset  = ($pg - 1) * $perPage;
$total   = $db->query("SELECT COUNT(*) FROM activity_log")->fetchColumn();
$logs    = $db->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}")->fetchAll();
$pages   = (int)ceil($total / $perPage);

ob_start();
?>
<div class="admin-activity">
  <div class="section-header">
    <h2 class="section-title">📋 Activity Log</h2>
    <span class="count-badge"><?= number_format($total) ?> entries</span>
  </div>
  <div class="card table-card">
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>User</th><th>Action</th><th>IP</th><th>Time</th></tr></thead>
        <tbody>
          <?php foreach ($logs as $log): ?>
          <tr>
            <td><strong><?= h($log['username'] ?: '—') ?></strong></td>
            <td><?= h($log['action']) ?></td>
            <td><?= h($log['ip'] ?: '—') ?></td>
            <td><?= date('M j, Y H:i', strtotime($log['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($pages > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $pages; $i++): ?>
      <a href="index.php?page=admin-activity&p=<?= $i ?>" class="page-btn <?= $i===$pg?'active':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
