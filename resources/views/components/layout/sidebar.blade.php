@php
  $user = auth()->user();
  $roleNames = $user?->roles?->pluck('name')->toArray() ?? [];
  $isStaff = in_array('admin', $roleNames, true) || in_array('osa', $roleNames, true);
  $currentRoute = request()->route()->getName();
  $initial = strtoupper(substr($user->email ?? 'U', 0, 1));
@endphp

<aside class="main-sidebar" id="mainSidebar" role="complementary" aria-label="Sidebar navigation">
  <div class="sidebar-content">
    <!-- Main Navigation -->
    <div class="sidebar-section">
      <div class="sidebar-section-title" id="nav-heading">Navigation</div>

      <a href="{{ route('dashboard') }}" class="sidebar-item {{ $currentRoute === 'dashboard' ? 'active' : '' }}" aria-current="{{ $currentRoute === 'dashboard' ? 'page' : 'false' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-grid-fill" aria-hidden="true"></i>
        </div>
        <span class="sidebar-item-text">Dashboard</span>
      </a>

      <a href="{{ route('reports.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'reports.') ? 'active' : '' }}" aria-current="{{ str_starts_with($currentRoute, 'reports.') ? 'page' : 'false' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-inbox-fill" aria-hidden="true"></i>
        </div>
        <span class="sidebar-item-text">Reports</span>
        @if(isset($pendingReportsCount) && $pendingReportsCount > 0)
          <span class="sidebar-badge">{{ $pendingReportsCount }}</span>
        @endif
      </a>

      <a href="{{ route('claims.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'claims.') ? 'active' : '' }}" aria-current="{{ str_starts_with($currentRoute, 'claims.') ? 'page' : 'false' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-person-check-fill" aria-hidden="true"></i>
        </div>
        <span class="sidebar-item-text">Claims</span>
      </a>

      <a href="{{ route('notifications.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'notifications.') ? 'active' : '' }}" aria-current="{{ str_starts_with($currentRoute, 'notifications.') ? 'page' : 'false' }}">
        <div class="sidebar-item-icon sidebar-item-icon-notification">
          <i class="bi bi-bell-fill" aria-hidden="true"></i>
          <span class="notification-dot"></span>
        </div>
        <span class="sidebar-item-text">Notifications</span>
      </a>
    </div>

    @if($isStaff)
    <!-- Staff Tools -->
    <div class="sidebar-section">
      <div class="sidebar-section-title">Staff Tools</div>

      <a href="{{ route('matches.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'matches.') ? 'active' : '' }}" aria-current="{{ str_starts_with($currentRoute, 'matches.') ? 'page' : 'false' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-diagram-2-fill" aria-hidden="true"></i>
        </div>
        <span class="sidebar-item-text">Matches</span>
      </a>

      <a href="{{ route('users.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'users.') ? 'active' : '' }}" aria-current="{{ str_starts_with($currentRoute, 'users.') ? 'page' : 'false' }}">
        <div class="sidebar-item-icon">
          <i class="bi bi-people-fill" aria-hidden="true"></i>
        </div>
        <span class="sidebar-item-text">Users</span>
        <i class="bi bi-chevron-down sidebar-item-arrow"></i>
      </a>
    </div>

    <!-- Management (Collapsible) -->
    <div class="sidebar-section sidebar-section-collapsible">
      <div class="sidebar-section-title sidebar-section-toggle" id="managementToggle" role="button" tabindex="0">
        <div class="sidebar-section-title-left">
          <i class="bi bi-list sidebar-section-burger"></i>
          <span>Management</span>
        </div>
        <i class="bi bi-chevron-down sidebar-section-chevron" id="managementChevron"></i>
      </div>

      <div class="sidebar-section-items" id="managementItems" role="group" aria-labelledby="managementToggle">
        <a href="{{ route('departments.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'departments.') ? 'active' : '' }}" aria-current="{{ str_starts_with($currentRoute, 'departments.') ? 'page' : 'false' }}">
          <div class="sidebar-item-icon">
            <i class="bi bi-diagram-3-fill" aria-hidden="true"></i>
          </div>
          <span class="sidebar-item-text">Departments</span>
        </a>

        <a href="{{ route('categories.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'categories.') ? 'active' : '' }}" aria-current="{{ str_starts_with($currentRoute, 'categories.') ? 'page' : 'false' }}">
          <div class="sidebar-item-icon">
            <i class="bi bi-tags-fill" aria-hidden="true"></i>
          </div>
          <span class="sidebar-item-text">Categories</span>
        </a>

        <a href="{{ route('locations.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'locations.') ? 'active' : '' }}" aria-current="{{ str_starts_with($currentRoute, 'locations.') ? 'page' : 'false' }}">
          <div class="sidebar-item-icon">
            <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
          </div>
          <span class="sidebar-item-text">Locations</span>
        </a>

        @if(in_array('admin', $roleNames, true))
        <a href="{{ route('roles.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'roles.') ? 'active' : '' }}" aria-current="{{ str_starts_with($currentRoute, 'roles.') ? 'page' : 'false' }}">
          <div class="sidebar-item-icon">
            <i class="bi bi-shield-fill" aria-hidden="true"></i>
          </div>
          <span class="sidebar-item-text">Roles</span>
        </a>

        <a href="{{ route('activity_logs.index') }}" class="sidebar-item {{ str_starts_with($currentRoute, 'activity_logs.') ? 'active' : '' }}" aria-current="{{ str_starts_with($currentRoute, 'activity_logs.') ? 'page' : 'false' }}">
          <div class="sidebar-item-icon">
            <i class="bi bi-clock-history" aria-hidden="true"></i>
          </div>
          <span class="sidebar-item-text">Activity Logs</span>
        </a>
        @endif
      </div>
    </div>
    @endif

    <!-- Quick Actions Grid -->
    <nav class="sidebar-quick-grid" aria-label="Quick actions">
      <a href="{{ route('reports.create') }}" class="sidebar-quick-item" title="New Report" aria-label="Create new report">
        <div class="sidebar-quick-icon sidebar-quick-blue">
          <i class="bi bi-plus-circle" aria-hidden="true"></i>
        </div>
        <span class="sidebar-quick-label">Report</span>
      </a>
      <a href="{{ route('reports.index') }}" class="sidebar-quick-item" title="View Reports" aria-label="View all reports">
        <div class="sidebar-quick-icon sidebar-quick-violet">
          <i class="bi bi-inbox" aria-hidden="true"></i>
        </div>
        <span class="sidebar-quick-label">Reports</span>
      </a>
      <a href="{{ route('claims.index') }}" class="sidebar-quick-item" title="My Claims" aria-label="View my claims">
        <div class="sidebar-quick-icon sidebar-quick-emerald">
          <i class="bi bi-person-check" aria-hidden="true"></i>
        </div>
        <span class="sidebar-quick-label">Claims</span>
      </a>
      <a href="{{ route('notifications.index') }}" class="sidebar-quick-item" title="Notifications" aria-label="View notifications">
        <div class="sidebar-quick-icon sidebar-quick-rose">
          <i class="bi bi-bell" aria-hidden="true"></i>
        </div>
        <span class="sidebar-quick-label">Alerts</span>
      </a>
    </nav>
  </div>

  <!-- Footer -->
  <div class="sidebar-footer">
    <a href="{{ route('profile.edit') }}" class="sidebar-footer-link">
      <div class="sidebar-footer-icon">
        <i class="bi bi-gear"></i>
      </div>
      <div class="sidebar-footer-text">
        <div class="sidebar-footer-label">Settings</div>
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
  const managementToggle = document.getElementById('managementToggle');
  const managementItems = document.getElementById('managementItems');
  const managementChevron = document.getElementById('managementChevron');

  if (overlay) {
    overlay.addEventListener('click', function() {
      if (sidebar) sidebar.classList.remove('show');
    });
  }

  if (sidebar && window.innerWidth <= 991) {
    sidebar.querySelectorAll('.sidebar-item, .sidebar-item-cta, .sidebar-footer-link, .sidebar-quick-item').forEach(link => {
      link.addEventListener('click', function() {
        sidebar.classList.remove('show');
      });
    });
  }

  // Management collapsible section
  if (managementToggle && managementItems) {
    const savedState = localStorage.getItem('sidebar_management_collapsed');
    if (savedState === 'true') {
      managementItems.classList.add('collapsed');
      if (managementChevron) managementChevron.classList.add('collapsed');
    }

    managementToggle.addEventListener('click', function() {
      managementItems.classList.toggle('collapsed');
      if (managementChevron) managementChevron.classList.toggle('collapsed');
      localStorage.setItem('sidebar_management_collapsed', managementItems.classList.contains('collapsed'));
    });

    managementToggle.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        managementToggle.click();
      }
    });
  }
});
</script>
