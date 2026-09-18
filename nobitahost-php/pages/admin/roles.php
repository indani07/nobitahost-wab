<?php
// Admin Roles Management
$user   = requireAdmin();
$pageId = 'admin-roles';
$title  = 'Manage Roles';
$db     = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $rid    = (int)($_POST['rid'] ?? 0);
    if ($action === 'create') {
        $n = trim($_POST['name'] ?? ''); $c = trim($_POST['color'] ?? '#3b82f6'); $so = (int)($_POST['sort_order'] ?? 0);
        if ($n) { $db->prepare("INSERT INTO roles (name,color,sort_order) VALUES (?,?,?)")->execute([$n,$c,$so]); setFlash('success','Role added!'); }
    } elseif ($action === 'edit' && $rid) {
        $db->prepare("UPDATE roles SET name=?,color=?,sort_order=? WHERE id=?")
           ->execute([trim($_POST['name']),trim($_POST['color']),(int)$_POST['sort_order'],$rid]);
        setFlash('success','Role updated!');
    } elseif ($action === 'delete' && $rid) {
        $db->prepare("DELETE FROM roles WHERE id=?")->execute([$rid]);
        setFlash('success','Role deleted.');
    }
    redirect('index.php?page=admin-roles');
}
$roles = $db->query("SELECT * FROM roles ORDER BY sort_order, id")->fetchAll();
$editRole = null; $editId = (int)($_GET['edit'] ?? 0);
if ($editId) { $es=$db->prepare("SELECT * FROM roles WHERE id=?"); $es->execute([$editId]); $editRole=$es->fetch(); }
ob_start();
?>
<div class="admin-roles">
  <div class="section-header"><h2 class="section-title">🎭 Manage Roles</h2>
    <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('open')">+ Add Role</button>
  </div>
  <div class="card table-card">
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Color</th><th>Name</th><th>Sort</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($roles as $r): ?>
          <tr>
            <td><span class="role-color" style="background:<?= h($r['color']) ?>"></span></td>
            <td><strong style="color:<?= h($r['color']) ?>"><?= h($r['name']) ?></strong></td>
            <td><?= h($r['sort_order']) ?></td>
            <td class="actions-cell">
              <a href="index.php?page=admin-roles&edit=<?= $r['id'] ?>" class="btn btn-xs btn-outline">Edit</a>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete role?')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="rid" value="<?= $r['id'] ?>">
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
    <div class="modal-header"><h3>Add Role</h3><button onclick="this.closest('.modal').classList.remove('open')">✕</button></div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="create">
      <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
      <div class="form-group"><label>Color</label><input type="color" name="color" value="#3b82f6"></div>
      <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
      <button type="submit" class="btn btn-primary">Add Role</button>
    </form>
  </div>
</div>
<?php if ($editRole): ?>
<div class="modal open">
  <div class="modal-backdrop" onclick="window.location='index.php?page=admin-roles'"></div>
  <div class="modal-box card">
    <div class="modal-header"><h3>Edit Role</h3><a href="index.php?page=admin-roles">✕</a></div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="edit"><input type="hidden" name="rid" value="<?= $editRole['id'] ?>">
      <div class="form-group"><label>Name</label><input type="text" name="name" value="<?= h($editRole['name']) ?>" required></div>
      <div class="form-group"><label>Color</label><input type="color" name="color" value="<?= h($editRole['color']) ?>"></div>
      <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?= h($editRole['sort_order']) ?>"></div>
      <button type="submit" class="btn btn-primary">Save</button>
    </form>
  </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
