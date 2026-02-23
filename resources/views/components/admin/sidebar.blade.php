@php
    $user = auth()->user();
    $roleNames = $user?->roles?->pluck('name')->toArray() ?? [];
    $isStaff = in_array('admin', $roleNames, true) || in_array('osa', $roleNames, true);
@endphp

@if($isStaff)
<!-- SweetAlert2 (only if not already loaded) -->
<script>
  if (typeof Swal === 'undefined') {
    document.write('<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">');
    document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
  }
</script>

<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <i class="bi bi-shield-lock"></i>
            <span>Admin Panel</span>
        </div>
    </div>

    <nav class="sidebar-nav">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>

        {{-- Reports --}}
        <div class="nav-section">Operations</div>

        <a href="{{ route('reports.index') }}"
           class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-inbox"></i>
            Reports
        </a>

        <a href="{{ route('claims.index') }}"
           class="nav-item {{ request()->routeIs('claims.*') ? 'active' : '' }}">
            <i class="bi bi-person-check"></i>
            Claims
        </a>

        <a href="{{ route('matches.index') }}"
           class="nav-item {{ request()->routeIs('matches.*') ? 'active' : '' }}">
            <i class="bi bi-link-45deg"></i>
            Matches
        </a>

        {{-- Users --}}
        <div class="nav-section">Management</div>

        <a href="{{ route('users.index') }}"
           class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            Users
        </a>

        <a href="{{ route('roles.index') }}"
           class="nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
            <i class="bi bi-shield"></i>
            Roles
        </a>

        {{-- Reference Tables --}}
        <a href="{{ route('departments.index') }}"
           class="nav-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i>
            Departments
        </a>

        <a href="{{ route('categories.index') }}"
           class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i>
            Categories
        </a>

        <a href="{{ route('locations.index') }}"
           class="nav-item {{ request()->routeIs('locations.*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt"></i>
            Locations
        </a>

        {{-- Logs --}}
        <div class="nav-section">System</div>

        <a href="{{ route('activity_logs.index') }}"
           class="nav-item {{ request()->routeIs('activity_logs.*') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i>
            Activity Logs
        </a>

        {{-- Profile --}}
        <a href="{{ route('profile.edit') }}"
           class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-circle"></i>
            My Profile
        </a>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" class="mt-3" id="logoutForm">
            @csrf
            <button class="nav-item logout-btn" type="button" onclick="confirmLogout()">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </button>
        </form>

    </nav>
</aside>

<script>
function confirmLogout() {
    Swal.fire({
        title: 'Logout?',
        text: "Are you sure you want to logout?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('logoutForm').submit();
        }
    })
}
</script>
@endif

<style>
.admin-sidebar {
    width: 260px;
    min-height: 100vh;
    background: var(--bg-secondary);
    color: var(--text-primary);
    position: fixed;
    top: 0;
    left: 0;
    padding: 1.5rem 1rem;
    border-right: 1px solid var(--border-default);
    box-shadow: var(--shadow-lg);
    z-index: 1000;
}

.sidebar-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-default);
}

.brand {
    display: flex;
    align-items: center;
    gap: .75rem;
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--text-primary);
}

.brand i {
    color: var(--accent-primary);
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: .25rem;
}

.nav-section {
    margin-top: 1.5rem;
    margin-bottom: .5rem;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-muted);
    font-weight: 700;
    padding: 0 0.85rem;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem .85rem;
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--text-secondary);
    background: transparent;
    border: none;
    text-align: left;
    font-size: 0.9rem;
    font-weight: 500;
    transition: var(--transition-fast);
    position: relative;
}

.nav-item i {
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

.nav-item:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}

.nav-item.active {
    background: var(--accent-primary);
    color: var(--text-primary);
    font-weight: 600;
}

.nav-item.active i {
    color: var(--text-primary);
}

.logout-btn {
    width: 100%;
    cursor: pointer;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-default);
    color: var(--danger);
}

.logout-btn:hover {
    background: var(--danger-bg);
    color: var(--danger);
}
</style>
