<?php
// Terms Page
$user   = requireLogin();
$pageId = 'terms';
$title  = 'Terms of Service';
$page   = getContentPage('terms');
$content_text = $page ? $page['content'] : '<p>Terms of service coming soon.</p>';

ob_start();
?>
<div class="terms-page">
  <div class="card content-page">
    <h2 class="section-title">📜 Terms of Service</h2>
    <div class="content-body"><?= $content_text ?></div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
