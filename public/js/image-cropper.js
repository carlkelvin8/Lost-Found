/**
 * Premium Image Cropper Component
 * Allows users to crop and adjust profile pictures
 */

class ImageCropper {
    constructor(options = {}) {
        this.options = {
            aspectRatio: options.aspectRatio || 1, // 1:1 for profile pictures
            viewMode: options.viewMode || 1,
            minCropBoxWidth: options.minCropBoxWidth || 200,
            minCropBoxHeight: options.minCropBoxHeight || 200,
            maxWidth: options.maxWidth || 800,
            maxHeight: options.maxHeight || 800,
            quality: options.quality || 0.9,
            targetInput: options.targetInput || 'input[name="avatar"]',
            ...options
        };

        this.cropper = null;
        this.modal = null;
        this.originalFile = null;
        this.croppedBlob = null;
    }

    open(file) {
        if (!file) return;
        
        this.originalFile = file;
        this.createModal();
        this.loadImage(file);
    }

    createModal() {
        // Remove existing modal
        const existing = document.getElementById('imageCropperModal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'imageCropperModal';
        modal.className = 'image-cropper-modal';
        modal.innerHTML = `
            <div class="image-cropper-content">
                <div class="cropper-header">
                    <h3><i class="bi bi-crop"></i> Crop Profile Picture</h3>
                    <button class="cropper-close" onclick="imageCropper.close()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                
                <div class="cropper-body">
                    <div class="cropper-container">
                        <img id="cropperImage" style="max-width: 100%;">
                    </div>
                    
                    <div class="cropper-toolbar">
                        <div class="toolbar-group">
                            <button class="btn-tool" onclick="imageCropper.zoom(0.1)" title="Zoom In">
                                <i class="bi bi-zoom-in"></i>
                            </button>
                            <button class="btn-tool" onclick="imageCropper.zoom(-0.1)" title="Zoom Out">
                                <i class="bi bi-zoom-out"></i>
                            </button>
                        </div>
                        
                        <div class="toolbar-group">
                            <button class="btn-tool" onclick="imageCropper.rotate(-90)" title="Rotate Left">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                            <button class="btn-tool" onclick="imageCropper.rotate(90)" title="Rotate Right">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        
                        <div class="toolbar-group">
                            <button class="btn-tool" onclick="imageCropper.flip('horizontal')" title="Flip Horizontal">
                                <i class="bi bi-arrows-expand"></i>
                            </button>
                            <button class="btn-tool" onclick="imageCropper.flip('vertical')" title="Flip Vertical">
                                <i class="bi bi-arrows-collapse"></i>
                            </button>
                        </div>
                        
                        <div class="toolbar-group">
                            <button class="btn-tool" onclick="imageCropper.reset()" title="Reset">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="cropper-preview-section">
                        <div class="preview-label">Preview</div>
                        <div class="cropper-preview-container">
                            <div id="cropperPreview" class="cropper-preview"></div>
                        </div>
                    </div>
                </div>
                
                <div class="cropper-footer">
                    <button class="btn btn-outline-secondary" onclick="imageCropper.close()">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button class="btn btn-primary" onclick="imageCropper.crop()">
                        <i class="bi bi-check-circle"></i> Apply Crop
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        this.modal = modal;

        // Animate in
        setTimeout(() => modal.classList.add('active'), 10);
    }

    loadImage(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = this.modal.querySelector('#cropperImage');
            img.src = e.target.result;

            // Initialize Cropper.js
            this.initCropper(img);
        };
        reader.readAsDataURL(file);
    }

    initCropper(img) {
        // Destroy existing cropper
        if (this.cropper) {
            this.cropper.destroy();
        }

        // Initialize new cropper
        this.cropper = new Cropper(img, {
            aspectRatio: this.options.aspectRatio,
            viewMode: this.options.viewMode,
            dragMode: 'move',
            autoCropArea: 0.8,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            minCropBoxWidth: this.options.minCropBoxWidth,
            minCropBoxHeight: this.options.minCropBoxHeight,
            preview: '#cropperPreview',
            ready: () => {
                // Cropper is ready
                this.updatePreview();
            },
            crop: () => {
                this.updatePreview();
            }
        });
    }

    updatePreview() {
        // Preview is automatically updated by Cropper.js
    }

    zoom(ratio) {
        if (this.cropper) {
            this.cropper.zoom(ratio);
        }
    }

    rotate(degree) {
        if (this.cropper) {
            this.cropper.rotate(degree);
        }
    }

    flip(direction) {
        if (this.cropper) {
            if (direction === 'horizontal') {
                const scaleX = this.cropper.getData().scaleX || 1;
                this.cropper.scaleX(-scaleX);
            } else {
                const scaleY = this.cropper.getData().scaleY || 1;
                this.cropper.scaleY(-scaleY);
            }
        }
    }

    reset() {
        if (this.cropper) {
            this.cropper.reset();
        }
    }

    async crop() {
        if (!this.cropper) return;

        // Get cropped canvas
        const canvas = this.cropper.getCroppedCanvas({
            maxWidth: this.options.maxWidth,
            maxHeight: this.options.maxHeight,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        // Convert to blob
        canvas.toBlob((blob) => {
            this.croppedBlob = blob;
            this.applyToInput();
            this.showPreviewInPage();
            this.close();
        }, 'image/jpeg', this.options.quality);
    }

    applyToInput() {
        const fileInput = document.querySelector(this.options.targetInput);
        if (!fileInput || !this.croppedBlob) return;

        // Create new File from blob
        const file = new File(
            [this.croppedBlob],
            this.originalFile.name.replace(/\.[^/.]+$/, '') + '-cropped.jpg',
            {
                type: 'image/jpeg',
                lastModified: Date.now()
            }
        );

        // Create DataTransfer to set files
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;

        // Trigger change event
        const event = new Event('change', { bubbles: true });
        fileInput.dispatchEvent(event);
    }

    showPreviewInPage() {
        const preview = document.getElementById('avatarPreview');
        if (preview && this.croppedBlob) {
            const url = URL.createObjectURL(this.croppedBlob);
            preview.innerHTML = `<img src="${url}" alt="Preview" class="avatar-preview-img">`;
            preview.style.display = 'block';
        }
    }

    close() {
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }

        if (this.modal) {
            this.modal.classList.remove('active');
            setTimeout(() => {
                this.modal.remove();
                this.modal = null;
            }, 300);
        }

        this.originalFile = null;
        this.croppedBlob = null;
    }
}

// Global instance
let imageCropper = null;

// Initialize function
function initImageCropper(file, options = {}) {
    if (!imageCropper) {
        imageCropper = new ImageCropper(options);
    }
    imageCropper.open(file);
}

// Handle file input change
function handleAvatarChange(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        if (!file.type.match('image.*')) {
            alert('Please select an image file');
            input.value = '';
            return;
        }
        
        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size must be less than 5MB');
            input.value = '';
            return;
        }
        
        // Open cropper
        initImageCropper(file, {
            targetInput: input.name ? `input[name="${input.name}"]` : 'input[name="avatar"]'
        });
    }
}
