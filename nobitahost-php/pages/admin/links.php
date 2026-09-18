<?php
// Admin Links Management
$user   = requireAdmin();
$pageId = 'admin-links';
$title  = 'Manage Links';
$db     = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $lid    = (int)($_POST['lid'] ?? 0);
    if ($action === 'create') {
        $db->prepare("INSERT INTO links (title,url,icon,sort_order) VALUES (?,?,?,?)")
           ->execute([trim($_POST['title']), trim($_POST['url']), trim($_POST['icon']), (int)($_POST['sort_order'] ?? 0)]);
        setFlash('success', 'Link added!');
    } elseif ($action === 'edit' && $lid) {
        $db->prepare("UPDATE links SET title=?,url=?,icon=?,sort_order=? WHERE id=?")
           ->execute([trim($_POST['title']),trim($_POST['url']),trim($_POST['icon']),(int)($_POST['sort_order']??0),$lid]);
        setFlash('success', 'Link updated!');
    } elseif ($action === 'delete' && $lid) {
        $db->prepare("DELETE FROM links WHERE id=?")->execute([$lid]);
        setFlash('success', 'Link deleted.');
    }
    redirect('index.php?page=admin-links');
}

$links  = $db->query("SELECT * FROM links ORDER BY sort_order, id")->fetchAll();
$editLink = null;
$editId   = (int)($_GET['edit'] ?? 0);
if ($editId) { $es=$db->prepare("SELECT * FROM links WHERE id=?"); $es->execute([$editId]); $editLink=$es->fetch(); }

ob_start();
?>
<div class="admin-links">
  <div class="section-header">
    <h2 class="section-title">🔗 Manage Links</h2>
    <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('open')">+ Add Link</button>
  </div>
  <div class="card table-card">
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Icon</th><th>Title</th><th>URL</th><th>Order</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($links as $l): ?>
          <tr>
            <td class="icon-cell"><?= h($l['icon']) ?></td>
            <td><?= h($l['title']) ?></td>
            <td><a href="<?= h($l['url']) ?>" target="_blank" class="link-text"><?= h(mb_strimwidth($l['url'],0,50,'...')) ?></a></td>
            <td><?= h($l['sort_order']) ?></td>
            <td class="actions-cell">
              <a href="index.php?page=admin-links&edit=<?= $l['id'] ?>" class="btn btn-xs btn-outline">Edit</a>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete?')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="lid" value="<?= $l['id'] ?>">
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

<div class="modal" id="createModal">
  <div class="modal-backdrop" onclick="this.parentElement.classList.remove('open')"></div>
  <div class="modal-box card">
    <div class="modal-header"><h3>Add Link</h3><button onclick="this.closest('.modal').classList.remove('open')">✕</button></div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="create">
      <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
      <div class="form-group"><label>URL</label><input type="url" name="url" required></div>
      <div class="form-group"><label>Icon (emoji)</label><input type="text" name="icon" value="🔗" maxlength="10"></div>
      <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
      <button type="submit" class="btn btn-primary">Add Link</button>
    </form>
  </div>
</div>

<?php if ($editLink): ?>
<div class="modal open">
  <div class="modal-backdrop" onclick="window.location='index.php?page=admin-links'"></div>
  <div class="modal-box card">
    <div class="modal-header"><h3>Edit Link</h3><a href="index.php?page=admin-links">✕</a></div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="edit"><input type="hidden" name="lid" value="<?= $editLink['id'] ?>">
      <div class="form-group"><label>Title</label><input type="text" name="title" value="<?= h($editLink['title']) ?>" required></div>
      <div class="form-group"><label>URL</label><input type="url" name="url" value="<?= h($editLink['url']) ?>" required></div>
      <div class="form-group"><label>Icon</label><input type="text" name="icon" value="<?= h($editLink['icon']) ?>"></div>
      <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= h($editLink['sort_order']) ?>"></div>
      <button type="submit" class="btn btn-primary">Save</button>
    </form>
  </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
