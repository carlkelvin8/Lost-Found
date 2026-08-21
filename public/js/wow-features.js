/**
 * WOW Features JavaScript
 * Toast, Skeleton, Confetti, Lightbox, Drag & Drop, Animated Counters, Keyboard Shortcuts
 */

// ==================== SKELETON LOADING ====================
function showSkeleton(container, type = 'card', count = 3) {
    const templates = {
        card: `
            <div class="skeleton-card">
                <div class="skeleton skeleton-title"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text-sm"></div>
            </div>
        `,
        stat: `
            <div class="skeleton-stat">
                <div class="skeleton skeleton-stat-icon"></div>
                <div class="skeleton-stat-info">
                    <div class="skeleton skeleton-text-sm"></div>
                    <div class="skeleton skeleton-title" style="width:50%"></div>
                </div>
            </div>
        `,
        list: `
            <div class="skeleton-card" style="display:flex;gap:1rem;align-items:center;">
                <div class="skeleton skeleton-avatar"></div>
                <div style="flex:1">
                    <div class="skeleton skeleton-text" style="width:70%"></div>
                    <div class="skeleton skeleton-text-sm"></div>
                </div>
            </div>
        `
    };
    
    let html = '';
    for (let i = 0; i < count; i++) {
        html += templates[type] || templates.card;
    }
    container.innerHTML = html;
}

// ==================== CONFETTI ====================
function launchConfetti() {
    const container = document.createElement('div');
    container.className = 'confetti-container';
    document.body.appendChild(container);

    const colors = ['#0041C7', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];
    const shapes = ['circle', 'square'];

    for (let i = 0; i < 80; i++) {
        const piece = document.createElement('div');
        piece.className = 'confetti-piece';
        const color = colors[Math.floor(Math.random() * colors.length)];
        const shape = shapes[Math.floor(Math.random() * shapes.length)];
        const left = Math.random() * 100;
        const delay = Math.random() * 0.5;
        const size = Math.random() * 8 + 6;

        piece.style.cssText = `
            left: ${left}%;
            width: ${size}px;
            height: ${size}px;
            background: ${color};
            border-radius: ${shape === 'circle' ? '50%' : '2px'};
            animation-delay: ${delay}s;
            animation-duration: ${2 + Math.random() * 2}s;
        `;

        container.appendChild(piece);
    }

    setTimeout(() => container.remove(), 5000);
}

// ==================== LIGHTBOX ====================
class Lightbox {
    constructor() {
        this.images = [];
        this.currentIndex = 0;
        this.overlay = null;
        this.createOverlay();
    }

    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'lightbox-overlay';
        this.overlay.innerHTML = `
            <div class="lightbox-content">
                <button class="lightbox-close" aria-label="Close lightbox"><i class="bi bi-x-lg"></i></button>
                <button class="lightbox-nav lightbox-prev" aria-label="Previous image"><i class="bi bi-chevron-left"></i></button>
                <button class="lightbox-nav lightbox-next" aria-label="Next image"><i class="bi bi-chevron-right"></i></button>
                <img src="" alt="Preview">
                <div class="lightbox-counter"></div>
            </div>
        `;
        document.body.appendChild(this.overlay);

        this.overlay.querySelector('.lightbox-close').addEventListener('click', () => this.close());
        this.overlay.querySelector('.lightbox-prev').addEventListener('click', () => this.prev());
        this.overlay.querySelector('.lightbox-next').addEventListener('click', () => this.next());
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) this.close();
        });

        document.addEventListener('keydown', (e) => {
            if (!this.overlay.classList.contains('active')) return;
            if (e.key === 'Escape') this.close();
            if (e.key === 'ArrowLeft') this.prev();
            if (e.key === 'ArrowRight') this.next();
        });
    }

    open(images, index = 0) {
        this.images = images;
        this.currentIndex = index;
        this.updateImage();
        this.overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    close() {
        this.overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    prev() {
        this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.updateImage();
    }

    next() {
        this.currentIndex = (this.currentIndex + 1) % this.images.length;
        this.updateImage();
    }

    updateImage() {
        const img = this.overlay.querySelector('img');
        const counter = this.overlay.querySelector('.lightbox-counter');
        const prev = this.overlay.querySelector('.lightbox-prev');
        const next = this.overlay.querySelector('.lightbox-next');

        img.src = this.images[this.currentIndex];
        counter.textContent = `${this.currentIndex + 1} / ${this.images.length}`;
        
        prev.style.display = this.images.length > 1 ? 'flex' : 'none';
        next.style.display = this.images.length > 1 ? 'flex' : 'none';
    }
}

window.lightbox = new Lightbox();

// ==================== DRAG & DROP ====================
function initDragDrop(zone, input, previewContainer) {
    if (!zone || !input) return;

    ['dragenter', 'dragover'].forEach(evt => {
        zone.addEventListener(evt, (e) => {
            e.preventDefault();
            zone.classList.add('drag-over');
        });
    });

    ['dragleave', 'drop'].forEach(evt => {
        zone.addEventListener(evt, (e) => {
            e.preventDefault();
            zone.classList.remove('drag-over');
        });
    });

    zone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length) {
            input.files = files;
            input.dispatchEvent(new Event('change'));
        }
    });

    zone.addEventListener('click', () => input.click());
}

