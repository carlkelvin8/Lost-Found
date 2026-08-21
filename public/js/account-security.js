/**
 * Account Security Features
 * Login history, session management, activity timeline
 */

// ==================== LOGIN HISTORY ====================
function createLoginHistoryTable(history) {
    if (!history || !history.length) {
        return '<div style="text-align:center;padding:2rem;color:#94a3b8">No login history available</div>';
    }

    return `
        <div class="security-table">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="border-bottom:1px solid #e2e8f0">
                        <th style="text-align:left;padding:0.75rem;font-size:0.8125rem;color:#64748b;font-weight:600">Date & Time</th>
                        <th style="text-align:left;padding:0.75rem;font-size:0.8125rem;color:#64748b;font-weight:600">IP Address</th>
                        <th style="text-align:left;padding:0.75rem;font-size:0.8125rem;color:#64748b;font-weight:600">Device</th>
                        <th style="text-align:left;padding:0.75rem;font-size:0.8125rem;color:#64748b;font-weight:600">Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${history.map(h => `
                        <tr style="border-bottom:1px solid #f1f5f9">
                            <td style="padding:0.75rem;font-size:0.875rem;color:#1e293b">${new Date(h.created_at).toLocaleString()}</td>
                            <td style="padding:0.75rem;font-size:0.875rem;color:#64748b;font-family:monospace">${h.ip_address || 'N/A'}</td>
                            <td style="padding:0.75rem;font-size:0.8125rem;color:#64748b;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${parseUserAgent(h.user_agent)}</td>
                            <td style="padding:0.75rem">
                                <span style="display:inline-flex;align-items:center;gap:0.375rem;padding:0.2rem 0.625rem;border-radius:6px;font-size:0.75rem;font-weight:600;background:rgba(16,185,129,0.1);color:#059669">
                                    <i class="bi bi-check-circle-fill" style="font-size:0.625rem"></i> Success
                                </span>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

function parseUserAgent(ua) {
    if (!ua) return 'Unknown Device';
    
    let device = 'Unknown';
    let browser = 'Unknown';

    // Detect device
    if (ua.includes('Mobile') || ua.includes('Android')) device = 'Mobile';
    else if (ua.includes('Tablet') || ua.includes('iPad')) device = 'Tablet';
    else device = 'Desktop';

    // Detect browser
    if (ua.includes('Chrome')) browser = 'Chrome';
    else if (ua.includes('Firefox')) browser = 'Firefox';
    else if (ua.includes('Safari')) browser = 'Safari';
    else if (ua.includes('Edge')) browser = 'Edge';

    return `${device} · ${browser}`;
}

// ==================== SESSION MANAGEMENT ====================
function createSessionList(sessions, currentSessionId) {
    if (!sessions || !sessions.length) {
        return '<div style="text-align:center;padding:2rem;color:#94a3b8">No active sessions</div>';
    }

    return `
        <div style="display:flex;flex-direction:column;gap:0.75rem">
            ${sessions.map(s => `
                <div class="session-item" style="display:flex;align-items:center;gap:1rem;padding:1rem;background:${s.id === currentSessionId ? 'rgba(0,65,199,0.04)' : 'white'};border:1px solid ${s.id === currentSessionId ? 'rgba(0,65,199,0.2)' : '#e2e8f0'};border-radius:12px">
                    <div style="width:44px;height:44px;border-radius:12px;background:${s.id === currentSessionId ? 'rgba(0,65,199,0.1)' : '#f1f5f9'};display:flex;align-items:center;justify-content:center;color:${s.id === currentSessionId ? '#0041C7' : '#94a3b8'};font-size:1.25rem;flex-shrink:0">
                        <i class="bi bi-${getDeviceIcon(s.user_agent)}"></i>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:0.875rem;font-weight:600;color:#1e293b;display:flex;align-items:center;gap:0.5rem">
                            ${parseUserAgent(s.user_agent)}
                            ${s.id === currentSessionId ? '<span style="font-size:0.6875rem;background:#0041C7;color:white;padding:1px 6px;border-radius:4px">Current</span>' : ''}
                        </div>
                        <div style="font-size:0.8125rem;color:#94a3b8;margin-top:0.25rem">
                            <i class="bi bi-geo-alt" style="font-size:0.75rem"></i> ${s.ip_address || 'Unknown'} · ${timeAgo(new Date(s.last_activity || s.created_at))}
                        </div>
                    </div>
                    ${s.id !== currentSessionId ? `
                        <button onclick="revokeSession('${s.id}')" style="padding:0.5rem 1rem;background:rgba(239,68,68,0.08);color:#dc2626;border:none;border-radius:8px;font-size:0.8125rem;font-weight:600;cursor:pointer;transition:all 0.2s;font-family:inherit" onmouseover="this.style.background='rgba(239,68,68,0.15)'" onmouseout="this.style.background='rgba(239,68,68,0.08)'">
                            Revoke
                        </button>
                    ` : ''}
                </div>
            `).join('')}
        </div>
    `;
}

function getDeviceIcon(ua) {
    if (!ua) return 'device-desktop';
    if (ua.includes('Mobile') || ua.includes('Android')) return 'device-phone';
    if (ua.includes('Tablet') || ua.includes('iPad')) return 'device-tablet';
    return 'device-desktop';
}

async function revokeSession(sessionId) {
    if (!confirm('Revoke this session? The user will be logged out.')) return;
    
    try {
        const response = await fetch(`/sessions/${sessionId}/revoke`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            toast.success('Session revoked');
            setTimeout(() => location.reload(), 1000);
        } else {
            toast.error('Failed to revoke session');
        }
    } catch (err) {
        toast.error('Network error');
    }
}

// ==================== ACTIVITY TIMELINE ====================
function createActivityTimeline(activities) {
    if (!activities || !activities.length) {
        return '<div style="text-align:center;padding:2rem;color:#94a3b8">No recent activity</div>';
    }

    const actionIcons = {
        'login': { icon: 'bi-box-arrow-in-right', color: '#10b981' },
        'logout': { icon: 'bi-box-arrow-right', color: '#94a3b8' },
        'report.create': { icon: 'bi-file-earmark-plus', color: '#0041C7' },
        'report.update': { icon: 'bi-pencil', color: '#f59e0b' },
        'report.match': { icon: 'bi-link', color: '#8b5cf6' },
        'claim.create': { icon: 'bi-person-check', color: '#10b981' },
        'claim.approve': { icon: 'bi-check-circle', color: '#10b981' },
        'claim.reject': { icon: 'bi-x-circle', color: '#ef4444' },
        'password.change': { icon: 'bi-key', color: '#f59e0b' },
        'profile.update': { icon: 'bi-person', color: '#0041C7' }
    };

    return `
        <div class="activity-timeline" style="position:relative;padding-left:24px">
            <div style="position:absolute;left:11px;top:0;bottom:0;width:2px;background:#e2e8f0"></div>
            ${activities.map(a => {
                const config = actionIcons[a.action] || { icon: 'bi-circle', color: '#94a3b8' };
                return `
                    <div style="position:relative;padding-bottom:1.5rem;padding-left:20px">
                        <div style="position:absolute;left:-13px;top:4px;width:24px;height:24px;border-radius:50%;background:white;border:2px solid ${config.color};display:flex;align-items:center;justify-content:center;z-index:1">
                            <i class="bi ${config.icon}" style="font-size:0.625rem;color:${config.color}"></i>
                        </div>
                        <div style="font-size:0.875rem;color:#1e293b;font-weight:500">${formatAction(a.action)}</div>
                        <div style="font-size:0.8125rem;color:#94a3b8;margin-top:0.25rem">${timeAgo(new Date(a.created_at))}</div>
                        ${a.meta_json ? `<div style="font-size:0.75rem;color:#64748b;margin-top:0.25rem;background:#f8fafc;padding:0.375rem 0.625rem;border-radius:6px;font-family:monospace">${a.meta_json}</div>` : ''}
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

function formatAction(action) {
    const actions = {
        'login': 'Logged in',
        'logout': 'Logged out',
        'report.create': 'Created a report',
        'report.update': 'Updated a report',
        'report.match': 'Report was matched',
        'report.returned': 'Item was returned',
        'claim.create': 'Submitted a claim',
        'claim.approve': 'Claim was approved',
        'claim.reject': 'Claim was rejected',
        'password.change': 'Changed password',
        'profile.update': 'Updated profile'
    };
    return actions[action] || action.replace(/\./g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function timeAgo(date) {
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
        if (count > 0) {
            return `${count} ${interval.label}${count > 1 ? 's' : ''} ago`;
        }
    }
    return 'Just now';
}

// ==================== SECURITY SCORE ====================
function createSecurityScore(score) {
    const color = score >= 80 ? '#10b981' : score >= 50 ? '#f59e0b' : '#ef4444';
    const label = score >= 80 ? 'Strong' : score >= 50 ? 'Moderate' : 'Weak';
    const suggestions = [];
    
    if (score < 100) suggestions.push('Enable two-factor authentication');
    if (score < 80) suggestions.push('Use a stronger password');
    if (score < 60) suggestions.push('Review your login history');
    
    return `
        <div style="text-align:center;padding:1.5rem;background:white;border:1px solid #e2e8f0;border-radius:14px">
            <div style="position:relative;width:100px;height:100px;margin:0 auto 1rem">
                <svg width="100" height="100" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="42" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                    <circle cx="50" cy="50" r="42" fill="none" stroke="${color}" stroke-width="8"
                            stroke-dasharray="${2 * Math.PI * 42}" 
                            stroke-dashoffset="${2 * Math.PI * 42 * (1 - score / 100)}"
                            stroke-linecap="round" transform="rotate(-90 50 50)"
                            style="transition: stroke-dashoffset 1s ease-out"/>
                </svg>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center">
                    <div style="font-size:1.5rem;font-weight:700;color:${color}">${score}</div>
                </div>
            </div>
            <div style="font-size:0.875rem;font-weight:600;color:${color};margin-bottom:0.5rem">${label} Security</div>
            ${suggestions.length ? `
                <div style="text-align:left;margin-top:1rem">
                    ${suggestions.map(s => `
                        <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.8125rem;color:#64748b;padding:0.375rem 0">
                            <i class="bi bi-lightbulb" style="color:#f59e0b"></i> ${s}
                        </div>
                    `).join('')}
                </div>
            ` : '<div style="font-size:0.8125rem;color:#10b981"><i class="bi bi-check-circle"></i> Your account is secure!</div>'}
        </div>
    `;
}
