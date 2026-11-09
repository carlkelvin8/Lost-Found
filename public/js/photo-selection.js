document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('photoInput');
    const container = document.getElementById('photoPreviewContainer');
    if (!input || !container) return;
    let selected = [];
    let urls = [];
    let removing = false;
    const key = file => `${file.name}|${file.size}|${file.lastModified}`;
    function render() {
        urls.forEach(url => URL.revokeObjectURL(url));
        urls = [];
        container.replaceChildren();
        selected.forEach((file, index) => {
            const card = document.createElement('div');
            card.className = 'photo-preview-item fade-in';
            const image = document.createElement('img');
            image.alt = `Selected photo ${index + 1}`;
            image.loading = 'lazy';
            const url = URL.createObjectURL(file);
            urls.push(url);
            image.src = url;
            image.onerror = () => { image.alt = 'Photo preview unavailable. HEIC/HEIF will be converted if supported.'; };
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-danger';
            remove.textContent = 'Remove';
            remove.setAttribute('aria-label', `Remove photo ${index + 1}`);
            remove.addEventListener('click', event => {
                event.stopPropagation();
                selected.splice(index, 1);
                removing = true;
                const transfer = new DataTransfer();
                selected.forEach(file => transfer.items.add(file));
                input.files = transfer.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
            card.append(image, remove);
            container.appendChild(card);
        });
    }
    input.addEventListener('change', () => {
        const files = Array.from(input.files || []);
        const unique = new Map((removing ? files : selected.concat(files)).map(file => [key(file), file]));
        if (unique.size > 5) {
            const feedback = document.getElementById('reportSubmitFeedback');
            feedback.textContent = 'Maximum 5 photos. Remove a selected photo before adding another.';
            feedback.classList.remove('d-none');
        }
        selected = Array.from(unique.values()).slice(0, 5);
        removing = false;
        const transfer = new DataTransfer();
        selected.forEach(file => transfer.items.add(file));
        input.files = transfer.files;
        render();
    });
    document.getElementById('takePhotoBtn')?.addEventListener('click', event => {
        event.stopPropagation();
        if (selected.length >= 5) return;
        initCamera({ maxPhotos: 5, targetInput: '#photoInput' });
    });
    initDragDrop(document.getElementById('dragDropZone'), input, container);
    window.addEventListener('pagehide', () => urls.forEach(url => URL.revokeObjectURL(url)));
});
