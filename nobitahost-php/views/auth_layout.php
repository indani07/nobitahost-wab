<?php
// ─────────────────────────────────────────────
//  Auth Layout  |  views/auth_layout.php
// ─────────────────────────────────────────────
$s      = $s ?? getSettings();
$bg     = resolveBackground($s);
$panelName  = h($s['panel_name'] ?? 'NobitaHost');
$logoEmoji  = h($s['logo_emoji'] ?? '🔷');
$logoType   = $s['logo_type'] ?? 'emoji';
$logoUrl    = h($s['logo_url'] ?? '');
$accentColor= h($s['accent_color'] ?? '#0044ff');
$blur       = h($s['panel_blur'] ?? '16');
$radius     = h($s['card_radius'] ?? '16');
$faviconUrl = h($s['favicon_url'] ?? '');
$flash      = getFlash();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= h($s['theme'] ?? 'dark') ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Login' ?> — <?= $panelName ?></title>
  <?php if ($faviconUrl): ?>
    <link rel="icon" href="<?= h(baseUrl()) ?>/<?= $faviconUrl ?>">
  <?php else: ?>
    <link rel="icon" href="public/img/favicon.svg" type="image/svg+xml">
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/css/style.css">
  <style>
    :root {
      --accent: <?= $accentColor ?>;
      --blur: <?= $blur ?>px;
      --radius: <?= $radius ?>px;
    }
    <?php if ($bg['url'] && !in_array(pathinfo($bg['url'], PATHINFO_EXTENSION), ['mp4','webm'])): ?>
    body.auth-page::before {
      background: url('<?= h($bg['url']) ?>') center/cover no-repeat;
    }
    <?php endif; ?>
  </style>
</head>
<body class="auth-page">
  <?php if ($bg['url'] && in_array(pathinfo($bg['url'], PATHINFO_EXTENSION), ['mp4','webm'])): ?>
  <video class="bg-video" autoplay muted loop playsinline>
    <source src="<?= h($bg['url']) ?>" type="video/<?= pathinfo($bg['url'], PATHINFO_EXTENSION) ?>">
  </video>
  <?php endif; ?>
  <div class="bg-overlay"></div>

  <div class="auth-container">
    <div class="auth-brand">
      <?php if ($logoType === 'image' && $logoUrl): ?>
        <img src="<?= $logoUrl ?>" alt="logo" class="auth-logo-img">
      <?php else: ?>
        <span class="auth-logo-emoji"><?= $logoEmoji ?></span>
      <?php endif; ?>
      <span class="auth-brand-name"><?= $panelName ?></span>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= h($flash['type']) ?>">
      <?= h($flash['message']) ?>
    </div>
    <?php endif; ?>

    <?= $content ?? '' ?>

    <p class="auth-footer"><?= $panelName ?> &copy; <?= date('Y') ?></p>
  </div>
  <script src="public/js/app.js"></script>
</body>
</html>
