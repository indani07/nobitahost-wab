<?php
// ─────────────────────────────────────────────
//  Admin Settings  |  pages/admin/settings.php
//  EVERYTHING is editable in the browser here
// ─────────────────────────────────────────────
$user   = requireAdmin();
$pageId = 'admin-settings';
$title  = 'Settings';
$db     = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? 'general';

    $allowed = [
        'general'    => ['panel_name','panel_tagline','logo_type','logo_emoji','logo_url','favicon_url'],
        'appearance' => ['theme','panel_blur','transparency','card_radius','accent_color','transparent_bar','blur_bar'],
        'background' => ['background_type','background_url','background_source','background_overlay'],
        'music'      => ['music_type','music_url','music_volume'],
        'widgets'    => ['discord_enabled','discord_server_id','discord_channel','discord_theme','youtube_enabled','youtube_channel','youtube_api_key'],
        'social'     => ['instagram_handle','github_username','github_token'],
        'mail'       => ['mail_enabled','smtp_host','smtp_port','smtp_secure','smtp_user','smtp_pass','mail_from'],
        'panel'      => ['register_open','maintenance','script_enabled','script_badge','script_description','script_command'],
    ];

    // Handle file uploads
    if ($section === 'general') {
        $logoUpload    = handleSettingUpload('logo_file', 'logo');
        $faviconUpload = handleSettingUpload('favicon_file', 'favicon');
        if ($logoUpload) {
            saveSetting('logo_url', $logoUpload);
            saveSetting('logo_type', 'image');
        }
        if ($faviconUpload) saveSetting('favicon_url', $faviconUpload);
    }
    if ($section === 'background') {
        $bgUpload = handleSettingUpload('background_file', 'background');
        if ($bgUpload) {
            saveSetting('background_url', $bgUpload);
            $ext = pathinfo($bgUpload, PATHINFO_EXTENSION);
            saveSetting('background_type', in_array($ext, ['mp4','webm']) ? 'video' : 'image');
        }
    }

    // Save posted fields
    foreach (($allowed[$section] ?? []) as $key) {
        if (isset($_POST[$key])) {
            saveSetting($key, $_POST[$key]);
        } else {
            // Checkbox — if not set, it was unchecked → save 'off'
            $checkboxes = ['background_overlay','transparent_bar','blur_bar','discord_enabled','youtube_enabled','register_open','maintenance','script_enabled','mail_enabled'];
            if (in_array($key, $checkboxes)) saveSetting($key, 'off');
        }
    }

    logActivity($user['id'], $user['username'], "Updated settings [{$section}]", clientIp());
    setFlash('success', 'Settings saved successfully!');
    redirect("index.php?page=admin-settings&tab={$section}");
}

// Reload settings after save
$s   = getSettings();
$tab = $_GET['tab'] ?? 'general';
$flash = getFlash();

