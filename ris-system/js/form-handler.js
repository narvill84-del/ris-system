/**
 * Form Handler - AJAX Operations
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

// Get App URL from current location
const APP_PATH = window.location.pathname.split('/pages/')[0] || window.location.pathname.split('/print/')[0] || '/ris-form-system';
const API_BASE_URL = APP_PATH + '/api';

// Save RIS Form
function saveRISForm() {
    if (!validateRISForm()) {
        return;
    }

    const formData = new FormData(document.getElementById('ris-form'));
    
    // Collect line items
    const lineItems = [];
    const lineItemRows = document.querySelectorAll('table tbody tr');
    
    lineItemRows.forEach((row, index) => {
        const item = {
            stock_number: row.querySelector(`[name="stock_number_${index}"]`)?.value || '',
            unit: row.querySelector(`[name="unit_${index}"]`)?.value || '',
            description: row.querySelector(`[name="description_${index}"]`)?.value || '',
            quantity_requested: row.querySelector(`[name="quantity_requested_${index}"]`)?.value || 0,
            quantity_received: row.querySelector(`[name="quantity_received_${index}"]`)?.value || 0,
            remarks: row.querySelector(`[name="remarks_${index}"]`)?.value || ''
        };
        lineItems.push(item);
    });

    formData.append('line_items', JSON.stringify(lineItems));

    const saveBtn = document.querySelector('button[name="save"]');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    fetch(`${API_BASE_URL}/save-form.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;

        if (data.success) {
            showAlert('Form saved successfully!', 'success');
            setTimeout(() => {
                window.location.href = `view.php?id=${data.ris_id}`;
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to save form', 'danger');
        }
    })
    .catch(error => {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
        console.error('Error:', error);
        showAlert('An error occurred while saving the form', 'danger');
    });
}

// Update RIS Form
function updateRISForm(risId) {
    if (!validateRISForm()) {
        return;
    }

    const formData = new FormData(document.getElementById('ris-form'));
    formData.append('id', risId);

    // Collect line items
    const lineItems = [];
    const lineItemRows = document.querySelectorAll('table tbody tr');
    
    lineItemRows.forEach((row, index) => {
        const item = {
            stock_number: row.querySelector(`[name="stock_number_${index}"]`)?.value || '',
            unit: row.querySelector(`[name="unit_${index}"]`)?.value || '',
            description: row.querySelector(`[name="description_${index}"]`)?.value || '',
            quantity_requested: row.querySelector(`[name="quantity_requested_${index}"]`)?.value || 0,
            quantity_received: row.querySelector(`[name="quantity_received_${index}"]`)?.value || 0,
            remarks: row.querySelector(`[name="remarks_${index}"]`)?.value || ''
        };
        lineItems.push(item);
    });

    formData.append('line_items', JSON.stringify(lineItems));

    const updateBtn = document.querySelector('button[name="update"]');
    const originalText = updateBtn.textContent;
    updateBtn.disabled = true;
    updateBtn.textContent = 'Updating...';

    fetch(`${API_BASE_URL}/update-form.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        updateBtn.disabled = false;
        updateBtn.textContent = originalText;

        if (data.success) {
            showAlert('Form updated successfully!', 'success');
            setTimeout(() => {
                window.location.href = `view.php?id=${risId}`;
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to update form', 'danger');
        }
    })
    .catch(error => {
        updateBtn.disabled = false;
        updateBtn.textContent = originalText;
        console.error('Error:', error);
        showAlert('An error occurred while updating the form', 'danger');
    });
}

// Delete RIS Form
function deleteRISForm(risId) {
    if (!confirm('Are you sure you want to delete this form? This action cannot be undone.')) {
        return;
    }

    fetch(`${API_BASE_URL}/delete-form.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: risId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Form deleted successfully!', 'success');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to delete form', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while deleting the form', 'danger');
    });
}

// Get RIS Form Data
function getRISForm(risId) {
    return fetch(`${API_BASE_URL}/get-form.php?id=${risId}`)
        .then(response => response.json())
        .catch(error => {
            console.error('Error:', error);
            return null;
        });
}

// Get All RIS Forms
function getAllRISForms(page = 1, limit = 10) {
    return fetch(`${API_BASE_URL}/get-forms.php?page=${page}&limit=${limit}`)
        .then(response => response.json())
        .catch(error => {
            console.error('Error:', error);
            return null;
        });
}

// Add Line Item Row
function addLineItem() {
    const tableBody = document.querySelector('table tbody');
    const rowCount = tableBody.rows.length;
    
    const newRow = tableBody.insertRow();
    newRow.innerHTML = `
        <td>
            <input type="text" name="stock_number_${rowCount}" class="form-control" placeholder="Stock No.">
        </td>
        <td>
            <input type="text" name="unit_${rowCount}" class="form-control" placeholder="Unit">
        </td>
        <td>
            <input type="text" name="description_${rowCount}" class="form-control" placeholder="Description" required>
        </td>
        <td>
            <input type="number" name="quantity_requested_${rowCount}" class="form-control" placeholder="Qty" required min="1">
        </td>
        <td>
            <input type="number" name="quantity_received_${rowCount}" class="form-control" placeholder="Qty" min="0">
        </td>
        <td>
            <input type="text" name="remarks_${rowCount}" class="form-control" placeholder="Remarks">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-small" onclick="removeLineItem(this)">Remove</button>
        </td>
    `;
}

// Remove Line Item Row
function removeLineItem(button) {
    const row = button.closest('tr');
    row.remove();
}

// Print RIS Form - FIXED
function printRISForm(risId) {
    const printUrl = APP_PATH + '/print/print-form.php?id=' + risId;
    console.log('Opening print URL:', printUrl);
    window.open(printUrl, '_blank');
}

// Generate Report - FIXED
function generateReport(filters = {}) {
    const params = new URLSearchParams();
    
    if (filters.start_date) params.append('start_date', filters.start_date);
    if (filters.end_date) params.append('end_date', filters.end_date);
    if (filters.office_name) params.append('office_name', filters.office_name);
    if (filters.status) params.append('status', filters.status);

    const reportUrl = APP_PATH + '/print/print-report.php?' + params.toString();
    console.log('Opening report URL:', reportUrl);
    window.open(reportUrl, '_blank');
}

// Export Form List to CSV
function exportFormListToCSV() {
    const table = document.querySelector('table');
    if (!table) {
        showAlert('No data to export', 'danger');
        return;
    }

    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        let rowData = [];
        const cols = row.querySelectorAll('td, th');
        
        cols.forEach(col => {
            // Exclude action buttons
            if (!col.querySelector('button')) {
                rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
            }
        });
        
        if (rowData.length > 0) {
            csv.push(rowData.join(','));
        }
    });

    const csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
    const link = document.createElement('a');
    link.setAttribute('href', csvContent);
    link.setAttribute('download', `RIS_Forms_${new Date().toISOString().split('T')[0]}.csv`);
    link.click();
}

// Load Form into Edit Mode
function loadFormForEdit(risId) {
    getRISForm(risId).then(data => {
        if (data && data.success) {
            const form = data.form;
            
            document.getElementById('office_name').value = form.office_name;
            document.getElementById('ris_number').value = form.ris_number;
            document.getElementById('sai_number').value = form.sai_number || '';
            document.getElementById('responsibility_center_code').value = form.responsibility_center_code || '';
            document.getElementById('ris_date').value = form.ris_date;
            document.getElementById('sai_date').value = form.sai_date || '';
            document.getElementById('purpose').value = form.purpose || '';
            document.getElementById('requested_by').value = form.requested_by || '';
            document.getElementById('requested_by_designation').value = form.requested_by_designation || '';
            document.getElementById('requested_by_date').value = form.requested_by_date || '';
            document.getElementById('approved_by').value = form.approved_by || '';
            document.getElementById('approved_by_designation').value = form.approved_by_designation || '';
            document.getElementById('approved_by_date').value = form.approved_by_date || '';
            document.getElementById('received_by').value = form.received_by || '';
            document.getElementById('received_by_designation').value = form.received_by_designation || '';
            document.getElementById('received_by_date').value = form.received_by_date || '';

            // Load line items
            const tableBody = document.querySelector('table tbody');
            tableBody.innerHTML = '';

            if (data.line_items && data.line_items.length > 0) {
                data.line_items.forEach((item, index) => {
                    const row = tableBody.insertRow();
                    row.innerHTML = `
                        <td>
                            <input type="text" name="stock_number_${index}" class="form-control" value="${item.stock_number || ''}">
                        </td>
                        <td>
                            <input type="text" name="unit_${index}" class="form-control" value="${item.unit || ''}">
                        </td>
                        <td>
                            <input type="text" name="description_${index}" class="form-control" value="${item.description || ''}" required>
                        </td>
                        <td>
                            <input type="number" name="quantity_requested_${index}" class="form-control" value="${item.quantity_requested || 0}" required min="1">
                        </td>
                        <td>
                            <input type="number" name="quantity_received_${index}" class="form-control" value="${item.quantity_received || 0}" min="0">
                        </td>
                        <td>
                            <input type="text" name="remarks_${index}" class="form-control" value="${item.remarks || ''}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-small" onclick="removeLineItem(this)">Remove</button>
                        </td>
                    `;
                });
            }
        } else {
            showAlert('Failed to load form data', 'danger');
        }
    });
}

// Initialize Date Pickers
document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.max = new Date().toISOString().split('T')[0];
    });
});
