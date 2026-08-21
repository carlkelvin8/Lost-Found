/**
 * Advanced UI/UX Features
 * Language toggle (Filipino/English), Breadcrumbs, Infinite Scroll
 */

// ==================== LANGUAGE SYSTEM ====================
const translations = {
    en: {
        dashboard: 'Dashboard',
        reports: 'Reports',
        claims: 'Claims',
        notifications: 'Notifications',
        matches: 'Matches',
        users: 'Users',
        settings: 'Settings',
        search: 'Search reports, claims...',
        newReport: 'New Report',
        viewReports: 'View Reports',
        myClaims: 'My Claims',
        back: 'Back',
        save: 'Save',
        cancel: 'Cancel',
        delete: 'Delete',
        edit: 'Edit',
        submit: 'Submit',
        loading: 'Loading...',
        noResults: 'No results found',
        confirmDelete: 'Are you sure you want to delete this?',
        welcome: 'Welcome',
        goodMorning: 'Good morning',
        goodAfternoon: 'Good afternoon',
        goodEvening: 'Good evening',
        heresWhat: "Here's what's happening with your lost & found items",
        myReports: 'My Reports',
        pending: 'Pending',
        totalUsers: 'Total Users',
        totalReports: 'Total Reports',
        recentReports: 'Recent Reports',
        recentActivity: 'Recent Activity',
        viewAll: 'View all',
        noReportsYet: 'No reports yet',
        createFirst: 'Create your first report to get started',
        quickActions: 'Quick Actions',
        submitLostFound: 'Submit lost/found item',
        browseAll: 'Browse all reports',
        trackClaims: 'Track your claims',
        viewUpdates: 'View updates',
        analyticsOverview: 'Analytics Overview',
        reportsByType: 'Reports by Type',
        reportsByStatus: 'Reports by Status',
        lost: 'Lost',
        found: 'Found',
        matched: 'Matched',
        claimed: 'Claimed',
        returned: 'Returned',
        archived: 'Archived',
        suggested: 'Suggested',
        confirmed: 'Confirmed',
        rejected: 'Rejected'
    },
    fil: {
        dashboard: 'Dashboard',
        reports: 'Mga Ulat',
        claims: 'Mga Hclaim',
        notifications: 'Mga Abiso',
        matches: 'Mga Tugma',
        users: 'Mga Gumagamit',
        settings: 'Mga Setting',
        search: 'Maghanap ng ulat, claim...',
        newReport: 'Bagong Ulat',
        viewReports: 'Tingnan ang Mga Ulat',
        myClaims: 'Ang Mga Claim Ko',
        back: 'Bumalik',
        save: 'I-save',
        cancel: 'Kanselahin',
        delete: 'Tanggalin',
        edit: 'I-edit',
        submit: 'I-submit',
        loading: 'Naglo-load...',
        noResults: 'Walang resulta',
        confirmDelete: 'Sigurado ka bang gusto mong tanggalin ito?',
        welcome: 'Maligayang pagdating',
        goodMorning: 'Magandang umaga',
        goodAfternoon: 'Magandang hapon',
        goodEvening: 'Magandang gabi',
        heresWhat: 'Narito ang nangyayari sa iyong lost & found items',
        myReports: 'Ang Mga Ulat Ko',
        pending: 'Naghihintay',
        totalUsers: 'Kabuuang Gumagamit',
        totalReports: 'Kabuuang Ulat',
        recentReports: 'Kamakailang Mga Ulat',
        recentActivity: 'Kamakailang Aktibidad',
        viewAll: 'Tingnan lahat',
        noReportsYet: 'Wala pang mga ulat',
        createFirst: 'Gumawa ng iyong unang ulat para magsimula',
        quickActions: 'Mabilis na Aksyon',
        submitLostFound: 'I-submit ang lost/found item',
        browseAll: 'Tingnan ang lahat ng ulat',
        trackClaims: 'I-track ang iyong mga claim',
        viewUpdates: 'Tingnan ang mga update',
        analyticsOverview: 'Buod ng Analytics',
        reportsByType: 'Ulat ayon sa Uri',
        reportsByStatus: 'Ulat ayon sa Estado',
        lost: 'Nawala',
        found: 'Nahanap',
        matched: 'Na-match',
        claimed: 'Na-claim',
        returned: 'Naibalik',
        archived: 'Na-archive',
        suggested: 'Iminungkahing',
        confirmed: 'Kumpirmado',
        rejected: 'Tinanggihan'
    }
};

let currentLang = localStorage.getItem('language') || 'en';

function t(key) {
    return translations[currentLang]?.[key] || translations.en[key] || key;
}

function toggleLanguage() {
    currentLang = currentLang === 'en' ? 'fil' : 'en';
    localStorage.setItem('language', currentLang);
    updatePageLanguage();
    toast.info(currentLang === 'fil' ? 'Wika: Filipino' : 'Language: English');
}

function updatePageLanguage() {
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.dataset.i18n;
        el.textContent = t(key);
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
        const key = el.dataset.i18nPlaceholder;
        el.placeholder = t(key);
    });
    // Update language toggle button text
    const langBtn = document.getElementById('langToggle');
    if (langBtn) {
        langBtn.querySelector('span').textContent = currentLang === 'en' ? 'EN' : 'FIL';
    }
}

// ==================== BREADCRUMBS ====================
function generateBreadcrumbs() {
    const path = window.location.pathname;
    const segments = path.split('/').filter(s => s);
    const container = document.getElementById('breadcrumbs');
    if (!container) return;

    let html = '<a href="/dashboard" class="breadcrumb-item"><i class="bi bi-house"></i></a>';
    
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
        // Create sentinel element
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

    /* Language Toggle */
    .lang-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 4px 10px;
        background: var(--bg-primary, white);
        border: 1px solid var(--border-default, #e2e8f0);
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-secondary, #64748b);
        cursor: pointer;
        transition: all 0.2s;
        letter-spacing: 0.5px;
    }
    .lang-toggle:hover {
        border-color: #0041C7;
        color: #0041C7;
    }

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
