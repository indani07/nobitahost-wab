<?php
// ─────────────────────────────────────────────
//  2FA Verify  |  pages/auth/verify-2fa.php
// ─────────────────────────────────────────────
$title = 'Two-Factor Auth';
$error = null;

if (!isset($_SESSION['pending_2fa_user'])) redirect('index.php?page=login');

$userId = (int)$_SESSION['pending_2fa_user'];
$db     = getDB();
$stmt   = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$pendingUser = $stmt->fetch();

if (!$pendingUser) { unset($_SESSION['pending_2fa_user']); redirect('index.php?page=login'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $secret = $pendingUser['two_factor_secret'] ?? '';

    if (verifyTotp($secret, $code)) {
        unset($_SESSION['pending_2fa_user']);
        loginUser($pendingUser, clientIp());
        logActivity($pendingUser['id'], $pendingUser['username'], 'Logged in (2FA)', clientIp());
        $next = $_POST['next'] ?? $_GET['next'] ?? 'index.php?page=home';
        if (!str_starts_with($next, 'index.php')) $next = 'index.php?page=home';
        redirect($next);
    } else {
        $error = 'Invalid 2FA code. Please try again.';
    }
}

/**
 * Verify TOTP code against secret (RFC 6238, 30-second window ±1)
 */
function verifyTotp(string $secret, string $code): bool {
    $code = str_replace(' ', '', $code);
    if (!preg_match('/^\d{6}$/', $code)) return false;
    $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = strtoupper(str_replace(' ', '', $secret));
    $secretLen = strlen($secret);
    $bits = '';
    for ($i = 0; $i < $secretLen; $i++) {
        $val = strpos($base32chars, $secret[$i]);
        if ($val === false) continue;
        $bits .= str_pad(decbin($val), 5, '0', STR_PAD_LEFT);
    }
    $bLen = strlen($bits);
    $key  = '';
    for ($i = 0; $i + 7 < $bLen; $i += 8) {
        $key .= chr(bindec(substr($bits, $i, 8)));
    }
    $t = (int)(time() / 30);
    for ($offset = -1; $offset <= 1; $offset++) {
        $timeBytes = pack('N*', 0) . pack('N*', $t + $offset);
        $hash  = hash_hmac('sha1', $timeBytes, $key, true);
        $otp   = ord($hash[19]) & 0x0F;
        $otp   = ((ord($hash[$otp]) & 0x7F) << 24) | ((ord($hash[$otp+1]) & 0xFF) << 16) | ((ord($hash[$otp+2]) & 0xFF) << 8) | (ord($hash[$otp+3]) & 0xFF);
        $otp   = $otp % 1000000;
        if (str_pad((string)$otp, 6, '0', STR_PAD_LEFT) === $code) return true;
    }
    return false;
}

ob_start();
?>
<div class="auth-card">
  <h2 class="auth-title">Two-Factor Auth</h2>
  <p class="auth-subtitle">Enter your 6-digit authenticator code</p>

  <?php if ($error): ?>
  <div class="alert alert-error"><?= h($error) ?></div>
  <?php endif; ?>

  <form method="post" class="auth-form">
    <input type="hidden" name="next" value="<?= h($_GET['next'] ?? 'index.php?page=home') ?>">
    <div class="form-group">
      <label for="code">Authenticator Code</label>
      <input type="text" id="code" name="code" inputmode="numeric" pattern="\d{6}"
             maxlength="6" required autofocus autocomplete="one-time-code"
             placeholder="000000" style="letter-spacing:0.3em;text-align:center;font-size:1.5rem;">
    </div>
    <button type="submit" class="btn btn-primary btn-block">Verify</button>
  </form>
  <p class="auth-switch">
    <a href="index.php?page=login" onclick="<?php unset($_SESSION['pending_2fa_user']); ?>">← Use a different account</a>
  </p>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/auth_layout.php';
