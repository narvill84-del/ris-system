/**
 * Form Handler - AJAX Operations
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

// Get App URL from current location
const APP_PATH = window.location.pathname.split('/pages/')[0] || window.location.pathname.split('/print/')[0] || '/ris-form-system';
const API_BASE_URL = APP_PATH + '/api';

// Collect Line Items - Helper Function
function collectLineItems() {
    const lineItems = [];
    const tableBody = document.querySelector('table tbody');
    
    if (!tableBody) {
        console.error('Table body not found');
        return lineItems;
    }
    
    const rows = tableBody.querySelectorAll('tr');
    console.log('Total rows in table:', rows.length);
    
    rows.forEach((row, rowIndex) => {
        // Get all input fields in this row
        const inputs = row.querySelectorAll('input[type="text"], input[type="number"]');
        
        if (inputs.length >= 6) {
            const stockNumber = inputs[0].value || '';
            const unit = inputs[1].value || '';
            const description = inputs[2].value || '';
            const quantityRequested = parseInt(inputs[3].value) || 0;
            const quantityReceived = parseInt(inputs[4].value) || 0;
            const remarks = inputs[5].value || '';
            
            console.log(`Row ${rowIndex}:`, {
                stock_number: stockNumber,
                unit: unit,
                description: description,
                quantity_requested: quantityRequested,
                quantity_received: quantityReceived,
                remarks: remarks
            });
            
            // Add all rows, even empty ones (will be filtered by backend)
            const item = {
                stock_number: stockNumber,
                unit: unit,
                description: description,
                quantity_requested: quantityRequested,
                quantity_received: quantityReceived,
                remarks: remarks
            };
            lineItems.push(item);
            console.log('Added item to collection:', item);
        }
    });
    
    console.log('Final line items collected:', lineItems);
    console.log('Total items to save:', lineItems.length);
    return lineItems;
}

// Save RIS Form
function saveRISForm() {
    console.log('=== SAVE FORM STARTED ===');
    
    if (!validateRISForm()) {
        console.log('Form validation failed');
        return;
    }

    const formElement = document.getElementById('ris-form');
    const formData = new FormData(formElement);
    
    // Collect line items
    const lineItems = collectLineItems();
    
    // Check if there are any items with descriptions
    const hasDescription = lineItems.some(item => item.description && item.description.trim());
    
    if (!hasDescription) {
        showAlert('Please add at least one line item with a description', 'warning');
        console.log('No items with description found');
        return;
    }
    
    console.log('=== SENDING TO API ===');
    console.log('Form data entries:');
    for (let [key, value] of formData.entries()) {
        if (key !== 'line_items') {
            console.log(`  ${key}: ${value}`);
        }
    }
    
    formData.append('line_items', JSON.stringify(lineItems));
    console.log('Line items JSON:', JSON.stringify(lineItems, null, 2));

    const saveBtn = document.querySelector('button[name="save"]');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    fetch(`${API_BASE_URL}/save-form.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('=== API RESPONSE ===');
        console.log(data);
        
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;

        if (data.success) {
            console.log('✓ Success! RIS ID:', data.ris_id);
            showAlert('Form saved successfully!', 'success');
            setTimeout(() => {
                window.location.href = `view.php?id=${data.ris_id}`;
            }, 1500);
        } else {
            console.log('✗ Failed:', data.message);
            showAlert(data.message || 'Failed to save form', 'danger');
        }
    })
    .catch(error => {
        console.error('=== FETCH ERROR ===');
        console.error(error);
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
        showAlert('An error occurred while saving the form: ' + error.message, 'danger');
    });
}

// Update RIS Form
function updateRISForm(risId) {
    console.log('=== UPDATE FORM STARTED ===');
    
    if (!validateRISForm()) {
        return;
    }

    const formData = new FormData(document.getElementById('ris-form'));
    formData.append('id', risId);

    // Collect line items
    const lineItems = collectLineItems();
    
    const hasDescription = lineItems.some(item => item.description && item.description.trim());
    
    if (!hasDescription) {
        showAlert('Please add at least one line item with a description', 'warning');
        return;
    }
    
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
        console.log('API Response:', data);
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
        console.error('Error:', error);
        updateBtn.disabled = false;
        updateBtn.textContent = originalText;
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

// Print RIS Form
function printRISForm(risId) {
    const printUrl = APP_PATH + '/print/print-form.php?id=' + risId;
    console.log('Opening print URL:', printUrl);
    window.open(printUrl, '_blank');
}

// Generate Report
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

// Export Table to CSV
function exportTableToCSV(filename = 'export.csv') {
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
            rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        
        if (rowData.length > 0) {
            csv.push(rowData.join(','));
        }
    });

    const csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
    const link = document.createElement('a');
    link.setAttribute('href', csvContent);
    link.setAttribute('download', filename);
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
