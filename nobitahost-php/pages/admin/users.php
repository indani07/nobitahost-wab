<?php
// Admin Users Management
$user   = requireAdmin();
$pageId = 'admin-users';
$title  = 'Manage Users';
$db     = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $uid    = (int)($_POST['uid'] ?? 0);

    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = in_array($_POST['role'] ?? '', ['admin','user','moderator']) ? $_POST['role'] : 'user';

        if ($username && $email && $password && isValidUsername($username) && isValidEmail($email)) {
            $exists = $db->prepare("SELECT id FROM users WHERE username=? OR email=?");
            $exists->execute([$username, $email]);
            if ($exists->fetch()) {
                setFlash('error', 'Username or email already exists.');
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $db->prepare("INSERT INTO users (username,email,password,role,status) VALUES (?,?,?,?,?)")
                   ->execute([$username, $email, $hash, $role, 'active']);
                logActivity($user['id'], $user['username'], "Created user: {$username}", clientIp());
                setFlash('success', "User '{$username}' created successfully!");
            }
        } else {
            setFlash('error', 'Please fill in all required fields correctly.');
        }
    } elseif ($action === 'edit' && $uid && $uid !== $user['id']) {
        $newRole   = in_array($_POST['role'] ?? '', ['admin','user','moderator']) ? $_POST['role'] : 'user';
        $newStatus = in_array($_POST['status'] ?? '', ['active','suspended']) ? $_POST['status'] : 'active';
        $newPass   = $_POST['new_password'] ?? '';
        $newBio    = trim($_POST['bio'] ?? '');
        if ($newPass && strlen($newPass) >= 6) {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET role=?,status=?,bio=?,password=? WHERE id=?")->execute([$newRole,$newStatus,$newBio,$hash,$uid]);
        } else {
            $db->prepare("UPDATE users SET role=?,status=?,bio=? WHERE id=?")->execute([$newRole,$newStatus,$newBio,$uid]);
        }
        logActivity($user['id'], $user['username'], "Edited user ID#{$uid}", clientIp());
        setFlash('success', 'User updated.');
    } elseif ($action === 'delete' && $uid && $uid !== $user['id']) {
        $del = $db->prepare("SELECT username FROM users WHERE id=?");
        $del->execute([$uid]);
        $du = $del->fetch();
        $db->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        logActivity($user['id'], $user['username'], "Deleted user: " . ($du['username'] ?? ''), clientIp());
        setFlash('success', 'User deleted.');
    } elseif ($action === 'toggle_status' && $uid && $uid !== $user['id']) {
        $st = $db->prepare("SELECT status FROM users WHERE id=?")->execute([$uid]) ? null : null;
        $stS = $db->prepare("SELECT status FROM users WHERE id=?");
        $stS->execute([$uid]);
        $stR = $stS->fetch();
        $newSt = ($stR['status'] === 'active') ? 'suspended' : 'active';
        $db->prepare("UPDATE users SET status=? WHERE id=?")->execute([$newSt, $uid]);
        setFlash('success', 'User status updated.');
    }
    redirect('index.php?page=admin-users');
}

$users     = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$editUser  = null;
$editId    = (int)($_GET['edit'] ?? 0);
if ($editId) {
    $es = $db->prepare("SELECT * FROM users WHERE id=?");
    $es->execute([$editId]);
    $editUser = $es->fetch();
}

ob_start();
?>
<div class="admin-users">
  <div class="section-header">
    <h2 class="section-title">👤 Manage Users</h2>
    <button class="btn btn-primary" onclick="document.getElementById('createModal').classList.add('open')">+ Create User</button>
  </div>

  <!-- Users Table -->
  <div class="card table-card">
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Avatar</th><th>Username</th><th>Email</th><th>Role</th>
            <th>Status</th><th>Last Login</th><th>Joined</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><img src="<?= h(avatarUrl($u['profile_pic'], $u['username'])) ?>" class="table-avatar" onerror="this.src='public/img/avatar.svg'"></td>
            <td><strong><?= h($u['username']) ?></strong></td>
            <td><?= h($u['email']) ?></td>
            <td><span class="badge badge-<?= h($u['role']) ?>"><?= ucfirst(h($u['role'])) ?></span></td>
            <td><span class="badge badge-<?= $u['status']==='active'?'success':'danger' ?>"><?= ucfirst(h($u['status'])) ?></span></td>
            <td><?= $u['last_login'] ? timeAgo($u['last_login']) : '—' ?></td>
            <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            <td class="actions-cell">
              <?php if ($u['id'] !== $user['id']): ?>
              <a href="index.php?page=admin-users&edit=<?= $u['id'] ?>" class="btn btn-xs btn-outline">Edit</a>
              <form method="post" style="display:inline">
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                <button class="btn btn-xs btn-warning" type="submit"><?= $u['status']==='active'?'Suspend':'Activate' ?></button>
              </form>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete user <?= h($u['username']) ?>?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                <button class="btn btn-xs btn-danger" type="submit">Delete</button>
              </form>
              <?php else: ?>
              <span class="badge">You</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create User Modal -->
<div class="modal" id="createModal">
  <div class="modal-backdrop" onclick="this.parentElement.classList.remove('open')"></div>
  <div class="modal-box card">
    <div class="modal-header">
      <h3>Create New User</h3>
      <button onclick="document.getElementById('createModal').classList.remove('open')">✕</button>
    </div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="create">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required placeholder="e.g. john123">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required placeholder="user@example.com">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required placeholder="Min 6 characters">
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role">
          <option value="user">User</option>
          <option value="moderator">Moderator</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Create User</button>
    </form>
  </div>
</div>

<!-- Edit User Modal -->
<?php if ($editUser): ?>
<div class="modal open" id="editModal">
  <div class="modal-backdrop" onclick="window.location='index.php?page=admin-users'"></div>
  <div class="modal-box card">
    <div class="modal-header">
      <h3>Edit: <?= h($editUser['username']) ?></h3>
      <a href="index.php?page=admin-users">✕</a>
    </div>
    <form method="post" class="form-grid">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="uid" value="<?= $editUser['id'] ?>">
      <div class="form-group">
        <label>Role</label>
        <select name="role">
          <option value="user"      <?= $editUser['role']==='user'?'selected':'' ?>>User</option>
          <option value="moderator" <?= $editUser['role']==='moderator'?'selected':'' ?>>Moderator</option>
          <option value="admin"     <?= $editUser['role']==='admin'?'selected':'' ?>>Admin</option>
        </select>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="active"    <?= $editUser['status']==='active'?'selected':'' ?>>Active</option>
          <option value="suspended" <?= $editUser['status']==='suspended'?'selected':'' ?>>Suspended</option>
        </select>
      </div>
      <div class="form-group">
        <label>Bio</label>
        <textarea name="bio" rows="2"><?= h($editUser['bio']) ?></textarea>
      </div>
      <div class="form-group">
        <label>New Password <small>(leave blank to keep current)</small></label>
        <input type="password" name="new_password" placeholder="New password (optional)">
      </div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
