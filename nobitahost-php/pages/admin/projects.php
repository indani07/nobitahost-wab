<?php
// Admin Projects Management
$user   = requireAdmin();
$pageId = 'admin-projects';
$title  = 'Manage Projects';
$db     = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pid    = (int)($_POST['pid'] ?? 0);
    if ($action === 'create') {
        $db->prepare("INSERT INTO projects (name,description,url,button,icon,sort_order) VALUES (?,?,?,?,?,?)")
           ->execute([trim($_POST['name']), trim($_POST['description']), trim($_POST['url']), trim($_POST['button'])?:'View Project', trim($_POST['icon'])?:'🚀', (int)($_POST['sort_order']??0)]);
        setFlash('success', 'Project added!');
    } elseif ($action === 'edit' && $pid) {
        $db->prepare("UPDATE projects SET name=?,description=?,url=?,button=?,icon=?,sort_order=? WHERE id=?")
           ->execute([trim($_POST['name']),trim($_POST['description']),trim($_POST['url']),trim($_POST['button'])?:'View Project',trim($_POST['icon'])?:'🚀',(int)($_POST['sort_order']??0),$pid]);
        setFlash('success', 'Project updated!');
    } elseif ($action === 'delete' && $pid) {
        $db->prepare("DELETE FROM projects WHERE id=?")->execute([$pid]);
        setFlash('success', 'Project deleted.');
    }
    redirect('index.php?page=admin-projects');
}

$projects = $db->query("SELECT * FROM projects ORDER BY sort_order, id")->fetchAll();
$editProj = null;
$editId   = (int)($_GET['edit'] ?? 0);
if ($editId) { $es=$db->prepare("SELECT * FROM projects WHERE id=?"); $es->execute([$editId]); $editProj=$es->fetch(); }

ob_start();
?>
<div class="admin-projects">
  <div class="section-header">
    <h2 class="section-title">🚀 Manage Projects</h2>
    <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('open')">+ Add Project</button>
  </div>
  <div class="card table-card">
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Icon</th><th>Name</th><th>Description</th><th>URL</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($projects as $p): ?>
          <tr>
            <td class="icon-cell"><?= h($p['icon']) ?></td>
            <td><strong><?= h($p['name']) ?></strong></td>
            <td><?= h(mb_strimwidth($p['description'],0,60,'...')) ?></td>
            <td><?php if ($p['url']): ?><a href="<?= h($p['url']) ?>" target="_blank" class="link-text">🔗</a><?php endif; ?></td>
            <td class="actions-cell">
              <a href="index.php?page=admin-projects&edit=<?= $p['id'] ?>" class="btn btn-xs btn-outline">Edit</a>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete?')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="pid" value="<?= $p['id'] ?>">
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
    <div class="modal-header"><h3>Add Project</h3><button onclick="this.closest('.modal').classList.remove('open')">✕</button></div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="create">
      <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
      <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
      <div class="form-group"><label>URL</label><input type="url" name="url"></div>
      <div class="form-group"><label>Button Label</label><input type="text" name="button" value="View Project"></div>
      <div class="form-group"><label>Icon (emoji)</label><input type="text" name="icon" value="🚀" maxlength="10"></div>
      <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
      <button type="submit" class="btn btn-primary">Add Project</button>
    </form>
  </div>
</div>

<?php if ($editProj): ?>
<div class="modal open">
  <div class="modal-backdrop" onclick="window.location='index.php?page=admin-projects'"></div>
  <div class="modal-box card">
    <div class="modal-header"><h3>Edit Project</h3><a href="index.php?page=admin-projects">✕</a></div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="edit"><input type="hidden" name="pid" value="<?= $editProj['id'] ?>">
      <div class="form-group"><label>Name</label><input type="text" name="name" value="<?= h($editProj['name']) ?>" required></div>
      <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= h($editProj['description']) ?></textarea></div>
      <div class="form-group"><label>URL</label><input type="url" name="url" value="<?= h($editProj['url']) ?>"></div>
      <div class="form-group"><label>Button Label</label><input type="text" name="button" value="<?= h($editProj['button']) ?>"></div>
      <div class="form-group"><label>Icon</label><input type="text" name="icon" value="<?= h($editProj['icon']) ?>"></div>
      <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= h($editProj['sort_order']) ?>"></div>
      <button type="submit" class="btn btn-primary">Save</button>
    </form>
  </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
