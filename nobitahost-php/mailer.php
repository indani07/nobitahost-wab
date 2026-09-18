<?php
// ─────────────────────────────────────────────
//  NobitaHost — PHP Edition  |  mailer.php
// ─────────────────────────────────────────────

require_once __DIR__ . '/settings.php';

/**
 * Send an email using PHP's mail() or a simple SMTP wrapper.
 * For real SMTP, drop in PHPMailer — this uses PHP's built-in mail() 
 * with the settings from the admin panel.
 */
function sendMail(string $to, string $subject, string $htmlBody): bool {
    $s = getSettings();

    if (($s['mail_enabled'] ?? 'off') !== 'on') {
        return false; // Mail disabled
    }

    $from = $s['mail_from'] ?? 'NobitaHost <noreply@localhost>';

    // If PHPMailer is available (composer), use it
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        return sendWithPhpMailer($to, $subject, $htmlBody, $s, $from);
    }

    // Fallback: PHP built-in mail()
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$from}\r\n";
    $headers .= "Reply-To: {$from}\r\n";
    return mail($to, $subject, $htmlBody, $headers);
}

function sendWithPhpMailer(string $to, string $subject, string $html, array $s, string $from): bool {
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $s['smtp_host'] ?? 'localhost';
        $mail->Port       = (int)($s['smtp_port'] ?? 587);
        $mail->SMTPAuth   = !empty($s['smtp_user']);
        $mail->Username   = $s['smtp_user'] ?? '';
        $mail->Password   = $s['smtp_pass'] ?? '';
        $mail->SMTPSecure = ($s['smtp_secure'] === 'true') ? 'ssl' : (($s['smtp_port'] ?? 587) == 465 ? 'ssl' : 'tls');
        // Parse "Name <email>" format
        if (preg_match('/^(.+?)\s*<(.+?)>$/', $from, $m)) {
            $mail->setFrom($m[2], trim($m[1]));
        } else {
            $mail->setFrom($from);
        }
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log('Mailer error: ' . $e->getMessage());
        return false;
    }
}

/** Build a password reset email */
function buildResetEmail(string $resetUrl, string $panelName): string {
    return "
    <div style='font-family:sans-serif;max-width:480px;margin:0 auto;background:#111;color:#eee;padding:32px;border-radius:12px;'>
      <h2 style='color:#4f8ef7;'>Password Reset — {$panelName}</h2>
      <p>You requested a password reset. Click the button below to reset your password.</p>
      <p><a href='{$resetUrl}' style='display:inline-block;background:#4f8ef7;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;'>Reset Password</a></p>
      <p style='color:#888;font-size:12px;'>This link expires in 1 hour. If you didn't request this, ignore this email.</p>
    </div>";
}
