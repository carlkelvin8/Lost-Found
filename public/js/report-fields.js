// Enhance existing selects; the original select remains usable without JavaScript.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('select[data-searchable]').forEach(select => {
        const options = Array.from(select.options).filter(option => option.value);
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control';
        input.id = select.id + '_search';
        input.autocomplete = 'off';
        input.setAttribute('list', select.id + '_options');
        input.setAttribute('aria-describedby', select.getAttribute('aria-describedby') || '');
        input.placeholder = select.name === 'category_id' ? 'Type to search categories' : 'Type to search locations';
        input.value = options.find(option => option.value === select.value)?.text || '';
        input.required = select.required;
        const list = document.createElement('datalist');
        list.id = select.id + '_options';
        options.forEach(option => {
            const choice = document.createElement('option');
            choice.value = option.text;
            list.appendChild(choice);
        });
        select.before(input, list);
        select.hidden = true;
        select.required = false;
        document.querySelector(`label[for="${select.id}"]`)?.setAttribute('for', input.id);
        let syncing = false;
        function sync() {
            const typed = input.value.trim().replace(/\s+/g, ' ').toLocaleLowerCase();
            const option = options.find(option => option.text.trim().replace(/\s+/g, ' ').toLocaleLowerCase() === typed);
            select.value = option?.value || '';
            input.setCustomValidity(typed && !option ? 'Choose an existing option from the suggestions. Enter a custom room in the specific location field.' : '');
            syncing = true;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            syncing = false;
        }
        input.addEventListener('input', sync);
        select.addEventListener('change', () => {
            if (syncing) return;
            input.value = options.find(option => option.value === select.value)?.text || '';
            input.setCustomValidity('');
        });
    });
    document.querySelectorAll('form').forEach(form => {
        const location = form.elements.location_id;
        const detail = form.elements.location_detail;
        if (!location || !detail) return;
        function validateLocation() {
            detail.required = !location.value;
            detail.setCustomValidity(!location.value && !detail.value.trim() ? 'Choose a location or enter a specific location.' : '');
        }
        location.addEventListener('change', validateLocation);
        detail.addEventListener('input', validateLocation);
        validateLocation();
    });
    document.querySelectorAll('[name="item_name"], [name="color"], [name="item_description"]').forEach(input => {
        const validate = () => input.setCustomValidity(input.required && !input.value.trim() ? 'Please complete this field.' : '');
        input.addEventListener('input', validate);
        validate();
    });
    document.querySelectorAll('#reportCreateForm input, #reportCreateForm textarea, #editForm input, #editForm textarea').forEach(input => {
        input.addEventListener('invalid', () => {
            input.classList.add('is-invalid');
            input.setAttribute('aria-invalid', 'true');
            let feedback = input.parentElement.querySelector('.field-validation-message');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback field-validation-message';
                input.after(feedback);
            }
            feedback.textContent = input.validationMessage;
        });
        input.addEventListener('input', () => {
            input.classList.remove('is-invalid');
            input.removeAttribute('aria-invalid');
            input.parentElement.querySelector('.field-validation-message')?.remove();
        });
    });
});
