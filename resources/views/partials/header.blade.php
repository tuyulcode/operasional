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

  .theme-switch {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    flex-shrink: 0;
  }

  .theme-switch-track {
    position: relative;
    display: flex;
    align-items: center;
    width: 54px;
    height: 28px;
    border-radius: 999px;
    background: linear-gradient(135deg, #4099ff 0%, #2ed8b6 100%);
    box-shadow: 0 2px 8px rgba(64, 153, 255, 0.35);
    transition: background 0.3s ease;
  }

  .theme-switch-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25);
    transition: transform 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .theme-switch-icon {
    position: absolute;
    font-size: 0.7rem;
    transition: opacity 0.2s ease;
  }

  .theme-switch-icon.icon-sun {
    color: #f59e0b;
    opacity: 1;
  }

  .theme-switch-icon.icon-moon {
    color: #334155;
    opacity: 0;
  }

  body.dark-mode .theme-switch-track {
    background: linear-gradient(135deg, #334155 0%, #0f172a 100%);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.5);
  }

  body.dark-mode .theme-switch-thumb {
    transform: translateX(26px);
  }

  body.dark-mode .theme-switch-icon.icon-sun {
    opacity: 0;
  }

  body.dark-mode .theme-switch-icon.icon-moon {
    opacity: 1;
  }
</style>

<header class="top-navbar">
  <div class="navbar-right" style="margin-left: auto;">
    {{-- Theme Toggle Switch --}}
    <button class="theme-switch" id="themeToggleBtn" type="button" title="Toggle Light/Dark Theme">
      <span class="theme-switch-track">
        <span class="theme-switch-thumb">
          <i class="fa-solid fa-sun theme-switch-icon icon-sun"></i>
          <i class="fa-solid fa-moon theme-switch-icon icon-moon"></i>
        </span>
      </span>
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