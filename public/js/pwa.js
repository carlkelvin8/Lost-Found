/**
 * PWA Features
 * Service worker registration, install prompt, offline indicator
 */

class PWAManager {
    constructor() {
        this.deferredPrompt = null;
        this.isOnline = navigator.onLine;
        this.swRegistration = null;
        this.init();
    }

    async init() {
        // Register service worker
        if ('serviceWorker' in navigator) {
            try {
                this.swRegistration = await navigator.serviceWorker.register('/sw.js');
                console.log('[PWA] Service Worker registered');
                
                // Listen for updates
                this.swRegistration.addEventListener('updatefound', () => {
                    const newWorker = this.swRegistration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'activated') {
                            toast.info('App updated! Refresh for latest version.');
                        }
                    });
                });
            } catch (err) {
                console.error('[PWA] SW registration failed:', err);
            }
        }

        // Listen for install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallBanner();
        });

        // Track online/offline status
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.hideOfflineIndicator();
            toast.success('You\'re back online!');
            this.syncPendingData();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.showOfflineIndicator();
            toast.warning('You\'re offline. Some features may be limited.');
        });

        // Check initial status
        if (!this.isOnline) {
            this.showOfflineIndicator();
        }

        // Request notification permission
        this.requestNotificationPermission();
    }

    // ==================== INSTALL PROMPT ====================
    showInstallBanner() {
        // Only show if not already installed
        if (window.matchMedia('(display-mode: standalone)').matches) return;
        
        const banner = document.createElement('div');
        banner.id = 'pwa-install-banner';
        banner.innerHTML = `
            <div style="position:fixed;bottom:0;left:0;right:0;background:white;border-top:1px solid #e2e8f0;padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;z-index:9999;box-shadow:0 -4px 20px rgba(0,0,0,0.1)">
                <div style="display:flex;align-items:center;gap:0.75rem">
                    <div style="width:40px;height:40px;background:rgba(0,65,199,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center">
                        <i class="bi bi-phone" style="color:#0041C7;font-size:1.125rem"></i>
                    </div>
                    <div>
                        <div style="font-weight:600;color:#1e293b">Install NAAP Lost & Found</div>
                        <div style="font-size:0.8125rem;color:#94a3b8">Add to your home screen for quick access</div>
                    </div>
                </div>
                <div style="display:flex;gap:0.5rem">
                    <button onclick="pwa.dismissInstall()" style="padding:0.5rem 1rem;background:none;border:1px solid #e2e8f0;border-radius:8px;color:#64748b;cursor:pointer;font-weight:500;font-family:inherit">Later</button>
                    <button onclick="pwa.install()" style="padding:0.5rem 1.25rem;background:#0041C7;border:none;border-radius:8px;color:white;cursor:pointer;font-weight:600;font-family:inherit">Install</button>
                </div>
            </div>
        `;
        document.body.appendChild(banner);
    }

    async install() {
        if (!this.deferredPrompt) return;
        
        this.deferredPrompt.prompt();
        const { outcome } = await this.deferredPrompt.userChoice;
        
        if (outcome === 'accepted') {
            toast.success('App installed successfully!');
        }
        
        this.deferredPrompt = null;
        this.hideInstallBanner();
    }

    dismissInstall() {
        this.hideInstallBanner();
        sessionStorage.setItem('pwa-install-dismissed', 'true');
    }

    hideInstallBanner() {
        const banner = document.getElementById('pwa-install-banner');
        if (banner) banner.remove();
    }

    // ==================== OFFLINE INDICATOR ====================
    showOfflineIndicator() {
        let indicator = document.getElementById('offline-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'offline-indicator';
            indicator.innerHTML = `
                <div style="position:fixed;top:0;left:0;right:0;background:#f59e0b;color:#92400e;padding:0.5rem;text-align:center;font-size:0.8125rem;font-weight:600;z-index:10000;display:flex;align-items:center;justify-content:center;gap:0.5rem">
                    <i class="bi bi-wifi-off"></i>
                    You're offline - some features may be limited
                </div>
            `;
            document.body.appendChild(indicator);
        }
    }

    hideOfflineIndicator() {
        const indicator = document.getElementById('offline-indicator');
        if (indicator) indicator.remove();
    }

    // ==================== OFFLINE STORAGE ====================
    async saveOfflineReport(reportData) {
        try {
            const db = await this.openDB();
            const tx = db.transaction('pending-reports', 'readwrite');
            await tx.objectStore('pending-reports').add({
                data: reportData,
                csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
                timestamp: new Date().toISOString()
            });
            toast.info('Report saved offline. Will sync when online.');
        } catch (err) {
            console.error('[PWA] Failed to save offline:', err);
        }
    }

    async syncPendingData() {
        if (!navigator.onLine) return;
        
        try {
            const db = await this.openDB();
            const tx = db.transaction('pending-reports', 'readwrite');
            const store = tx.objectStore('pending-reports');
            const request = store.getAll();
            
            request.onsuccess = async () => {
                const reports = request.result;
                for (const report of reports) {
                    try {
                        const formData = new FormData();
                        Object.keys(report.data).forEach(key => {
                            formData.append(key, report.data[key]);
                        });

                        const response = await fetch('/reports', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': report.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            const deleteTx = db.transaction('pending-reports', 'readwrite');
                            await deleteTx.objectStore('pending-reports').delete(report.id);
                            toast.success('Offline report synced!');
                        }
                    } catch (err) {
                        console.error('[PWA] Sync failed:', err);
                    }
                }
            };
        } catch (err) {
            console.error('[PWA] Sync error:', err);
        }
    }

    openDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open('naap-lf-offline', 1);
            request.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains('pending-reports')) {
                    db.createObjectStore('pending-reports', { keyPath: 'id', autoIncrement: true });
                }
            };
            request.onsuccess = (e) => resolve(e.target.result);
            request.onerror = (e) => reject(e);
        });
    }

    // ==================== NOTIFICATIONS ====================
    async requestNotificationPermission() {
        if (!('Notification' in window)) return;
        
        if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                console.log('[PWA] Notifications enabled');
            }
        }
    }

    async subscribeToPush() {
        if (!this.swRegistration) return;
        
        try {
            const subscription = await this.swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(
                    document.querySelector('meta[name="vapid-key"]')?.content || ''
                )
            });
            
            // Send subscription to server
            await fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(subscription)
            });
        } catch (err) {
            console.error('[PWA] Push subscription failed:', err);
        }
    }

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
}

window.pwa = new PWAManager();
