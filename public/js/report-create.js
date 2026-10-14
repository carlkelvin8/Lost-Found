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
        name.required = type.value === 'found' && contact.value.trim() !== '';
        name.setCustomValidity(name.required && !name.value.trim() ? 'Reporter name is required for a Found report with Contact Override.' : '');
        document.getElementById('reporterNameRequired').classList.toggle('d-none', !name.required);
    }
    [type, contact, name].forEach(input => input.addEventListener('input', validateName));
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
            const response = await fetch(form.action, {
                method: 'POST', body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await response.json();
            if (!response.ok) {
                const messages = Object.values(result.errors || {}).flat();
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
