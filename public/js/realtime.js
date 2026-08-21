/**
 * Real-time Features
 * Online indicators, live notification polling, auto-refresh stats
 */

class RealTimeFeatures {
    constructor() {
        this.notificationPollInterval = null;
        this.statsRefreshInterval = null;
        this.onlineUsers = new Set();
        this.init();
    }

    init() {
        // Start notification polling
        this.startNotificationPolling();
        
        // Start stats auto-refresh
        this.startStatsRefresh();

        // Track user presence
        this.trackPresence();

        // Listen for visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopPolling();
            } else {
                this.startNotificationPolling();
                this.checkNotifications();
            }
        });
    }

    // ==================== NOTIFICATION POLLING ====================
    startNotificationPolling() {
        this.checkNotifications();
        this.notificationPollInterval = setInterval(() => this.checkNotifications(), 30000); // Every 30s
    }

    stopPolling() {
        if (this.notificationPollInterval) {
            clearInterval(this.notificationPollInterval);
            this.notificationPollInterval = null;
        }
    }

    async checkNotifications() {
        try {
            const response = await fetch('/notifications/check', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (response.ok) {
                const data = await response.json();
                this.updateNotificationBadge(data.count);
                
                if (data.latest && data.latest > 0) {
                    this.showNewNotificationAlert(data.latest);
                }
            }
        } catch (err) {
            // Silently fail - polling is best effort
        }
    }

    updateNotificationBadge(count) {
        const badges = document.querySelectorAll('.notification-badge');
        badges.forEach(badge => {
            badge.textContent = count > 9 ? '9+' : count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        });

        // Update sidebar notification dot
        const dots = document.querySelectorAll('.notification-dot');
        dots.forEach(dot => {
            dot.style.display = count > 0 ? 'block' : 'none';
        });
    }

    showNewNotificationAlert(count) {
        // Only show if user is on the page (not in notification page)
        if (window.location.pathname.includes('/notifications')) return;
        
        // Show toast for new notification
        if (window.toast) {
            toast.info(`You have ${count} new notification${count > 1 ? 's' : ''}`);
        }
    }

    // ==================== STATS AUTO-REFRESH ====================
    startStatsRefresh() {
        this.statsRefreshInterval = setInterval(() => this.refreshStats(), 60000); // Every 60s
    }

    async refreshStats() {
        if (window.location.pathname !== '/dashboard') return;
        
        try {
            const response = await fetch('/dashboard/stats', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (response.ok) {
                const data = await response.json();
                this.updateDashboardStats(data);
            }
        } catch (err) {
            // Silently fail
        }
    }

    updateDashboardStats(data) {
        // Update stat values with animation
        document.querySelectorAll('[data-count]').forEach(el => {
            const key = el.dataset.statKey;
            if (key && data[key] !== undefined) {
                const newVal = parseInt(data[key], 10);
                const oldVal = parseInt(el.textContent, 10);
                if (newVal !== oldVal) {
                    animateCounter(el, newVal, 800);
                    // Flash effect
                    el.style.color = '#0041C7';
                    setTimeout(() => el.style.color = '', 1000);
                }
            }
        });
    }

    // ==================== USER PRESENCE ====================
    trackPresence() {
        // Send heartbeat
        this.sendHeartbeat();
        setInterval(() => this.sendHeartbeat(), 60000);

        // Mark user as online on page load
        window.addEventListener('beforeunload', () => {
            navigator.sendBeacon('/presence/leave');
        });
    }

    async sendHeartbeat() {
        try {
            await fetch('/presence/heartbeat', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
        } catch (err) {
            // Silently fail
        }
    }

    // ==================== LIVE TIMESTAMP ====================
    startLiveTimestamps() {
        document.querySelectorAll('[data-timestamp]').forEach(el => {
            const update = () => {
                const time = el.dataset.timestamp;
                if (time) {
                    el.textContent = this.timeAgo(new Date(time));
                }
            };
            update();
            setInterval(update, 60000);
        });
    }

    timeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        
        const intervals = [
            { label: 'y', seconds: 31536000 },
            { label: 'mo', seconds: 2592000 },
            { label: 'w', seconds: 604800 },
            { label: 'd', seconds: 86400 },
            { label: 'h', seconds: 3600 },
            { label: 'm', seconds: 60 },
        ];

        for (const interval of intervals) {
            const count = Math.floor(seconds / interval.seconds);
            if (count > 0) {
                return `${count}${interval.label} ago`;
            }
        }
        return 'Just now';
    }

    // ==================== CLEANUP ====================
    destroy() {
        this.stopPolling();
        if (this.statsRefreshInterval) {
            clearInterval(this.statsRefreshInterval);
        }
    }
}

// Initialize
window.realTime = new RealTimeFeatures();
