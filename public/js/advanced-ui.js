/**
 * Advanced UI/UX Features
 * Breadcrumbs, Infinite Scroll
 */

// ==================== BREADCRUMBS ====================
function generateBreadcrumbs() {
    const path = window.location.pathname;
    const segments = path.split('/').filter(s => s);
    const container = document.getElementById('breadcrumbs');
    if (!container) return;

    let html = '<a href="' + window.appUrl('/dashboard') + '" class="breadcrumb-item"><i class="bi bi-house"></i></a>';
    
    const routeNames = {
        'dashboard': 'Dashboard',
        'reports': 'Reports',
        'claims': 'Claims',
        'notifications': 'Notifications',
        'matches': 'Matches',
        'users': 'Users',
        'departments': 'Departments',
        'categories': 'Categories',
        'locations': 'Locations',
        'roles': 'Roles',
        'profile': 'Profile',
        'create': 'Create',
        'edit': 'Edit'
    };

    segments.forEach((seg, i) => {
        const name = routeNames[seg] || seg;
        const isLast = i === segments.length - 1;
        html += `<span class="breadcrumb-sep">/</span>`;
        
        if (isLast) {
            html += `<span class="breadcrumb-item breadcrumb-active">${name}</span>`;
        } else {
            const href = '/' + segments.slice(0, i + 1).join('/');
            html += `<a href="${href}" class="breadcrumb-item">${name}</a>`;
        }
    });

    container.innerHTML = html;
}

// ==================== INFINITE SCROLL ====================
class InfiniteScroll {
    constructor(container, loadMore, options = {}) {
        this.container = container;
        this.loadMore = loadMore;
        this.loading = false;
        this.page = 1;
        this.threshold = options.threshold || 200;
        this.observer = null;
        this.sentinel = null;
        this.init();
    }

    init() {
        this.sentinel = document.createElement('div');
        this.sentinel.className = 'infinite-scroll-sentinel';
        this.sentinel.style.height = '1px';
        this.container.appendChild(this.sentinel);

        this.observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !this.loading) {
                this.load();
            }
        }, { rootMargin: `${this.threshold}px` });

        this.observer.observe(this.sentinel);
    }

    async load() {
        this.loading = true;
        this.showLoader();

        try {
            const hasMore = await this.loadMore(this.page);
            this.page++;
            
            if (!hasMore) {
                this.observer.disconnect();
                this.sentinel.innerHTML = '<div style="text-align:center;padding:1rem;color:#94a3b8;font-size:0.875rem">No more items</div>';
            }
        } catch (err) {
            console.error('Infinite scroll error:', err);
        } finally {
            this.loading = false;
            this.hideLoader();
        }
    }

    showLoader() {
        const loader = document.createElement('div');
        loader.className = 'infinite-scroll-loader';
        loader.innerHTML = '<div class="spinner"></div>';
        this.sentinel.innerHTML = '';
        this.sentinel.appendChild(loader);
    }

    hideLoader() {
        const loader = this.sentinel.querySelector('.infinite-scroll-loader');
        if (loader) loader.remove();
    }

    destroy() {
        if (this.observer) this.observer.disconnect();
    }
}

// Add styles
const uiStyles = document.createElement('style');
uiStyles.textContent = `
    /* Breadcrumbs */
    .breadcrumbs {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 0;
        margin-bottom: 1rem;
        font-size: 0.8125rem;
    }
    .breadcrumb-item {
        color: #64748b;
        text-decoration: none;
        transition: color 0.2s;
    }
    a.breadcrumb-item:hover { color: #0041C7; }
    .breadcrumb-active { color: #1e293b; font-weight: 600; }
    .breadcrumb-sep { color: #cbd5e1; }
    .breadcrumb-item i { font-size: 0.875rem; }

    /* Infinite Scroll */
    .infinite-scroll-loader {
        display: flex;
        justify-content: center;
        padding: 1.5rem;
    }
    .spinner {
        width: 32px;
        height: 32px;
        border: 3px solid #e2e8f0;
        border-top-color: #0041C7;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(uiStyles);
