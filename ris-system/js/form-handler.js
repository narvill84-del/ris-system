(function () {
    'use strict';

    let lineItemCounter = 1;

    const $ = (selector, parent = document) =>
        parent.querySelector(selector);

    const $$ = (selector, parent = document) =>
        Array.from(parent.querySelectorAll(selector));

    const value = (selector, parent = document) => {
        const field = $(selector, parent);
        return field ? String(field.value || '').trim() : '';
    };

    function notify(message, type = 'info') {
        if (typeof window.showAlert === 'function') {
            window.showAlert(message, type);
        } else {
            window.alert(message);
        }
    }

    function showSaveConfirmation(risNumber) {
        return new Promise(resolve => {
            const existing = document.getElementById('ris-save-confirmation');
            if (existing) existing.remove();

            const modal = document.createElement('div');
            modal.id = 'ris-save-confirmation';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('aria-labelledby', 'ris-save-confirmation-title');
            modal.innerHTML = `
                <div class="ris-save-backdrop"></div>
                <div class="ris-save-modal" role="document">
                    <button type="button" class="ris-save-close" aria-label="Close">&times;</button>
                    <div class="ris-save-icon" aria-hidden="true">✓</div>
                    <h2 id="ris-save-confirmation-title">Saved successfully</h2>
                    <p>RIS form <strong>${String(risNumber || '').replace(/[&<>"']/g, '')}</strong> was saved successfully.</p>
                    <button type="button" class="ris-save-confirm">OK, create another form</button>
                    <p class="ris-save-countdown">Returning to the create form in <span>5</span> seconds...</p>
                </div>
            `;

            const style = document.createElement('style');
            style.textContent = `
                #ris-save-confirmation { position: fixed; inset: 0; z-index: 10000; display: grid; place-items: center; padding: 20px; }
                .ris-save-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.78); backdrop-filter: blur(4px); }
                .ris-save-modal { position: relative; width: min(430px, 100%); padding: 32px 28px 24px; color: #f5f1e6; background: linear-gradient(145deg,#242018,#0d0d0d); border: 1px solid rgba(212,175,55,.55); border-radius: 18px; box-shadow: 0 25px 70px rgba(0,0,0,.65); text-align: center; font-family: inherit; }
                .ris-save-modal h2 { margin: 14px 0 8px; color: #f3d77a; }
                .ris-save-modal p { color: #c8c1b0; line-height: 1.5; }
                .ris-save-icon { display: grid; place-items: center; width: 58px; height: 58px; margin: 0 auto; color: #111; background: #79d895; border-radius: 50%; font-size: 2rem; font-weight: 900; }
                .ris-save-confirm { min-height: 44px; padding: 10px 18px; color: #17120a; background: linear-gradient(135deg,#f3d77a,#9c7c19); border: 0; border-radius: 10px; cursor: pointer; font-weight: 800; }
                .ris-save-close { position: absolute; top: 10px; right: 14px; color: #c8c1b0; background: transparent; border: 0; cursor: pointer; font-size: 1.7rem; }
                .ris-save-countdown { margin: 16px 0 0; color: #918a7a !important; font-size: .8rem; }
            `;
            document.head.appendChild(style);
            document.body.appendChild(modal);

            let seconds = 5;
            const countdown = modal.querySelector('.ris-save-countdown span');
            const redirect = () => {
                clearInterval(timer);
                modal.remove();
                window.location.href = 'create.php';
                resolve();
            };
            const timer = window.setInterval(() => {
                seconds -= 1;
                if (countdown) countdown.textContent = String(seconds);
                if (seconds <= 0) redirect();
            }, 1000);

            modal.querySelector('.ris-save-confirm')?.addEventListener('click', redirect);
            modal.querySelector('.ris-save-close')?.addEventListener('click', redirect);
            modal.querySelector('.ris-save-backdrop')?.addEventListener('click', redirect);
            modal.querySelector('.ris-save-confirm')?.focus();
        });
    }

    function collectLineItems() {
        return $$('#line-items-body tr')
            .map(row => ({
                stock_number: value('[name^="stock_number_"]', row),
                unit: value('[name^="unit_"]', row),
                descriptions: value('[name^="description_"]', row),
                quantity_requested: value('[name^="quantity_requested_"]', row),
                quantity_received: value('[name^="quantity_received_"]', row) || '0',
                remarks: value('[name^="remarks_"]', row)
            }))
            .filter(item => Object.values(item).some(Boolean));
    }

    function validateItems(items) {
        if (!items.length) return 'Please add at least one line item.';
        for (let index = 0; index < items.length; index += 1) {
            const item = items[index];
            const number = index + 1;
            const requested = Number(item.quantity_requested);
            const received = Number(item.quantity_received);
            if (!item.descriptions) return `Description is required for line item ${number}.`;
            if (!Number.isInteger(requested) || requested < 1) return `Quantity requested is invalid for line item ${number}.`;
            if (!Number.isInteger(received) || received < 0 || received > requested) return `Quantity received is invalid for line item ${number}.`;
        }
        return null;
    }

    window.addLineItem = function () {
        const tbody = $('#line-items-body');
        if (!tbody) return notify('The line-item table was not found.', 'danger');
        const index = lineItemCounter++;
        const row = document.createElement('tr');
        row.innerHTML = `<td><input name="stock_number_${index}"></td><td><input name="unit_${index}"></td><td><input name="description_${index}" required></td><td><input type="number" min="1" name="quantity_requested_${index}" required></td><td><input type="number" min="0" name="quantity_received_${index}" value="0"></td><td><input name="remarks_${index}"></td><td><button type="button" class="remove-item-button" onclick="removeLineItem(this)">Remove</button></td>`;
        tbody.appendChild(row);
        $(`[name="description_${index}"]`, row)?.focus();
    };

    window.removeLineItem = function (button) {
        const rows = $$('#line-items-body tr');
        if (rows.length <= 1) return notify('At least one line-item row is required.', 'warning');
        button.closest('tr')?.remove();
    };

    async function readJsonResponse(response) {
        const text = await response.text();
        let result;
        try { result = JSON.parse(text); } catch (error) { throw new Error('The server returned an invalid response.'); }
        if (!response.ok || !result.success) throw new Error(result.message || 'Unable to save the form.');
        return result;
    }

    document.addEventListener('DOMContentLoaded', () => {
        $$('#line-items-body input[name]').forEach(field => {
            const match = field.name.match(/_(\d+)$/);
            if (match) lineItemCounter = Math.max(lineItemCounter, Number(match[1]) + 1);
        });

        const form = $('#ris-form');
        if (!form) return;

        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (typeof window.validateRISForm === 'function' && !window.validateRISForm()) return;
            const items = collectLineItems();
            const validationError = validateItems(items);
            if (validationError) return notify(validationError, 'danger');

            const lineItemsJson = JSON.stringify(items);
            const hiddenItems = $('#line_items');
            if (hiddenItems) hiddenItems.value = lineItemsJson;
            const formData = new FormData(form);
            formData.set('line_items', lineItemsJson);
            const saveButton = $('#save-ris-button');
            const originalText = saveButton?.textContent || 'Save Form';
            if (saveButton) { saveButton.disabled = true; saveButton.textContent = 'Saving...'; }

            try {
                const response = await fetch('../api/save-form.php', { method: 'POST', body: formData, headers: { Accept: 'application/json' } });
                const result = await readJsonResponse(response);
                if (result.success) {
                    await showSaveConfirmation(result.ris_number);
                    return;
                }
            } catch (error) {
                console.error('Save RIS form error:', error);
                notify(error.message || 'An error occurred while saving the form.', 'danger');
                if (saveButton) { saveButton.disabled = false; saveButton.textContent = originalText; }
            }
        });
    });
})();
