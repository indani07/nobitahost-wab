<?php
// ─────────────────────────────────────────────
//  Register  |  pages/auth/register.php
// ─────────────────────────────────────────────
if (($s['register_open'] ?? 'on') !== 'on') {
    setFlash('error', 'Registration is currently closed.');
    redirect('index.php?page=login');
}

$title = 'Register';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!$username || !$email || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif (!isValidUsername($username)) {
        $error = 'Username must be 3–30 characters, letters/numbers/underscore only.';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = getDB();
        $exists = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $exists->execute([$username, $email]);
        if ($exists->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $db->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'user', 'active')")
               ->execute([$username, $email, $hash]);
            $newId = (int)$db->lastInsertId();
            logActivity($newId, $username, 'Account registered', clientIp());
            setFlash('success', 'Account created! Please log in.');
            redirect('index.php?page=login');
        }
    }
}

ob_start();
?>
<div class="auth-card">
  <h2 class="auth-title">Create Account</h2>
  <p class="auth-subtitle">Join the panel today</p>

  <?php if ($error): ?>
  <div class="alert alert-error"><?= h($error) ?></div>
  <?php endif; ?>

  <form method="post" class="auth-form">
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" autocomplete="username"
             value="<?= h($_POST['username'] ?? '') ?>" required placeholder="e.g. cooluser123">
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" autocomplete="email"
             value="<?= h($_POST['email'] ?? '') ?>" required placeholder="you@example.com">
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <div class="input-with-toggle">
        <input type="password" id="password" name="password" required placeholder="Min 6 characters">
        <button type="button" class="pwd-toggle" onclick="togglePwd('password')">👁</button>
      </div>
    </div>
    <div class="form-group">
      <label for="confirm">Confirm Password</label>
      <input type="password" id="confirm" name="confirm" required placeholder="Repeat password">
    </div>
    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
  </form>
  <p class="auth-switch">Already have an account? <a href="index.php?page=login">Sign in</a></p>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/auth_layout.php';
