@php
  $user = auth()->user();
  $roleNames = $user?->roles?->pluck('name')->toArray() ?? [];
  $isStaff = in_array('admin', $roleNames, true) || in_array('osa', $roleNames, true);
  $currentRoute = request()->route()->getName();
@endphp

<aside class="main-sidebar" id="mainSidebar">
  <div class="sidebar-content">
    <!-- Main Navigation -->
    <div class="sidebar-section">
      <div class="sidebar-section-title">Main</div>
      
      <a href="{{ route('dashboard') }}" class="sidebar-item {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
      </a>

      <a href="{{ route('reports.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'reports.') ? 'active' : '' }}">
        <i class="bi bi-inbox"></i>
        <span>Reports</span>
        @if(isset($pendingReportsCount) && $pendingReportsCount > 0)
          <span class="sidebar-badge">{{ $pendingReportsCount }}</span>
        @endif
      </a>

      <a href="{{ route('claims.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'claims.') ? 'active' : '' }}">
        <i class="bi bi-person-check"></i>
        <span>Claims</span>
      </a>

      <a href="{{ route('notifications.index') }}" class="sidebar-item sidebar-item-notification {{ str_starts_with($currentRoute, 'notifications.') ? 'active' : '' }}">
        <div class="sidebar-item-icon-wrapper">
          <i class="bi bi-bell"></i>
          <span class="notification-dot"></span>
        </div>
        <span>Notifications</span>
        <span class="sidebar-badge sidebar-badge-notification">3</span>
      </a>
    </div>

    @if($isStaff)
    <!-- Staff Section -->
    <div class="sidebar-section">
      <div class="sidebar-section-title">Staff</div>
      
      <a href="{{ route('matches.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'matches.') ? 'active' : '' }}">
        <i class="bi bi-diagram-2"></i>
        <span>Matches</span>
      </a>

      <a href="{{ route('users.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'users.') ? 'active' : '' }}">
        <i class="bi bi-people"></i>
        <span>Users</span>
      </a>
    </div>

    <!-- Management Section -->
    <div class="sidebar-section">
      <div class="sidebar-section-title">Management</div>
      
      <a href="{{ route('departments.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'departments.') ? 'active' : '' }}">
        <i class="bi bi-diagram-3"></i>
        <span>Departments</span>
      </a>

      <a href="{{ route('categories.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'categories.') ? 'active' : '' }}">
        <i class="bi bi-tags"></i>
        <span>Categories</span>
      </a>

      <a href="{{ route('locations.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'locations.') ? 'active' : '' }}">
        <i class="bi bi-geo-alt"></i>
        <span>Locations</span>
      </a>

      @if(in_array('admin', $roleNames, true))
      <a href="{{ route('roles.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'roles.') ? 'active' : '' }}">
        <i class="bi bi-shield"></i>
        <span>Roles</span>
      </a>

      <a href="{{ route('activity_logs.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'activity_logs.') ? 'active' : '' }}">
        <i class="bi bi-clock-history"></i>
        <span>Activity Logs</span>
      </a>
      @endif
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="sidebar-section">
      <div class="sidebar-section-title">Quick Actions</div>
      
      <a href="{{ route('reports.create') }}" class="sidebar-item sidebar-item-primary">
        <i class="bi bi-plus-circle"></i>
        <span>New Report</span>
      </a>
    </div>
  </div>

  <!-- Sidebar Footer -->
  <div class="sidebar-footer">
    <a href="{{ route('profile.edit') }}" class="sidebar-footer-item">
      <i class="bi bi-gear"></i>
      <span>Settings</span>
    </a>
  </div>
</aside>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
.main-sidebar {
  position: fixed;
  top: 70px;
  left: 0;
  width: 260px;
  height: calc(100vh - 70px);
  background: var(--bg-primary);
  border-right: 1px solid var(--border-default);
  display: flex;
  flex-direction: column;
  z-index: 999;
  transition: transform var(--transition-base);
  overflow-y: auto;
  overflow-x: hidden;
}

.sidebar-content {
  flex: 1;
  padding: var(--space-lg) 0;
}

.sidebar-section {
  margin-bottom: var(--space-xl);
}

