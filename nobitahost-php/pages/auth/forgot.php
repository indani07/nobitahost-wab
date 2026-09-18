<?php
// ─────────────────────────────────────────────
//  Forgot Password  |  pages/auth/forgot.php
// ─────────────────────────────────────────────
$title   = 'Forgot Password';
$error   = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $found = $stmt->fetch();

        // Always show success to prevent email enumeration
        $success = 'If that email exists, a reset link has been sent.';

        if ($found) {
            $token   = generateToken(32);
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            // Invalidate old tokens
            $db->prepare("UPDATE password_resets SET used=1 WHERE email=?")->execute([$email]);
            $db->prepare("INSERT INTO password_resets (email,token,expires_at) VALUES (?,?,?)")
               ->execute([$email, $token, $expires]);

            $resetUrl = baseUrl() . '/index.php?page=reset&token=' . $token;
            $html     = buildResetEmail($resetUrl, $s['panel_name'] ?? 'NobitaHost');
            sendMail($email, 'Password Reset — ' . ($s['panel_name'] ?? 'NobitaHost'), $html);
            logActivity($found['id'], $found['username'], 'Password reset requested', clientIp());
        }
    }
}

ob_start();
?>
<div class="auth-card">
  <h2 class="auth-title">Forgot Password</h2>
  <p class="auth-subtitle">Enter your email to receive a reset link</p>

  <?php if ($error): ?>
  <div class="alert alert-error"><?= h($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="alert alert-success"><?= h($success) ?></div>
  <?php endif; ?>

  <form method="post" class="auth-form">
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" required autocomplete="email"
             value="<?= h($_POST['email'] ?? '') ?>" placeholder="you@example.com">
    </div>
    <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
  </form>
  <p class="auth-switch"><a href="index.php?page=login">← Back to Login</a></p>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/auth_layout.php';
