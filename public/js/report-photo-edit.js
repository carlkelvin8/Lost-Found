document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-photo-upload]').forEach(form => {
        let submitting = false;
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (submitting) return;
            submitting = true;
            const button = form.querySelector('button[type="submit"]');
            const label = button.innerHTML;
            button.disabled = true;
            const feedback = form.querySelector('[role="alert"]');
            feedback.textContent = '';
            try {
                const body = new FormData(form);
                const files = await window.ReportPhotos.prepare(Array.from(form.elements.photo.files), text => { button.textContent = text; });
                body.set('photo', files[0]);
                button.textContent = 'Uploading…';
                const response = await fetch(form.action, { method: 'POST', body, headers: { Accept: 'application/json' } });
                if (!response.ok) {
                    if (response.status === 413) throw new Error('The server rejected the upload size. Try a smaller JPEG.');
                    const result = await response.json();
                    throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.message || 'Upload failed. Please retry.');
                }
                window.location.reload();
            } catch (error) {
                feedback.textContent = error.message;
                submitting = false;
                button.disabled = false;
                button.innerHTML = label;
            }
        });
    });
});
