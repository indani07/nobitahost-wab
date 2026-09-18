<?php
// Links Page
$user   = requireLogin();
$pageId = 'links';
$title  = 'Links';
$db     = getDB();
$links  = $db->query("SELECT * FROM links ORDER BY sort_order, id")->fetchAll();

ob_start();
?>
<div class="links-page">
  <div class="section-header">
    <h2 class="section-title">🔗 Links</h2>
    <span class="count-badge"><?= count($links) ?> links</span>
  </div>
  <?php if (empty($links)): ?>
  <div class="card empty-state">
    <span class="empty-icon">🔗</span>
    <h3>No links yet</h3>
  </div>
  <?php else: ?>
  <div class="links-page-grid">
    <?php foreach ($links as $link): ?>
    <a href="<?= h($link['url']) ?>" target="_blank" rel="noopener" class="link-page-card card">
      <span class="link-icon-lg"><?= h($link['icon']) ?></span>
      <span class="link-page-title"><?= h($link['title']) ?></span>
      <span class="link-url"><?= h(parse_url($link['url'], PHP_URL_HOST) ?: $link['url']) ?></span>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
