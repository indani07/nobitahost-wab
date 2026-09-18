<?php
$s = getSettings();
$panelName = h($s['panel_name'] ?? 'NobitaHost');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maintenance — <?= $panelName ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="auth-page">
  <div class="bg-overlay"></div>
  <div class="auth-container">
    <div class="auth-card maintenance-card">
      <span class="maintenance-icon">🔧</span>
      <h1>Under Maintenance</h1>
      <p><?= $panelName ?> is currently undergoing maintenance. Please check back soon.</p>
      <p><small>Admins can still <a href="index.php?page=login">log in here</a>.</small></p>
    </div>
  </div>
</body>
</html>
