<?php
// ─────────────────────────────────────────────
//  Login  |  pages/auth/login.php
// ─────────────────────────────────────────────
$title = 'Login';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Please enter username/email and password.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $found = $stmt->fetch();

        if (!$found || !password_verify($password, $found['password'])) {
            $error = 'Invalid username or password.';
        } elseif ($found['status'] !== 'active') {
            $error = 'Your account is suspended. Contact an administrator.';
        } elseif ($found['two_factor_enabled']) {
            // Require 2FA verification
            $_SESSION['pending_2fa_user'] = $found['id'];
            $next = $_POST['next'] ?? $_GET['next'] ?? 'index.php?page=home';
            redirect("index.php?page=verify-2fa&next=" . urlencode($next));
        } else {
            loginUser($found, clientIp());
            logActivity($found['id'], $found['username'], 'Logged in', clientIp());
            $next = $_POST['next'] ?? $_GET['next'] ?? 'index.php?page=home';
            // Security: only allow relative redirects
            if (!str_starts_with($next, 'index.php')) $next = 'index.php?page=home';
            redirect($next);
        }
    }
}

$registerOpen = ($s['register_open'] ?? 'on') === 'on';
ob_start();
?>
<div class="auth-card">
  <h2 class="auth-title">Welcome back</h2>
  <p class="auth-subtitle">Sign in to your account</p>

  <?php if ($error): ?>
  <div class="alert alert-error"><?= h($error) ?></div>
  <?php endif; ?>

  <form method="post" class="auth-form">
    <input type="hidden" name="next" value="<?= h($_GET['next'] ?? 'index.php?page=home') ?>">
    <div class="form-group">
      <label for="username">Username or Email</label>
      <input type="text" id="username" name="username" autocomplete="username"
             value="<?= h($_POST['username'] ?? '') ?>" required placeholder="Enter username or email">
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <div class="input-with-toggle">
        <input type="password" id="password" name="password" autocomplete="current-password"
               required placeholder="Enter password">
        <button type="button" class="pwd-toggle" onclick="togglePwd('password')">👁</button>
      </div>
    </div>
    <a href="index.php?page=forgot" class="forgot-link">Forgot password?</a>
    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
  </form>

  <?php if ($registerOpen): ?>
  <p class="auth-switch">Don't have an account? <a href="index.php?page=register">Register</a></p>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/auth_layout.php';
