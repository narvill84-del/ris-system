// Global Variables
let currentRisId = null;
let allRisData = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadRISList();
    loadResponsibilityCenters();
    setupEventListeners();
    setCurrentDate();
});

// Setup Event Listeners
function setupEventListeners() {
    // New RIS Button
    document.getElementById('newRisBtn').addEventListener('click', showRisForm);
    
    // Form Submit
    document.getElementById('risFormElement').addEventListener('submit', saveRIS);
    
    // Cancel Form
    document.getElementById('cancelFormBtn').addEventListener('click', showRisList);
    
    // Item Modal
    document.getElementById('closeItemModal').addEventListener('click', closeItemModal);
    document.getElementById('cancelItemBtn').addEventListener('click', closeItemModal);
    document.getElementById('addItemBtn').addEventListener('click', showAddItemForm);
    document.getElementById('itemFormElement').addEventListener('submit', saveItem);
    
    // Back from Items
    document.getElementById('backFromItemsBtn').addEventListener('click', showRisList);
    
    // Print Button
    document.getElementById('printBtn').addEventListener('click', printRIS);
    
    // Search
    document.getElementById('searchInput').addEventListener('keyup', searchRIS);
}

// Set Current Date
function setCurrentDate() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('risDate').value = today;
    document.getElementById('requestedByDate').value = today;
    document.getElementById('approvedDate').value = today;
    document.getElementById('receivedDate').value = today;
}

// Load RIS List
function loadRISList() {
    fetch('api.php?action=read_ris')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allRisData = data.data;
                populateRisTable(allRisData);
            } else {
                showNotification('Error loading RIS data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading RIS data', 'error');
        });
}

// Populate RIS Table
function populateRisTable(data) {
    const tbody = document.getElementById('risTableBody');
    tbody.innerHTML = '';
    
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No RIS records found</td></tr>';
        return;
    }
    
    data.forEach(ris => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${ris.ris_number}</strong></td>
            <td>${formatDate(ris.ris_date)}</td>
            <td>${ris.office}</td>
            <td>${ris.responsibility_center_code || '-'}</td>
            <td>${ris.requested_by || '-'}</td>
            <td class="text-center">
                <button class="btn btn-view" onclick="viewItems(${ris.id})">View Items</button>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-edit" onclick="editRIS(${ris.id})">Edit</button>
                    <button class="btn btn-danger" onclick="deleteRIS(${ris.id})">Delete</button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Show RIS Form
function showRisForm() {
    currentRisId = null;
    document.getElementById('risId').value = '';
    document.getElementById('risFormElement').reset();
    document.getElementById('formTitle').textContent = 'New Requisition and Issue Slip';
    setCurrentDate();
    showSection('risForm');
}

// Show RIS List
function showRisList() {
    showSection('risList');
    loadRISList();
}

// Show Section
function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById(sectionId).classList.add('active');
}

// Save RIS
function saveRIS(e) {
    e.preventDefault();
    
    const risData = {
        id: document.getElementById('risId').value,
        ris_number: document.getElementById('risNumber').value,
        ris_date: document.getElementById('risDate').value,
        sai_number: document.getElementById('saiNumber').value || null,
        sai_date: document.getElementById('saiDate').value || null,
        office: document.getElementById('office').value,
        responsibility_center_code: document.getElementById('responsibilityCenter').value,
        purpose: document.getElementById('purpose').value || null,
        requested_by: document.getElementById('requestedBy').value || null,
        requested_by_designation: document.getElementById('requestedByDesignation').value || null,
        requested_by_date: document.getElementById('requestedByDate').value || null,
        approved_by: document.getElementById('approvedBy').value || null,
        approved_by_designation: document.getElementById('approvedByDesignation').value || null,
        approved_date: document.getElementById('approvedDate').value || null,
        received_by: document.getElementById('receivedBy').value || null,
        received_by_designation: document.getElementById('receivedByDesignation').value || null,
        received_date: document.getElementById('receivedDate').value || null
    };
    
    const action = risData.id ? 'update_ris' : 'create_ris';
    const method = risData.id ? 'PUT' : 'POST';
    
    fetch(`api.php?action=${action}`, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(risData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            if (!risData.id) {
                currentRisId = data.id;
                viewItems(data.id);
            } else {
                showRisList();
            }
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving RIS', 'error');
    });
}

