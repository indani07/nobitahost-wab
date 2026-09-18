<?php
// About Page
$user   = requireLogin();
$pageId = 'about';
$title  = 'About';
$page   = getContentPage('about');
$content_text = $page ? $page['content'] : '<p>About page content coming soon.</p>';

ob_start();
?>
<div class="about-page">
  <div class="card content-page">
    <h2 class="section-title">ℹ️ About</h2>
    <div class="content-body"><?= $content_text ?></div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
