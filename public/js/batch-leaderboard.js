/**
 * Batch Operations
 * Bulk actions on multiple reports (delete, status change, export)
 */

class BatchManager {
    constructor() {
        this.selected = new Set();
        this.isSelectionMode = false;
    }

    toggleSelectionMode() {
        this.isSelectionMode = !this.isSelectionMode;
        this.selected.clear();
        this.updateUI();

        document.querySelectorAll('.batch-checkbox').forEach(cb => {
            cb.style.display = this.isSelectionMode ? 'block' : 'none';
            cb.checked = false;
        });

        if (!this.isSelectionMode) {
            document.querySelectorAll('.report-row').forEach(row => {
                row.classList.remove('selected');
            });
        }
    }

    toggleItem(id, checkbox) {
        if (this.selected.has(id)) {
            this.selected.delete(id);
            checkbox.closest('.report-row')?.classList.remove('selected');
        } else {
            this.selected.add(id);
            checkbox.closest('.report-row')?.classList.add('selected');
        }
        this.updateUI();
    }

    selectAll() {
        document.querySelectorAll('.batch-checkbox').forEach(cb => {
            const id = parseInt(cb.dataset.id, 10);
            this.selected.add(id);
            cb.checked = true;
            cb.closest('.report-row')?.classList.add('selected');
        });
        this.updateUI();
    }

    deselectAll() {
        this.selected.clear();
        document.querySelectorAll('.batch-checkbox').forEach(cb => {
            cb.checked = false;
            cb.closest('.report-row')?.classList.remove('selected');
        });
        this.updateUI();
    }

    updateUI() {
        const toolbar = document.getElementById('batch-toolbar');
        const countEl = document.getElementById('batch-count');
        
        if (toolbar) {
            toolbar.style.display = this.isSelectionMode && this.selected.size > 0 ? 'flex' : 'none';
        }
        if (countEl) {
            countEl.textContent = this.selected.size;
        }
    }

    async bulkDelete() {
        if (!confirm(`Delete ${this.selected.size} selected reports? This cannot be undone.`)) return;

        try {
            const response = await fetch('/reports/bulk-delete', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids: Array.from(this.selected) })
            });

            if (response.ok) {
                toast.success(`${this.selected.size} reports deleted`);
                this.selected.clear();
                this.updateUI();
                setTimeout(() => location.reload(), 1000);
            } else {
                toast.error('Failed to delete reports');
            }
        } catch (err) {
            toast.error('Network error');
        }
    }

    async bulkStatusChange(status) {
        if (!this.selected.size) return;

        try {
            const response = await fetch('/reports/bulk-status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids: Array.from(this.selected), status })
            });

            if (response.ok) {
                toast.success(`${this.selected.size} reports updated to ${status}`);
                this.selected.clear();
                this.updateUI();
                setTimeout(() => location.reload(), 1000);
            } else {
                toast.error('Failed to update reports');
            }
        } catch (err) {
            toast.error('Network error');
        }
    }

    bulkExport() {
        const ids = Array.from(this.selected);
        const url = `/reports/bulk-export?ids=${ids.join(',')}`;
        window.open(url, '_blank');
        toast.info('Export started');
    }

    bulkBookmark() {
        const ids = Array.from(this.selected);
        ids.forEach(id => {
            const title = document.querySelector(`[data-id="${id}"]`)?.dataset.title || `Report #${id}`;
            window.bookmarks?.toggle(id, title);
        });
        toast.success(`${ids.length} reports bookmarked`);
    }
}

window.batch = new BatchManager();

// ==================== LEADERBOARD ====================
class Leaderboard {
    constructor() {
        this.data = [];
    }

    async load() {
        try {
            const response = await fetch('/leaderboard', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (response.ok) {
                this.data = await response.json();
                this.render();
            }
        } catch (err) {
            console.error('Failed to load leaderboard:', err);
        }
    }

    render() {
        const container = document.getElementById('leaderboard-container');
        if (!container) return;

        const medals = ['🥇', '🥈', '🥉'];

        container.innerHTML = `
            <div style="display:flex;flex-direction:column;gap:0.75rem">
                ${this.data.length
                    ? this.data.map((user, i) => `
                        <div class="leaderboard-item" style="display:flex;align-items:center;gap:1rem;padding:1rem;background:white;border:1px solid #e2e8f0;border-radius:12px;transition:all 0.2s"
                            onmouseover="this.style.borderColor='#0041C7'" onmouseout="this.style.borderColor='#e2e8f0'">
                            <div style="width:40px;height:40px;border-radius:50%;background:${i < 3 ? ['rgba(255,215,0,0.1)', 'rgba(192,192,192,0.1)', 'rgba(205,127,50,0.1)'][i] : '#f1f5f9'};display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0">
                                ${i < 3 ? medals[i] : `<span style="font-weight:700;color:#64748b;font-size:0.9375rem">${i + 1}</span>`}
                            </div>
                            <div style="flex:1;min-width:0">
                                <div style="font-weight:600;color:#1e293b;font-size:0.9375rem">${user.name}</div>
                                <div style="font-size:0.8125rem;color:#94a3b8">${user.reports_count} reports · ${user.claims_count} claims</div>
                            </div>
                            <div style="text-align:right">
                                <div style="font-size:1.25rem;font-weight:700;color:${i < 3 ? '#0041C7' : '#64748b'}">${user.score || 0}</div>
                                <div style="font-size:0.75rem;color:#94a3b8">pts</div>
                            </div>
                        </div>
                    `).join('')
                    : '<div style="text-align:center;padding:3rem;color:#94a3b8">No leaderboard data yet</div>'
                }
            </div>
        `;
    }
}

window.leaderboard = new Leaderboard();
