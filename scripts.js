// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        const formGroup = field.closest('.form-group');
        if (!field.value.trim()) {
            formGroup.classList.add('error');
            const errorText = formGroup.querySelector('.error-text') || document.createElement('div');
            errorText.className = 'error-text';
            errorText.textContent = 'This field is required';
            if (!formGroup.querySelector('.error-text')) {
                formGroup.appendChild(errorText);
            }
            isValid = false;
        } else {
            formGroup.classList.remove('error');
            const errorText = formGroup.querySelector('.error-text');
            if (errorText) errorText.remove();
        }
    });

    return isValid;
}

// Loading State
function setLoading(formId, isLoading) {
    const form = document.getElementById(formId);
    if (!form) return;

    if (isLoading) {
        form.classList.add('loading');
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';
        }
    } else {
        form.classList.remove('loading');
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = submitButton.getAttribute('data-original-text') || 'Submit';
        }
    }
}

// Confirmation Dialog
function showConfirmation(message, onConfirm, onCancel) {
    const dialog = document.createElement('div');
    dialog.className = 'confirmation-dialog';
    dialog.innerHTML = `
        <div class="dialog-content">
            <p>${message}</p>
            <div class="dialog-buttons">
                <button onclick="this.closest('.confirmation-dialog').remove()">Cancel</button>
                <button class="confirm-btn">Confirm</button>
            </div>
        </div>
    `;

    document.body.appendChild(dialog);
    dialog.style.display = 'block';

    const confirmBtn = dialog.querySelector('.confirm-btn');
    confirmBtn.onclick = () => {
        dialog.remove();
        if (onConfirm) onConfirm();
    };

    if (onCancel) {
        const cancelBtn = dialog.querySelector('button:not(.confirm-btn)');
        cancelBtn.onclick = () => {
            dialog.remove();
            onCancel();
        };
    }
}

// Error Message Display
function showError(message, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message visible';
    errorDiv.textContent = message;
    container.insertBefore(errorDiv, container.firstChild);

    setTimeout(() => {
        errorDiv.remove();
    }, 5000);
}

// Success Message Display
function showSuccess(message, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const successDiv = document.createElement('div');
    successDiv.className = 'success-message visible';
    successDiv.textContent = message;
    container.insertBefore(successDiv, container.firstChild);

    setTimeout(() => {
        successDiv.remove();
    }, 5000);
}

// Form Submission Handler
function handleFormSubmit(formId, submitUrl, successCallback) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.onsubmit = async (e) => {
        e.preventDefault();
        
        if (!validateForm(formId)) {
            showError('Please fill in all required fields', formId);
            return;
        }

        setLoading(formId, true);

        try {
            const formData = new FormData(form);
            const response = await fetch(submitUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showSuccess(result.message || 'Operation successful', formId);
                if (successCallback) successCallback(result);
            } else {
                showError(result.message || 'An error occurred', formId);
            }
        } catch (error) {
            showError('An error occurred while processing your request', formId);
        } finally {
            setLoading(formId, false);
        }
    };
}

// Initialize form handlers
document.addEventListener('DOMContentLoaded', () => {
    // Add data-original-text to all submit buttons
    document.querySelectorAll('button[type="submit"]').forEach(button => {
        button.setAttribute('data-original-text', button.textContent);
    });

    // Add confirmation to delete buttons
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.onclick = (e) => {
            e.preventDefault();
            showConfirmation(
                'Are you sure you want to delete this item?',
                () => {
                    const form = button.closest('form');
                    if (form) form.submit();
                }
            );
        };
    });
}); 