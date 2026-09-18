<?php
// ─────────────────────────────────────────────
//  Main Layout  |  views/layout.php
//  Usage: include with $title, $pageId, $user, $s set
// ─────────────────────────────────────────────
$s        = $s ?? getSettings();
$bg       = resolveBackground($s);
$accentColor = h($s['accent_color'] ?? '#0044ff');
$blur     = h($s['panel_blur'] ?? '16');
$radius   = h($s['card_radius'] ?? '16');
$transparency = (int)($s['transparency'] ?? 100);
$panelName= h($s['panel_name'] ?? 'NobitaHost');
$logoEmoji= h($s['logo_emoji'] ?? '🔷');
$logoType = $s['logo_type'] ?? 'emoji';
$logoUrl  = h($s['logo_url'] ?? '');
$faviconUrl = h($s['favicon_url'] ?? '');
$pageId   = $pageId ?? '';
$flash    = getFlash();
$baseUrl  = baseUrl();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= h($s['theme'] ?? 'dark') ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? $panelName ?> — <?= $panelName ?></title>
  <?php if ($faviconUrl): ?>
    <link rel="icon" href="<?= $baseUrl ?>/<?= $faviconUrl ?>">
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
      --accent-rgb: <?= hexToRgb($accentColor) ?>;
      --blur: <?= $blur ?>px;
      --radius: <?= $radius ?>px;
      --transparency: <?= $transparency ?>;
    }
    <?php if ($bg['url']): ?>
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      <?php if (in_array(pathinfo($bg['url'], PATHINFO_EXTENSION), ['mp4','webm'])): ?>
      /* video handled by JS */
      <?php else: ?>
      background: url('<?= h($bg['url']) ?>') center/cover no-repeat;
      <?php endif; ?>
      z-index: -1;
    }
    <?php endif; ?>
  </style>
</head>
<body class="<?= $pageId ?>">
  <?php if ($bg['url'] && in_array(pathinfo($bg['url'], PATHINFO_EXTENSION), ['mp4','webm'])): ?>
  <video class="bg-video" autoplay muted loop playsinline>
    <source src="<?= h($bg['url']) ?>" type="video/<?= pathinfo($bg['url'], PATHINFO_EXTENSION) ?>">
  </video>
  <?php endif; ?>
  <?php if ($bg['overlay']): ?>
  <div class="bg-overlay"></div>
  <?php endif; ?>

  <div class="app-wrapper">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <div class="main-area">
      <?php include __DIR__ . '/partials/topbar.php'; ?>
      <main class="content">
        <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?>" id="flash-msg">
          <?= h($flash['message']) ?>
          <button class="alert-close" onclick="this.parentElement.remove()">×</button>
        </div>
        <?php endif; ?>
        <?= $content ?? '' ?>
      </main>
      <?php include __DIR__ . '/partials/footer.php'; ?>
    </div>
  </div>

  <?php if (($s['music_type'] ?? 'none') !== 'none' && !empty($s['music_url'])): ?>
  <div class="music-player" id="musicPlayer">
    <audio id="bgMusic" loop>
      <source src="<?= h($s['music_url']) ?>">
    </audio>
    <button id="musicToggle" title="Toggle music">🎵</button>
  </div>
  <?php endif; ?>

  <script>
    window.APP = {
      accent: '<?= $accentColor ?>',
      musicVolume: <?= (int)($s['music_volume'] ?? 40) ?>,
      musicEnabled: <?= (($s['music_type'] ?? 'none') !== 'none') ? 'true' : 'false' ?>
    };
  </script>
  <script src="public/js/app.js"></script>
</body>
</html>
<?php
function hexToRgb(string $hex): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) $hex = str_repeat(substr($hex,0,1),2).str_repeat(substr($hex,1,1),2).str_repeat(substr($hex,2,1),2);
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));
    return "$r, $g, $b";
}
