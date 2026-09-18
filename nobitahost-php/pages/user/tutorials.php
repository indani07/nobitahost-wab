<?php
// Tutorials Page
$user      = requireLogin();
$pageId    = 'tutorials';
$title     = 'Tutorials';
$db        = getDB();
$tutorials = $db->query("SELECT t.*, u.username as author FROM tutorials t LEFT JOIN users u ON u.id=t.author_id ORDER BY t.created_at DESC")->fetchAll();

ob_start();
?>
<div class="tutorials-page">
  <div class="section-header">
    <h2 class="section-title">📚 Tutorials</h2>
    <span class="count-badge"><?= count($tutorials) ?> videos</span>
  </div>

  <?php if (empty($tutorials)): ?>
  <div class="card empty-state">
    <span class="empty-icon">📚</span>
    <h3>No tutorials yet</h3>
    <p>Check back soon for video guides!</p>
  </div>
  <?php else: ?>
  <div class="tutorials-grid">
    <?php foreach ($tutorials as $tut): ?>
    <?php $vid = youtubeId($tut['video_url']); ?>
    <div class="tutorial-card card">
      <?php if ($vid): ?>
      <div class="tutorial-thumb" onclick="openYT('<?= h($tut['video_url']) ?>')">
        <img src="https://img.youtube.com/vi/<?= h($vid) ?>/mqdefault.jpg"
             alt="<?= h($tut['title']) ?>" loading="lazy">
        <div class="play-overlay">▶</div>
      </div>
      <?php else: ?>
      <div class="tutorial-thumb-placeholder">🎬</div>
      <?php endif; ?>
      <div class="tutorial-info">
        <h4><?= h($tut['title']) ?></h4>
        <p><?= h($tut['description']) ?></p>
        <div class="tutorial-meta">
          <span>👤 <?= h($tut['author'] ?? 'Admin') ?></span>
          <span>📅 <?= date('M j, Y', strtotime($tut['created_at'])) ?></span>
        </div>
        <?php if ($tut['video_url']): ?>
        <a href="<?= h($tut['video_url']) ?>" target="_blank" class="btn btn-sm btn-primary">▶ Watch on YouTube</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<script>
function openYT(url) { window.open(url, '_blank'); }
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
