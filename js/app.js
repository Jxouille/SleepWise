// ── Thème ────────────────────────────────────────────────────
const html = document.documentElement;
const saved = document.cookie.match(/theme=([^;]+)/)?.[1] || 'light';
html.setAttribute('data-theme', saved);

function setTheme(t) {
  html.setAttribute('data-theme', t);
  document.cookie = `theme=${t};path=/;max-age=31536000`;
  const lbl = document.getElementById('theme-label');
  if (lbl) lbl.textContent = t === 'dark' ? 'Désactiver' : 'Activer';
}

document.querySelectorAll('#theme-toggle').forEach(btn => {
  btn.addEventListener('click', () => {
    setTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
  });
});

const lbl = document.getElementById('theme-label');
if (lbl) lbl.textContent = saved === 'dark' ? 'Désactiver' : 'Activer';

// ── Sidebar ────────────────────────────────────────────────────
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebar-toggle');
const mainWrapper = document.querySelector('.main-wrapper');

if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', () => {
    const collapsed = sidebar.classList.toggle('collapsed');
    if (mainWrapper) mainWrapper.classList.toggle('full', collapsed);
  });
}
