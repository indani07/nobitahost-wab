// ─────────────────────────────────────────────
//  NobitaHost PHP  |  public/js/app.js
// ─────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {

  // ─── Sidebar Toggle ───
  const sidebar       = document.getElementById('sidebar');
  const sidebarOpen   = document.getElementById('sidebarOpen');
  const sidebarClose  = document.getElementById('sidebarClose');
  const backdrop      = document.getElementById('sidebarBackdrop');

  function openSidebar() {
    sidebar?.classList.add('open');
    backdrop?.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar() {
    sidebar?.classList.remove('open');
    backdrop?.classList.remove('open');
    document.body.style.overflow = '';
  }
  sidebarOpen?.addEventListener('click', openSidebar);
  sidebarClose?.addEventListener('click', closeSidebar);
  backdrop?.addEventListener('click', closeSidebar);

  // ─── Auto-dismiss Flash Messages ───
  const flash = document.getElementById('flash-msg');
  if (flash) {
    setTimeout(() => {
      flash.style.opacity = '0';
      flash.style.transform = 'translateY(-8px)';
      flash.style.transition = 'all 0.4s ease';
      setTimeout(() => flash.remove(), 400);
    }, 4000);
  }

  // ─── Music Player ───
  const music  = document.getElementById('bgMusic');
  const toggle = document.getElementById('musicToggle');
  let playing  = false;

  if (music && toggle && window.APP?.musicEnabled) {
    music.volume = (window.APP?.musicVolume ?? 40) / 100;
    toggle.addEventListener('click', () => {
      if (playing) {
        music.pause();
        toggle.textContent = '🎵';
        playing = false;
      } else {
        music.play().then(() => {
          toggle.textContent = '⏸';
          playing = true;
        }).catch(() => {});
      }
    });
  }

  // ─── Active Nav Highlight ───
  const currentPage = new URLSearchParams(window.location.search).get('page');
  document.querySelectorAll('.nav-item').forEach(item => {
    const href = item.getAttribute('href') || '';
    if (href.includes(`page=${currentPage}`)) {
      item.classList.add('active');
    }
  });
});

// ─── Password Toggle ───
function togglePwd(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.type = el.type === 'password' ? 'text' : 'password';
}

// ─── Toast Notifications ───
function showToast(message, type = 'success') {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.textContent = message;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(8px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 2500);
}

// ─── Copy to Clipboard ───
function copyText(text) {
  navigator.clipboard.writeText(text)
    .then(() => showToast('Copied to clipboard!', 'success'))
    .catch(() => showToast('Copy failed', 'error'));
}
