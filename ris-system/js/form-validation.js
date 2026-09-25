/**
 * RIS Form System - Form Validation
 * Margosatubig, Zamboanga del Sur LGU
 */

(function () {
    'use strict';

    function getField(fieldId) {
        return document.getElementById(fieldId);
    }

    function getValue(fieldId) {
        const field = getField(fieldId);

        return field
            ? String(field.value || '').trim()
            : '';
    }

    function errorId(fieldId) {
        return `${fieldId}-error`;
    }

    function showFieldError(fieldId, message) {
        const field = getField(fieldId);

        if (!field) {
            return;
        }

        clearFieldError(fieldId);

        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');

        const error = document.createElement('small');

        error.id = errorId(fieldId);
        error.className = 'validation-error';
        error.setAttribute('role', 'alert');
        error.textContent = message;

        if (field.parentElement) {
            field.parentElement.appendChild(error);
        }
    }

    function clearFieldError(fieldId) {
        const field = getField(fieldId);
        const error = document.getElementById(
            errorId(fieldId)
        );

        if (field) {
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');
        }

        if (error) {
            error.remove();
        }
    }

    function clearAllErrors() {
        document
            .querySelectorAll('.validation-error')
            .forEach(error => error.remove());

        document
            .querySelectorAll('.is-invalid')
            .forEach(field => {
                field.classList.remove('is-invalid');
                field.removeAttribute('aria-invalid');
            });
    }

    function validDate(value) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return false;
        }

        const parts = value.split('-').map(Number);
        const date = new Date(
            parts[0],
            parts[1] - 1,
            parts[2]
        );

        return (
            date.getFullYear() === parts[0] &&
            date.getMonth() === parts[1] - 1 &&
            date.getDate() === parts[2]
        );
    }

    function validateRequired(fieldId, fieldName) {
        const value = getValue(fieldId);

        if (value === '') {
            showFieldError(
                fieldId,
                `${fieldName} is required.`
            );

            return false;
        }

        clearFieldError(fieldId);
        return true;
    }

    function validateDate(fieldId, fieldName) {
        const value = getValue(fieldId);

        if (value === '') {
            showFieldError(
                fieldId,
                `${fieldName} is required.`
            );

            return false;
        }

        if (!validDate(value)) {
            showFieldError(
                fieldId,
                `${fieldName} is invalid.`
            );

            return false;
        }

        clearFieldError(fieldId);
        return true;
    }

    function validateOptionalDate(fieldId, fieldName) {
        const value = getValue(fieldId);

        if (value === '') {
            clearFieldError(fieldId);
            return true;
        }

        return validateDate(fieldId, fieldName);
    }

    function validateLineItems() {
        const tbody = document.getElementById(
            'line-items-body'
        );

        if (!tbody) {
            return false;
        }

        const rows = Array.from(
            tbody.querySelectorAll('tr')
        );

        if (rows.length === 0) {
            showAlert(
                'Please add at least one line item.',
                'danger'
            );

            return false;
        }

        let validItems = 0;
        let valid = true;

        rows.forEach((row, index) => {
            const itemNumber = index + 1;

            const description = row.querySelector(
                '[name^="description_"]'
            );

            const requested = row.querySelector(
                '[name^="quantity_requested_"]'
            );

            const received = row.querySelector(
                '[name^="quantity_received_"]'
            );

            const descriptionValue = description
                ? description.value.trim()
                : '';

            const requestedValue = requested
                ? requested.value.trim()
                : '';

            const receivedValue = received
                ? received.value.trim()
                : '';

            const rowIsEmpty =
                descriptionValue === '' &&
                requestedValue === '' &&
                receivedValue === '';

            if (rowIsEmpty) {
                return;
            }

            validItems++;

            if (descriptionValue === '') {
                valid = false;

                if (description) {
                    markInvalid(
                        description,
                        `Description is required for line item ${itemNumber}.`
                    );
                }
            }

            const requestedNumber = Number(
                requestedValue
            );

            const receivedNumber = receivedValue === ''
                ? 0
                : Number(receivedValue);

            if (
                !Number.isInteger(requestedNumber) ||
                requestedNumber < 1
            ) {
                valid = false;

                if (requested) {
                    markInvalid(
                        requested,
                        `Quantity requested must be at least 1 for line item ${itemNumber}.`
                    );
                }
            }

            if (
                !Number.isInteger(receivedNumber) ||
                receivedNumber < 0
            ) {
                valid = false;

                if (received) {
                    markInvalid(
                        received,
                        `Quantity received is invalid for line item ${itemNumber}.`
                    );
                }
            }

            if (
                receivedNumber > requestedNumber
            ) {
                valid = false;

                if (received) {
                    markInvalid(
                        received,
                        `Quantity received cannot exceed requested quantity for line item ${itemNumber}.`
                    );
                }
            }
        });

        if (validItems === 0) {
            showAlert(
                'Please add at least one valid line item.',
                'danger'
            );

            return false;
        }

        return valid;
    }

    function markInvalid(field, message) {
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');

        const oldError =
            field.parentElement.querySelector(
                '.validation-error'
            );

        if (oldError) {
            oldError.remove();
        }

        const error = document.createElement('small');

        error.className = 'validation-error';
        error.setAttribute('role', 'alert');
        error.textContent = message;

        field.parentElement.appendChild(error);
    }

    window.validateRISForm = function () {
        clearAllErrors();

        let valid = true;

        const requiredFields = [
            ['office_name', 'Office'],
            ['ris_date', 'RIS Date'],
            ['purpose', 'Purpose'],
            ['requested_by', 'Requested by'],
            [
                'requested_by_designation',
                'Requested-by designation'
            ],
            [
                'requested_by_date',
                'Requested-by date'
            ],
            ['approved_by', 'Approved by'],
            [
                'approved_by_designation',
                'Approved-by designation'
            ],
            [
                'approved_by_date',
                'Approved-by date'
            ]
        ];

        requiredFields.forEach(
            ([fieldId, fieldName]) => {
                if (
                    !validateRequired(
                        fieldId,
                        fieldName
                    )
                ) {
                    valid = false;
                }
            }
        );

        if (
            getValue('ris_date') !== '' &&
            !validateDate('ris_date', 'RIS Date')
        ) {
            valid = false;
        }

        if (
            getValue('requested_by_date') !== '' &&
            !validateDate(
                'requested_by_date',
                'Requested-by date'
            )
        ) {
            valid = false;
        }

        if (
            getValue('approved_by_date') !== '' &&
            !validateDate(
                'approved_by_date',
                'Approved-by date'
            )
        ) {
            valid = false;
        }

        if (
            getValue('sai_date') !== '' &&
            !validateOptionalDate(
                'sai_date',
                'SAI date'
            )
        ) {
            valid = false;
        }

        if (
            getValue('received_by_date') !== '' &&
            !validateOptionalDate(
                'received_by_date',
                'Received-by date'
            )
        ) {
            valid = false;
        }

        if (!validateLineItems()) {
            valid = false;
        }

        if (!valid) {
            showAlert(
                'Please correct the highlighted fields.',
                'danger'
            );
        }

        return valid;
    };

    window.showAlert = function (
        message,
        type = 'info'
    ) {
        const existing = document.querySelector(
            '.ris-alert'
        );

        if (existing) {
            existing.remove();
        }

        const alert = document.createElement('div');

        alert.className = `ris-alert ris-alert-${type}`;
        alert.setAttribute('role', 'alert');
        alert.textContent = message;

        const container =
            document.querySelector('.create-card-body') ||
            document.body;

        container.insertBefore(
            alert,
            container.firstChild
        );

        window.setTimeout(() => {
            if (alert.isConnected) {
                alert.remove();
            }
        }, 5000);
    };

    function addValidationStyles() {
        if (
            document.getElementById(
                'ris-validation-styles'
            )
        ) {
            return;
        }

        const style = document.createElement('style');

        style.id = 'ris-validation-styles';

        style.textContent = `
            .validation-error {
                display: block;
                margin-top: 5px;
                color: #f18b8b;
                font-size: 0.78rem;
                line-height: 1.4;
            }

            .is-invalid {
                border-color: #e36b6b !important;
                box-shadow:
                    0 0 0 3px
                    rgba(227, 107, 107, 0.14) !important;
            }

            .ris-alert {
                width: 100%;
                margin-bottom: 18px;
                padding: 13px 16px;
                border-radius: 10px;
                font-size: 0.9rem;
                font-weight: 600;
            }

            .ris-alert-info {
                color: #bae6fd;
                background: rgba(56, 189, 248, 0.1);
                border: 1px solid rgba(56, 189, 248, 0.25);
            }

            .ris-alert-success {
                color: #b7efc5;
                background: rgba(95, 207, 128, 0.1);
                border: 1px solid rgba(95, 207, 128, 0.25);
            }

            .ris-alert-danger {
                color: #ffc1c1;
                background: rgba(227, 107, 107, 0.1);
                border: 1px solid rgba(227, 107, 107, 0.3);
            }

            .ris-alert-warning {
                color: #f8df93;
                background: rgba(228, 184, 76, 0.1);
                border: 1px solid rgba(228, 184, 76, 0.3);
            }
        `;

        document.head.appendChild(style);
    }

    document.addEventListener(
        'DOMContentLoaded',
        () => {
            addValidationStyles();

            document
                .querySelectorAll('[data-validate]')
                .forEach(field => {
                    field.addEventListener(
                        'blur',
                        () => {
                            const type =
                                field.dataset.validate;

                            const name =
                                field.dataset.fieldName ||
                                field.name ||
                                field.id;

                            if (type === 'required') {
                                validateRequired(
                                    field.id,
                                    name
                                );
                            }

                            if (type === 'date') {
                                validateDate(
                                    field.id,
                                    name
                                );
                            }
                        }
                    );

                    field.addEventListener(
                        'input',
                        () => {
                            clearFieldError(field.id);
                        }
                    );
                });
        }
    );
})();