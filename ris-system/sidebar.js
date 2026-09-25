// sidebar.js
// Handles toggle & search for sidebar
document.addEventListener('DOMContentLoaded', function () {
  const sidebar = document.getElementById('app-sidebar');
  const toggle = document.getElementById('sidebar-toggle');
  const searchInput = document.getElementById('sidebar-search');

  // Toggle collapse (desktop) or open/close (mobile) depending on width
  function isMobile() { return window.matchMedia('(max-width: 860px)').matches; }

  toggle.addEventListener('click', function (e) {
    if (isMobile()) {
      sidebar.classList.toggle('open');
      const expanded = sidebar.classList.contains('open');
      toggle.setAttribute('aria-expanded', expanded);
    } else {
      sidebar.classList.toggle('collapsed');
      const expanded = !sidebar.classList.contains('collapsed');
      toggle.setAttribute('aria-expanded', expanded);
      // store preference in localStorage
      try { localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed')); } catch (err) {}
    }
  });

  // Restore collapsed state
  try {
    const saved = localStorage.getItem('sidebar-collapsed');
    if (saved === 'true') sidebar.classList.add('collapsed');
  } catch (err) {}

  // Close mobile sidebar when clicking outside
  document.addEventListener('click', function (e) {
    if (isMobile() && sidebar.classList.contains('open')) {
      const inside = sidebar.contains(e.target) || toggle.contains(e.target);
      if (!inside) {
        sidebar.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    }
  });

  // Search/filter menu
  if (searchInput) {
    searchInput.addEventListener('input', function (e) {
      const q = e.target.value.trim().toLowerCase();
      const items = sidebar.querySelectorAll('.nav-item');
      items.forEach(item => {
        const text = (item.textContent || '').toLowerCase();
        if (!q || text.includes(q)) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    });

    // support keyboard escape to clear
    searchInput.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape') {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
        searchInput.blur();
      }
    });
  }
});