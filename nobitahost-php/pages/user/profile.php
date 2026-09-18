<?php
// ─────────────────────────────────────────────
//  Profile  |  pages/user/profile.php
// ─────────────────────────────────────────────
$user   = requireLogin();
$pageId = 'profile';
$title  = 'My Profile';
$db     = getDB();
$error  = null;
$success= null;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $bio   = trim($_POST['bio'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if (!isValidEmail($email)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check email uniqueness (exclude self)
            $exists = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $exists->execute([$email, $user['id']]);
            if ($exists->fetch()) {
                $error = 'That email is already used by another account.';
            } else {
                // Handle avatar upload
                $picPath = $user['profile_pic'] ?? '';
                if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                    $up = handleSettingUpload('profile_pic', 'avatar_' . $user['id']);
                    if ($up) $picPath = $up;
                }
                $db->prepare("UPDATE users SET bio=?,email=?,profile_pic=? WHERE id=?")
                   ->execute([$bio, $email, $picPath, $user['id']]);
                // Refresh
                $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
                $stmt->execute([$user['id']]);
                $user = $stmt->fetch();
                logActivity($user['id'], $user['username'], 'Updated profile', clientIp());
                $success = 'Profile updated successfully!';
            }
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $user['id']]);
            logActivity($user['id'], $user['username'], 'Changed password', clientIp());
            $success = 'Password changed successfully!';
        }
    }
}

// Refresh user data
$stmt = $db->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user['id']]);
$user = $stmt->fetch();

ob_start();
?>
<div class="profile-page">
  <?php if ($error): ?>
  <div class="alert alert-error"><?= h($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="alert alert-success"><?= h($success) ?></div>
  <?php endif; ?>

  <div class="profile-grid">
    <!-- Avatar & Info Card -->
    <div class="card profile-card">
      <div class="profile-avatar-wrap">
        <img src="<?= h(avatarUrl($user['profile_pic'], $user['username'])) ?>"
             alt="avatar" id="avatarPreview" class="profile-avatar" onerror="this.src='public/img/avatar.svg'">
        <div class="profile-badge <?= h($user['role']) ?>"><?= ucfirst(h($user['role'])) ?></div>
      </div>
      <h2 class="profile-username"><?= h($user['username']) ?></h2>
      <p class="profile-email"><?= h($user['email']) ?></p>
      <?php if ($user['bio']): ?>
      <p class="profile-bio"><?= h($user['bio']) ?></p>
      <?php endif; ?>
      <div class="profile-meta">
        <span>📅 Joined <?= date('M Y', strtotime($user['created_at'])) ?></span>
        <?php if ($user['last_login']): ?>
        <span>🕐 Last login <?= timeAgo($user['last_login']) ?></span>
        <?php endif; ?>
        <?php if ($user['last_ip']): ?>
        <span>🌐 <?= h($user['last_ip']) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <div class="profile-forms">
      <!-- Update Profile Form -->
      <div class="card">
        <h3 class="card-title">Edit Profile</h3>
        <form method="post" enctype="multipart/form-data" class="form-grid">
          <input type="hidden" name="action" value="update_profile">
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= h($user['email']) ?>" required>
          </div>
          <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="3" placeholder="Tell us about yourself..."><?= h($user['bio']) ?></textarea>
          </div>
          <div class="form-group">
            <label for="profile_pic">Profile Picture</label>
            <input type="file" id="profile_pic" name="profile_pic" accept="image/*"
                   onchange="previewAvatar(this)">
          </div>
          <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
      </div>

      <!-- Change Password Form -->
      <div class="card">
        <h3 class="card-title">Change Password</h3>
        <form method="post" class="form-grid">
          <input type="hidden" name="action" value="change_password">
          <div class="form-group">
            <label for="current_password">Current Password</label>
            <div class="input-with-toggle">
              <input type="password" id="current_password" name="current_password" required>
              <button type="button" class="pwd-toggle" onclick="togglePwd('current_password')">👁</button>
            </div>
          </div>
          <div class="form-group">
            <label for="new_password">New Password</label>
            <div class="input-with-toggle">
              <input type="password" id="new_password" name="new_password" required>
              <button type="button" class="pwd-toggle" onclick="togglePwd('new_password')">👁</button>
            </div>
          </div>
          <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
          </div>
          <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
      </div>

      <!-- 2FA Setup -->
      <div class="card">
        <h3 class="card-title">Two-Factor Authentication</h3>
        <?php if ($user['two_factor_enabled']): ?>
        <div class="alert alert-success">✅ 2FA is currently enabled</div>
        <form method="post">
          <input type="hidden" name="action" value="disable_2fa">
          <button type="submit" class="btn btn-danger" onclick="return confirm('Disable 2FA?')">Disable 2FA</button>
        </form>
        <?php else: ?>
        <p>Two-factor authentication adds an extra layer of security to your account.</p>
        <a href="index.php?page=profile&setup2fa=1" class="btn btn-outline">Enable 2FA</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { document.getElementById('avatarPreview').src = e.target.result; };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
