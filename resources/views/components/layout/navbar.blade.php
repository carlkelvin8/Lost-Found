@php
  $user = auth()->user();
  $roleNames = $user?->roles?->pluck('name')->toArray() ?? [];
  $isStaff = in_array('admin', $roleNames, true) || in_array('osa', $roleNames, true);
@endphp

<nav class="main-navbar">
  <div class="navbar-container">
    <!-- Logo/Brand -->
    <div class="navbar-brand-section">
      <button class="sidebar-toggle" id="sidebarToggle" type="button">
        <i class="bi bi-list"></i>
      </button>
      <a class="navbar-brand" href="{{ route('dashboard') }}">
        <img src="{{ asset('storage/image.png') }}" alt="NAAP Logo" style="height: 40px; width: auto; object-fit: contain;">
        <span>NAAP Lost & Found</span>
      </a>
    </div>

    <!-- Search Bar (Desktop) -->
    <div class="navbar-search">
      <form class="search-wrapper" id="navbarSearchForm" method="GET" action="{{ route('reports.index') }}">
        <i class="bi bi-search"></i>
        <input type="text" class="search-input" name="q" placeholder="Search reports, claims..." autocomplete="off">
        <kbd class="search-kbd">Ctrl K</kbd>
      </form>
    </div>

    <!-- Right Section -->
    <div class="navbar-actions">
      <!-- Notifications -->
      @php
        $unreadCount = $user ? \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->count() : 0;
      @endphp
      <a href="{{ route('notifications.index') }}" class="navbar-icon-btn" title="Notifications">
        <i class="bi bi-bell"></i>
        @if($unreadCount > 0)
          <span class="notification-badge">{{ $unreadCount }}</span>
        @endif
      </a>

      <!-- User Menu -->
      <div class="navbar-user-menu">
        <button class="user-menu-trigger" type="button" id="userMenuTrigger">
          <div class="user-avatar">
            @if($user->profile && $user->profile->avatar_url)
              <img src="{{ asset($user->profile->avatar_url) }}" alt="{{ $user->profile->full_name ?? 'User' }}">
            @else
              {{ strtoupper(substr($user->email ?? 'U', 0, 1)) }}
            @endif
          </div>
          <div class="user-info">
            <div class="user-name">{{ $user->profile->full_name ?? 'User' }}</div>
            <div class="user-role">{{ $isStaff ? 'Staff' : 'Student' }}</div>
          </div>
          <i class="bi bi-chevron-down"></i>
        </button>

        <!-- Dropdown Menu -->
        <div class="user-dropdown" id="userDropdown">
          <div class="dropdown-header">
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

  // User menu toggle
  if (userMenuTrigger && userDropdown) {
    userMenuTrigger.addEventListener('click', function(e) {
      e.stopPropagation();
      userDropdown.classList.toggle('show');
      const userRole = userMenuTrigger.querySelector('.user-role');
      if (userRole) {
        userRole.style.display = userDropdown.classList.contains('show') ? 'none' : 'block';
      }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!userMenuTrigger.contains(e.target) && !userDropdown.contains(e.target)) {
        userDropdown.classList.remove('show');
        const userRole = userMenuTrigger.querySelector('.user-role');
        if (userRole) {
          userRole.style.display = 'block';
        }
      }
    });
  }

  // Sidebar toggle
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
      const sidebar = document.getElementById('mainSidebar');
      const overlay = document.getElementById('sidebarOverlay');
      if (sidebar) {
        sidebar.classList.toggle('show');
        if (overlay) {
          overlay.classList.toggle('show');
        }
      }
    });
  }

  // Ctrl+K Search Shortcut
  document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      if (searchInput) {
        searchInput.focus();
      }
    }
  });

  // Search form submission
  if (navbarSearchForm && searchInput) {
    navbarSearchForm.addEventListener('submit', function(e) {
      const query = searchInput.value.trim();
      if (!query) {
        e.preventDefault();
      }
    });
  }
});
</script>
