/**
 * Premium Form Validation System
 * Provides real-time validation feedback with beautiful UI
 */

(function() {
    'use strict';

    // Validation patterns
    const patterns = {
        email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        phone: /^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/,
        alphaNumeric: /^[a-zA-Z0-9\s\-\.]+$/,
        alpha: /^[a-zA-Z\s\-\.\']+$/,
        schoolId: /^[A-Z0-9\-]+$/i,
        time: /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/,
        url: /^https?:\/\/.+/,
    };

    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        const feedback = [];

        if (password.length >= 8) strength++;
        else feedback.push('At least 8 characters');

        if (/[a-z]/.test(password)) strength++;
        else feedback.push('One lowercase letter');

        if (/[A-Z]/.test(password)) strength++;
        else feedback.push('One uppercase letter');

        if (/[0-9]/.test(password)) strength++;
        else feedback.push('One number');

        if (/[@$!%*?&]/.test(password)) strength++;
        else feedback.push('One special character');

        return { strength, feedback };
    }

    // Show validation message
    function showValidation(input, isValid, message = '') {
        const formGroup = input.closest('.form-group, .mb-3, .mb-4');
        if (!formGroup) return;

        // Remove existing feedback
        const existingFeedback = formGroup.querySelector('.validation-feedback');
        if (existingFeedback) existingFeedback.remove();

        // Remove existing classes
        input.classList.remove('is-valid', 'is-invalid');

        if (message) {
            // Add new feedback
            const feedback = document.createElement('div');
            feedback.className = `validation-feedback ${isValid ? 'valid-feedback' : 'invalid-feedback'}`;
            feedback.style.display = 'block';
            feedback.style.marginTop = '0.5rem';
            feedback.style.fontSize = '0.875rem';
            feedback.innerHTML = `<i class="bi bi-${isValid ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            
            input.parentNode.appendChild(feedback);
            input.classList.add(isValid ? 'is-valid' : 'is-invalid');
        }
    }

    // Validate email
    function validateEmail(input) {
        const value = input.value.trim();
        
        if (!value) {
            showValidation(input, false, 'Email is required');
            return false;
        }

        if (!patterns.email.test(value)) {
            showValidation(input, false, 'Please enter a valid email address');
            return false;
        }

        showValidation(input, true, 'Email looks good!');
        return true;
    }

    // Validate password
    function validatePassword(input) {
        const value = input.value;
        
        if (!value) {
            showValidation(input, false, 'Password is required');
            return false;
        }

        const { strength, feedback } = checkPasswordStrength(value);

        if (strength < 5) {
            showValidation(input, false, `Missing: ${feedback.join(', ')}`);
            return false;
        }

        showValidation(input, true, 'Strong password!');
        return true;
    }

    // Validate password confirmation
    function validatePasswordConfirmation(input) {
        const password = document.querySelector('input[name="password"]');
        const value = input.value;

        if (!value) {
            showValidation(input, false, 'Please confirm your password');
            return false;
        }

        if (password && value !== password.value) {
            showValidation(input, false, 'Passwords do not match');
            return false;
        }

        showValidation(input, true, 'Passwords match!');
        return true;
    }

    // Validate required field
    function validateRequired(input) {
        const value = input.value.trim();
        const label = input.closest('.form-group, .mb-3, .mb-4')?.querySelector('label')?.textContent || 'This field';

        if (!value) {
            showValidation(input, false, `${label} is required`);
            return false;
        }

        if (input.hasAttribute('minlength')) {
            const minLength = parseInt(input.getAttribute('minlength'));
            if (value.length < minLength) {
                showValidation(input, false, `Minimum ${minLength} characters required`);
                return false;
            }
        }

        if (input.hasAttribute('maxlength')) {
            const maxLength = parseInt(input.getAttribute('maxlength'));
            if (value.length > maxLength) {
                showValidation(input, false, `Maximum ${maxLength} characters allowed`);
                return false;
            }
        }

        showValidation(input, true, '');
        return true;
    }

    // Validate phone number
    function validatePhone(input) {
        const value = input.value.trim();

        if (value && !patterns.phone.test(value)) {
            showValidation(input, false, 'Please enter a valid phone number');
            return false;
        }

        if (value) {
            showValidation(input, true, '');
        }
        return true;
    }

    // Validate file upload
    function validateFile(input) {
        const files = input.files;
        const maxSize = parseInt(input.getAttribute('data-max-size') || '4096'); // KB
        const allowedTypes = (input.getAttribute('accept') || '').split(',').map(t => t.trim());

        if (!files || files.length === 0) return true;

        for (let file of files) {
            // Check file size
            if (file.size > maxSize * 1024) {
                showValidation(input, false, `File size must not exceed ${maxSize / 1024}MB`);
                return false;
            }

            // Check file type
            if (allowedTypes.length > 0) {
                const fileExt = '.' + file.name.split('.').pop().toLowerCase();
                const mimeType = file.type;
                
                const isAllowed = allowedTypes.some(type => {
                    if (type.startsWith('.')) {
                        return fileExt === type.toLowerCase();
                    }
                    return mimeType.match(new RegExp(type.replace('*', '.*')));
                });

                if (!isAllowed) {
                    showValidation(input, false, `Only ${allowedTypes.join(', ')} files are allowed`);
                    return false;
                }
            }
        }

        showValidation(input, true, `${files.length} file(s) selected`);
        return true;
    }

    // Validate date
    function validateDate(input) {
        const value = input.value;
        
        if (!value) return true;

        const date = new Date(value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (input.hasAttribute('data-max-date') && input.getAttribute('data-max-date') === 'today') {
            if (date > today) {
                showValidation(input, false, 'Date cannot be in the future');
                return false;
            }
        }

        if (input.hasAttribute('data-min-date')) {
            const minDate = new Date(input.getAttribute('data-min-date'));
            if (date < minDate) {
                showValidation(input, false, 'Date is too far in the past');
                return false;
            }
        }

        showValidation(input, true, '');
        return true;
    }

    // Main validation function
    function validateInput(input) {
        const type = input.type;
        const name = input.name;

        // Skip if disabled or readonly
        if (input.disabled || input.readOnly) return true;

        // Email validation
        if (type === 'email' || name === 'email') {
            return validateEmail(input);
        }

        // Password validation
        if (type === 'password' && name === 'password') {
            return validatePassword(input);
        }

        // Password confirmation
        if (type === 'password' && (name === 'password_confirmation' || name === 'password_confirm')) {
            return validatePasswordConfirmation(input);
        }

        // Phone validation
        if (name === 'contact_no' || name === 'phone' || name === 'contact_info') {
            return validatePhone(input);
        }

        // File validation
        if (type === 'file') {
            return validateFile(input);
        }

        // Date validation
        if (type === 'date') {
            return validateDate(input);
        }

        // Required field validation
        if (input.required || input.hasAttribute('required')) {
            return validateRequired(input);
        }

        return true;
    }

    // Initialize validation on form
    function initFormValidation(form) {
        const inputs = form.querySelectorAll('input, textarea, select');

        inputs.forEach(input => {
            // Validate on blur
            input.addEventListener('blur', function() {
                validateInput(this);
            });

            // Real-time validation for certain fields
            if (input.type === 'email' || input.type === 'password' || input.name === 'password_confirmation') {
                input.addEventListener('input', debounce(function() {
                    validateInput(this);
                }, 500));
            }

            // File input change
            if (input.type === 'file') {
                input.addEventListener('change', function() {
                    validateInput(this);
                });
            }
        });

        // Form submit validation
        form.addEventListener('submit', function(e) {
            let isValid = true;

            inputs.forEach(input => {
                if (!validateInput(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                
                // Scroll to first error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }

                // Show error alert
                showAlert('Please fix the errors in the form before submitting.', 'danger');
            }
        });
    }

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Show alert
    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';
        alertDiv.style.animation = 'slideInRight 0.3s ease-out';
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'danger' ? 'exclamation-triangle' : 'info-circle'}"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        const forms = document.querySelectorAll('form[data-validate="true"], form.needs-validation');
        forms.forEach(form => initFormValidation(form));
    }

    // Export for manual initialization
    window.FormValidation = {
        init: initFormValidation,
        validate: validateInput,
        showAlert: showAlert
    };
})();
