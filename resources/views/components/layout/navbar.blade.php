@php
  $user = auth()->user();
  $roleNames = $user?->roles?->pluck('name')->toArray() ?? [];
  $isStaff = in_array('admin', $roleNames, true) || in_array('osa', $roleNames, true);
@endphp

<nav class="main-navbar">
  <div class="navbar-container">
    <!-- Left: Brand -->
    <div class="navbar-brand-section">
      <button class="sidebar-toggle" id="sidebarToggle" type="button">
        <i class="bi bi-list"></i>
      </button>
      <a class="navbar-brand" href="{{ route('dashboard') }}">
        <div class="navbar-logo">
          <img src="{{ asset('image.png') }}" alt="NAAP Logo">
        </div>
        <span class="navbar-brand-text">NAAP Lost & Found</span>
      </a>
    </div>

    <!-- Center: Search -->
    <div class="navbar-search">
      <form class="search-wrapper" id="navbarSearchForm" method="GET" action="{{ route('reports.index') }}">
        <i class="bi bi-search search-icon"></i>
        <input type="text" class="search-input" name="q" placeholder="Search reports, claims..." autocomplete="off">
        <kbd class="search-kbd">Ctrl K</kbd>
      </form>
    </div>

    <!-- Right: Actions -->
    <div class="navbar-actions">
      <!-- Language Toggle -->
      <button class="lang-toggle" id="langToggle" type="button" onclick="toggleLanguage()" aria-label="Toggle language">
        <i class="bi bi-translate"></i>
        <span>EN</span>
      </button>

      <!-- Bookmarks -->
      <button class="navbar-icon-btn" onclick="showBookmarksPanel()" title="Bookmarks" aria-label="Bookmarks" style="position:relative">
        <i class="bi bi-bookmark"></i>
        <span class="bookmark-count notification-badge" style="display:none">0</span>
      </button>

      <!-- Dark Mode Toggle -->
      <button class="dark-mode-toggle" id="darkModeToggle" type="button" aria-label="Toggle dark mode">
        <i class="bi bi-moon-fill"></i>
      </button>

      <!-- Notifications -->
      @php
        $unreadCount = $user ? \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->count() : 0;
      @endphp
      <a href="{{ route('notifications.index') }}" class="navbar-icon-btn {{ $unreadCount > 0 ? 'has-unread' : '' }}" title="Notifications" aria-label="Notifications{{ $unreadCount > 0 ? " ({$unreadCount} unread)" : '' }}">
        <i class="bi bi-bell" aria-hidden="true"></i>
        @if($unreadCount > 0)
          <span class="notification-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
      </a>

      <!-- User Menu -->
      <div class="navbar-user-menu">
        <button class="user-menu-trigger" type="button" id="userMenuTrigger">
          <div class="user-avatar">
            @if($user->profile && $user->profile->avatar_url)
              @php $avatarSrc = str_starts_with($user->profile->avatar_url, 'storage/') ? substr($user->profile->avatar_url, 8) : $user->profile->avatar_url; @endphp
              <img src="{{ asset($avatarSrc) }}" alt="{{ $user->profile->full_name ?? 'User' }}" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
              <span class="user-avatar-fallback" style="display:none">{{ strtoupper(substr($user->email ?? 'U', 0, 1)) }}</span>
            @else
              <span class="user-avatar-fallback">{{ strtoupper(substr($user->email ?? 'U', 0, 1)) }}</span>
            @endif
          </div>
          <div class="user-info">
            <span class="user-name">{{ $user->profile->full_name ?? 'User' }}</span>
            <span class="user-role">{{ $isStaff ? 'Staff' : 'Student' }}</span>
          </div>
          <i class="bi bi-chevron-down user-chevron"></i>
        </button>

        <!-- Dropdown -->
        <div class="user-dropdown" id="userDropdown">
          <div class="dropdown-header">
            <div class="dropdown-avatar">
              @if($user->profile && $user->profile->avatar_url)
                @php $avatarSrc = str_starts_with($user->profile->avatar_url, 'storage/') ? substr($user->profile->avatar_url, 8) : $user->profile->avatar_url; @endphp
                <img src="{{ asset($avatarSrc) }}" alt="" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                <span class="dropdown-avatar-fallback" style="display:none">{{ strtoupper(substr($user->email ?? 'U', 0, 1)) }}</span>
              @else
                <span class="dropdown-avatar-fallback">{{ strtoupper(substr($user->email ?? 'U', 0, 1)) }}</span>
              @endif
            </div>
            <div class="dropdown-user-info">
              <div class="dropdown-user-name">{{ $user->profile->full_name ?? 'User' }}</div>
              <div class="dropdown-user-email">{{ $user->email }}</div>
            </div>
          </div>
          <div class="dropdown-divider"></div>
          <a href="{{ route('profile.edit') }}" class="dropdown-item">
            <i class="bi bi-person"></i>
            <span>My Profile</span>
          </a>
          <a href="{{ route('reports.index') }}" class="dropdown-item">
            <i class="bi bi-inbox"></i>
            <span>My Reports</span>
          </a>
          <a href="{{ route('claims.index') }}" class="dropdown-item">
            <i class="bi bi-person-check"></i>
            <span>My Claims</span>
          </a>
          <div class="dropdown-divider"></div>
          <form method="POST" action="{{ route('logout') }}" id="logoutForm">
            @csrf
            <button
              type="submit"
              class="dropdown-item dropdown-item-danger"
              data-confirm="Are you sure you want to logout?"
              data-confirm-text="Logout"
              data-confirm-danger="true"
            >
              <i class="bi bi-box-arrow-right"></i>
              <span>Logout</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const userMenuTrigger = document.getElementById('userMenuTrigger');
  const userDropdown = document.getElementById('userDropdown');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const navbarSearchForm = document.getElementById('navbarSearchForm');
  const searchInput = document.querySelector('.search-input');

  if (userMenuTrigger && userDropdown) {
    userMenuTrigger.addEventListener('click', function(e) {
      e.stopPropagation();
      userDropdown.classList.toggle('show');
      userMenuTrigger.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
      if (!userMenuTrigger.contains(e.target) && !userDropdown.contains(e.target)) {
        userDropdown.classList.remove('show');
        userMenuTrigger.classList.remove('active');
      }
    });
  }

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
      const sidebar = document.getElementById('mainSidebar');
      const overlay = document.getElementById('sidebarOverlay');
      if (sidebar) {
        sidebar.classList.toggle('show');
        if (overlay) overlay.classList.toggle('show');
      }
    });
  }

  document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      if (searchInput) searchInput.focus();
    }
  });

  if (navbarSearchForm && searchInput) {
    navbarSearchForm.addEventListener('submit', function(e) {
      if (!searchInput.value.trim()) e.preventDefault();
    });
  }
});
</script>
