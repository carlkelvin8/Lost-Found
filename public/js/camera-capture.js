/**
 * Premium Camera Capture Component - Max Level Fix
 * Robust handling for all browsers, secure contexts, permissions, and file merging
 */

class CameraCapture {
    constructor(options = {}) {
        this.options = {
            maxPhotos: options.maxPhotos || 5,
            quality: options.quality || 0.82,
            maxWidth: options.maxWidth || 1920,
            maxHeight: options.maxHeight || 1920,
            facingMode: options.facingMode || 'environment',
            targetInput: options.targetInput || 'input[name="photos[]"]',
            onDone: options.onDone || null,
            ...options
        };
        this.stream = null;
        this.capturedPhotos = [];
        this.isOpen = false;
        this._escapeHandler = null;
    }

    isSecureContextOk() {
        return location.protocol === 'https:' 
            || location.hostname === 'localhost' 
            || location.hostname === '127.0.0.1'
            || location.hostname === '::1'
            || location.hostname === '[::1]'
            || location.hostname === '' // file:// for testing
            || window.isSecureContext === true;
    }

    async open() {
        if (this.isOpen) return;

        // Check API availability first
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (!this.isSecureContextOk()) {
                this.showError('Camera requires HTTPS or localhost. Please use https:// or http://127.0.0.1:8000');
                this.fallbackToFilePicker();
            } else {
                this.showError('Camera API not supported in this browser. Please use file upload.');
                this.fallbackToFilePicker();
            }
            return;
        }

        if (!this.isSecureContextOk() && location.protocol !== 'https:') {
            // On insecure context, getUserMedia will fail anyway, but try and handle error
            console.warn('Insecure context, camera may fail');
        }

        // Try constraints with fallbacks
        const constraintsList = [
            { video: { facingMode: { ideal: this.options.facingMode }, width: { ideal: 1920 }, height: { ideal: 1080 } }, audio: false },
            { video: { facingMode: this.options.facingMode, width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false },
            { video: { facingMode: this.options.facingMode }, audio: false },
            { video: true, audio: false }
        ];

        let lastError = null;
        for (const constraints of constraintsList) {
            try {
                this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                // Update facingMode to actual if succeeded with fallback
                if (constraints.video && constraints.video.facingMode && typeof constraints.video.facingMode === 'string') {
                    this.options.facingMode = constraints.video.facingMode;
                }
                lastError = null;
                break;
            } catch (e) {
                lastError = e;
                // Don't retry on permission denied
                if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError' || e.name === 'SecurityError') {
                    break;
                }
            }
        }

        if (!this.stream) {
            console.error('Camera access error:', lastError);
            if (lastError) {
                if (lastError.name === 'NotAllowedError' || lastError.name === 'PermissionDeniedError' || lastError.name === 'SecurityError') {
                    this.showError('Camera permission denied. Please allow camera in browser settings (click lock icon → Allow camera) then reload.');
                } else if (lastError.name === 'NotFoundError' || lastError.name === 'DevicesNotFoundError' || lastError.name === 'OverconstrainedError') {
                    this.showError('No camera found. Please use file upload instead.');
                    this.fallbackToFilePicker();
                } else if (lastError.name === 'NotReadableError' || lastError.name === 'TrackStartError' || lastError.name === 'AbortError') {
                    this.showError('Camera is busy by another app. Close other camera apps and try again.');
                } else if (lastError.name === 'NotSupportedError') {
                    this.showError('Camera not supported. Use file upload.');
                    this.fallbackToFilePicker();
                } else {
                    this.showError('Unable to access camera: ' + (lastError.message || lastError.name) + '. Try file upload.');
                }
            }
            return;
        }

        this.createModal();
        this.isOpen = true;

