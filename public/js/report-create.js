// Keep validation responses on the same page so camera and upload Files survive.
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reportCreateForm');
    if (!form) return;
    const type = form.elements.report_type;
    const contact = form.elements.contact_override;
    const name = form.elements.reporter_name;
    const feedback = document.getElementById('reportSubmitFeedback');
    const button = form.querySelector('button[type="submit"]');
    let submitting = false;
    function validateName() {
        const surrender = document.getElementById('studentSurrenderCheckbox');
        name.required = type.value === 'found' && !surrender?.checked;
        name.disabled = !name.required;
        document.getElementById('reporterNameGroup').classList.toggle('d-none', !name.required);
        name.setCustomValidity(name.required && !name.value.trim() ? 'Name is required for a Found report.' : '');
        document.getElementById('reporterNameRequired').classList.toggle('d-none', !name.required);
    }
    [type, contact, name].forEach(input => input.addEventListener('input', validateName));
    document.getElementById('studentSurrenderCheckbox')?.addEventListener('input', validateName);
    validateName();
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (submitting) return;
        validateName();
        if (!form.reportValidity()) return;
        submitting = true;
        button.disabled = true;
        const label = button.innerHTML;
        button.textContent = 'Submitting…';
        feedback.classList.add('d-none');
        try {
            const body = new FormData(form);
            if (window.ReportPhotos) {
                const photoInput = form.querySelector('input[name="photos[]"]');
                const photos = await window.ReportPhotos.prepare(Array.from(photoInput.files || []), text => { button.textContent = text; });
                body.delete('photos[]');
                photos.forEach(photo => body.append('photos[]', photo));
            }
            button.textContent = 'Saving report…';
            const response = await fetch(form.action, {
                method: 'POST', body,
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (response.status === 413) throw new Error('The server rejected the upload size. Try fewer photos or smaller JPEGs.');
            const result = await response.json().catch(() => { throw new Error('The server could not process this upload. Your entries and photos remain selected.'); });
            if (!response.ok) {
                const messages = Object.values(result.errors || {}).flat();
                Object.entries(result.errors || {}).forEach(([field, errors]) => {
                    const input = document.getElementById(field + '_search') || form.elements.namedItem?.(field) || form.elements.namedItem?.(field.split('.')[0] + '[]');
                    if (input?.classList) {
                        input.classList.add('is-invalid');
                        input.setAttribute('aria-invalid', 'true');
                        const message = document.createElement('div');
                        message.className = 'invalid-feedback';
                        message.textContent = errors[0];
                        input.after(message);
                        input.addEventListener('input', () => { input.classList.remove('is-invalid'); input.removeAttribute('aria-invalid'); message.remove(); }, { once: true });
                    }
                });
                throw new Error(messages.join(' ') || result.message || 'Unable to submit report. Please try again.');
            }
            if (!result.redirect) throw new Error('Unexpected response. Please try again.');
            window.location.assign(result.redirect);
        } catch (error) {
            feedback.textContent = error.message || 'Connection failed. Your photos remain selected. Please retry.';
            feedback.classList.remove('d-none');
            feedback.focus();
            submitting = false;
            button.disabled = false;
            button.innerHTML = label;
        }
    });
});
