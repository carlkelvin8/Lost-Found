/**
 * Bookmarks & Compare Features
 * Favorite/bookmark reports, compare reports side-by-side
 */

// ==================== BOOKMARKS ====================
class BookmarkManager {
    constructor() {
        this.storageKey = 'report_bookmarks';
        this.bookmarks = this.load();
    }

    load() {
        try {
            return JSON.parse(localStorage.getItem(this.storageKey)) || [];
        } catch {
            return [];
        }
    }

    save() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.bookmarks));
        this.updateUI();
    }

    toggle(reportId, reportTitle) {
        const index = this.bookmarks.findIndex(b => b.id === reportId);
        if (index > -1) {
            this.bookmarks.splice(index, 1);
            toast.info('Bookmark removed');
        } else {
            this.bookmarks.push({ id: reportId, title: reportTitle, date: new Date().toISOString() });
            toast.success('Report bookmarked!');
        }
        this.save();
    }

    isBookmarked(reportId) {
        return this.bookmarks.some(b => b.id === reportId);
    }

    getAll() {
        return this.bookmarks;
    }

    clear() {
        this.bookmarks = [];
        this.save();
        toast.info('All bookmarks cleared');
    }

    updateUI() {
        document.querySelectorAll('.bookmark-btn').forEach(btn => {
            const id = parseInt(btn.dataset.reportId, 10);
            const icon = btn.querySelector('i');
            if (this.isBookmarked(id)) {
                btn.classList.add('bookmarked');
                icon.className = 'bi bi-bookmark-fill';
            } else {
                btn.classList.remove('bookmarked');
                icon.className = 'bi bi-bookmark';
            }
        });

        // Update bookmark count in sidebar
        const countEl = document.querySelector('.bookmark-count');
        if (countEl) {
            countEl.textContent = this.bookmarks.length;
            countEl.style.display = this.bookmarks.length > 0 ? 'flex' : 'none';
        }
    }
}

window.bookmarks = new BookmarkManager();

// ==================== BOOKMARK BUTTON ====================
function createBookmarkButton(reportId, reportTitle) {
    const isBookmarked = window.bookmarks.isBookmarked(reportId);
    return `
        <button class="bookmark-btn ${isBookmarked ? 'bookmarked' : ''}" 
                data-report-id="${reportId}" 
                data-report-title="${reportTitle}"
                onclick="bookmarks.toggle(${reportId}, '${reportTitle.replace(/'/g, "\\'")}')"
                aria-label="${isBookmarked ? 'Remove bookmark' : 'Add bookmark'}">
            <i class="bi bi-bookmark${isBookmarked ? '-fill' : ''}"></i>
        </button>
    `;
}

// ==================== BOOKMARKS PANEL ====================
function showBookmarksPanel() {
    const items = window.bookmarks.getAll();
    
    let modal = document.getElementById('bookmarks-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'bookmarks-modal';
        modal.className = 'shortcuts-modal';
        document.body.appendChild(modal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('active');
        });
    }

    const content = items.length ? items.map(item => `
        <a href="/reports/${item.id}" class="bookmark-item">
            <i class="bi bi-file-earmark-text" style="color:#0041C7"></i>
            <div style="flex:1;min-width:0">
                <div style="font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${item.title || 'Report #' + item.id}</div>
                <div style="font-size:0.75rem;color:#94a3b8">${new Date(item.date).toLocaleDateString()}</div>
            </div>
            <button onclick="event.preventDefault();event.stopPropagation();bookmarks.toggle(${item.id}, '${(item.title || '').replace(/'/g, "\\'")}')" 
                    style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px" aria-label="Remove bookmark">
                <i class="bi bi-x-lg"></i>
            </button>
        </a>
    `).join('') : '<div style="text-align:center;padding:2rem;color:#94a3b8"><i class="bi bi-bookmark" style="font-size:2rem;display:block;margin-bottom:0.5rem"></i>No bookmarks yet</div>';

    modal.innerHTML = `
        <div class="shortcuts-content" style="max-width:400px">
            <div class="shortcuts-title">
                <i class="bi bi-bookmark"></i> Bookmarks 
                ${items.length ? `<span style="font-size:0.75rem;background:#0041C7;color:white;padding:2px 8px;border-radius:10px;font-weight:600">${items.length}</span>` : ''}
            </div>
            <div style="max-height:400px;overflow-y:auto;display:flex;flex-direction:column;gap:0.5rem">
                ${content}
            </div>
            ${items.length ? `
                <div style="margin-top:1rem;text-align:center">
                    <button onclick="bookmarks.clear();showBookmarksPanel()" 
                            style="font-size:0.8125rem;color:#ef4444;background:none;border:none;cursor:pointer;font-weight:500">
                        Clear all bookmarks
                    </button>
                </div>
            ` : ''}
            <div style="margin-top:1rem;text-align:center">
                <button onclick="document.getElementById('bookmarks-modal').classList.remove('active')" 
                        style="padding:0.5rem 1.5rem;background:#e2e8f0;color:#475569;border:none;border-radius:8px;cursor:pointer;font-weight:600">
                    Close
                </button>
            </div>
        </div>
    `;
    modal.classList.add('active');
}

