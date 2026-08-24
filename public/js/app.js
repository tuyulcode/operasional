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

/* ==========================================
   TOAST NOTIFICATIONS
========================================== */
function showToast(message, type) {
  type = type || 'success';

  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = 'toast toast-' + type;

  const icon = document.createElement('div');
  icon.className = 'toast-icon';
  icon.innerHTML = type === 'error'
    ? '<i class="fa-solid fa-circle-xmark"></i>'
    : '<i class="fa-solid fa-circle-check"></i>';

  const body = document.createElement('div');
  body.className = 'toast-body';

  const title = document.createElement('div');
  title.className = 'toast-title';
  title.textContent = type === 'error' ? 'Gagal' : 'Berhasil';

  const msg = document.createElement('div');
  msg.className = 'toast-message';
  msg.textContent = message;

  body.appendChild(title);
  body.appendChild(msg);

  const close = document.createElement('button');
  close.type = 'button';
  close.className = 'toast-close';
  close.innerHTML = '<i class="fa-solid fa-xmark"></i>';
  close.title = 'Tutup';

  const progress = document.createElement('div');
  progress.className = 'toast-progress';

  const duration = 4000;
  toast.appendChild(icon);
  toast.appendChild(body);
  toast.appendChild(close);
  toast.appendChild(progress);
  container.appendChild(toast);

  const remove = function() {
    if (toast.classList.contains('toast-removing')) return;
    toast.classList.add('toast-removing');
    setTimeout(function() { toast.remove(); }, 300);
  };

  close.addEventListener('click', remove);
  progress.style.animationDuration = duration + 'ms';
  setTimeout(remove, duration);
}

/* ==========================================
   MONTH-YEAR PICKER COMPONENT
   ========================================== */

var MONTH_NAMES = [
  'Januari','Februari','Maret','April','Mei','Juni',
  'Juli','Agustus','September','Oktober','November','Desember'
];

function MonthYearPicker(opts) {
  this.hiddenInput = document.getElementById(opts.hiddenId);
  this.onChange = opts.onChange || function() {};
  this.currentYear = new Date().getFullYear();
  this.currentMonth = new Date().getMonth();
  this.selectedYear = null;
  this.selectedMonth = null;
  this.isOpen = false;
  this.view = 'month';
  this.yearPageStart = 0;

  var val = this.hiddenInput.value;
  if (val && /^\d{4}-\d{2}$/.test(val)) {
    this.selectedYear = parseInt(val.substring(0, 4), 10);
    this.selectedMonth = parseInt(val.substring(5, 7), 10) - 1;
    this.currentYear = this.selectedYear;
    this.currentMonth = this.selectedMonth;
  }

  this._build();
  this._bindEvents();
  this._updateDisplay();
}

MonthYearPicker.prototype._build = function() {
  var wrap = document.createElement('div');
  wrap.className = 'myp-wrap';

  var inp = document.createElement('input');
  inp.type = 'text';
  inp.className = 'myp-input';
  inp.readOnly = true;
  inp.placeholder = 'Pilih Bulan / Tahun';
  inp.setAttribute('autocomplete', 'off');

  var icon = document.createElement('span');
  icon.className = 'myp-icon';
  icon.innerHTML = '<i class="fa-solid fa-calendar-days"></i>';

  var popup = document.createElement('div');
  popup.className = 'myp-popup';

  var header = document.createElement('div');
  header.className = 'myp-header';

  var btnPrev = document.createElement('button');
  btnPrev.type = 'button';
  btnPrev.className = 'myp-header-btn';
  btnPrev.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';

  var label = document.createElement('span');
  label.className = 'myp-header-label';

  var btnNext = document.createElement('button');
  btnNext.type = 'button';
  btnNext.className = 'myp-header-btn';
  btnNext.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';

  header.appendChild(btnPrev);
  header.appendChild(label);
  header.appendChild(btnNext);

  var months = document.createElement('div');
  months.className = 'myp-months';

  var years = document.createElement('div');
  years.className = 'myp-years';
  years.style.display = 'none';

  popup.appendChild(header);
  popup.appendChild(months);
  popup.appendChild(years);

  wrap.appendChild(inp);
  wrap.appendChild(icon);
  wrap.appendChild(popup);

  this.hiddenInput.parentNode.insertBefore(wrap, this.hiddenInput);

  this.el = { wrap: wrap, inp: inp, popup: popup, label: label, months: months, years: years, btnPrev: btnPrev, btnNext: btnNext };
};

