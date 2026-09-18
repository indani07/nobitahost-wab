<?php
// Admin Blog Management
$user   = requireAdmin();
$pageId = 'admin-blog';
$title  = 'Manage Blog';
$db     = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bid    = (int)($_POST['bid'] ?? 0);

    if ($action === 'create') {
        $t   = trim($_POST['title'] ?? '');
        $cnt = $_POST['content'] ?? '';
        $exc = trim($_POST['excerpt'] ?? '');
        $pub = isset($_POST['published']) ? 1 : 0;
        if ($t) {
            $slug = uniqueSlug($t, 'blog_posts');
            $db->prepare("INSERT INTO blog_posts (slug,title,content,excerpt,author_id,published) VALUES (?,?,?,?,?,?)")
               ->execute([$slug, $t, $cnt, $exc, $user['id'], $pub]);
            setFlash('success', 'Blog post created!');
        }
    } elseif ($action === 'edit' && $bid) {
        $pub = isset($_POST['published']) ? 1 : 0;
        $db->prepare("UPDATE blog_posts SET title=?,content=?,excerpt=?,published=?,updated_at=datetime('now') WHERE id=?")
           ->execute([trim($_POST['title']), $_POST['content'], trim($_POST['excerpt']), $pub, $bid]);
        setFlash('success', 'Post updated!');
    } elseif ($action === 'delete' && $bid) {
        $db->prepare("DELETE FROM blog_posts WHERE id=?")->execute([$bid]);
        setFlash('success', 'Post deleted.');
    }
    redirect('index.php?page=admin-blog');
}

$posts  = $db->query("SELECT * FROM blog_posts ORDER BY created_at DESC")->fetchAll();
$editPost = null;
$editId = (int)($_GET['edit'] ?? 0);
if ($editId) {
    $es = $db->prepare("SELECT * FROM blog_posts WHERE id=?");
    $es->execute([$editId]);
    $editPost = $es->fetch();
}

ob_start();
?>
<div class="admin-blog">
  <div class="section-header">
    <h2 class="section-title">📝 Manage Blog</h2>
    <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('open')">+ New Post</button>
  </div>

  <div class="card table-card">
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Title</th><th>Status</th><th>Author</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($posts as $post): ?>
          <tr>
            <td><strong><?= h($post['title']) ?></strong></td>
            <td><span class="badge badge-<?= $post['published']?'success':'warning' ?>"><?= $post['published']?'Published':'Draft' ?></span></td>
            <td><?= h($post['author_id']) ?></td>
            <td><?= timeAgo($post['created_at']) ?></td>
            <td class="actions-cell">
              <a href="index.php?page=admin-blog&edit=<?= $post['id'] ?>" class="btn btn-xs btn-outline">Edit</a>
              <a href="index.php?page=blog-post&slug=<?= h($post['slug']) ?>" target="_blank" class="btn btn-xs btn-ghost">View</a>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete post?')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="bid" value="<?= $post['id'] ?>">
                <button class="btn btn-xs btn-danger">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create Modal -->
<div class="modal" id="createModal">
  <div class="modal-backdrop" onclick="this.parentElement.classList.remove('open')"></div>
  <div class="modal-box modal-lg card">
    <div class="modal-header"><h3>New Blog Post</h3><button onclick="this.closest('.modal').classList.remove('open')">✕</button></div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="create">
      <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
      <div class="form-group"><label>Excerpt</label><input type="text" name="excerpt" placeholder="Short summary..."></div>
      <div class="form-group"><label>Content (Markdown / HTML)</label><textarea name="content" rows="12" class="code-editor"></textarea></div>
      <div class="form-group toggle-group">
        <label class="toggle-label">
          <input type="checkbox" name="published" value="1">
          <span class="toggle-slider"></span> Publish immediately
        </label>
      </div>
      <button type="submit" class="btn btn-primary">Create Post</button>
    </form>
  </div>
</div>

<?php if ($editPost): ?>
<div class="modal open">
  <div class="modal-backdrop" onclick="window.location='index.php?page=admin-blog'"></div>
  <div class="modal-box modal-lg card">
    <div class="modal-header"><h3>Edit Post</h3><a href="index.php?page=admin-blog">✕</a></div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="edit"><input type="hidden" name="bid" value="<?= $editPost['id'] ?>">
      <div class="form-group"><label>Title</label><input type="text" name="title" value="<?= h($editPost['title']) ?>" required></div>
      <div class="form-group"><label>Excerpt</label><input type="text" name="excerpt" value="<?= h($editPost['excerpt']) ?>"></div>
      <div class="form-group"><label>Content</label><textarea name="content" rows="14" class="code-editor"><?= h($editPost['content']) ?></textarea></div>
      <div class="form-group toggle-group">
        <label class="toggle-label">
          <input type="checkbox" name="published" value="1" <?= $editPost['published']?'checked':'' ?>>
          <span class="toggle-slider"></span> Published
        </label>
      </div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