ob_start();
?>
<div class="admin-settings">
  <h2 class="section-title">⚙️ Panel Settings</h2>

  <?php if ($flash): ?>
  <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
  <?php endif; ?>

  <!-- Tab Nav -->
  <div class="settings-tabs">
    <?php $tabs = ['general'=>'🏷️ General','appearance'=>'🎨 Appearance','background'=>'🖼️ Background','music'=>'🎵 Music','widgets'=>'📡 Widgets','social'=>'🌐 Social','mail'=>'📧 Mail','panel'=>'🔧 Panel']; ?>
    <?php foreach ($tabs as $tid => $tlabel): ?>
    <a href="index.php?page=admin-settings&tab=<?= $tid ?>"
       class="settings-tab <?= ($tab === $tid) ? 'active' : '' ?>"><?= $tlabel ?></a>
    <?php endforeach; ?>
  </div>

  <!-- ═══════════════════════════════════ GENERAL ═══════════════════════════════════ -->
  <?php if ($tab === 'general'): ?>
  <form method="post" enctype="multipart/form-data" class="settings-form card">
    <input type="hidden" name="section" value="general">
    <h3 class="card-title">General Settings</h3>

    <div class="form-row">
      <div class="form-group">
        <label for="panel_name">Panel Name</label>
        <input type="text" id="panel_name" name="panel_name" value="<?= h($s['panel_name']) ?>" required>
      </div>
      <div class="form-group">
        <label for="panel_tagline">Tagline</label>
        <input type="text" id="panel_tagline" name="panel_tagline" value="<?= h($s['panel_tagline']) ?>">
      </div>
    </div>

    <div class="form-group">
      <label>Logo Type</label>
      <div class="radio-group">
        <label class="radio-label">
          <input type="radio" name="logo_type" value="emoji" <?= $s['logo_type']==='emoji'?'checked':'' ?>> Emoji
        </label>
        <label class="radio-label">
          <input type="radio" name="logo_type" value="image" <?= $s['logo_type']==='image'?'checked':'' ?>> Image URL
        </label>
        <label class="radio-label">
          <input type="radio" name="logo_type" value="upload" <?= $s['logo_type']==='upload'?'checked':'' ?>> Upload File
        </label>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group" id="logoEmojiGroup">
        <label for="logo_emoji">Logo Emoji</label>
        <input type="text" id="logo_emoji" name="logo_emoji" value="<?= h($s['logo_emoji']) ?>" maxlength="10">
      </div>
      <div class="form-group" id="logoUrlGroup">
        <label for="logo_url">Logo Image URL</label>
        <input type="text" id="logo_url" name="logo_url" value="<?= h($s['logo_url']) ?>" placeholder="https://...">
      </div>
      <div class="form-group" id="logoUploadGroup">
        <label for="logo_file">Upload Logo</label>
        <input type="file" id="logo_file" name="logo_file" accept="image/*">
        <?php if ($s['logo_url']): ?>
        <img src="<?= h($s['logo_url']) ?>" alt="logo" class="preview-img">
        <?php endif; ?>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="favicon_url">Favicon URL</label>
        <input type="text" id="favicon_url" name="favicon_url" value="<?= h($s['favicon_url']) ?>" placeholder="https://...">
      </div>
      <div class="form-group">
        <label for="favicon_file">Upload Favicon</label>
        <input type="file" id="favicon_file" name="favicon_file" accept="image/*,.ico,.svg">
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Save General Settings</button>
  </form>

  <!-- ═══════════════════════════════════ APPEARANCE ══════════════════════════════════ -->
  <?php elseif ($tab === 'appearance'): ?>
  <form method="post" class="settings-form card">
    <input type="hidden" name="section" value="appearance">
    <h3 class="card-title">Appearance Settings</h3>

    <div class="form-row">
      <div class="form-group">
        <label for="theme">Theme</label>
        <select id="theme" name="theme">
          <option value="dark"  <?= $s['theme']==='dark'?'selected':'' ?>>Dark</option>
          <option value="light" <?= $s['theme']==='light'?'selected':'' ?>>Light</option>
        </select>
      </div>
      <div class="form-group">
        <label for="accent_color">Accent Color</label>
        <div class="color-pick-row">
          <input type="color" id="accent_color" name="accent_color" value="<?= h($s['accent_color']) ?>">
          <span class="color-hex" id="accentHex"><?= h($s['accent_color']) ?></span>
        </div>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="panel_blur">Panel Blur (px) — <span id="blurVal"><?= h($s['panel_blur']) ?></span></label>
        <input type="range" id="panel_blur" name="panel_blur" min="0" max="40" step="1"
               value="<?= h($s['panel_blur']) ?>" oninput="document.getElementById('blurVal').textContent=this.value">
      </div>
      <div class="form-group">
        <label for="transparency">Glass Transparency (%) — <span id="transVal"><?= h($s['transparency']) ?></span></label>
        <input type="range" id="transparency" name="transparency" min="0" max="100" step="1"
               value="<?= h($s['transparency']) ?>" oninput="document.getElementById('transVal').textContent=this.value">
      </div>
      <div class="form-group">
        <label for="card_radius">Card Radius (px) — <span id="radVal"><?= h($s['card_radius']) ?></span></label>
        <input type="range" id="card_radius" name="card_radius" min="0" max="32" step="1"
               value="<?= h($s['card_radius']) ?>" oninput="document.getElementById('radVal').textContent=this.value">
      </div>
    </div>

    <div class="form-row toggles-row">
      <div class="form-group toggle-group">
        <label class="toggle-label">
          <input type="checkbox" name="transparent_bar" value="on" <?= ($s['transparent_bar']==='on')?'checked':'' ?>>
          <span class="toggle-slider"></span>
          Transparent Topbar
        </label>
      </div>
      <div class="form-group toggle-group">
        <label class="toggle-label">
          <input type="checkbox" name="blur_bar" value="on" <?= ($s['blur_bar']==='on')?'checked':'' ?>>
          <span class="toggle-slider"></span>
          Blur Topbar
        </label>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Appearance</button>
  </form>

  <!-- ═══════════════════════════════════ BACKGROUND ══════════════════════════════════ -->
  <?php elseif ($tab === 'background'): ?>
  <form method="post" enctype="multipart/form-data" class="settings-form card">
    <input type="hidden" name="section" value="background">
    <h3 class="card-title">Background Settings</h3>

    <div class="form-group">
      <label>Background Type</label>
      <div class="radio-group">
        <label class="radio-label"><input type="radio" name="background_type" value="image" <?= $s['background_type']==='image'?'checked':'' ?>> Image</label>
        <label class="radio-label"><input type="radio" name="background_type" value="video" <?= $s['background_type']==='video'?'checked':'' ?>> Video</label>
        <label class="radio-label"><input type="radio" name="background_type" value="none"  <?= $s['background_type']==='none'?'checked':'' ?>> None (solid dark)</label>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="background_url">Background URL</label>
        <input type="text" id="background_url" name="background_url" value="<?= h($s['background_url']) ?>" placeholder="https://...">
      </div>
      <div class="form-group">
        <label for="background_file">Or Upload Background</label>
        <input type="file" id="background_file" name="background_file" accept="image/*,video/mp4,video/webm">
        <?php if ($s['background_url']): ?>
        <p class="file-hint">Current: <code><?= h($s['background_url']) ?></code></p>
        <?php endif; ?>
      </div>
    </div>

    <div class="form-group">
      <label for="background_source">Wallpaper Source</label>
      <select id="background_source" name="background_source">
        <?php $sources = ['none'=>'None (solid dark)','custom'=>'Custom URL/Upload','cute-kawaii-wallpapers'=>'Cute Kawaii','ultrawide-monitor-hd-wallpapers'=>'Ultrawide HD','cool-wallpapers'=>'Cool Wallpapers','black-dark'=>'Black & Dark']; ?>
        <?php foreach ($sources as $val => $lbl): ?>
        <option value="<?= h($val) ?>" <?= $s['background_source']===$val?'selected':'' ?>><?= h($lbl) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group toggle-group">
      <label class="toggle-label">
        <input type="checkbox" name="background_overlay" value="on" <?= ($s['background_overlay']==='on')?'checked':'' ?>>
        <span class="toggle-slider"></span>
        Dark Overlay on Background
      </label>
    </div>

    <button type="submit" class="btn btn-primary">Save Background</button>
  </form>

  <!-- ═══════════════════════════════════ MUSIC ══════════════════════════════════ -->
  <?php elseif ($tab === 'music'): ?>
  <form method="post" class="settings-form card">
    <input type="hidden" name="section" value="music">
    <h3 class="card-title">Background Music</h3>

    <div class="form-group">
      <label for="music_type">Music Type</label>
      <select id="music_type" name="music_type">
        <option value="none" <?= $s['music_type']==='none'?'selected':'' ?>>Disabled</option>
        <option value="direct" <?= $s['music_type']==='direct'?'selected':'' ?>>Direct URL (mp3/ogg)</option>
      </select>
    </div>

    <div class="form-group">
      <label for="music_url">Music URL</label>
      <input type="text" id="music_url" name="music_url" value="<?= h($s['music_url']) ?>" placeholder="https://example.com/music.mp3">
    </div>

    <div class="form-group">
      <label for="music_volume">Volume (<?= h($s['music_volume']) ?>%) — <span id="volVal"><?= h($s['music_volume']) ?></span></label>
      <input type="range" id="music_volume" name="music_volume" min="0" max="100" step="1"
             value="<?= h($s['music_volume']) ?>" oninput="document.getElementById('volVal').textContent=this.value">
    </div>

    <button type="submit" class="btn btn-primary">Save Music Settings</button>
  </form>

  <!-- ═══════════════════════════════════ WIDGETS ══════════════════════════════════ -->
  <?php elseif ($tab === 'widgets'): ?>
  <form method="post" class="settings-form card">
    <input type="hidden" name="section" value="widgets">
    <h3 class="card-title">Widget Settings</h3>

    <h4 class="subheader">💬 Discord</h4>
    <div class="form-group toggle-group">
      <label class="toggle-label">
        <input type="checkbox" name="discord_enabled" value="on" <?= ($s['discord_enabled']==='on')?'checked':'' ?>>
        <span class="toggle-slider"></span>
        Enable Discord Widget
      </label>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="discord_server_id">Server ID</label>
        <input type="text" id="discord_server_id" name="discord_server_id" value="<?= h($s['discord_server_id']) ?>" placeholder="1234567890">
      </div>
      <div class="form-group">
        <label for="discord_channel">Channel ID (optional)</label>
        <input type="text" id="discord_channel" name="discord_channel" value="<?= h($s['discord_channel']) ?>">
      </div>
      <div class="form-group">
        <label for="discord_theme">Widget Theme</label>
        <select id="discord_theme" name="discord_theme">
          <option value="dark"  <?= $s['discord_theme']==='dark'?'selected':'' ?>>Dark</option>
          <option value="light" <?= $s['discord_theme']==='light'?'selected':'' ?>>Light</option>
        </select>
      </div>
    </div>

    <h4 class="subheader">▶️ YouTube</h4>
    <div class="form-group toggle-group">
      <label class="toggle-label">
        <input type="checkbox" name="youtube_enabled" value="on" <?= ($s['youtube_enabled']==='on')?'checked':'' ?>>
        <span class="toggle-slider"></span>
        Enable YouTube Section
      </label>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="youtube_channel">Channel Handle</label>
        <input type="text" id="youtube_channel" name="youtube_channel" value="<?= h($s['youtube_channel']) ?>" placeholder="@yourchannel">
      </div>
      <div class="form-group">
        <label for="youtube_api_key">YouTube API Key (optional)</label>
        <input type="text" id="youtube_api_key" name="youtube_api_key" value="<?= h($s['youtube_api_key']) ?>">
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Widget Settings</button>
  </form>

  <!-- ═══════════════════════════════════ SOCIAL ══════════════════════════════════ -->
  <?php elseif ($tab === 'social'): ?>
  <form method="post" class="settings-form card">
    <input type="hidden" name="section" value="social">
    <h3 class="card-title">Social Settings</h3>

    <div class="form-row">
      <div class="form-group">
        <label for="instagram_handle">Instagram Handle</label>
        <input type="text" id="instagram_handle" name="instagram_handle" value="<?= h($s['instagram_handle']) ?>" placeholder="@yourhandle">
      </div>
      <div class="form-group">
        <label for="github_username">GitHub Username</label>
        <input type="text" id="github_username" name="github_username" value="<?= h($s['github_username']) ?>">
      </div>
      <div class="form-group">
        <label for="github_token">GitHub Personal Access Token</label>
        <input type="password" id="github_token" name="github_token" value="<?= h($s['github_token']) ?>" placeholder="ghp_...">
        <small class="hint">Used to increase API rate limits for GitHub data</small>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Social Settings</button>
  </form>

  <!-- ═══════════════════════════════════ MAIL ══════════════════════════════════ -->
  <?php elseif ($tab === 'mail'): ?>
  <form method="post" class="settings-form card">
    <input type="hidden" name="section" value="mail">
    <h3 class="card-title">Email / SMTP Settings</h3>

    <div class="form-group toggle-group">
      <label class="toggle-label">
        <input type="checkbox" name="mail_enabled" value="on" <?= ($s['mail_enabled']==='on')?'checked':'' ?>>
        <span class="toggle-slider"></span>
        Enable Email Sending
      </label>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="smtp_host">SMTP Host</label>
        <input type="text" id="smtp_host" name="smtp_host" value="<?= h($s['smtp_host']) ?>" placeholder="smtp.gmail.com">
      </div>
      <div class="form-group">
        <label for="smtp_port">SMTP Port</label>
        <input type="number" id="smtp_port" name="smtp_port" value="<?= h($s['smtp_port']) ?>" placeholder="587">
      </div>
      <div class="form-group">
        <label for="smtp_secure">SMTP Secure</label>
        <select id="smtp_secure" name="smtp_secure">
          <option value="false" <?= $s['smtp_secure']==='false'?'selected':'' ?>>TLS (STARTTLS)</option>
          <option value="true"  <?= $s['smtp_secure']==='true'?'selected':'' ?>>SSL</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="smtp_user">SMTP Username</label>
        <input type="text" id="smtp_user" name="smtp_user" value="<?= h($s['smtp_user']) ?>" placeholder="you@gmail.com">
      </div>
      <div class="form-group">
        <label for="smtp_pass">SMTP Password</label>
        <input type="password" id="smtp_pass" name="smtp_pass" value="<?= h($s['smtp_pass']) ?>">
      </div>
    </div>

    <div class="form-group">
      <label for="mail_from">From Address</label>
      <input type="text" id="mail_from" name="mail_from" value="<?= h($s['mail_from']) ?>" placeholder="NobitaHost &lt;noreply@yoursite.com&gt;">
    </div>

    <button type="submit" class="btn btn-primary">Save Mail Settings</button>
  </form>

  <!-- ═══════════════════════════════════ PANEL ══════════════════════════════════ -->
  <?php elseif ($tab === 'panel'): ?>
  <form method="post" class="settings-form card">
    <input type="hidden" name="section" value="panel">
    <h3 class="card-title">Panel Behavior</h3>

    <div class="form-row toggles-row">
      <div class="form-group toggle-group">
        <label class="toggle-label">
          <input type="checkbox" name="register_open" value="on" <?= ($s['register_open']==='on')?'checked':'' ?>>
          <span class="toggle-slider"></span>
          Allow Public Registration
        </label>
      </div>
      <div class="form-group toggle-group">
        <label class="toggle-label">
          <input type="checkbox" name="maintenance" value="on" <?= ($s['maintenance']==='on')?'checked':'' ?>>
          <span class="toggle-slider"></span>
          Maintenance Mode <small>(Admins can still access)</small>
        </label>
      </div>
    </div>

    <h4 class="subheader">📋 Install Script</h4>
    <div class="form-group toggle-group">
      <label class="toggle-label">
        <input type="checkbox" name="script_enabled" value="on" <?= ($s['script_enabled']==='on')?'checked':'' ?>>
        <span class="toggle-slider"></span>
        Show Script on Home
      </label>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="script_badge">Script Badge Label</label>
        <input type="text" id="script_badge" name="script_badge" value="<?= h($s['script_badge']) ?>">
      </div>
      <div class="form-group">
        <label for="script_description">Script Description</label>
        <input type="text" id="script_description" name="script_description" value="<?= h($s['script_description']) ?>">
      </div>
    </div>
    <div class="form-group">
      <label for="script_command">Script Command</label>
      <textarea id="script_command" name="script_command" rows="3"><?= h($s['script_command']) ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Save Panel Settings</button>
  </form>
  <?php endif; ?>
</div>

<script>
// Logo type toggler
document.querySelectorAll('input[name="logo_type"]').forEach(r => {
  r.addEventListener('change', () => {
    document.getElementById('logoEmojiGroup').style.display  = (r.value === 'emoji')  ? '' : 'none';
    document.getElementById('logoUrlGroup').style.display    = (r.value === 'image')  ? '' : 'none';
    document.getElementById('logoUploadGroup').style.display = (r.value === 'upload') ? '' : 'none';
  });
});
// Accent color hex display
const ac = document.getElementById('accent_color');
if (ac) ac.addEventListener('input', () => document.getElementById('accentHex').textContent = ac.value);
// Initial logo type show/hide
const lt = document.querySelector('input[name="logo_type"]:checked');
if (lt) lt.dispatchEvent(new Event('change'));
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../views/layout.php';
