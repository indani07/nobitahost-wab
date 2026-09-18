<?php
// Blog List Page
$user   = requireLogin();
$pageId = 'blog';
$title  = 'Blog';
$db     = getDB();
$posts  = $db->query("SELECT bp.*, u.username as author FROM blog_posts bp LEFT JOIN users u ON u.id=bp.author_id WHERE bp.published=1 ORDER BY bp.created_at DESC")->fetchAll();

ob_start();
?>
<div class="blog-page">
  <div class="section-header">
    <h2 class="section-title">📝 Blog</h2>
    <span class="count-badge"><?= count($posts) ?> posts</span>
  </div>
  <?php if (empty($posts)): ?>
  <div class="card empty-state">
    <span class="empty-icon">📝</span>
    <h3>No blog posts yet</h3>
  </div>
  <?php else: ?>
  <div class="blog-list">
    <?php foreach ($posts as $post): ?>
    <a href="index.php?page=blog-post&slug=<?= h($post['slug']) ?>" class="blog-list-card card">
      <?php if ($post['thumbnail']): ?>
      <img src="<?= h($post['thumbnail']) ?>" alt="<?= h($post['title']) ?>" class="blog-list-thumb" loading="lazy">
      <?php endif; ?>
      <div class="blog-list-info">
        <h3><?= h($post['title']) ?></h3>
        <p><?= h(mb_strimwidth($post['excerpt'] ?: strip_tags($post['content']), 0, 150, '...')) ?></p>
        <span class="blog-meta">✍️ <?= h($post['author'] ?? 'Admin') ?> · <?= timeAgo($post['created_at']) ?></span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
