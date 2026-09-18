<?php
// Admin Content Editor
$user   = requireAdmin();
$pageId = 'admin-content';
$title  = 'Content Editor';

$pages = [
    'about'  => 'About Page',
    'terms'  => 'Terms of Service',
    'footer' => 'Footer Text',
    'navbar' => 'Navbar Brand',
    'hub'    => 'Hub Description',
];

$editing = $_GET['slug'] ?? 'about';
if (!array_key_exists($editing, $pages)) $editing = 'about';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug    = $_POST['slug'] ?? $editing;
    $title_p = trim($_POST['page_title'] ?? $pages[$slug] ?? $slug);
    $content = $_POST['content'] ?? '';
    if (array_key_exists($slug, $pages)) {
        saveContentPage($slug, $title_p, $content, $user['id']);
        logActivity($user['id'], $user['username'], "Updated content page: {$slug}", clientIp());
        setFlash('success', "Page '{$pages[$slug]}' saved!");
        redirect("index.php?page=admin-content&slug={$slug}");
    }
}

$currentPage = getContentPage($editing);

ob_start();
?>
<div class="admin-content">
  <h2 class="section-title">📄 Content Editor</h2>
  <p class="hint">Edit the content of each page directly in the browser. HTML is supported.</p>

  <div class="content-editor-layout">
    <!-- Page List -->
    <div class="content-pages-list card">
      <h3 class="card-title">Pages</h3>
      <?php foreach ($pages as $slug => $label): ?>
      <a href="index.php?page=admin-content&slug=<?= $slug ?>"
         class="content-page-item <?= ($editing === $slug) ? 'active' : '' ?>">
        📄 <?= h($label) ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Editor -->
    <div class="content-editor-area">
      <div class="card">
        <h3 class="card-title">Editing: <?= h($pages[$editing]) ?></h3>
        <form method="post" class="form-grid">
          <input type="hidden" name="slug" value="<?= h($editing) ?>">
          <div class="form-group">
            <label for="page_title">Page Title</label>
            <input type="text" id="page_title" name="page_title"
                   value="<?= h($currentPage ? $currentPage['title'] : $pages[$editing]) ?>">
          </div>
          <div class="form-group">
            <label for="content">Content (HTML supported)</label>
            <textarea id="content" name="content" rows="18" class="code-editor"><?= h($currentPage ? $currentPage['content'] : '') ?></textarea>
          </div>
          <div class="editor-actions">
            <button type="submit" class="btn btn-primary">💾 Save Page</button>
            <button type="button" class="btn btn-outline" onclick="togglePreview()">👁 Preview</button>
          </div>
        </form>
        <div id="previewPanel" class="content-preview" style="display:none">
          <h4>Live Preview</h4>
          <div id="previewContent" class="content-body"></div>
        </div>
      </div>
      <?php if ($currentPage && $currentPage['updated_at']): ?>
      <p class="hint">Last updated: <?= date('M j, Y H:i', strtotime($currentPage['updated_at'])) ?></p>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
function togglePreview() {
  const panel = document.getElementById('previewPanel');
  const editor = document.getElementById('content');
  if (panel.style.display === 'none') {
    document.getElementById('previewContent').innerHTML = editor.value;
    panel.style.display = 'block';
  } else {
    panel.style.display = 'none';
  }
}
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