// ==================== ANIMATED COUNTERS ====================
function animateCounter(element, target, duration = 1500) {
    let start = 0;
    const startTime = performance.now();

    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function (ease-out)
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(start + (target - start) * easeOut);
        
        element.textContent = current.toLocaleString();

        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }

    requestAnimationFrame(update);
}

function initAnimatedCounters() {
    document.querySelectorAll('[data-count]').forEach(el => {
        const target = parseInt(el.dataset.count, 10);
        if (!isNaN(target)) {
            animateCounter(el, target);
        }
    });
}

// ==================== KEYBOARD SHORTCUTS ====================
const shortcuts = [
    { keys: ['Ctrl', 'K'], label: 'Search', action: () => document.querySelector('.search-input')?.focus() },
    { keys: ['Ctrl', 'N'], label: 'New Report', action: () => window.location.href = '/reports/create' },
    { keys: ['Ctrl', 'D'], label: 'Dashboard', action: () => window.location.href = '/dashboard' },
    { keys: ['?'], label: 'Show Shortcuts', action: () => toggleShortcutsModal() },
];

function toggleShortcutsModal() {
    let modal = document.getElementById('shortcuts-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'shortcuts-modal';
        modal.className = 'shortcuts-modal';
        modal.innerHTML = `
            <div class="shortcuts-content">
                <div class="shortcuts-title">
                    <i class="bi bi-keyboard"></i> Keyboard Shortcuts
                </div>
                ${shortcuts.map(s => `
                    <div class="shortcut-row">
                        <span class="shortcut-label">${s.label}</span>
                        <div class="shortcut-keys">
                            ${s.keys.map(k => `<span class="kbd">${k}</span>`).join(' + ')}
                        </div>
                    </div>
                `).join('')}
                <div style="margin-top:1.5rem;text-align:center">
                    <button onclick="document.getElementById('shortcuts-modal').classList.remove('active')" 
                            style="padding:0.5rem 1.5rem;background:#0041C7;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600">
                        Got it!
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('active');
        });
    }
    modal.classList.toggle('active');
}

document.addEventListener('keydown', (e) => {
    // Ignore if typing in input/textarea
    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
    
    // Ctrl+key shortcuts
    if (e.ctrlKey || e.metaKey) {
        const shortcut = shortcuts.find(s => s.keys.length === 2 && s.keys[0] === 'Ctrl' && s.keys[1].toLowerCase() === e.key.toLowerCase());
        if (shortcut) {
            e.preventDefault();
            shortcut.action();
        }
    }
    
    // ? key for shortcuts
    if (e.key === '?' && !e.ctrlKey && !e.metaKey) {
        toggleShortcutsModal();
    }
});

// ==================== STATUS TIMELINE ====================
function createStatusTimeline(currentStatus) {
    const statuses = ['pending', 'matched', 'claimed', 'returned'];
    const currentIndex = statuses.indexOf(currentStatus);
    
    return `
        <div class="status-timeline">
            ${statuses.map((status, i) => {
                let className = 'timeline-step';
                if (i < currentIndex) className += ' completed';
                else if (i === currentIndex) className += ' active';
                
                const icons = {
                    pending: 'bi-clock',
                    matched: 'bi-link',
                    claimed: 'bi-person-check',
                    returned: 'bi-check-circle'
                };
                
                return `
                    <div class="${className}">
                        <div class="timeline-dot">
                            <i class="bi ${i < currentIndex ? 'bi-check' : icons[status]}"></i>
                        </div>
                        <span class="timeline-label">${status}</span>
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

// ==================== QR CODE ====================
function generateQRCode(text, size = 128) {
    // Simple QR code using API
    const encoded = encodeURIComponent(text);
    return `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encoded}&bgcolor=FFFFFF&color=0041C7&margin=10`;
}

// ==================== TOAST HELPER ====================
function showToast(message, type = 'success') {
    if (window.toast) {
        window.toast.show(message, type);
    }
}

// ==================== INIT ====================
document.addEventListener('DOMContentLoaded', () => {
    initAnimatedCounters();
    
    // Auto-show toast for flash messages
    const flashSuccess = document.querySelector('.alert-success');
    const flashError = document.querySelector('.alert-danger');
    
    if (flashSuccess) {
        showToast(flashSuccess.textContent.trim());
    }
    if (flashError) {
        showToast(flashError.textContent.trim(), 'error');
    }
});
