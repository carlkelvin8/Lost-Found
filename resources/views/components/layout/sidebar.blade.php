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
      <div class="sidebar-section-title">Main Menu</div>
      
      <a href="{{ route('dashboard') }}" class="sidebar-item {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-grid-fill"></i>
        </div>
        <span class="sidebar-item-text">Dashboard</span>
      </a>

      <a href="{{ route('reports.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'reports.') ? 'active' : '' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-inbox-fill"></i>
        </div>
        <span class="sidebar-item-text">Reports</span>
        @if(isset($pendingReportsCount) && $pendingReportsCount > 0)
          <span class="sidebar-badge">{{ $pendingReportsCount }}</span>
        @endif
      </a>

      <a href="{{ route('claims.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'claims.') ? 'active' : '' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-person-check-fill"></i>
        </div>
        <span class="sidebar-item-text">Claims</span>
      </a>

      <a href="{{ route('notifications.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'notifications.') ? 'active' : '' }}">
        <div class="sidebar-item-icon sidebar-item-icon-notification">
          <i class="bi bi-bell-fill"></i>
          <span class="notification-dot"></span>
        </div>
        <span class="sidebar-item-text">Notifications</span>
      </a>
    </div>

    @if($isStaff)
    <!-- Staff Section -->
    <div class="sidebar-section">
      <div class="sidebar-section-title">Staff Tools</div>
      
      <a href="{{ route('matches.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'matches.') ? 'active' : '' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-diagram-2-fill"></i>
        </div>
        <span class="sidebar-item-text">Matches</span>
      </a>

      <a href="{{ route('users.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'users.') ? 'active' : '' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-people-fill"></i>
        </div>
        <span class="sidebar-item-text">Users</span>
      </a>
    </div>

    <!-- Management Section -->
    <div class="sidebar-section">
      <div class="sidebar-section-title">Management</div>
      
      <a href="{{ route('departments.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'departments.') ? 'active' : '' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-diagram-3-fill"></i>
        </div>
        <span class="sidebar-item-text">Departments</span>
      </a>

      <a href="{{ route('categories.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'categories.') ? 'active' : '' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-tags-fill"></i>
        </div>
        <span class="sidebar-item-text">Categories</span>
      </a>

      <a href="{{ route('locations.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'locations.') ? 'active' : '' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-geo-alt-fill"></i>
        </div>
        <span class="sidebar-item-text">Locations</span>
      </a>

      @if(in_array('admin', $roleNames, true))
      <a href="{{ route('roles.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'roles.') ? 'active' : '' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-shield-fill"></i>
        </div>
        <span class="sidebar-item-text">Roles</span>
      </a>

      <a href="{{ route('activity_logs.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'activity_logs.') ? 'active' : '' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-clock-history"></i>
        </div>
        <span class="sidebar-item-text">Activity Logs</span>
      </a>
      @endif
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="sidebar-section">
      <a href="{{ route('reports.create') }}" class="sidebar-item-cta">
        <i class="bi bi-plus-lg"></i>
        <span>Create New Report</span>
      </a>
    </div>
  </div>

  <!-- Sidebar Footer -->
  <div class="sidebar-footer">
    <a href="{{ route('profile.edit') }}" class="sidebar-footer-link">
      <div class="sidebar-footer-icon">
        <i class="bi bi-gear-fill"></i>
      </div>
      <div class="sidebar-footer-text">
        <div class="sidebar-footer-label">Settings</div>
        <div class="sidebar-footer-sublabel">Manage your account</div>
      </div>
      <i class="bi bi-chevron-right sidebar-footer-arrow"></i>
    </a>
  </div>
</aside>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

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
