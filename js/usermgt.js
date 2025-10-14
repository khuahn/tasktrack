// usermgt.js - User Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize action confirmations
    initializeActionConfirmations();
});

function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    // Validate username format
    const usernameField = form.querySelector('input[name="username"]');
    if (usernameField && usernameField.value.trim()) {
        const username = usernameField.value.trim();
        if (username.length < 3) {
            showFieldError(usernameField, 'Username must be at least 3 characters');
            isValid = false;
        } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            showFieldError(usernameField, 'Username can only contain letters, numbers, and underscores');
            isValid = false;
        }
    }
    
    // Validate password strength
    const passwordField = form.querySelector('input[name="password"]');
    if (passwordField && passwordField.value.trim()) {
        const password = passwordField.value;
        if (password.length < 6) {
            showFieldError(passwordField, 'Password must be at least 6 characters');
            isValid = false;
        }
    }
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = 'var(--danger)';
    errorDiv.style.fontSize = '0.8rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

function initializeActionConfirmations() {
    // Freeze user confirmation
    const freezeLinks = document.querySelectorAll('a[href*="action=freeze"]');
    freezeLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to freeze this user? They will not be able to log in.')) {
                e.preventDefault();
            }
        });
    });
    
    // Delete user confirmation
    const deleteLinks = document.querySelectorAll('a[href*="action=delete"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
}

// Utility function to show success message
function showSuccessMessage(message) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message success';
    messageDiv.textContent = message;
    
    const container = document.querySelector('.usermgt-container');
    const firstChild = container.firstElementChild;
    container.insertBefore(messageDiv, firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

// Utility function to show error message
function showErrorMessage(message) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message error';
    messageDiv.textContent = message;
    
    const container = document.querySelector('.usermgt-container');
    const firstChild = container.firstElementChild;
    container.insertBefore(messageDiv, firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

// Add CSS for error states
const style = document.createElement('style');
style.textContent = `
    .form-control.error {
        border-color: var(--danger);
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }
    
    .field-error {
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
