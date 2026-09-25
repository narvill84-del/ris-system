(function () {
    'use strict';

    function openPrintWindow(url) {
        const printWindow = window.open(
            url,
            '_blank',
            'width=1200,height=900,noopener,noreferrer'
        );

        if (!printWindow) {
            window.alert(
                'The print window was blocked. Please allow pop-ups for this website.'
            );
            return false;
        }

        printWindow.focus();
        return true;
    }

    window.printRISForm = function (risId) {
        const id = Number(risId);

        if (!Number.isInteger(id) || id <= 0) {
            window.alert('Invalid RIS form ID.');
            return false;
        }

        const url = new URL(
            '../print/print-form.php',
            window.location.href
        );

        url.searchParams.set('id', String(id));

        return openPrintWindow(url.href);
    };

    window.generateReport = function (filters) {
        const url = new URL(
            '../print/print-report.php',
            window.location.href
        );

        Object.entries(filters || {}).forEach(function ([key, value]) {
            if (value !== null && value !== undefined && String(value) !== '') {
                url.searchParams.set(key, String(value));
            }
        });

        return openPrintWindow(url.href);
    };

    window.exportTableToCSV = function (filename) {
        const table = document.querySelector('table');

        if (!table) {
            window.alert('No table is available for export.');
            return false;
        }

        const rows = Array.from(table.querySelectorAll('tr'));

        const csv = rows.map(function (row) {
            const cells = Array.from(row.querySelectorAll('th, td'));

            return cells.map(function (cell) {
                const value = String(cell.innerText || '')
                    .replace(/\s+/g, ' ')
                    .trim()
                    .replace(/"/g, '""');

                return '"' + value + '"';
            }).join(',');
        }).join('\n');

        const blob = new Blob([csv], {
            type: 'text/csv;charset=utf-8;'
        });

        const link = document.createElement('a');
        const objectUrl = URL.createObjectURL(blob);

        link.href = objectUrl;
        link.download = filename || 'RIS_Report.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();

        URL.revokeObjectURL(objectUrl);

        return true;
    };
})();