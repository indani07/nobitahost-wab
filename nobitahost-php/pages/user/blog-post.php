<?php
// Blog Post Page
$user = requireLogin();
$slug = $_GET['slug'] ?? '';
if (!$slug) redirect('index.php?page=blog');

$db   = getDB();
$stmt = $db->prepare("SELECT bp.*, u.username as author FROM blog_posts bp LEFT JOIN users u ON u.id=bp.author_id WHERE bp.slug=? AND bp.published=1");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    setFlash('error', 'Blog post not found.');
    redirect('index.php?page=blog');
}

$pageId = 'blog';
$title  = $post['title'];

ob_start();
?>
<div class="blog-post-page">
  <article class="card blog-article">
    <?php if ($post['thumbnail']): ?>
    <img src="<?= h($post['thumbnail']) ?>" alt="<?= h($post['title']) ?>" class="blog-post-hero">
    <?php endif; ?>
    <div class="blog-post-body">
      <h1 class="blog-post-title"><?= h($post['title']) ?></h1>
      <div class="blog-post-meta">
        <span>✍️ <?= h($post['author'] ?? 'Admin') ?></span>
        <span>📅 <?= date('F j, Y', strtotime($post['created_at'])) ?></span>
        <?php if ($post['updated_at']): ?>
        <span>✏️ Updated <?= timeAgo($post['updated_at']) ?></span>
        <?php endif; ?>
      </div>
      <div class="blog-post-content">
        <?= markdownToHtml($post['content']) ?>
      </div>
    </div>
  </article>
  <a href="index.php?page=blog" class="btn btn-outline">← Back to Blog</a>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
