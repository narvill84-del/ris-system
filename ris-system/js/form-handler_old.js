/**
 * RIS Form System - Form Handler
 */

(function () {
    'use strict';

    /**
     * Collect line-item rows and convert them to the JSON structure
     * expected by api/save-form.php.
     */
    function collectLineItems() {
        const rows = document.querySelectorAll(
            '#line-items-body tr'
        );

        const lineItems = [];

        rows.forEach((row, index) => {
            const getValue = (selector) => {
                const field = row.querySelector(selector);
                return field ? field.value.trim() : '';
            };

            const stockNumber = getValue(
                '[name^="stock_number_"]'
            );

            const unit = getValue(
                '[name^="unit_"]'
            );

            const description = getValue(
                '[name^="description_"]'
            );

            const quantityRequested = getValue(
                '[name^="quantity_requested_"]'
            );

            const quantityReceived = getValue(
                '[name^="quantity_received_"]'
            );

            const remarks = getValue(
                '[name^="remarks_"]'
            );

            /*
             * Ignore completely empty rows.
             */
            if (
                stockNumber === '' &&
                unit === '' &&
                description === '' &&
                quantityRequested === '' &&
                quantityReceived === '' &&
                remarks === ''
            ) {
                return;
            }

            lineItems.push({
                stock_number: stockNumber,
                unit: unit,
                descriptions: description,
                quantity_requested: quantityRequested,
                quantity_received: quantityReceived === ''
                    ? 0
                    : quantityReceived,
                remarks: remarks
            });
        });

        return lineItems;
    }

    /**
     * Add a new line-item row.
     */
    window.addLineItem = function () {
        const tbody = document.getElementById(
            'line-items-body'
        );

        if (!tbody) {
            return;
        }

        const rowIndex = tbody.querySelectorAll('tr').length;

        const row = document.createElement('tr');

        row.innerHTML = `
            <td>
                <input
                    type="text"
                    name="stock_number_${rowIndex}"
                    class="form-control"
                    placeholder="Stock No.">
            </td>

            <td>
                <input
                    type="text"
                    name="unit_${rowIndex}"
                    class="form-control"
                    placeholder="Unit">
            </td>

            <td>
                <input
                    type="text"
                    name="description_${rowIndex}"
                    class="form-control"
                    placeholder="Description"
                    required>
            </td>

            <td>
                <input
                    type="number"
                    name="quantity_requested_${rowIndex}"
                    class="form-control"
                    placeholder="Qty"
                    min="1"
                    required>
            </td>

            <td>
                <input
                    type="number"
                    name="quantity_received_${rowIndex}"
                    class="form-control"
                    placeholder="Qty"
                    min="0"
                    value="0">
            </td>

            <td>
                <input
                    type="text"
                    name="remarks_${rowIndex}"
                    class="form-control"
                    placeholder="Remarks">
            </td>

            <td>
                <button
                    type="button"
                    class="remove-item-button"
                    onclick="removeLineItem(this)">
                    Remove
                </button>
            </td>
        `;

        tbody.appendChild(row);
    };

    /**
     * Remove a line-item row.
     *
     * At least one row must remain.
     */
    window.removeLineItem = function (button) {
        const tbody = document.getElementById(
            'line-items-body'
        );

        if (!tbody || !button) {
            return;
        }

        const rows = tbody.querySelectorAll('tr');

        if (rows.length <= 1) {
            alert('At least one line-item row is required.');
            return;
        }

        const row = button.closest('tr');

        if (row) {
            row.remove();
        }
    };

    /**
     * Validate the line items before sending.
     */
    function validateLineItems(lineItems) {
        if (!Array.isArray(lineItems) || lineItems.length === 0) {
            return 'Please add at least one line item.';
        }

        for (let index = 0; index < lineItems.length; index++) {
            const item = lineItems[index];
            const itemNumber = index + 1;

            if (!item.descriptions) {
                return `Description is required for line item ${itemNumber}.`;
            }

            const requested = Number(
                item.quantity_requested
            );

            const received = Number(
                item.quantity_received
            );

            if (!Number.isInteger(requested) || requested < 1) {
                return `Quantity requested must be at least 1 for line item ${itemNumber}.`;
            }

            if (!Number.isInteger(received) || received < 0) {
                return `Quantity received is invalid for line item ${itemNumber}.`;
            }

            if (received > requested) {
                return `Quantity received cannot exceed quantity requested for line item ${itemNumber}.`;
            }
        }

        return null;
    }

    /**
     * Save the RIS form using the API.
     */
    window.saveRISForm = async function () {
        const form = document.getElementById('ris-form');

        if (!form) {
            alert('RIS form was not found.');
            return;
        }

        /*
         * Use native browser validation first.
         */
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const lineItems = collectLineItems();
        const lineItemError = validateLineItems(lineItems);

        if (lineItemError) {
            alert(lineItemError);
            return;
        }

        const formData = new FormData(form);

        /*
         * The API expects line_items as JSON.
         */
        formData.set(
            'line_items',
            JSON.stringify(lineItems)
        );

        const saveButton = form.querySelector(
            '[name="save"]'
        );

        const originalButtonText = saveButton
            ? saveButton.textContent
            : '';

        if (saveButton) {
            saveButton.disabled = true;
            saveButton.textContent = 'Saving...';
        }

        try {
            const response = await fetch(
                '../api/save-form.php',
                {
                    method: 'POST',
                    body: formData,
                    headers: {
                        Accept: 'application/json'
                    }
                }
            );

            const responseText = await response.text();

            let result;

            try {
                result = JSON.parse(responseText);
            } catch (jsonError) {
                console.error(
                    'Invalid JSON response:',
                    responseText
                );

                throw new Error(
                    'The server returned an invalid response.'
                );
            }

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || 'Unable to save the RIS form.'
                );
            }

            alert(
                `Form saved successfully.\nRIS No.: ${result.ris_number}`
            );

            window.location.href =
                `view.php?id=${encodeURIComponent(result.ris_id)}`;

        } catch (error) {
            console.error(
                'Save RIS form error:',
                error
            );

            alert(
                error.message ||
                'An error occurred while saving the form.'
            );

        } finally {
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.textContent = originalButtonText;
            }
        }
    };

    /**
     * Generate a printable report.
     */
    window.generateReport = function (filters = {}) {
        const params = new URLSearchParams();

        Object.keys(filters).forEach(key => {
            if (
                filters[key] !== undefined &&
                filters[key] !== null &&
                filters[key] !== ''
            ) {
                params.set(key, filters[key]);
            }
        });

        const url = `report-print.php?${params.toString()}`;

        window.open(
            url,
            '_blank',
            'noopener,noreferrer'
        );
    };

    /**
     * Export the dashboard form list to CSV.
     */
    window.exportFormListToCSV = function () {
        const table = document.querySelector(
            '.table'
        );

        if (!table) {
            alert('No form data is available to export.');
            return;
        }

        const rows = Array.from(
            table.querySelectorAll('tr')
        );

        const csvRows = rows.map(row => {
            const cells = Array.from(
                row.querySelectorAll('th, td')
            );

            return cells
                .slice(0, -1)
                .map(cell => {
                    const value = cell.textContent
                        .trim()
                        .replace(/\s+/g, ' ')
                        .replace(/"/g, '""');

                    return `"${value}"`;
                })
                .join(',');
        });

        const csvContent = csvRows.join('\n');

        const blob = new Blob(
            [csvContent],
            {
                type: 'text/csv;charset=utf-8;'
            }
        );

        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = url;
        link.download = 'ris-forms.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();

        URL.revokeObjectURL(url);
    };

    /**
     * Print a specific RIS form.
     */
    window.printRISForm = function (risId) {
        if (!risId) {
            alert('Invalid RIS form ID.');
            return;
        }

        window.open(
            `print.php?id=${encodeURIComponent(risId)}`,
            '_blank',
            'noopener,noreferrer'
        );
    };

    /**
     * Delete a specific RIS form.
     */
    window.deleteRISForm = async function (risId) {
        if (!risId) {
            alert('Invalid RIS form ID.');
            return;
        }

        const confirmed = confirm(
            'Are you sure you want to delete this RIS form?'
        );

        if (!confirmed) {
            return;
        }

        try {
            const response = await fetch(
                '../api/delete-form.php',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json'
                    },
                    body: JSON.stringify({
                        id: risId
                    })
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || 'Unable to delete the form.'
                );
            }

            alert('RIS form deleted successfully.');
            window.location.reload();

        } catch (error) {
            console.error(
                'Delete RIS form error:',
                error
            );

            alert(
                error.message ||
                'An error occurred while deleting the form.'
            );
        }
    };
})();