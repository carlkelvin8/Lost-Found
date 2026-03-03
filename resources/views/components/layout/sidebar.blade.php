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

<style>
/* ============================================
   MODERN SIDEBAR DESIGN - BLUE GRADIENT THEME
   ============================================ */

.main-sidebar {
  position: fixed;
  top: 70px;
  left: 0;
  width: 280px;
  height: calc(100vh - 70px);
  background: white;
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
  padding: var(--space-xl) var(--space-md);
}

/* Section Styling */
.sidebar-section {
  margin-bottom: var(--space-2xl);
}

.sidebar-section-title {
  padding: 0 var(--space-md);
  margin-bottom: var(--space-md);
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-muted);
}

/* Sidebar Items */
.sidebar-item {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-md) var(--space-md);
  margin-bottom: var(--space-xs);
  color: var(--text-secondary);
  text-decoration: none;
  font-size: var(--text-sm);
  font-weight: 500;
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
  position: relative;
}

.sidebar-item:hover {
  background: var(--bg-hover);
  color: var(--text-primary);
  transform: translateX(2px);
}

.sidebar-item.active {
  background: linear-gradient(135deg, rgba(58, 203, 235, 0.1) 0%, rgba(0, 65, 199, 0.1) 100%);
  color: #0041C7;
  font-weight: 600;
}

.sidebar-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 20px;
  background: linear-gradient(180deg, #3ACBEB 0%, #0041C7 100%);
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
}

/* Icon Styling */
.sidebar-item-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
  color: var(--text-secondary);
  font-size: 1rem;
  transition: all var(--transition-fast);
  flex-shrink: 0;
}

.sidebar-item:hover .sidebar-item-icon {
  background: var(--bg-tertiary);
  color: var(--text-primary);
}

.sidebar-item.active .sidebar-item-icon {
  background: #0041C7;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 65, 199, 0.25);
}

.sidebar-item-text {
  flex: 1;
  line-height: 1.4;
}

/* Badge Styling */
.sidebar-badge {
  padding: 0.25rem 0.5rem;
  background: #0041C7;
  color: white;
  font-size: 0.6875rem;
  font-weight: 700;
  border-radius: var(--radius-full);
  line-height: 1;
  min-width: 22px;
  text-align: center;
  box-shadow: 0 2px 6px rgba(0, 65, 199, 0.3);
}

.sidebar-item.active .sidebar-badge {
  background: linear-gradient(135deg, #1CA3DE 0%, #0160C9 100%);
  color: white;
}

/* Notification Dot */
.sidebar-item-icon-notification {
  position: relative;
}

.notification-dot {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 8px;
  height: 8px;
  background: #ef4444;
  border: 2px solid white;
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

.sidebar-item:hover .notification-dot {
  animation: none;
}

.sidebar-item.active .notification-dot {
  border-color: rgba(58, 203, 235, 0.3);
}

/* Call-to-Action Button */
.sidebar-item-cta {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-sm);
  padding: var(--space-md) var(--space-lg);
  background: #0041C7;
  color: white;
  text-decoration: none;
  font-size: var(--text-sm);
  font-weight: 600;
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
  box-shadow: 0 4px 12px rgba(0, 65, 199, 0.25);
}

.sidebar-item-cta:hover {
  background: #0160C9;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 65, 199, 0.35);
}

.sidebar-item-cta i {
  font-size: 1.125rem;
}

/* Sidebar Footer */
.sidebar-footer {
  padding: var(--space-lg);
  border-top: 1px solid var(--border-default);
  background: var(--bg-secondary);
}

.sidebar-footer-link {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-md);
  color: var(--text-secondary);
  text-decoration: none;
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
}

.sidebar-footer-link:hover {
  background: white;
  color: var(--text-primary);
}

.sidebar-footer-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-sm);
  background: white;
  color: var(--text-secondary);
  font-size: 1.125rem;
  flex-shrink: 0;
  transition: all var(--transition-fast);
}

.sidebar-footer-link:hover .sidebar-footer-icon {
  background: rgba(0, 65, 199, 0.1);
  color: #0041C7;
}

.sidebar-footer-text {
  flex: 1;
}

.sidebar-footer-label {
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--text-primary);
  line-height: 1.3;
}

.sidebar-footer-sublabel {
  font-size: 0.75rem;
  color: var(--text-muted);
  line-height: 1.3;
}

.sidebar-footer-arrow {
  font-size: 0.875rem;
  color: var(--text-muted);
  transition: transform var(--transition-fast);
}

.sidebar-footer-link:hover .sidebar-footer-arrow {
  transform: translateX(2px);
  color: #0041C7;
}

/* Sidebar Overlay */
.sidebar-overlay {
  position: fixed;
  top: 70px;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(2px);
  z-index: 998;
  opacity: 0;
  visibility: hidden;
  transition: all var(--transition-base);
}

/* Mobile Styles */
@media (max-width: 991px) {
  .main-sidebar {
    transform: translateX(-100%);
    box-shadow: var(--shadow-xl);
  }

  .main-sidebar.show {
    transform: translateX(0);
  }

  .main-sidebar.show ~ .sidebar-overlay {
    opacity: 1;
    visibility: visible;
  }
}

/* Scrollbar Styling */
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

/* Update content-wrap margin for new sidebar width */
@media (min-width: 992px) {
  .content-wrap {
    margin-left: 280px;
  }
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
