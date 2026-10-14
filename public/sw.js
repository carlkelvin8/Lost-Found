const CACHE_NAME = 'naap-lf-v10';
const STATIC_CACHE = 'naap-lf-static-v10';
const DYNAMIC_CACHE = 'naap-lf-dynamic-v10';

const STATIC_ASSETS = [
    '/css/layout.css',
    '/css/dashboard.css',
    '/css/claims.css',
    '/css/wow-features.css',
    '/js/toast.js',
    '/js/wow-features.js',
    '/js/advanced-ui.js',
    '/js/export-share.js',
    '/js/realtime.js',
    '/js/analytics.js',
    '/js/bookmarks-compare.js',
    '/js/account-security.js',
    '/manifest.json',
    '/offline.html'
];

const OFFLINE_URL = '/offline.html';

// Install - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            console.log('[SW] Caching static assets');
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activate - clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter(key => key !== STATIC_CACHE && key !== DYNAMIC_CACHE)
                    .map(key => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

// Fetch - network first, fallback to cache
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip API calls and non-HTTP
    if (!url.protocol.startsWith('http')) return;

    // Skip external URLs (CDN, analytics, etc.)
    if (url.origin !== location.origin) return;

    if (!STATIC_ASSETS.includes(url.pathname) && url.pathname !== OFFLINE_URL) {
        if (request.mode === 'navigate') {
            event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL).then(cached => cached || new Response('Offline', { status: 503 }))));
        }
        return;
    }
    event.respondWith(caches.match(request).then(cached => cached || fetch(request)));

});

// Background sync for offline form submissions
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-reports') {
        event.waitUntil(syncReports());
    }
});

async function syncReports() {
    const db = await openDB();
    const tx = db.transaction('pending-reports', 'readonly');
    const store = tx.objectStore('pending-reports');
    const reports = await getAllFromStore(store);

    for (const report of reports) {
        try {
            // Never replay another account's offline draft using the current cookies.
            if (!report.ownerId || !/^\/(?:admin\/|session\/[0-9a-f-]{36}\/)?reports$/i.test(report.endpoint || '')) continue;
            const statsUrl = report.endpoint.replace(/\/reports$/, '/dashboard/stats');
            const identityResponse = await fetch(statsUrl, { headers: { Accept: 'application/json' } });
            if (!identityResponse.ok) continue;
            const identity = await identityResponse.json();
            if (String(identity.user_id) !== report.ownerId) continue;
            const formData = new FormData();
            Object.keys(report.data).forEach(key => {
                formData.append(key, report.data[key]);
            });

            const response = await fetch(report.endpoint, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': report.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                await deleteFromStore('pending-reports', report.id);
                self.clients.matchAll().then(clients => {
                    clients.forEach(client => {
                        client.postMessage({ type: 'SYNC_COMPLETE', reportId: report.id });
                    });
                });
            }
        } catch (err) {
            console.error('[SW] Sync failed:', err);
        }
    }
}

// IndexedDB helpers for offline storage
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('naap-lf-offline', 1);
        request.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('pending-reports')) {
                db.createObjectStore('pending-reports', { keyPath: 'id', autoIncrement: true });
            }
            if (!db.objectStoreNames.contains('cached-data')) {
                db.createObjectStore('cached-data', { keyPath: 'key' });
            }
        };
        request.onsuccess = (e) => resolve(e.target.result);
        request.onerror = (e) => reject(e);
    });
}

function getAllFromStore(store) {
    return new Promise((resolve, reject) => {
        const request = store.getAll();
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function deleteFromStore(storeName, key) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('naap-lf-offline', 1);
        request.onsuccess = (e) => {
            const db = e.target.result;
            const tx = db.transaction(storeName, 'readwrite');
            tx.objectStore(storeName).delete(key);
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        };
    });
}

// Push notifications
self.addEventListener('push', (event) => {
    const data = event.data?.json() || { title: 'NAAP Lost & Found', body: 'You have a new notification' };
    
    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/images/icons/icon-192x192.png',
            badge: '/images/icons/icon-72x72.png',
            vibrate: [100, 50, 100],
            data: data.url || '/notifications',
            actions: [
                { action: 'open', title: 'Open' },
                { action: 'dismiss', title: 'Dismiss' }
            ]
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    if (event.action === 'dismiss') return;
    
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(clientList => {
            for (const client of clientList) {
                if (client.url.includes('/') && 'focus' in client) {
                    return client.focus();
                }
            }
            return clients.openWindow(event.notification.data || '/');
        })
    );
});