// ==================== COMPARE REPORTS ====================
class CompareManager {
    constructor() {
        this.maxCompare = 2;
        this.selected = [];
    }

    toggle(reportId) {
        const index = this.selected.indexOf(reportId);
        if (index > -1) {
            this.selected.splice(index, 1);
        } else if (this.selected.length < this.maxCompare) {
            this.selected.push(reportId);
            toast.info(`Selected ${this.selected.length}/${this.maxCompare} for comparison`);
        } else {
            toast.warning(`Maximum ${this.maxCompare} reports for comparison`);
            return;
        }
        this.updateUI();
    }

    isSelected(reportId) {
        return this.selected.includes(reportId);
    }

    clear() {
        this.selected = [];
        this.updateUI();
    }

    updateUI() {
        document.querySelectorAll('.compare-checkbox').forEach(cb => {
            const id = parseInt(cb.dataset.reportId, 10);
            cb.checked = this.isSelected(id);
            cb.closest('.compare-item')?.classList.toggle('selected', this.isSelected(id));
        });

        // Show/hide compare button
        const compareBtn = document.getElementById('compareBtn');
        if (compareBtn) {
            compareBtn.style.display = this.selected.length === 2 ? 'inline-flex' : 'none';
            compareBtn.querySelector('span').textContent = `Compare (${this.selected.length})`;
        }
    }

    async showCompare() {
        if (this.selected.length !== 2) {
            toast.warning('Select exactly 2 reports to compare');
            return;
        }

        // Fetch both reports
        try {
            const [report1, report2] = await Promise.all([
                fetch(`/reports/${this.selected[0]}`).then(r => r.text()),
                fetch(`/reports/${this.selected[1]}`).then(r => r.text())
            ]);

            let modal = document.getElementById('compare-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'compare-modal';
                modal.className = 'shortcuts-modal';
                document.body.appendChild(modal);
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) modal.classList.remove('active');
                });
            }

            modal.innerHTML = `
                <div class="shortcuts-content" style="max-width:800px">
                    <div class="shortcuts-title">
                        <i class="bi bi-columns-gap"></i> Compare Reports
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;max-height:60vh;overflow-y:auto">
                        <div style="background:#f8fafc;border-radius:12px;padding:1rem">${this.extractContent(report1)}</div>
                        <div style="background:#f8fafc;border-radius:12px;padding:1rem">${this.extractContent(report2)}</div>
                    </div>
                    <div style="margin-top:1.5rem;text-align:center">
                        <button onclick="compare.clear();document.getElementById('compare-modal').classList.remove('active')" 
                                style="padding:0.5rem 1.5rem;background:#e2e8f0;color:#475569;border:none;border-radius:8px;cursor:pointer;font-weight:600">
                            Close
                        </button>
                    </div>
                </div>
            `;
            modal.classList.add('active');
        } catch (err) {
            toast.error('Failed to load reports for comparison');
        }
    }

    extractContent(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const details = doc.querySelector('.detail-card');
        return details ? details.innerHTML : '<p>Could not load report details</p>';
    }
}

window.compare = new CompareManager();

// Add styles
const bookmarkStyles = document.createElement('style');
bookmarkStyles.textContent = `
    .bookmark-btn {
        background: none;
        border: none;
        padding: 6px;
        cursor: pointer;
        color: #94a3b8;
        border-radius: 8px;
        transition: all 0.2s;
        font-size: 1rem;
    }
    .bookmark-btn:hover {
        background: rgba(0,65,199,0.08);
        color: #0041C7;
    }
    .bookmark-btn.bookmarked {
        color: #0041C7;
    }
    .bookmark-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .bookmark-item:hover {
        border-color: #0041C7;
        transform: translateX(4px);
    }
    .compare-item {
        position: relative;
    }
    .compare-item.selected {
        outline: 2px solid #0041C7;
        outline-offset: 2px;
    }
    .compare-checkbox {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
`;
document.head.appendChild(bookmarkStyles);
