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
        <i class="bi bi-box-seam"></i>
        <span>Lost & Found</span>
      </a>
    </div>

    <!-- Search Bar (Desktop) -->
    <div class="navbar-search">
      <div class="search-wrapper">
        <i class="bi bi-search"></i>
        <input type="text" class="search-input" placeholder="Search reports, claims...">
        <kbd class="search-kbd">Ctrl K</kbd>
      </div>
    </div>

    <!-- Right Section -->
    <div class="navbar-actions">
      <!-- Notifications -->
      <a href="{{ route('notifications.index') }}" class="navbar-icon-btn" title="Notifications">
        <i class="bi bi-bell"></i>
        <span class="notification-badge">3</span>
      </a>

      <!-- User Menu -->
      <div class="navbar-user-menu">
        <button class="user-menu-trigger" type="button" id="userMenuTrigger">
          <div class="user-avatar">
            {{ strtoupper(substr($user->email ?? 'U', 0, 1)) }}
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

<style>
.main-navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 70px;
  background: var(--bg-primary);
  border-bottom: 1px solid var(--border-default);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  z-index: 1000;
}

.navbar-container {
  height: 100%;
  display: flex;
  align-items: center;
  gap: var(--space-lg);
  padding: 0 var(--space-xl);
  max-width: 100%;
}

.navbar-brand-section {
  display: flex;
  align-items: center;
  gap: var(--space-md);
}

.sidebar-toggle {
  width: 40px;
  height: 40px;
  border: none;
  background: transparent;
  color: var(--text-primary);
  font-size: 1.5rem;
  cursor: pointer;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition-fast);
}

.sidebar-toggle:hover {
  background: var(--bg-hover);
}

.navbar-brand {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  font-size: var(--text-xl);
  font-weight: 700;
  color: var(--text-primary);
  text-decoration: none;
  letter-spacing: -0.02em;
  transition: var(--transition-fast);
}

.navbar-brand:hover {
  color: var(--text-secondary);
}

.navbar-brand i {
  font-size: 1.5rem;
}

.navbar-search {
  flex: 1;
  max-width: 500px;
}

.search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-wrapper i {
  position: absolute;
  left: var(--space-md);
  color: var(--text-muted);
  font-size: var(--text-base);
}

.search-input {
  width: 100%;
  height: 44px;
  padding: 0 80px 0 44px;
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: var(--text-sm);
  transition: var(--transition-fast);
}

.search-input:focus {
  outline: none;
  border-color: var(--text-primary);
  background: var(--bg-primary);
  box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
}

.search-kbd {
  position: absolute;
  right: var(--space-md);
  padding: 0.25rem 0.5rem;
  background: var(--bg-primary);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-sm);
  font-size: 0.75rem;
  color: var(--text-muted);
  font-family: var(--font-mono);
}

.navbar-actions {
  display: flex;
  align-items: center;
  gap: var(--space-md);
}

.navbar-icon-btn {
  position: relative;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  font-size: 1.25rem;
  transition: var(--transition-fast);
  text-decoration: none;
}

.navbar-icon-btn:hover {
  background: var(--bg-hover);
  color: var(--text-primary);
}

.notification-badge {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 18px;
  height: 18px;
  background: var(--danger);
  color: white;
  font-size: 0.65rem;
  font-weight: 700;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--bg-primary);
}

.navbar-user-menu {
  position: relative;
}

.user-menu-trigger {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  padding: 0.375rem 0.75rem;
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  background: var(--bg-primary);
  cursor: pointer;
  transition: var(--transition-fast);
}

.user-menu-trigger:hover {
  background: var(--bg-hover);
  border-color: var(--border-strong);
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--text-primary);
  color: var(--bg-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: var(--text-sm);
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  text-align: left;
}

.user-name {
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--text-primary);
  line-height: 1.2;
}

.user-role {
  font-size: 0.75rem;
  color: var(--text-muted);
  line-height: 1.2;
}

.user-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 280px;
  background: var(--bg-primary);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: var(--transition-fast);
}

.user-dropdown.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-header {
  padding: var(--space-lg);
}

.dropdown-user-name {
  font-size: var(--text-base);
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
}

.dropdown-user-email {
  font-size: var(--text-sm);
  color: var(--text-muted);
}

.dropdown-divider {
  height: 1px;
  background: var(--border-default);
  margin: var(--space-sm) 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-md) var(--space-lg);
  color: var(--text-secondary);
  text-decoration: none;
  font-size: var(--text-sm);
  transition: var(--transition-fast);
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
}

.dropdown-item:hover {
  background: var(--bg-hover);
  color: var(--text-primary);
}

.dropdown-item i {
  font-size: 1.1rem;
  width: 20px;
}

.dropdown-item-danger {
  color: var(--danger);
}

.dropdown-item-danger:hover {
  background: var(--danger-bg);
  color: var(--danger);
}

@media (max-width: 768px) {
  .navbar-container {
    padding: 0 var(--space-md);
    gap: var(--space-md);
  }

  .navbar-search {
    display: none;
  }

  .user-info {
    display: none;
  }

  .user-menu-trigger {
    padding: 0.375rem;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const userMenuTrigger = document.getElementById('userMenuTrigger');
  const userDropdown = document.getElementById('userDropdown');
  const sidebarToggle = document.getElementById('sidebarToggle');

  // User menu toggle
  if (userMenuTrigger && userDropdown) {
    userMenuTrigger.addEventListener('click', function(e) {
      e.stopPropagation();
      userDropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!userMenuTrigger.contains(e.target) && !userDropdown.contains(e.target)) {
        userDropdown.classList.remove('show');
      }
    });
  }

  // Sidebar toggle
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
      const sidebar = document.getElementById('mainSidebar');
      if (sidebar) {
        sidebar.classList.toggle('show');
      }
    });
  }
});
</script>
