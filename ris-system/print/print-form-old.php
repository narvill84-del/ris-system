<?php
/**
 * Print Two RIS Forms on One A4 Page
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$ris_id = (int) ($_GET['id'] ?? 0);

if ($ris_id <= 0) {
    die('Invalid RIS ID');
}

$form_stmt = $conn->prepare('SELECT * FROM ris_forms WHERE id = ?');
$form_stmt->bind_param('i', $ris_id);
$form_stmt->execute();
$form_result = $form_stmt->get_result();

if ($form_result->num_rows === 0) {
    die('Form not found');
}

$form = $form_result->fetch_assoc();
$form_stmt->close();

$items_stmt = $conn->prepare(
    'SELECT * FROM ris_line_items WHERE ris_id = ? ORDER BY id ASC'
);
$items_stmt->bind_param('i', $ris_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();

$h = static function ($value): string {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
};

$date = static function (
    $value,
    string $format = 'm/d/Y'
): string {
    if (empty($value)) {
        return '';
    }

    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? ''
        : date($format, $timestamp);
};

$generated_at = date('F d, Y H:i:s');
$ris_number = $form['ris_number'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        RIS Form - <?php echo $h($ris_number); ?>
    </title>

    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
        }

        .page {
            width: 100%;
            max-width: 794px;
            margin: 0 auto;
        }

        .form-copy {
            width: 100%;
            height: 136mm;
            overflow: hidden;
            padding: 0;
            page-break-inside: avoid;
        }

        .form-copy:first-child {
            margin-bottom: 3mm;
            padding-bottom: 3mm;
            border-bottom: 1.5px dashed #555;
        }

        .form-header {
            padding-top: 1mm;
            text-align: center;
        }

        .form-header img {
            display: block;
            width: 48mm;
            height: 15mm;
            margin: 0 auto 1mm;
            object-fit: contain;
        }

        .form-header p {
            margin: 0;
            font-size: 9pt;
            line-height: 1.15;
        }

        .form-header .municipality {
            font-weight: 700;
        }

        .form-header .location {
            font-size: 8pt;
        }

        .form-header h1 {
            margin: 2mm 0 1mm;
            font-size: 14pt;
            line-height: 1.1;
            letter-spacing: 0.02em;
        }

        .form-header .generated {
            font-size: 7pt;
        }

        .divider {
            margin: 2mm 0;
            border-top: 1.2px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .info-table {
            margin-bottom: 2mm;
            border: 1.2px solid #000;
        }

        .info-table td {
            width: 50%;
            padding: 1.5mm 2mm;
            border: 1px solid #000;
            font-size: 8pt;
            line-height: 1.25;
            vertical-align: top;
        }

        .label {
            font-weight: 700;
        }

        .items-table {
            margin-bottom: 2mm;
            border: 1.2px solid #000;
        }

        .items-table th,
        .items-table td {
            padding: 1.2mm 1mm;
            border: 1px solid #000;
            font-size: 7pt;
            line-height: 1.15;
            text-align: center;
            vertical-align: middle;
            overflow-wrap: anywhere;
        }

        .items-table th {
            height: 9mm;
            background: #f2f2f2;
            font-size: 6.5pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .items-table td.description,
        .items-table td.remarks {
            text-align: left;
        }

        .purpose-box {
            min-height: 14mm;
            margin-bottom: 2mm;
            border: 1.2px solid #000;
        }

        .purpose-title {
            display: block;
            padding: 1.5mm 2mm 0;
            font-size: 8pt;
            font-weight: 700;
        }

        .purpose-content {
            display: block;
            padding: 1mm 2mm 1.5mm;
            font-size: 7.5pt;
            line-height: 1.2;
        }

        .signature-table {
            border: 1.2px solid #000;
        }

        .signature-table td {
            width: 33.33%;
            height: 29mm;
            padding: 1.5mm 2mm;
            border: 1px solid #000;
            text-align: center;
            vertical-align: top;
        }

        .signature-label {
            display: block;
            margin-bottom: 1mm;
            font-size: 7.5pt;
            font-weight: 700;
            text-align: left;
        }

        .signature-line {
            height: 10mm;
            margin-bottom: 1mm;
            border-bottom: 1px solid #000;
        }

        .signature-name {
            display: block;
            min-height: 3mm;
            font-size: 7pt;
            font-weight: 700;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .signature-role,
        .signature-date {
            display: block;
            min-height: 2.5mm;
            font-size: 6.5pt;
            line-height: 1.1;
        }

        .no-print {
            margin-top: 12px;
            text-align: center;
        }

        .no-print button {
            margin: 0 4px;
            padding: 8px 14px;
            cursor: pointer;
        }

        @media print {
            html,
            body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .page {
                max-width: none;
            }

            .no-print {
                display: none !important;
            }

            .form-copy {
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <?php for ($copy = 1; $copy <= 2; $copy++): ?>
            <section class="form-copy">
                <header class="form-header">
                    <img
                        src="../uploads/mto1.png"
                        alt="Municipality logo">

                    <p>Republic of the Philippines</p>
                    <p>Province of Zamboanga del Sur</p>

                    <p class="municipality">
                        MUNICIPALITY OF MARGOSATUBIG
                    </p>

                    <p class="location">
                        Margosatubig, Zamboanga del Sur
                    </p>

                    <h1>
                        REQUISITION AND ISSUE SLIP
                    </h1>

                    <p class="generated">
                        Generated:
                        <?php echo $h($generated_at); ?>
                    </p>
                </header>

                <div class="divider"></div>

                <table class="info-table">
                    <tr>
                        <td>
                            <span class="label">OFFICE:</span>
                            <?php echo $h(
                                $form['office_name'] ?? 'N/A'
                            ); ?>
                        </td>

                        <td>
                            <span class="label">
                                Responsibility Center:
                            </span>

                            <?php echo $h(
                                $form[
                                    'responsibility_center_code'
                                ] ?? 'N/A'
                            ); ?>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <span class="label">RIS No.:</span>
                            <?php echo $h(
                                $form['ris_number'] ?? 'N/A'
                            ); ?>

                            <br>

                            <span class="label">Date:</span>
                            <?php echo $h(
                                $date($form['ris_date'] ?? null)
                            ); ?>
                        </td>

                        <td>
                            <span class="label">SAI No.:</span>
                            <?php echo $h(
                                $form['sai_number'] ?? 'N/A'
                            ); ?>

                            <br>

                            <span class="label">Date:</span>
                            <?php echo $h(
                                $date($form['sai_date'] ?? null)
                            ); ?>
                        </td>
                    </tr>
                </table>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Stock No.</th>
                            <th style="width: 8%;">Unit</th>
                            <th style="width: 30%;">Description</th>
                            <th style="width: 18%;">Qty Requested</th>
                            <th style="width: 18%;">Qty Received</th>
                            <th style="width: 14%;">Remarks</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <?php echo $h(
                                            $item['stock_number'] ?? ''
                                        ); ?>
                                    </td>

                                    <td>
                                        <?php echo $h(
                                            $item['unit'] ?? ''
                                        ); ?>
                                    </td>

                                    <td class="description">
                                        <?php echo $h(
                                            $item['descriptions'] ?? ''
                                        ); ?>
                                    </td>

                                    <td>
                                        <?php echo $h(
                                            $item[
                                                'quantity_requested'
                                            ] ?? 0
                                        ); ?>
                                    </td>

                                    <td>
                                        <?php echo $h(
                                            $item[
                                                'quantity_received'
                                            ] ?? 0
                                        ); ?>
                                    </td>

                                    <td class="remarks">
                                        <?php echo $h(
                                            $item['remarks'] ?? ''
                                        ); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    No items found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="purpose-box">
                    <span class="purpose-title">
                        Purpose:
                    </span>

                    <span class="purpose-content">
                        <?php echo nl2br(
                            $h($form['purpose'] ?? '')
                        ); ?>
                    </span>
                </div>

                <table class="signature-table">
                    <tr>
                        <td>
                            <span class="signature-label">
                                Requested by
                            </span>

                            <div class="signature-line"></div>

                            <span class="signature-name">
                                <?php echo $h(
                                    $form['requested_by'] ?? ''
                                ); ?>
                            </span>

                            <span class="signature-role">
                                <?php echo $h(
                                    $form[
                                        'requested_by_designation'
                                    ] ?? ''
                                ); ?>
                            </span>

                            <span class="signature-date">
                                <?php echo $h(
                                    $date(
                                        $form[
                                            'requested_by_date'
                                        ] ?? null
                                    )
                                ); ?>
                            </span>
                        </td>

                        <td>
                            <span class="signature-label">
                                Approved by
                            </span>

                            <div class="signature-line"></div>

                            <span class="signature-name">
                                <?php echo $h(
                                    $form['approved_by'] ?? ''
                                ); ?>
                            </span>

                            <span class="signature-role">
                                <?php echo $h(
                                    $form[
                                        'approved_by_designation'
                                    ] ?? ''
                                ); ?>
                            </span>

                            <span class="signature-date">
                                <?php echo $h(
                                    $date(
                                        $form[
                                            'approved_by_date'
                                        ] ?? null
                                    )
                                ); ?>
                            </span>
                        </td>

                        <td>
                            <span class="signature-label">
                                Received by
                            </span>

                            <div class="signature-line"></div>

                            <span class="signature-name">
                                <?php echo $h(
                                    $form['received_by'] ?? ''
                                ); ?>
                            </span>

                            <span class="signature-role">
                                <?php echo $h(
                                    $form[
                                        'received_by_designation'
                                    ] ?? ''
                                ); ?>
                            </span>

                            <span class="signature-date">
                                <?php echo $h(
                                    $date(
                                        $form[
                                            'received_by_date'
                                        ] ?? null
                                    )
                                ); ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </section>
        <?php endfor; ?>
    </main>

    <div class="no-print">
        <button
            type="button"
            onclick="window.print()">
            Print
        </button>

        <button
            type="button"
            onclick="window.close()">
            Close
        </button>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.focus();
            window.print();
        });
    </script>
</body>
</html>