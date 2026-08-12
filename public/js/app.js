/* ==========================================
   E-REKAP OPERASIONAL DASHBOARD JAVASCRIPT
   ========================================== */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Sidebar Toggle Functionality
  const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
  const body = document.body;

  if (sidebarToggleBtn) {
    sidebarToggleBtn.addEventListener('click', () => {
      if (window.innerWidth <= 768) {
        body.classList.toggle('sidebar-mobile-open');
      } else {
        body.classList.toggle('sidebar-collapsed');
      }
    });
  }

  // 2. Submenu Dropdown Handler
  const menuWithSub = document.querySelectorAll('.menu-item-has-sub > .menu-link');
  menuWithSub.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const parentItem = link.parentElement;
      parentItem.classList.toggle('open');
    });
  });

  // 3. User Profile Dropdown Handler
  const userProfile = document.getElementById('userProfileDropdown');
  const dropdownMenu = document.getElementById('profileMenu');

  if (userProfile && dropdownMenu) {
    userProfile.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdownMenu.classList.toggle('show');
    });

    document.addEventListener('click', () => {
      dropdownMenu.classList.remove('show');
    });
  }

  // 4. Fullscreen Toggle Handler
  const fullscreenBtn = document.getElementById('fullscreenBtn');
  if (fullscreenBtn) {
    fullscreenBtn.addEventListener('click', () => {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
          console.warn(`Error attempting to enable full-screen mode: ${err.message}`);
        });
      } else {
        if (document.exitFullscreen) {
          document.exitFullscreen();
        }
      }
    });
  }

  // 5. Light / Dark Mode Toggle
  const themeToggleBtn = document.getElementById('themeToggleBtn');
  const themeToggleIcon = document.getElementById('themeToggleIcon');

  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') {
    body.classList.add('dark-mode');
    if (themeToggleIcon) {
      themeToggleIcon.classList.replace('fa-moon', 'fa-sun');
    }
  }

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
      body.classList.toggle('dark-mode');
      const isDark = body.classList.contains('dark-mode');
      
      if (themeToggleIcon) {
        if (isDark) {
          themeToggleIcon.classList.replace('fa-moon', 'fa-sun');
        } else {
          themeToggleIcon.classList.replace('fa-sun', 'fa-moon');
        }
      }

      localStorage.setItem('theme', isDark ? 'dark' : 'light');

      // Update chart colors if operasional chart exists
      if (window.operasionalChartInstance) {
        const gridColor = isDark ? '#334155' : '#f0f4f8';
        const textColor = isDark ? '#94a3b8' : '#888888';
        window.operasionalChartInstance.options.scales.x.grid.color = gridColor;
        window.operasionalChartInstance.options.scales.x.ticks.color = textColor;
        window.operasionalChartInstance.options.scales.y.grid.color = gridColor;
        window.operasionalChartInstance.options.scales.y.ticks.color = textColor;
        window.operasionalChartInstance.options.plugins.legend.labels.color = textColor;
        window.operasionalChartInstance.update();
      }
    });
  }

  // 6. Close sidebar on mobile when clicking outside
  document.addEventListener('click', (e) => {
    if (window.innerWidth <= 768 && body.classList.contains('sidebar-mobile-open')) {
      const sidebar = document.getElementById('sidebar');
      if (sidebar && !sidebar.contains(e.target) && e.target !== sidebarToggleBtn) {
        body.classList.remove('sidebar-mobile-open');
      }
    }
  });
});
