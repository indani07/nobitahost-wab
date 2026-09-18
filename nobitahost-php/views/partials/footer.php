<?php
// ─────────────────────────────────────────────
//  Footer  |  views/partials/footer.php
// ─────────────────────────────────────────────
$s         = $s ?? getSettings();
$panelName = h($s['panel_name'] ?? 'NobitaHost');
$footerPage = getContentPage('footer');
$footerText = $footerPage ? $footerPage['content'] : "{$panelName} &copy; " . date('Y');
?>
<footer class="app-footer">
  <p><?= $footerText ?></p>
</footer>
