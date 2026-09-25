<?php
/**
 * Print Single RIS Form
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

$generated_at = date('F d, Y H:i:s');
$office_name = $form['office_name'] ?? 'N/A';
$responsibility_center = $form['responsibility_center_code'] ?? 'N/A';
$ris_number = $form['ris_number'] ?? 'N/A';
$ris_date = !empty($form['ris_date']) ? date('m/d/Y', strtotime($form['ris_date'])) : '';
$sai_number = $form['sai_number'] ?? 'N/A';
$sai_date = !empty($form['sai_date']) ? date('m/d/Y', strtotime($form['sai_date'])) : '';
$purpose = $form['purpose'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIS Form - <?php echo htmlspecialchars($ris_number); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 12mm 16mm 12mm;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 0;
        }

        .page {
            width: 100%;
            max-width: 830px;
            background: #fff;
            padding: 0;
        }

        .top-space {
            height: 8px;
        }

        .header-wrap {
            text-align: center;
            padding-top: 8px;
        }

        .seal {
            width: 56px;
            height: 56px;
            margin: 0 auto 8px;
            border-radius: 50%;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            background: #f9f9f9;
        }

        .header-wrap p {
            margin: 0;
            font-size: 18px;
            line-height: 1.3;
        }

        .header-wrap .title {
            font-size: 28px;
            font-weight: 700;
            margin-top: 10px;
            letter-spacing: 0.02em;
        }

        .header-wrap .generated {
            font-size: 15px;
            font-weight: 600;
            margin-top: 8px;
            color: #111;
        }

        .divider {
            border-top: 2px solid #000;
            margin: 14px 0 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            border: 2px solid #000;
            table-layout: fixed;
        }

        .info-table td {
            border: 1px solid #000;
            padding: 8px 10px;
            vertical-align: top;
            font-size: 17px;
            line-height: 1.4;
            background: #fff;
        }

        .info-table td:first-child {
            width: 50%;
        }

        .info-table td:last-child {
            width: 50%;
        }

        .label {
            font-weight: 700;
            text-transform: uppercase;
        }

        .line-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            border: 2px solid #000;
            table-layout: fixed;
        }

        .line-items th,
        .line-items td {
            border: 1px solid #000;
            padding: 7px 6px;
            text-align: center;
            font-size: 14px;
            vertical-align: middle;
        }

        .line-items th {
            background: #f2f2f2;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .purpose-box {
            width: 100%;
            border: 2px solid #000;
            margin-top: 12px;
            box-sizing: border-box;
            background: #fff;
        }

        .purpose-box .title {
            display: block;
            padding: 8px 10px 0;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .purpose-box .content {
            display: block;
            min-height: 34px;
            padding: 8px 10px 10px;
            font-size: 15px;
            line-height: 1.45;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            border: 2px solid #000;
            table-layout: fixed;
        }

        .signature-table td {
            border: 1px solid #000;
            padding: 8px 10px 6px;
            text-align: center;
            vertical-align: top;
            font-size: 13px;
            background: #fff;
        }

        .signature-table .sig-label {
            display: block;
            text-align: left;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .signature-line {
            height: 58px;
            border-bottom: 1px solid #000;
            margin-bottom: 8px;
        }

        .signature-name {
            display: block;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .signature-role {
            display: block;
            font-size: 12px;
            line-height: 1.4;
        }

        .signature-date {
            display: block;
            font-size: 12px;
            line-height: 1.4;
        }

        .no-print {
            display: none;
        }

        @media print {
            html, body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header-wrap">
            <div class="seal">M</div>
            <p>Republic of the Philippines</p>
            <p>Province of Zamboanga del Sur</p>
            <p style="font-weight: 700; letter-spacing: 0.02em;">MUNICIPALITY OF MARGOSATUBIG</p>
            <p style="font-size: 16px;">Margosatubig, Zamboanga del Sur</p>
            <div class="title">REQUISITION AND ISSUE SLIP FORM</div>
            <div class="generated">Generated: <?php echo htmlspecialchars($generated_at); ?></div>
        </div>

        <div class="divider"></div>

        <table class="info-table">
            <tr>
                <td><span class="label">Office:</span> <?php echo htmlspecialchars($office_name); ?></td>
                <td><span class="label">Responsibility Center:</span> <?php echo htmlspecialchars($responsibility_center); ?></td>
            </tr>
            <tr>
                <td>
                    <div><span class="label">RIS No.:</span> <?php echo htmlspecialchars($ris_number); ?></div>
                    <div><span class="label">Date:</span> <?php echo htmlspecialchars($ris_date); ?></div>
                </td>
                <td>
                    <div><span class="label">SAI No.:</span> <?php echo htmlspecialchars($sai_number); ?></div>
                    <div><span class="label">Date:</span> <?php echo htmlspecialchars($sai_date); ?></div>
                </td>
            </tr>
        </table>

        <table class="line-items">
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
                <?php if ($items_result->num_rows > 0): ?>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['stock_number'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td>
                            <td style="text-align: left; padding-left: 8px;"><?php echo htmlspecialchars($item['descriptions'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars((string) ($item['quantity_requested'] ?? 0)); ?></td>
                            <td><?php echo htmlspecialchars((string) ($item['quantity_received'] ?? 0)); ?></td>
                            <td><?php echo htmlspecialchars($item['remarks'] ?? ''); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 14px; text-align: center;">No items found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="purpose-box">
            <span class="title">Purpose:</span>
            <span class="content"><?php echo nl2br(htmlspecialchars($purpose)); ?></span>
        </div>

        <table class="signature-table">
            <tr>
                <td>
                    <span class="sig-label">Requested by</span>
                    <div class="signature-line"></div>
                    <span class="signature-name"><?php echo htmlspecialchars($form['requested_by'] ?? ''); ?></span>
                    <span class="signature-role"><?php echo htmlspecialchars($form['requested_by_designation'] ?? ''); ?></span>
                    <span class="signature-date"><?php echo !empty($form['requested_by_date']) ? date('m/d/Y', strtotime($form['requested_by_date'])) : ''; ?></span>
                </td>
                <td>
                    <span class="sig-label">Approved by</span>
                    <div class="signature-line"></div>
                    <span class="signature-name"><?php echo htmlspecialchars($form['approved_by'] ?? ''); ?></span>
                    <span class="signature-role"><?php echo htmlspecialchars($form['approved_by_designation'] ?? ''); ?></span>
                    <span class="signature-date"><?php echo !empty($form['approved_by_date']) ? date('m/d/Y', strtotime($form['approved_by_date'])) : ''; ?></span>
                </td>
                <td>
                    <span class="sig-label">Received by</span>
                    <div class="signature-line"></div>
                    <span class="signature-name"><?php echo htmlspecialchars($form['received_by'] ?? ''); ?></span>
                    <span class="signature-role"><?php echo htmlspecialchars($form['received_by_designation'] ?? ''); ?></span>
                    <span class="signature-date"><?php echo !empty($form['received_by_date']) ? date('m/d/Y', strtotime($form['received_by_date'])) : ''; ?></span>
                </td>
            </tr>
        </table>
    </div>

    <script>
        window.onload = function () {
            window.focus();
            window.print();
        };
    </script>
</body>
</html>