MonthYearPicker.prototype._bindEvents = function() {
  var self = this;

  this.el.inp.addEventListener('click', function(e) {
    e.stopPropagation();
    self.toggle();
  });

  this.el.btnPrev.addEventListener('click', function(e) {
    e.stopPropagation();
    if (self.view === 'month') {
      self.currentYear--;
      self._renderMonths();
    } else {
      self.yearPageStart -= 12;
      self._renderYears();
    }
  });

  this.el.btnNext.addEventListener('click', function(e) {
    e.stopPropagation();
    if (self.view === 'month') {
      self.currentYear++;
      self._renderMonths();
    } else {
      self.yearPageStart += 12;
      self._renderYears();
    }
  });

  this.el.label.addEventListener('click', function(e) {
    e.stopPropagation();
    if (self.view === 'month') {
      self._showYearPicker();
    }
  });

  document.addEventListener('click', function() {
    self.close();
  });

  this.el.popup.addEventListener('click', function(e) {
    e.stopPropagation();
  });
};

MonthYearPicker.prototype.toggle = function() {
  if (this.isOpen) { this.close(); } else { this.open(); }
};

MonthYearPicker.prototype.open = function() {
  this.el.popup.classList.add('myp-open');
  this.isOpen = true;
  this.view = 'month';
  this._renderMonths();
};

MonthYearPicker.prototype.close = function() {
  this.el.popup.classList.remove('myp-open');
  this.isOpen = false;
  this.view = 'month';
};

MonthYearPicker.prototype._showYearPicker = function() {
  this.view = 'year';
  this.yearPageStart = this.currentYear - 5;
  this.el.months.style.display = 'none';
  this.el.years.style.display = '';
  this._renderYears();
};

MonthYearPicker.prototype._showMonthPicker = function() {
  this.view = 'month';
  this.el.years.style.display = 'none';
  this.el.months.style.display = '';
  this._renderMonths();
};

MonthYearPicker.prototype._renderMonths = function() {
  var self = this;
  this.el.label.textContent = this.currentYear;
  this.el.months.innerHTML = '';

  var today = new Date();
  var thisYear = today.getFullYear();
  var thisMonth = today.getMonth();

  for (var i = 0; i < 12; i++) {
    (function(idx) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'myp-month-btn';
      btn.textContent = MONTH_NAMES[idx];

      if (idx === self.selectedMonth && self.currentYear === self.selectedYear) {
        btn.classList.add('myp-selected');
      }
      if (idx === thisMonth && self.currentYear === thisYear) {
        btn.classList.add('myp-today');
      }

      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        self.selectedMonth = idx;
        self.selectedYear = self.currentYear;
        self._updateDisplay();
        self._syncHidden();
        self.close();
        self.onChange(self.hiddenInput.value);
      });

      self.el.months.appendChild(btn);
    })(i);
  }
};

MonthYearPicker.prototype._renderYears = function() {
  var self = this;
  var start = this.yearPageStart;
  var end = start + 11;
  this.el.label.textContent = start + ' – ' + end;
  this.el.years.innerHTML = '';

  var today = new Date();
  var thisYear = today.getFullYear();

  for (var i = 0; i < 12; i++) {
    (function(idx) {
      var y = start + idx;
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'myp-year-btn';
      btn.textContent = y;

      if (y === self.selectedYear) {
        btn.classList.add('myp-selected');
      }
      if (y === thisYear) {
        btn.classList.add('myp-today');
      }

      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        self.currentYear = y;
        self._showMonthPicker();
      });

      self.el.years.appendChild(btn);
    })(i);
  }
};

MonthYearPicker.prototype._updateDisplay = function() {
  if (this.selectedMonth !== null && this.selectedYear !== null) {
    this.el.inp.value = MONTH_NAMES[this.selectedMonth] + ' ' + this.selectedYear;
  } else {
    this.el.inp.value = '';
  }
};

MonthYearPicker.prototype._syncHidden = function() {
  if (this.selectedMonth !== null && this.selectedYear !== null) {
    var m = String(this.selectedMonth + 1).padStart(2, '0');
    this.hiddenInput.value = this.selectedYear + '-' + m;
  } else {
    this.hiddenInput.value = '';
  }
};

MonthYearPicker.prototype.setValue = function(ym) {
  if (ym && /^\d{4}-\d{2}$/.test(ym)) {
    this.selectedYear = parseInt(ym.substring(0, 4), 10);
    this.selectedMonth = parseInt(ym.substring(5, 7), 10) - 1;
    this.currentYear = this.selectedYear;
    this.currentMonth = this.selectedMonth;
  } else {
    this.selectedYear = null;
    this.selectedMonth = null;
  }
  this._updateDisplay();
  this._syncHidden();
};
