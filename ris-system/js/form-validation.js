/**
 * Form Validation & Helper Functions
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

// Validate Required Field
function validateRequired(fieldId, fieldName) {
    const field = document.getElementById(fieldId);
    if (!field || field.value.trim() === '') {
        showError(fieldId, `${fieldName} is required`);
        return false;
    }
    clearError(fieldId);
    return true;
}

// Validate Email
function validateEmail(fieldId, fieldName) {
    const field = document.getElementById(fieldId);
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!field || field.value.trim() === '') {
        showError(fieldId, `${fieldName} is required`);
        return false;
    }
    
    if (!emailRegex.test(field.value)) {
        showError(fieldId, `${fieldName} must be a valid email`);
        return false;
    }
    
    clearError(fieldId);
    return true;
}

// Validate Number
function validateNumber(fieldId, fieldName) {
    const field = document.getElementById(fieldId);
    const numberRegex = /^\d+$/;
    
    if (!field || field.value.trim() === '') {
        showError(fieldId, `${fieldName} is required`);
        return false;
    }
    
    if (!numberRegex.test(field.value)) {
        showError(fieldId, `${fieldName} must be a number`);
        return false;
    }
    
    clearError(fieldId);
    return true;
}

// Validate Date
function validateDate(fieldId, fieldName) {
    const field = document.getElementById(fieldId);
    if (!field || field.value.trim() === '') {
        showError(fieldId, `${fieldName} is required`);
        return false;
    }
    
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(field.value)) {
        showError(fieldId, `${fieldName} must be a valid date (YYYY-MM-DD)`);
        return false;
    }
    
    clearError(fieldId);
    return true;
}

// Validate Min Length
function validateMinLength(fieldId, fieldName, minLength) {
    const field = document.getElementById(fieldId);
    if (!field || field.value.trim().length < minLength) {
        showError(fieldId, `${fieldName} must be at least ${minLength} characters`);
        return false;
    }
    clearError(fieldId);
    return true;
}

// Validate Max Length
function validateMaxLength(fieldId, fieldName, maxLength) {
    const field = document.getElementById(fieldId);
    if (!field || field.value.trim().length > maxLength) {
        showError(fieldId, `${fieldName} must not exceed ${maxLength} characters`);
        return false;
    }
    clearError(fieldId);
    return true;
}

// Show Error Message
function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorId = `${fieldId}-error`;
    
    // Remove existing error if present
    const existingError = document.getElementById(errorId);
    if (existingError) {
        existingError.remove();
    }
    
    if (field) {
        field.classList.add('is-invalid');
        const errorDiv = document.createElement('small');
        errorDiv.id = errorId;
        errorDiv.className = 'text-danger d-block mt-1';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
}

// Clear Error Message
function clearError(fieldId) {
    const field = document.getElementById(fieldId);
    const errorId = `${fieldId}-error`;
    const existingError = document.getElementById(errorId);
    
    if (field) {
        field.classList.remove('is-invalid');
    }
    
    if (existingError) {
        existingError.remove();
    }
}

// Clear All Errors
function clearAllErrors() {
    const errorElements = document.querySelectorAll('[id$="-error"]');
    errorElements.forEach(el => el.remove());
    
    const invalidFields = document.querySelectorAll('.is-invalid');
    invalidFields.forEach(field => field.classList.remove('is-invalid'));
}

// Validate RIS Form
function validateRISForm() {
    clearAllErrors();
    let isValid = true;

    // Validate Office Name
    if (!validateRequired('office_name', 'Office Name')) {
        isValid = false;
    }

    // Validate RIS Number
    if (!validateRequired('ris_number', 'RIS Number')) {
        isValid = false;
    }

    // Validate RIS Date
    if (!validateDate('ris_date', 'RIS Date')) {
        isValid = false;
    }

    // Validate Purpose
    if (!validateRequired('purpose', 'Purpose')) {
        isValid = false;
    }

    // Validate Requested By
    if (!validateRequired('requested_by', 'Requested By')) {
        isValid = false;
    }

    // Validate Requested By Designation
    if (!validateRequired('requested_by_designation', 'Requested By Designation')) {
        isValid = false;
    }

    // Validate Approved By
    if (!validateRequired('approved_by', 'Approved By')) {
        isValid = false;
    }

    // Validate Approved By Designation
    if (!validateRequired('approved_by_designation', 'Approved By Designation')) {
        isValid = false;
    }

    // Validate at least one line item
    const lineItems = document.querySelectorAll('table tbody tr');
    if (lineItems.length === 0) {
        showAlert('Please add at least one item', 'danger');
        isValid = false;
    }

    return isValid;
}

// Show Alert Message
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Format Currency
function formatCurrency(value) {
    const numValue = parseFloat(value);
    if (isNaN(numValue)) return '0.00';
    return numValue.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Format Date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', options);
}

// Get URL Parameters
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    const results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

// Copy to Clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Copied to clipboard!', 'success');
    }).catch(() => {
        showAlert('Failed to copy', 'danger');
    });
}

// Print Page
function printPage() {
    window.print();
}

// Export to CSV
function exportTableToCSV(filename) {
    const table = document.querySelector('table');
    if (!table) {
        showAlert('No table found to export', 'danger');
        return;
    }

    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        let rowData = [];
        const cols = row.querySelectorAll('td, th');
        
        cols.forEach(col => {
            rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        
        csv.push(rowData.join(','));
    });

    const csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
    const link = document.createElement('a');
    link.setAttribute('href', csvContent);
    link.setAttribute('download', filename || 'export.csv');
    link.click();
}

// Export to PDF (requires external library)
function exportToPDF(filename) {
    showAlert('PDF export feature requires configuration', 'info');
}

// Add Event Listeners for Real-time Validation
document.addEventListener('DOMContentLoaded', function() {
    const formInputs = document.querySelectorAll('input, textarea, select');
    
    formInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('data-validate')) {
                const validateType = this.getAttribute('data-validate');
                const fieldName = this.getAttribute('data-field-name') || this.name;
                
                switch(validateType) {
                    case 'required':
                        validateRequired(this.id, fieldName);
                        break;
                    case 'email':
                        validateEmail(this.id, fieldName);
                        break;
                    case 'number':
                        validateNumber(this.id, fieldName);
                        break;
                    case 'date':
                        validateDate(this.id, fieldName);
                        break;
                }
            }
        });
    });
});
