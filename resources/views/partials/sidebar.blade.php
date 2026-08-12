{{-- SIDEBAR --}}
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <a href="{{ route('dashboard') }}" class="brand-logo">
      <i class="fa-solid fa-shapes"></i>
      <span class="brand-title">e-Rekap</span>
    </a>
    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Navigation">
      <i class="fa-solid fa-bars-staggered"></i>
    </button>
  </div>

  <div class="sidebar-menu">
    {{-- Category: Navigation --}}
    <div class="menu-category">Navigation</div>

    <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <a href="{{ route('dashboard') }}" class="menu-link">
        <div class="link-left">
          <i class="fa-solid fa-house link-icon"></i>
          <span class="link-text">Dashboard</span>
        </div>
      </a>
    </li>

    {{-- Category: Transaksi --}}
    <div class="menu-category">Transaksi</div>

    <li class="menu-item">
      <a href="#" class="menu-link">
        <div class="link-left">
          <i class="fa-solid fa-road link-icon"></i>
          <span class="link-text">Pemakaian E-Toll</span>
        </div>
      </a>
    </li>

    <li class="menu-item">
      <a href="#" class="menu-link">
        <div class="link-left">
          <i class="fa-solid fa-gas-pump link-icon"></i>
          <span class="link-text">Pemakaian BBM</span>
        </div>
      </a>
    </li>

    <li class="menu-item">
      <a href="#" class="menu-link">
        <div class="link-left">
          <i class="fa-solid fa-droplet link-icon"></i>
          <span class="link-text">Tagihan Air</span>
        </div>
      </a>
    </li>

    {{-- Category: Master Data --}}
    <div class="menu-category">Master Data</div>

    <li class="menu-item menu-item-has-sub">
      <a href="#" class="menu-link">
        <div class="link-left">
          <i class="fa-solid fa-car link-icon"></i>
          <span class="link-text">Kendaraan</span>
        </div>
        <i class="fa-solid fa-chevron-right chevron-icon"></i>
      </a>
      <ul class="submenu">
        <li><a href="#" class="submenu-link"><i class="fa-solid fa-angle-right"></i> Data Kendaraan</a></li>
        <li><a href="#" class="submenu-link"><i class="fa-solid fa-angle-right"></i> Jenis Kendaraan</a></li>
      </ul>
    </li>

    <li class="menu-item">
      <a href="#" class="menu-link">
        <div class="link-left">
          <i class="fa-solid fa-money-bill-wave link-icon"></i>
          <span class="link-text">Harga BBM</span>
        </div>
      </a>
    </li>

    <li class="menu-item menu-item-has-sub">
      <a href="#" class="menu-link">
        <div class="link-left">
          <i class="fa-solid fa-faucet-drip link-icon"></i>
          <span class="link-text">Data Air</span>
        </div>
        <i class="fa-solid fa-chevron-right chevron-icon"></i>
      </a>
      <ul class="submenu">
        <li><a href="#" class="submenu-link"><i class="fa-solid fa-angle-right"></i> Pengambil/Pemakai</a></li>
        <li><a href="#" class="submenu-link"><i class="fa-solid fa-angle-right"></i> Titik Meter</a></li>
        <li><a href="#" class="submenu-link"><i class="fa-solid fa-angle-right"></i> PPN</a></li>
      </ul>
    </li>

    {{-- Category: Pengaturan --}}
    <div class="menu-category">Pengaturan</div>

    <li class="menu-item">
      <a href="#" class="menu-link">
        <div class="link-left">
          <i class="fa-solid fa-users-gear link-icon"></i>
          <span class="link-text">Users</span>
        </div>
      </a>
    </li>
  </div>
</aside>
