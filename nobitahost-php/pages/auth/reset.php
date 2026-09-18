<?php
// ─────────────────────────────────────────────
//  Reset Password  |  pages/auth/reset.php
// ─────────────────────────────────────────────
$title = 'Reset Password';
$token = $_GET['token'] ?? '';
$error = null;

if (!$token) redirect('index.php?page=login');

$db   = getDB();
$stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > datetime('now')");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    setFlash('error', 'This reset link is invalid or has expired.');
    redirect('index.php?page=forgot');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password = ? WHERE email = ?")->execute([$hash, $reset['email']]);
        $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")->execute([$token]);
        // Log
        $u = $db->prepare("SELECT * FROM users WHERE email = ?")->execute([$reset['email']]) ? null : null;
        $uStmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $uStmt->execute([$reset['email']]);
        $uu = $uStmt->fetch();
        if ($uu) logActivity($uu['id'], $uu['username'], 'Password reset via email link', clientIp());
        setFlash('success', 'Password reset successfully! Please log in.');
        redirect('index.php?page=login');
    }
}

ob_start();
?>
<div class="auth-card">
  <h2 class="auth-title">Reset Password</h2>
  <p class="auth-subtitle">Enter your new password</p>

  <?php if ($error): ?>
  <div class="alert alert-error"><?= h($error) ?></div>
  <?php endif; ?>

  <form method="post" class="auth-form">
    <input type="hidden" name="token" value="<?= h($token) ?>">
    <div class="form-group">
      <label for="password">New Password</label>
      <div class="input-with-toggle">
        <input type="password" id="password" name="password" required placeholder="Min 6 characters">
        <button type="button" class="pwd-toggle" onclick="togglePwd('password')">👁</button>
      </div>
    </div>
    <div class="form-group">
      <label for="confirm">Confirm Password</label>
      <input type="password" id="confirm" name="confirm" required placeholder="Repeat new password">
    </div>
    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
  </form>
  <p class="auth-switch"><a href="index.php?page=login">← Back to Login</a></p>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/auth_layout.php';