// Edit RIS
function editRIS(id) {
    fetch(`api.php?action=get_ris&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const ris = data.data;
                document.getElementById('risId').value = ris.id;
                document.getElementById('risNumber').value = ris.ris_number;
                document.getElementById('risDate').value = ris.ris_date;
                document.getElementById('saiNumber').value = ris.sai_number || '';
                document.getElementById('saiDate').value = ris.sai_date || '';
                document.getElementById('office').value = ris.office;
                document.getElementById('responsibilityCenter').value = ris.responsibility_center_code || '';
                document.getElementById('purpose').value = ris.purpose || '';
                document.getElementById('requestedBy').value = ris.requested_by || '';
                document.getElementById('requestedByDesignation').value = ris.requested_by_designation || '';
                document.getElementById('requestedByDate').value = ris.requested_by_date || '';
                document.getElementById('approvedBy').value = ris.approved_by || '';
                document.getElementById('approvedByDesignation').value = ris.approved_by_designation || '';
                document.getElementById('approvedDate').value = ris.approved_date || '';
                document.getElementById('receivedBy').value = ris.received_by || '';
                document.getElementById('receivedByDesignation').value = ris.received_by_designation || '';
                document.getElementById('receivedDate').value = ris.received_date || '';
                
                document.getElementById('formTitle').textContent = `Edit Requisition and Issue Slip - ${ris.ris_number}`;
                currentRisId = id;
                showSection('risForm');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading RIS data', 'error');
        });
}

// Delete RIS
function deleteRIS(id) {
    if (confirm('Are you sure you want to delete this RIS?')) {
        fetch(`api.php?action=delete_ris&id=${id}`, { method: 'DELETE' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    loadRISList();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error deleting RIS', 'error');
            });
    }
}

// View Items
function viewItems(id) {
    currentRisId = id;
    
    // Get RIS Details
    fetch(`api.php?action=get_ris&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const ris = data.data;
                const infoBox = document.getElementById('risInfoBox');
                infoBox.innerHTML = `
                    <h4>RIS No.: ${ris.ris_number}</h4>
                    <p><strong>Date:</strong> ${formatDate(ris.ris_date)}</p>
                    <p><strong>Office:</strong> ${ris.office}</p>
                    <p><strong>Purpose:</strong> ${ris.purpose || 'N/A'}</p>
                `;
            }
        });
    
    // Get Items
    fetch(`api.php?action=get_items&ris_id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateItemsTable(data.data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading items', 'error');
        });
    
    showSection('itemsView');
}

// Populate Items Table
function populateItemsTable(data) {
    const tbody = document.getElementById('itemsTableBody');
    tbody.innerHTML = '';
    
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No items found</td></tr>';
        return;
    }
    
    data.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.stock_no || '-'}</td>
            <td>${item.unit || '-'}</td>
            <td>${item.description}</td>
            <td class="text-center">${item.requisition_quantity}</td>
            <td class="text-center">${item.issuance_quantity}</td>
            <td>${item.remarks || '-'}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-edit" onclick="editItem(${item.id})">Edit</button>
                    <button class="btn btn-danger" onclick="deleteItem(${item.id})">Delete</button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Show Add Item Form
function showAddItemForm() {
    document.getElementById('itemId').value = '';
    document.getElementById('itemRisId').value = currentRisId;
    document.getElementById('itemFormElement').reset();
    document.getElementById('itemModalTitle').textContent = 'Add Item';
    openItemModal();
}

// Open Item Modal
function openItemModal() {
    document.getElementById('itemModal').classList.add('active');
}

// Close Item Modal
function closeItemModal() {
    document.getElementById('itemModal').classList.remove('active');
    document.getElementById('itemFormElement').reset();
}

// Save Item
function saveItem(e) {
    e.preventDefault();
    
    const itemData = {
        id: document.getElementById('itemId').value,
        ris_id: parseInt(document.getElementById('itemRisId').value),
        stock_no: document.getElementById('itemStockNo').value || null,
        unit: document.getElementById('itemUnit').value || null,
        description: document.getElementById('itemDescription').value,
        requisition_quantity: parseInt(document.getElementById('itemReqQty').value) || 0,
        issuance_quantity: parseInt(document.getElementById('itemIssueQty').value) || 0,
        remarks: document.getElementById('itemRemarks').value || null
    };
    
    const action = itemData.id ? 'update_item' : 'add_item';
    const method = itemData.id ? 'PUT' : 'POST';
    
    fetch(`api.php?action=${action}`, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(itemData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeItemModal();
            viewItems(currentRisId);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving item', 'error');
    });
}

// Edit Item
function editItem(id) {
    fetch(`api.php?action=get_items&ris_id=${currentRisId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.data.find(i => i.id === id);
                if (item) {
                    document.getElementById('itemId').value = item.id;
                    document.getElementById('itemRisId').value = item.ris_id;
                    document.getElementById('itemStockNo').value = item.stock_no || '';
                    document.getElementById('itemUnit').value = item.unit || '';
                    document.getElementById('itemDescription').value = item.description;
                    document.getElementById('itemReqQty').value = item.requisition_quantity;
                    document.getElementById('itemIssueQty').value = item.issuance_quantity;
                    document.getElementById('itemRemarks').value = item.remarks || '';
                    
                    document.getElementById('itemModalTitle').textContent = 'Edit Item';
                    openItemModal();
                }
            }
        });
}

// Delete Item
function deleteItem(id) {
    if (confirm('Are you sure you want to delete this item?')) {
        fetch(`api.php?action=delete_item&id=${id}`, { method: 'DELETE' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    viewItems(currentRisId);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error deleting item', 'error');
            });
    }
}

// Load Responsibility Centers
function loadResponsibilityCenters() {
    fetch('api.php?action=get_centers')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('responsibilityCenter');
                data.data.forEach(center => {
                    const option = document.createElement('option');
                    option.value = center.code;
                    option.textContent = `${center.code} - ${center.name}`;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

// Search RIS
function searchRIS() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    const filtered = allRisData.filter(ris => {
        return ris.ris_number.toLowerCase().includes(searchTerm) ||
               ris.office.toLowerCase().includes(searchTerm) ||
               (ris.requested_by && ris.requested_by.toLowerCase().includes(searchTerm));
    });
    
    populateRisTable(filtered);
}

// Print RIS
function printRIS() {
    if (!currentRisId) {
        showNotification('No RIS selected', 'warning');
        return;
    }
    
    window.print();
}

// Show Notification
function showNotification(message, type = 'info') {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `notification ${type} show`;
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

// Format Date
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}
