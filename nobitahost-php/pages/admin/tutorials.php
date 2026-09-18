<?php
// Admin Tutorials Management
$user   = requireAdmin();
$pageId = 'admin-tutorials';
$title  = 'Manage Tutorials';
$db     = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $tid    = (int)($_POST['tid'] ?? 0);

    if ($action === 'create') {
        $t = trim($_POST['title'] ?? '');
        $d = trim($_POST['description'] ?? '');
        $v = trim($_POST['video_url'] ?? '');
        if ($t) {
            $db->prepare("INSERT INTO tutorials (title,description,video_url,author_id) VALUES (?,?,?,?)")
               ->execute([$t, $d, $v, $user['id']]);
            setFlash('success', 'Tutorial added!');
        }
    } elseif ($action === 'edit' && $tid) {
        $db->prepare("UPDATE tutorials SET title=?,description=?,video_url=? WHERE id=?")
           ->execute([trim($_POST['title']),trim($_POST['description']),trim($_POST['video_url']),$tid]);
        setFlash('success', 'Tutorial updated!');
    } elseif ($action === 'delete' && $tid) {
        $db->prepare("DELETE FROM tutorials WHERE id=?")->execute([$tid]);
        setFlash('success', 'Tutorial deleted.');
    }
    redirect('index.php?page=admin-tutorials');
}

$tutorials = $db->query("SELECT * FROM tutorials ORDER BY created_at DESC")->fetchAll();
$editTut   = null;
$editId    = (int)($_GET['edit'] ?? 0);
if ($editId) {
    $es = $db->prepare("SELECT * FROM tutorials WHERE id=?");
    $es->execute([$editId]);
    $editTut = $es->fetch();
}

ob_start();
?>
<div class="admin-tutorials">
  <div class="section-header">
    <h2 class="section-title">📚 Manage Tutorials</h2>
    <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('open')">+ Add Tutorial</button>
  </div>

  <div class="card table-card">
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Thumbnail</th><th>Title</th><th>Description</th><th>Video URL</th><th>Added</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($tutorials as $tut): ?>
          <?php $vid = youtubeId($tut['video_url']); ?>
          <tr>
            <td><?php if ($vid): ?><img src="https://img.youtube.com/vi/<?= h($vid) ?>/default.jpg" class="table-thumb"><?php else: ?>🎬<?php endif; ?></td>
            <td><strong><?= h($tut['title']) ?></strong></td>
            <td><?= h(mb_strimwidth($tut['description'], 0, 60, '...')) ?></td>
            <td><?php if ($tut['video_url']): ?><a href="<?= h($tut['video_url']) ?>" target="_blank" class="link-text">▶ Watch</a><?php endif; ?></td>
            <td><?= timeAgo($tut['created_at']) ?></td>
            <td class="actions-cell">
              <a href="index.php?page=admin-tutorials&edit=<?= $tut['id'] ?>" class="btn btn-xs btn-outline">Edit</a>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete tutorial?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="tid" value="<?= $tut['id'] ?>">
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
  <div class="modal-box card">
    <div class="modal-header"><h3>Add Tutorial</h3><button onclick="this.closest('.modal').classList.remove('open')">✕</button></div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="create">
      <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
      <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
      <div class="form-group"><label>YouTube URL</label><input type="text" name="video_url" placeholder="https://www.youtube.com/watch?v=..."></div>
      <button type="submit" class="btn btn-primary">Add Tutorial</button>
    </form>
  </div>
</div>

<?php if ($editTut): ?>
<div class="modal open" id="editModal">
  <div class="modal-backdrop" onclick="window.location='index.php?page=admin-tutorials'"></div>
  <div class="modal-box card">
    <div class="modal-header"><h3>Edit Tutorial</h3><a href="index.php?page=admin-tutorials">✕</a></div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="tid" value="<?= $editTut['id'] ?>">
      <div class="form-group"><label>Title</label><input type="text" name="title" value="<?= h($editTut['title']) ?>" required></div>
      <div class="form-group"><label>Description</label><textarea name="description" rows="3"><?= h($editTut['description']) ?></textarea></div>
      <div class="form-group"><label>YouTube URL</label><input type="text" name="video_url" value="<?= h($editTut['video_url']) ?>"></div>
      <button type="submit" class="btn btn-primary">Save</button>
    </form>
  </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