        const video = this.modal.querySelector('#cameraVideo');
        video.srcObject = this.stream;
        // Ensure playsinline/muted for iOS
        video.setAttribute('playsinline', '');
        video.setAttribute('muted', '');
        video.muted = true;
        try {
            await video.play();
        } catch (e) {
            // Autoplay may be blocked, try muted play
            console.warn('video play failed', e);
            // Show play button overlay if needed
        }
        // Handle stream ended
        this.stream.getTracks().forEach(track => {
            track.onended = () => {
                if (this.isOpen) {
                    this.showError('Camera disconnected');
                }
            };
        });
    }

    fallbackToFilePicker() {
        const input = document.querySelector(this.options.targetInput || 'input[name="photos[]"]');
        if (input) {
            // Trigger file picker after short delay so user sees error first
            setTimeout(() => input.click(), 800);
        }
    }

    createModal() {
        const existing = document.getElementById('cameraModal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'cameraModal';
        modal.className = 'camera-modal';
        modal.innerHTML = `
            <div class="camera-modal-content">
                <div class="camera-header">
                    <h3><i class="bi bi-camera"></i> Take Photo</h3>
                    <button class="camera-close" type="button" aria-label="Close camera" onclick="cameraCapture.close()" title="Cancel">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="camera-body">
                    <div class="camera-preview" id="cameraPreviewContainer">
                        <video id="cameraVideo" autoplay playsinline muted></video>
                        <canvas id="cameraCanvas" style="display: none;"></canvas>
                        <div class="camera-overlay">
                            <div class="camera-frame"></div>
                        </div>
                    </div>
                    <div class="camera-controls">
                        <button class="btn-camera-switch" type="button" onclick="cameraCapture.switchCamera()" title="Switch Camera">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        <button class="btn-camera-capture" type="button" onclick="cameraCapture.capture()" title="Take Photo">
                            <div class="capture-ring">
                                <div class="capture-button"></div>
                            </div>
                        </button>
                        <button class="btn-camera-gallery" type="button" onclick="cameraCapture.viewGallery()" title="View Photos">
                            <i class="bi bi-images"></i>
                            <span class="photo-count" id="photoCount">0</span>
                        </button>
                    </div>
                    <div class="camera-gallery" id="cameraGallery" style="display: none;">
                        <div class="gallery-header">
                            <h4>Captured Photos (<span id="galleryCount">0</span>/${this.options.maxPhotos})</h4>
                            <div class="gallery-actions">
                                <button class="btn btn-sm btn-secondary" type="button" onclick="cameraCapture.backToCamera()">
                                    <i class="bi bi-arrow-left"></i> Back
                                </button>
                                <button class="btn btn-sm btn-primary" type="button" onclick="cameraCapture.done()">
                                    <i class="bi bi-check-lg"></i> Done
                                </button>
                            </div>
                        </div>
                        <div class="gallery-grid" id="galleryGrid"></div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        this.modal = modal;
        setTimeout(() => modal.classList.add('active'), 10);

        // ESC to close
        this._escapeHandler = (e) => { if (e.key === 'Escape' && this.isOpen) this.close(); };
        document.addEventListener('keydown', this._escapeHandler);
        // Click outside to close
        modal.addEventListener('click', (e) => { if (e.target === modal) this.close(); });
    }

    async capture() {
        if (!this.modal) return;
        const video = this.modal.querySelector('#cameraVideo');
        const canvas = this.modal.querySelector('#cameraCanvas');
        if (!video || !canvas) return;

        // Ensure video has dimensions
        const vw = video.videoWidth || 1280;
        const vh = video.videoHeight || 720;
        if (vw === 0 || vh === 0) {
            this.showError('Camera not ready, please wait a moment and try again');
            return;
        }
        // Check existing files count to enforce max
        const fileInput = document.querySelector(this.options.targetInput || 'input[name="photos[]"]');
        const existingCount = fileInput ? fileInput.files.length : 0;
        if (existingCount + this.capturedPhotos.length >= this.options.maxPhotos) {
            this.showError(`Maximum ${this.options.maxPhotos} photos allowed`);
            return;
        }
        if (this.capturedPhotos.length >= this.options.maxPhotos) {
            this.showError(`Maximum ${this.options.maxPhotos} photos allowed`);
            return;
        }

        canvas.width = vw;
        canvas.height = vh;
        const context = canvas.getContext('2d');
        // Handle front camera mirror? Keep as is for now

        try {
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
        } catch (e) {
            this.showError('Capture failed: ' + e.message);
            return;
        }

        const blob = await this.canvasToBlob(canvas);
        if (!blob) {
            this.showError('Failed to process image');
            return;
        }

        const photo = {
            id: Date.now() + Math.random(),
            blob: blob,
            url: URL.createObjectURL(blob),
            timestamp: new Date()
        };
        this.capturedPhotos.push(photo);
        this.updatePhotoCount();
        this.showCaptureAnimation();
        // Haptic feedback if available
        if (navigator.vibrate) navigator.vibrate(50);

        if (this.capturedPhotos.length === 1) {
            setTimeout(() => this.viewGallery(), 400);
        }
    }

    canvasToBlob(canvas) {
        return new Promise((resolve) => {
            canvas.toBlob((blob) => {
                if (!blob) {
                    // Fallback via toDataURL
                    try {
                        const dataUrl = canvas.toDataURL('image/jpeg', this.options.quality);
                        const arr = dataUrl.split(',');
                        const mime = arr[0].match(/:(.*?);/)[1];
                        const bstr = atob(arr[1]);
                        let n = bstr.length;
                        const u8arr = new Uint8Array(n);
                        while (n--) u8arr[n] = bstr.charCodeAt(n);
                        resolve(new Blob([u8arr], { type: mime }));
                    } catch (e) { resolve(null); }
                } else {
                    resolve(blob);
                }
            }, 'image/jpeg', this.options.quality);
        });
    }

    showCaptureAnimation() {
        const overlay = this.modal.querySelector('.camera-overlay');
        if (!overlay) return;
        overlay.style.animation = 'cameraFlash 0.3s ease-out';
        setTimeout(() => { overlay.style.animation = ''; }, 300);
    }

    updatePhotoCount() {
        const count = this.capturedPhotos.length;
        const countEl = this.modal.querySelector('#photoCount');
        const galleryCountEl = this.modal.querySelector('#galleryCount');
        if (countEl) {
            countEl.textContent = count;
            countEl.style.display = count > 0 ? 'flex' : 'none';
        }
        if (galleryCountEl) galleryCountEl.textContent = count;
    }

    viewGallery() {
        const gallery = this.modal.querySelector('#cameraGallery');
        const preview = this.modal.querySelector('#cameraPreviewContainer');
        const controls = this.modal.querySelector('.camera-controls');
        const grid = this.modal.querySelector('#galleryGrid');
        if (!gallery || !preview || !controls || !grid) return;
        grid.innerHTML = '';
        this.capturedPhotos.forEach((photo, index) => {
            const item = document.createElement('div');
            item.className = 'gallery-item';
            item.innerHTML = `
                <img src="${photo.url}" alt="Photo ${index + 1}">
                <div class="gallery-item-actions">
                    <button class="btn-delete-photo" type="button" onclick="cameraCapture.deletePhoto(${photo.id})" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            grid.appendChild(item);
        });
        preview.style.display = 'none';
        controls.style.display = 'none';
        gallery.style.display = 'flex';
        this.updatePhotoCount();
    }

    deletePhoto(photoId) {
        const index = this.capturedPhotos.findIndex(p => p.id === photoId);
        if (index > -1) {
            URL.revokeObjectURL(this.capturedPhotos[index].url);
            this.capturedPhotos.splice(index, 1);
            if (this.capturedPhotos.length === 0) {
                this.backToCamera();
            } else {
                this.viewGallery();
            }
        }
    }

    backToCamera() {
        const gallery = this.modal.querySelector('#cameraGallery');
        const preview = this.modal.querySelector('#cameraPreviewContainer');
        const controls = this.modal.querySelector('.camera-controls');
        if (!gallery || !preview || !controls) return;
        preview.style.display = '';
        controls.style.display = 'flex';
        gallery.style.display = 'none';
    }

    async switchCamera() {
        this.options.facingMode = this.options.facingMode === 'user' ? 'environment' : 'user';
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }
        // Try with new facingMode, fallback to any
        const tries = [
            { video: { facingMode: this.options.facingMode, width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false },
            { video: { facingMode: { ideal: this.options.facingMode } }, audio: false },
            { video: true, audio: false }
        ];
        let newStream = null;
        let lastErr = null;
        for (const c of tries) {
            try { newStream = await navigator.mediaDevices.getUserMedia(c); break; } catch (e) { lastErr = e; }
        }
        if (!newStream) {
            console.error('Camera switch error:', lastErr);
            if (lastErr && (lastErr.name === 'NotAllowedError' || lastErr.name === 'PermissionDeniedError')) {
                this.showError('Camera permission denied.');
            } else {
                this.showError('Unable to switch camera. Trying to reopen.');
                // Reopen with previous stream
                this.open();
            }
            return;
        }
        this.stream = newStream;
        const video = this.modal.querySelector('#cameraVideo');
        if (video) {
            video.srcObject = this.stream;
            try { await video.play(); } catch (e) {}
        }
    }

    done() {
        if (this.capturedPhotos.length === 0) {
            this.showError('Please capture at least one photo');
            return;
        }
        const fileInput = document.querySelector(this.options.targetInput || 'input[name="photos[]"]');
        if (!fileInput) {
            this.showError('Upload input not found');
            return;
        }
        const maxPhotos = this.options.maxPhotos;
        const existingFiles = Array.from(fileInput.files || []);
        const totalAfter = existingFiles.length + this.capturedPhotos.length;
        if (totalAfter > maxPhotos) {
            this.showError(`Maximum ${maxPhotos} photos allowed. You have ${existingFiles.length} already, tried to add ${this.capturedPhotos.length}.`);
            return;
        }
        const dataTransfer = new DataTransfer();
        // Keep existing files first
        existingFiles.forEach(f => dataTransfer.items.add(f));
        // Add captured photos
        this.capturedPhotos.forEach((photo, index) => {
            const file = new File([photo.blob], `camera-photo-${Date.now()}-${index}.jpg`, {
                type: 'image/jpeg',
                lastModified: photo.timestamp.getTime()
            });
            dataTransfer.items.add(file);
        });
        fileInput.files = dataTransfer.files;
        const event = new Event('change', { bubbles: true });
        fileInput.dispatchEvent(event);

        if (typeof this.options.onDone === 'function') {
            try { this.options.onDone(this.capturedPhotos); } catch (e) { console.error(e); }
        }
        this.close();
    }

    close() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        if (this._escapeHandler) {
            document.removeEventListener('keydown', this._escapeHandler);
            this._escapeHandler = null;
        }
        if (this.modal) {
            this.modal.classList.remove('active');
            const m = this.modal;
            setTimeout(() => { if (m.parentNode) m.remove(); }, 300);
            this.modal = null;
        }
        this.capturedPhotos.forEach(photo => { try { URL.revokeObjectURL(photo.url); } catch (e) {} });
        this.capturedPhotos = [];
        this.isOpen = false;
    }

    showError(message) {
        // Remove existing alerts
        document.querySelectorAll('.camera-alert').forEach(el => el.remove());
        const alert = document.createElement('div');
        alert.className = 'camera-alert';
        alert.innerHTML = `
            <i class="bi bi-exclamation-triangle"></i>
            <span>${message}</span>
            <button type="button" onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;color:white;font-size:1.2rem;cursor:pointer">&times;</button>
        `;
        document.body.appendChild(alert);
        setTimeout(() => alert.classList.add('show'), 10);
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => { if (alert.parentNode) alert.remove(); }, 300);
        }, 4000);
    }
}

// Global instance
let cameraCapture = null;

function initCamera(options = {}) {
    if (cameraCapture) {
        try { cameraCapture.close(); } catch (e) {}
        cameraCapture = null;
    }
    cameraCapture = new CameraCapture(options);
    cameraCapture.open();
    return cameraCapture;
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { CameraCapture, initCamera };
}
