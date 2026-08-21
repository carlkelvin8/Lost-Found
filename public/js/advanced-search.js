/**
 * Advanced Search with Saved Filters
 * Powerful search with filter presets, recent searches, and saved filters
 */

class AdvancedSearch {
    constructor() {
        this.recentSearches = JSON.parse(localStorage.getItem('recent_searches') || '[]');
        this.savedFilters = JSON.parse(localStorage.getItem('saved_filters') || '[]');
        this.currentFilters = {};
        this.init();
    }

    init() {
        // Keyboard shortcut: Ctrl+Shift+F for advanced search
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey && e.key === 'F') {
                e.preventDefault();
                this.showSearchModal();
            }
        });
    }

    showSearchModal() {
        let modal = document.getElementById('advanced-search-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'advanced-search-modal';
            modal.className = 'shortcuts-modal';
            document.body.appendChild(modal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.classList.remove('active');
            });
        }

        modal.innerHTML = `
            <div class="shortcuts-content" style="max-width:600px">
                <div class="shortcuts-title">
                    <i class="bi bi-search"></i> Advanced Search
                    <span style="font-size:0.75rem;color:#94a3b8;font-weight:400;margin-left:0.5rem">Ctrl+Shift+F</span>
                </div>

                <!-- Search Input -->
                <div style="position:relative;margin-bottom:1.5rem">
                    <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8"></i>
                    <input type="text" id="adv-search-input" placeholder="Search reports by title, description, item name..."
                        style="width:100%;padding:0.75rem 2.5rem 0.75rem 2.5rem;border:2px solid #e2e8f0;border-radius:12px;font-size:1rem;transition:border-color 0.2s"
                        onfocus="this.style.borderColor='#0041C7'" onblur="this.style.borderColor='#e2e8f0'"
                        oninput="advancedSearch.onSearchInput(this.value)">
                    <button onclick="advancedSearch.clearSearch()" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <!-- Recent Searches -->
                ${this.recentSearches.length ? `
                    <div style="margin-bottom:1.5rem">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem">
                            <span style="font-size:0.8125rem;font-weight:600;color:#64748b">Recent Searches</span>
                            <button onclick="advancedSearch.clearRecentSearches()" style="font-size:0.75rem;color:#94a3b8;background:none;border:none;cursor:pointer">Clear</button>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem">
                            ${this.recentSearches.slice(0, 6).map(s => `
                                <button onclick="advancedSearch.applySearch('${s.replace(/'/g, "\\'")}')"
                                    style="padding:0.375rem 0.75rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:0.8125rem;color:#475569;cursor:pointer;transition:all 0.2s"
                                    onmouseover="this.style.borderColor='#0041C7'" onmouseout="this.style.borderColor='#e2e8f0'">
                                    <i class="bi bi-clock-history" style="margin-right:0.25rem;font-size:0.75rem"></i>${s}
                                </button>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}

                <!-- Filter Options -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:600;color:#64748b;margin-bottom:0.375rem">Type</label>
                        <select id="adv-type" style="width:100%;padding:0.5rem;border:1px solid #e2e8f0;border-radius:8px;font-size:0.875rem;background:white">
                            <option value="">All Types</option>
                            <option value="lost">Lost</option>
                            <option value="found">Found</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:600;color:#64748b;margin-bottom:0.375rem">Status</label>
                        <select id="adv-status" style="width:100%;padding:0.5rem;border:1px solid #e2e8f0;border-radius:8px;font-size:0.875rem;background:white">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="matched">Matched</option>
                            <option value="claimed">Claimed</option>
                            <option value="returned">Returned</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:600;color:#64748b;margin-bottom:0.375rem">Category</label>
                        <select id="adv-category" style="width:100%;padding:0.5rem;border:1px solid #e2e8f0;border-radius:8px;font-size:0.875rem;background:white">
                            <option value="">All Categories</option>
                            ${window.categories?.map(c => `<option value="${c.id}">${c.name}</option>`).join('') || ''}
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:600;color:#64748b;margin-bottom:0.375rem">Date Range</label>
                        <select id="adv-date" style="width:100%;padding:0.5rem;border:1px solid #e2e8f0;border-radius:8px;font-size:0.875rem;background:white">
                            <option value="">Any Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="year">This Year</option>
                        </select>
                    </div>
                </div>

                <!-- Saved Filters -->
                <div style="margin-bottom:1.5rem">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem">
                        <span style="font-size:0.8125rem;font-weight:600;color:#64748b">Saved Filters</span>
                        <button onclick="advancedSearch.saveCurrentFilter()" style="font-size:0.75rem;color:#0041C7;background:none;border:none;cursor:pointer;font-weight:600">
                            <i class="bi bi-plus-lg"></i> Save Current
                        </button>
                    </div>
                    <div id="saved-filters-list" style="display:flex;flex-direction:column;gap:0.5rem">
                        ${this.savedFilters.length
                            ? this.savedFilters.map((f, i) => `
                                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0.75rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px">
                                    <button onclick="advancedSearch.applySavedFilter(${i})" style="flex:1;text-align:left;background:none;border:none;cursor:pointer;font-size:0.875rem;color:#1e293b;font-weight:500">
                                        <i class="bi bi-funnel" style="color:#0041C7;margin-right:0.5rem"></i>${f.name}
                                    </button>
                                    <button onclick="advancedSearch.deleteSavedFilter(${i})" style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:2px" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `).join('')
                            : '<div style="text-align:center;padding:1rem;color:#94a3b8;font-size:0.8125rem">No saved filters yet</div>'
                        }
                    </div>
                </div>

                <!-- Actions -->
                <div style="display:flex;gap:0.75rem;justify-content:flex-end">
                    <button onclick="document.getElementById('advanced-search-modal').classList.remove('active')" 
                        style="padding:0.5rem 1.25rem;background:#e2e8f0;color:#475569;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-family:inherit">
                        Cancel
                    </button>
                    <button onclick="advancedSearch.executeSearch()" 
                        style="padding:0.5rem 1.5rem;background:#0041C7;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-family:inherit">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>
        `;
        modal.classList.add('active');
        document.getElementById('adv-search-input')?.focus();
    }

    onSearchInput(value) {
        // Live search as user types (debounced)
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            if (value.length >= 2) {
                this.executeSearch();
            }
        }, 500);
    }

    executeSearch() {
        const params = new URLSearchParams();
        
        const query = document.getElementById('adv-search-input')?.value;
        const type = document.getElementById('adv-type')?.value;
        const status = document.getElementById('adv-status')?.value;
        const category = document.getElementById('adv-category')?.value;
        const date = document.getElementById('adv-date')?.value;

        if (query) params.set('q', query);
        if (type) params.set('type', type);
        if (status) params.set('status', status);
        if (category) params.set('category', category);
        if (date) params.set('date', date);

        // Save to recent searches
        if (query && query.length >= 2) {
            this.addRecentSearch(query);
        }

        // Navigate to search results
        window.location.href = `/reports?${params.toString()}`;
    }

    applySearch(query) {
        const input = document.getElementById('adv-search-input');
        if (input) input.value = query;
        this.executeSearch();
    }

    clearSearch() {
        const input = document.getElementById('adv-search-input');
        if (input) input.value = '';
    }

    // ==================== RECENT SEARCHES ====================
    addRecentSearch(query) {
        this.recentSearches = this.recentSearches.filter(s => s !== query);
        this.recentSearches.unshift(query);
        this.recentSearches = this.recentSearches.slice(0, 10);
        localStorage.setItem('recent_searches', JSON.stringify(this.recentSearches));
    }

    clearRecentSearches() {
        this.recentSearches = [];
        localStorage.removeItem('recent_searches');
        this.showSearchModal();
        toast.info('Recent searches cleared');
    }

    // ==================== SAVED FILTERS ====================
    saveCurrentFilter() {
        const name = prompt('Filter name:');
        if (!name) return;

        const filter = {
            name,
            type: document.getElementById('adv-type')?.value || '',
            status: document.getElementById('adv-status')?.value || '',
            category: document.getElementById('adv-category')?.value || '',
            date: document.getElementById('adv-date')?.value || ''
        };

        this.savedFilters.push(filter);
        localStorage.setItem('saved_filters', JSON.stringify(this.savedFilters));
        this.showSearchModal();
        toast.success('Filter saved!');
    }

    applySavedFilter(index) {
        const filter = this.savedFilters[index];
        if (!filter) return;

        const typeEl = document.getElementById('adv-type');
        const statusEl = document.getElementById('adv-status');
        const categoryEl = document.getElementById('adv-category');
        const dateEl = document.getElementById('adv-date');

        if (typeEl) typeEl.value = filter.type;
        if (statusEl) statusEl.value = filter.status;
        if (categoryEl) categoryEl.value = filter.category;
        if (dateEl) dateEl.value = filter.date;

        this.executeSearch();
    }

    deleteSavedFilter(index) {
        this.savedFilters.splice(index, 1);
        localStorage.setItem('saved_filters', JSON.stringify(this.savedFilters));
        this.showSearchModal();
        toast.info('Filter deleted');
    }
}

window.advancedSearch = new AdvancedSearch();
