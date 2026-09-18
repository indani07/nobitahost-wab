<?php
// Projects Page
$user     = requireLogin();
$pageId   = 'projects';
$title    = 'Projects';
$db       = getDB();
$projects = $db->query("SELECT * FROM projects ORDER BY sort_order, id")->fetchAll();

ob_start();
?>
<div class="projects-page">
  <div class="section-header">
    <h2 class="section-title">🚀 Projects</h2>
    <span class="count-badge"><?= count($projects) ?> projects</span>
  </div>
  <?php if (empty($projects)): ?>
  <div class="card empty-state">
    <span class="empty-icon">🚀</span>
    <h3>No projects yet</h3>
  </div>
  <?php else: ?>
  <div class="projects-page-grid">
    <?php foreach ($projects as $proj): ?>
    <div class="project-page-card card">
      <div class="project-icon-lg"><?= h($proj['icon']) ?></div>
      <h3 class="project-name"><?= h($proj['name']) ?></h3>
      <p class="project-desc"><?= h($proj['description']) ?></p>
      <?php if ($proj['url']): ?>
      <a href="<?= h($proj['url']) ?>" target="_blank" rel="noopener" class="btn btn-primary"><?= h($proj['button']) ?></a>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
