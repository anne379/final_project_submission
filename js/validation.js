
document.addEventListener('DOMContentLoaded', () => {

    // Configuration
    const patterns = {
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/, // Min 8, Lower, Upper, Digit, Special
        email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        phone: /^\+?[0-9]{10,15}$/, // 10-15 digits, optional +
        id_number: /^[a-zA-Z0-9]{6,20}$/, // 6-20 Alphanumeric
        account_number: /^\d{10}$/ // Exact 10 digits
    };

    const messages = {
        password: "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.",
        email: "Please enter a valid email address.",
        phone: "Please enter a valid phone number (10-15 digits, no spaces).",
        id_number: "ID Number must be 6-20 alphanumeric characters.",
        account_number: "Account Number must be exactly 10 digits."
    };

    // Helper functions
    const showError = (input, msg) => {
        // Clear existing error
        clearError(input);

        const parent = input.parentElement;
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-400 text-xs mt-1 validation-error';
        errorDiv.innerText = msg;
        parent.appendChild(errorDiv);

        input.classList.add('border-red-500');
        input.classList.remove('border-white/10'); // Remove default border style if present
    };

    const clearError = (input) => {
        const parent = input.parentElement;
        const existing = parent.querySelector('.validation-error');
        if (existing) {
            existing.remove();
        }
        input.classList.remove('border-red-500');
        input.classList.add('border-white/10');
    };

    const validateInput = (input) => {
        const name = input.name;
        const value = input.value.trim();

        // 1. Required Check (if attribute exists)
        if (input.hasAttribute('required') && value === '') {
            showError(input, "This field is required.");
            return false;
        }

        // 2. Pattern Checks
        if (name.includes('password') && name !== 'current_password') { // Don't validate check against current password logic, only new/confirm
            // Special case: Confirm Password
            if (name === 'confirm_password') {
                // Find the main password field (try 'password' or 'new_password')
                let mainPass = input.form.querySelector('input[name="password"]') || input.form.querySelector('input[name="new_password"]');
                if (mainPass && value !== mainPass.value) {
                    showError(input, "Passwords do not match.");
                    return false;
                }
            } else {
                // Main Password validation
                if (value !== '' && !patterns.password.test(value)) {
                    showError(input, messages.password);
                    return false;
                }
            }
        }

        if ((name === 'email' || name === 'email_address') && value !== '') {
            if (!patterns.email.test(value)) {
                showError(input, messages.email);
                return false;
            }
        }

        if ((name === 'phone' || name === 'phone_number') && value !== '') {
            if (!patterns.phone.test(value)) {
                showError(input, messages.phone);
                return false;
            }
        }

        if (name === 'id_number' && value !== '') {
            if (!patterns.id_number.test(value)) {
                showError(input, messages.id_number);
                return false;
            }
        }

        if (name === 'account_number' && value !== '') {
            if (!patterns.account_number.test(value)) {
                showError(input, messages.account_number);
                return false;
            }
        }

        // Attach Listeners ONLY to forms that explicitly request validation
        const forms = document.querySelectorAll('form[data-validate="true"]');
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea');

            // Real-time validation on blur
            inputs.forEach(input => {
                input.addEventListener('blur', () => validateInput(input));
                // Optional: validate on input change (typing) - might be too aggressive for regex often
                input.addEventListener('input', () => {
                    if (input.classList.contains('border-red-500')) {
                        validateInput(input);
                    }
                });
            });

            // Form Submit Validation
            form.addEventListener('submit', (e) => {
                let isValid = true;
                inputs.forEach(input => {
                    if (!validateInput(input)) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });

    });