.sidebar-section-title {
  padding: 0 var(--space-lg);
  margin-bottom: var(--space-sm);
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--text-muted);
}

.sidebar-item {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-md) var(--space-lg);
  color: var(--text-secondary);
  text-decoration: none;
  font-size: var(--text-sm);
  font-weight: 500;
  transition: var(--transition-fast);
  position: relative;
}

.sidebar-item:hover {
  background: var(--bg-hover);
  color: var(--text-primary);
}

.sidebar-item.active {
  background: var(--bg-tertiary);
  color: var(--text-primary);
  font-weight: 600;
}

.sidebar-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: var(--text-primary);
}

.sidebar-item i {
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

.sidebar-item span {
  flex: 1;
}

.sidebar-badge {
  padding: 0.125rem 0.5rem;
  background: var(--text-primary);
  color: var(--bg-primary);
  font-size: 0.7rem;
  font-weight: 700;
  border-radius: var(--radius-full);
  line-height: 1;
  min-width: 20px;
  text-align: center;
}

.sidebar-badge-notification {
  background: #ef4444;
  color: #ffffff;
  animation: pulse-badge 2s ease-in-out infinite;
}

@keyframes pulse-badge {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.9;
  }
}

.sidebar-item-notification {
  position: relative;
}

.sidebar-item-icon-wrapper {
  position: relative;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notification-dot {
  position: absolute;
  top: -2px;
  right: -2px;
  width: 8px;
  height: 8px;
  background: #ef4444;
  border: 2px solid var(--bg-primary);
  border-radius: 50%;
  animation: pulse-dot 2s ease-in-out infinite;
}

@keyframes pulse-dot {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  }
  50% {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0);
  }
}

.sidebar-item-notification:hover .notification-dot {
  animation: none;
}

.sidebar-item-notification.active .notification-dot {
  border-color: var(--bg-tertiary);
}

.sidebar-item-primary {
  margin: 0 var(--space-md);
  border-radius: var(--radius-md);
  background: var(--text-primary);
  color: var(--bg-primary);
  font-weight: 600;
}

.sidebar-item-primary:hover {
  background: var(--text-secondary);
  color: var(--bg-primary);
}

.sidebar-footer {
  padding: var(--space-lg);
  border-top: 1px solid var(--border-default);
}

.sidebar-footer-item {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-md);
  color: var(--text-secondary);
  text-decoration: none;
  font-size: var(--text-sm);
  font-weight: 500;
  border-radius: var(--radius-md);
  transition: var(--transition-fast);
}

.sidebar-footer-item:hover {
  background: var(--bg-hover);
  color: var(--text-primary);
}

.sidebar-footer-item i {
  font-size: 1.1rem;
}

.sidebar-overlay {
  position: fixed;
  top: 70px;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 998;
  opacity: 0;
  visibility: hidden;
  transition: var(--transition-base);
}

/* Mobile Styles */
@media (max-width: 991px) {
  .main-sidebar {
    transform: translateX(-100%);
  }

  .main-sidebar.show {
    transform: translateX(0);
  }

  .main-sidebar.show ~ .sidebar-overlay {
    opacity: 1;
    visibility: visible;
  }
}

/* Scrollbar for sidebar */
.main-sidebar::-webkit-scrollbar {
  width: 6px;
}

.main-sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.main-sidebar::-webkit-scrollbar-thumb {
  background: var(--border-default);
  border-radius: var(--radius-full);
}

.main-sidebar::-webkit-scrollbar-thumb:hover {
  background: var(--border-strong);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.getElementById('mainSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const sidebarToggle = document.getElementById('sidebarToggle');

  // Close sidebar when clicking overlay
  if (overlay) {
    overlay.addEventListener('click', function() {
      if (sidebar) {
        sidebar.classList.remove('show');
      }
    });
  }

  // Close sidebar when clicking a link on mobile
  if (sidebar && window.innerWidth <= 991) {
    const sidebarLinks = sidebar.querySelectorAll('.sidebar-item');
    sidebarLinks.forEach(link => {
      link.addEventListener('click', function() {
        sidebar.classList.remove('show');
      });
    });
  }
});
</script>
