/**
 * Report Comments/Notes System
 * Add notes to reports, status updates, internal comments
 */

class CommentManager {
    constructor(reportId) {
        this.reportId = reportId;
        this.comments = [];
    }

    async loadComments() {
        try {
            const response = await fetch(`/reports/${this.reportId}/comments`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (response.ok) {
                this.comments = await response.json();
                this.renderComments();
            }
        } catch (err) {
            console.error('Failed to load comments:', err);
        }
    }

    async addComment(text, type = 'comment') {
        try {
            const response = await fetch(`/reports/${this.reportId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ text, type })
            });

            if (response.ok) {
                const comment = await response.json();
                this.comments.unshift(comment);
                this.renderComments();
                toast.success('Comment added!');
                return true;
            }
        } catch (err) {
            toast.error('Failed to add comment');
        }
        return false;
    }

    async deleteComment(commentId) {
        if (!confirm('Delete this comment?')) return;

        try {
            const response = await fetch(`/reports/${this.reportId}/comments/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                this.comments = this.comments.filter(c => c.id !== commentId);
                this.renderComments();
                toast.success('Comment deleted');
            }
        } catch (err) {
            toast.error('Failed to delete comment');
        }
    }

    renderComments() {
        const container = document.getElementById('comments-container');
        if (!container) return;

        const html = this.comments.length
            ? this.comments.map(c => this.renderComment(c)).join('')
            : '<div style="text-align:center;padding:2rem;color:#94a3b8">No comments yet. Be the first to add one!</div>';

        container.innerHTML = `
            <div class="comments-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                <h3 style="font-size:1rem;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:0.5rem">
                    <i class="bi bi-chat-dots" style="color:#0041C7"></i>
                    Notes & Comments
                    <span style="font-size:0.75rem;background:#e2e8f0;color:#64748b;padding:2px 8px;border-radius:10px">${this.comments.length}</span>
                </h3>
            </div>
            <div class="comment-form" style="margin-bottom:1.5rem">
                <textarea id="comment-input" placeholder="Add a note or comment..." 
                    style="width:100%;padding:0.75rem;border:1px solid #e2e8f0;border-radius:10px;font-size:0.875rem;resize:vertical;min-height:80px;font-family:inherit;transition:border-color 0.2s"
                    onfocus="this.style.borderColor='#0041C7'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.5rem">
                    <select id="comment-type" style="padding:0.375rem 0.75rem;border:1px solid #e2e8f0;border-radius:6px;font-size:0.8125rem;color:#64748b">
                        <option value="comment">Comment</option>
                        <option value="note">Internal Note</option>
                        <option value="status_update">Status Update</option>
                    </select>
                    <button onclick="commentManager.submitComment()" 
                        style="padding:0.5rem 1.25rem;background:#0041C7;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:0.875rem;font-family:inherit">
                        <i class="bi bi-send"></i> Post
                    </button>
                </div>
            </div>
            <div class="comments-list" style="display:flex;flex-direction:column;gap:1rem">
                ${html}
            </div>
        `;
    }

    renderComment(comment) {
        const typeConfig = {
            'comment': { icon: 'bi-chat', color: '#64748b', bg: '#f8fafc' },
            'note': { icon: 'bi-sticky', color: '#f59e0b', bg: '#fffbeb' },
            'status_update': { icon: 'bi-arrow-repeat', color: '#10b981', bg: '#f0fdf4' }
        };
        const config = typeConfig[comment.type] || typeConfig.comment;
        const isOwn = comment.user_id === window.currentUserId;

        return `
            <div class="comment-item" style="display:flex;gap:0.75rem;padding:1rem;background:${config.bg};border:1px solid ${config.color}20;border-radius:12px">
                <div style="width:36px;height:36px;border-radius:10px;background:${config.color}15;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:${config.color}">
                    <i class="bi ${config.icon}"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <div style="font-size:0.8125rem;font-weight:600;color:#1e293b">${comment.user?.name || 'Unknown'}</div>
                        <div style="display:flex;align-items:center;gap:0.5rem">
                            <span style="font-size:0.6875rem;color:${config.color};font-weight:600;text-transform:uppercase;letter-spacing:0.5px">${comment.type?.replace('_', ' ')}</span>
                            ${isOwn ? `<button onclick="commentManager.deleteComment(${comment.id})" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:0.875rem;padding:2px" title="Delete"><i class="bi bi-trash"></i></button>` : ''}
                        </div>
                    </div>
                    <div style="font-size:0.875rem;color:#475569;margin-top:0.375rem;line-height:1.5;white-space:pre-wrap">${this.escapeHtml(comment.text)}</div>
                    <div style="font-size:0.75rem;color:#94a3b8;margin-top:0.5rem">${this.timeAgo(new Date(comment.created_at))}</div>
                </div>
            </div>
        `;
    }

    async submitComment() {
        const input = document.getElementById('comment-input');
        const typeSelect = document.getElementById('comment-type');
        
        if (!input?.value.trim()) {
            toast.warning('Please enter a comment');
            return;
        }

        const success = await this.addComment(input.value.trim(), typeSelect.value);
        if (success) {
            input.value = '';
            typeSelect.value = 'comment';
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    timeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        const intervals = [
            { label: 'year', seconds: 31536000 },
            { label: 'month', seconds: 2592000 },
            { label: 'week', seconds: 604800 },
            { label: 'day', seconds: 86400 },
            { label: 'hour', seconds: 3600 },
            { label: 'minute', seconds: 60 },
        ];
        for (const interval of intervals) {
            const count = Math.floor(seconds / interval.seconds);
            if (count > 0) return `${count} ${interval.label}${count > 1 ? 's' : ''} ago`;
        }
        return 'Just now';
    }
}
