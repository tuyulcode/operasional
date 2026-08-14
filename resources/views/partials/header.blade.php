{{-- TOP NAVBAR --}}
<style>
  .user-avatar-photo {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    display: block;
  }
</style>

<header class="top-navbar">
  <div class="navbar-left">
    <div class="search-box">
      <i class="fa-solid fa-magnifying-glass" style="color: #999;"></i>
      <input type="text" placeholder="Cari...">
    </div>
    <button class="navbar-action-btn" id="fullscreenBtn" title="Toggle Fullscreen">
      <i class="fa-solid fa-expand"></i>
    </button>
  </div>

  <div class="navbar-right">
    {{-- Theme Toggle Button --}}
    <button class="nav-icon-btn" id="themeToggleBtn" title="Toggle Light/Dark Theme">
      <i class="fa-regular fa-moon" id="themeToggleIcon"></i>
    </button>

    {{-- User Profile Dropdown --}}
    <div class="user-profile" id="userProfileDropdown">
      @if(Auth::user()->photo ?? false)
        <img src="{{ asset('storage/' . Auth::user()->photo) }}" alt="Foto Profil" class="user-avatar-photo">
      @else
        <div class="user-avatar-initial">
          {{ strtoupper(substr(Auth::user()->username ?? 'U', 0, 1)) }}
        </div>
      @endif
      <div class="user-info">
        <span class="user-name">{{ Auth::user()->username ?? 'User' }}</span>
        <span class="user-role">{{ ucfirst(Auth::user()->role ?? 'user') }}</span>
      </div>
      <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem; color: #888;"></i>

      <div class="dropdown-menu" id="profileMenu">
        <a href="{{ route('profile.index') }}" class="dropdown-item"><i class="fa-solid fa-user"></i> Profil Saya</a>
        <a href="#" class="dropdown-item"><i class="fa-solid fa-gear"></i> Pengaturan</a>
        <div class="dropdown-divider"></div>
        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
          @csrf
          <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer; color: var(--danger-color); font-family: inherit; font-size: inherit; padding: 10px 20px;">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
          </button>
        </form>
      </div>
    </div>
  </div>
</header>