/**
 * Premium Camera Capture Component
 * Allows users to take photos directly from their device camera
 */

class CameraCapture {
    constructor(options = {}) {
        this.options = {
            maxPhotos: options.maxPhotos || 5,
            quality: options.quality || 0.9,
            maxWidth: options.maxWidth || 1920,
            maxHeight: options.maxHeight || 1920,
            facingMode: options.facingMode || 'environment', // 'user' for front camera
            ...options
        };

        this.stream = null;
        this.capturedPhotos = [];
        this.isOpen = false;
    }

    async open() {
        if (this.isOpen) return;

        try {
            // Request camera permission
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: this.options.facingMode,
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                },
                audio: false
            });

            this.createModal();
            this.isOpen = true;

            // Start video stream
            const video = this.modal.querySelector('#cameraVideo');
            video.srcObject = this.stream;
            video.play();

        } catch (error) {
            console.error('Camera access error:', error);
            this.showError('Unable to access camera. Please check permissions.');
        }
    }

    createModal() {
        // Remove existing modal if any
        const existing = document.getElementById('cameraModal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'cameraModal';
        modal.className = 'camera-modal';
        modal.innerHTML = `
            <div class="camera-modal-content">
                <div class="camera-header">
                    <h3><i class="bi bi-camera"></i> Take Photo</h3>
                    <button class="camera-close" onclick="cameraCapture.close()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                
                <div class="camera-body">
                    <div class="camera-preview">
                        <video id="cameraVideo" autoplay playsinline></video>
                        <canvas id="cameraCanvas" style="display: none;"></canvas>
                        
                        <div class="camera-overlay">
                            <div class="camera-frame"></div>
                        </div>
                    </div>
                    
                    <div class="camera-controls">
                        <button class="btn-camera-switch" onclick="cameraCapture.switchCamera()" title="Switch Camera">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        
                        <button class="btn-camera-capture" onclick="cameraCapture.capture()">
                            <div class="capture-ring">
                                <div class="capture-button"></div>
                            </div>
                        </button>
                        
                        <button class="btn-camera-gallery" onclick="cameraCapture.viewGallery()" title="View Photos">
                            <i class="bi bi-images"></i>
                            <span class="photo-count" id="photoCount">0</span>
                        </button>
                    </div>
                    
                    <div class="camera-gallery" id="cameraGallery" style="display: none;">
                        <div class="gallery-header">
                            <h4>Captured Photos (<span id="galleryCount">0</span>/${this.options.maxPhotos})</h4>
                            <button class="btn btn-sm btn-primary" onclick="cameraCapture.done()">
                                <i class="bi bi-check-lg"></i> Done
                            </button>
                        </div>
                        <div class="gallery-grid" id="galleryGrid"></div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        this.modal = modal;

        // Animate in
        setTimeout(() => modal.classList.add('active'), 10);
    }

    async capture() {
        const video = this.modal.querySelector('#cameraVideo');
        const canvas = this.modal.querySelector('#cameraCanvas');
        const context = canvas.getContext('2d');

        // Set canvas size to video size
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // Draw video frame to canvas
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Compress and convert to blob
        const blob = await this.canvasToBlob(canvas);
        
        if (this.capturedPhotos.length >= this.options.maxPhotos) {
            this.showError(`Maximum ${this.options.maxPhotos} photos allowed`);
            return;
        }

        // Add to captured photos
        const photo = {
            id: Date.now(),
            blob: blob,
            url: URL.createObjectURL(blob),
            timestamp: new Date()
        };

        this.capturedPhotos.push(photo);
        this.updatePhotoCount();
        this.showCaptureAnimation();

        // Auto-show gallery after first capture
        if (this.capturedPhotos.length === 1) {
            setTimeout(() => this.viewGallery(), 500);
        }
    }

    canvasToBlob(canvas) {
        return new Promise((resolve) => {
            canvas.toBlob((blob) => {
                resolve(blob);
            }, 'image/jpeg', this.options.quality);
        });
    }

    showCaptureAnimation() {
        const overlay = this.modal.querySelector('.camera-overlay');
        overlay.style.animation = 'cameraFlash 0.3s ease-out';
        setTimeout(() => {
            overlay.style.animation = '';
        }, 300);
    }

    updatePhotoCount() {
        const count = this.capturedPhotos.length;
        const countEl = this.modal.querySelector('#photoCount');
        const galleryCountEl = this.modal.querySelector('#galleryCount');
        
        if (countEl) {
            countEl.textContent = count;
            countEl.style.display = count > 0 ? 'flex' : 'none';
        }
        
        if (galleryCountEl) {
            galleryCountEl.textContent = count;
        }
    }

    viewGallery() {
        const gallery = this.modal.querySelector('#cameraGallery');
        const grid = this.modal.querySelector('#galleryGrid');
        
        // Clear grid
        grid.innerHTML = '';
        
        // Add photos
        this.capturedPhotos.forEach((photo, index) => {
            const item = document.createElement('div');
            item.className = 'gallery-item';
            item.innerHTML = `
                <img src="${photo.url}" alt="Photo ${index + 1}">
                <button class="btn-delete-photo" onclick="cameraCapture.deletePhoto(${photo.id})">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            grid.appendChild(item);
        });
        
        gallery.style.display = 'block';
        this.updatePhotoCount();
    }

    deletePhoto(photoId) {
        const index = this.capturedPhotos.findIndex(p => p.id === photoId);
        if (index > -1) {
            URL.revokeObjectURL(this.capturedPhotos[index].url);
            this.capturedPhotos.splice(index, 1);
            this.viewGallery();
        }
    }

    async switchCamera() {
        this.options.facingMode = this.options.facingMode === 'user' ? 'environment' : 'user';
        
        // Stop current stream
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }
        
        // Restart with new facing mode
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: this.options.facingMode,
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                },
                audio: false
            });
            
            const video = this.modal.querySelector('#cameraVideo');
            video.srcObject = this.stream;
            video.play();
        } catch (error) {
            console.error('Camera switch error:', error);
            this.showError('Unable to switch camera');
        }
    }

    done() {
        if (this.capturedPhotos.length === 0) {
            this.showError('Please capture at least one photo');
            return;
        }

        // Convert photos to File objects and add to file input
        const fileInput = document.querySelector(this.options.targetInput || 'input[name="photos[]"]');
        if (fileInput) {
            const dataTransfer = new DataTransfer();
            
            // Add existing files
            if (fileInput.files) {
                Array.from(fileInput.files).forEach(file => {
                    dataTransfer.items.add(file);
                });
            }
            
            // Add captured photos
            this.capturedPhotos.forEach((photo, index) => {
                const file = new File([photo.blob], `camera-photo-${Date.now()}-${index}.jpg`, {
                    type: 'image/jpeg',
                    lastModified: photo.timestamp.getTime()
                });
                dataTransfer.items.add(file);
            });
            
            fileInput.files = dataTransfer.files;
            
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
            
            // Show preview
            this.showPreview(fileInput);
        }

        this.close();
    }

    showPreview(fileInput) {
        const previewContainer = document.getElementById('photoPreviewContainer');
        if (!previewContainer) return;

        previewContainer.innerHTML = '';
        
        Array.from(fileInput.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'photo-preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Photo ${index + 1}">
                    <span class="photo-number">${index + 1}</span>
                `;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    close() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }

        if (this.modal) {
            this.modal.classList.remove('active');
            setTimeout(() => {
                this.modal.remove();
                this.modal = null;
            }, 300);
        }

        // Clean up captured photos URLs
        this.capturedPhotos.forEach(photo => {
            URL.revokeObjectURL(photo.url);
        });
        this.capturedPhotos = [];
        this.isOpen = false;
    }

    showError(message) {
        const alert = document.createElement('div');
        alert.className = 'camera-alert';
        alert.innerHTML = `
            <i class="bi bi-exclamation-triangle"></i>
            ${message}
        `;
        
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    }
}

// Global instance
let cameraCapture = null;

// Initialize function
function initCamera(options = {}) {
    if (!cameraCapture) {
        cameraCapture = new CameraCapture(options);
    }
    cameraCapture.open();
}
