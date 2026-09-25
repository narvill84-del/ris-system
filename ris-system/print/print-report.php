<?php
/**
 * Print Report
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

// Get filter parameters
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$office_name = $_GET['office_name'] ?? '';
$status = $_GET['status'] ?? '';

$query = "SELECT 
    rf.id,
    rf.ris_number,
    rf.ris_date,
    rf.office_name,
    rf.purpose,
    rf.requested_by,
    rf.status,
    rli.stock_number,
    rli.descriptions AS item_description,
    rli.unit,
    rli.quantity_requested,
    rli.quantity_received
FROM ris_forms rf
LEFT JOIN ris_line_items rli ON rf.id = rli.ris_id
WHERE 1=1";

if (!empty($start_date)) {
    $query .= " AND rf.ris_date >= '" . $conn->real_escape_string($start_date) . "'";
}
if (!empty($end_date)) {
    $query .= " AND rf.ris_date <= '" . $conn->real_escape_string($end_date) . "'";
}
if (!empty($office_name)) {
    $query .= " AND rf.office_name LIKE '%" . $conn->real_escape_string($office_name) . "%'";
}
if (!empty($status)) {
    $query .= " AND rf.status = '" . $conn->real_escape_string($status) . "'";
}

$query .= " ORDER BY rf.ris_number ASC, rli.id ASC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIS Report</title>
    <style>
        body {
            margin: 0;
            padding: 24px;
            background: #fff;
            color: #111;
            font-family: Arial, sans-serif;
        }

        .report-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .report-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 14px;
        }

        .report-header img {
            width: 150px;
            height: auto;
            margin-bottom: 8px;
        }

        .report-header p,
        .report-header h2 {
            margin: 4px 0;
        }

        .filter-summary {
            margin: 10px 0 20px;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-size: 12px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #003366;
            color: #fff;
            font-weight: 700;
        }

        tfoot td {
            font-weight: 700;
            background: #f8f9fa;
        }

        .no-print {
            text-align: center;
            margin-top: 24px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <img src="../uploads/mto1.png" alt="MTO Image">
            <p>Republic of the Philippines</p>
            <p>Province of Zamboanga del Sur</p>
            <p><strong>MUNICIPALITY OF MARGOSATUBIG</strong></p>
            <p>Margosatubig, Zamboanga del Sur</p>
            <h2>REQUISITION AND ISSUE SLIP</h2>
            <p>Report Generated: <?php echo date('F d, Y H:i:s'); ?></p>
        </div>

        <div class="filter-summary">
            <?php if (!empty($start_date)): ?>
                <strong>From:</strong> <?php echo date('F d, Y', strtotime($start_date)); ?>&nbsp;&nbsp;
            <?php endif; ?>
            <?php if (!empty($end_date)): ?>
                <strong>To:</strong> <?php echo date('F d, Y', strtotime($end_date)); ?>&nbsp;&nbsp;
            <?php endif; ?>
            <?php if (!empty($office_name)): ?>
                <strong>Office:</strong> <?php echo htmlspecialchars($office_name); ?>&nbsp;&nbsp;
            <?php endif; ?>
            <?php if (!empty($status)): ?>
                <strong>Status:</strong> <?php echo htmlspecialchars($status); ?>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>RIS No.</th>
                    <th>Requested By</th>
                    <th>Description</th>
                    <th>Stock No.</th>
                    <th>Unit</th>
                    <th>Date Issued</th>
                    <th>Qty Requested</th>
                    <th>Qty Received</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_items = 0;
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        $total_items++;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ris_number'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['requested_by'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['item_description'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['stock_number'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['unit'] ?? ''); ?></td>
                        <td><?php echo $row['ris_date'] ? date('m/d/Y', strtotime($row['ris_date'])) : ''; ?></td>
                        <td><?php echo htmlspecialchars((string) ($row['quantity_requested'] ?? 0)); ?></td>
                        <td><?php echo htmlspecialchars((string) ($row['quantity_received'] ?? 0)); ?></td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding: 12px;">No records found</td>
                    </tr>
                <?php
                endif;
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align:right;">Total Items:</td>
                    <td><?php echo $total_items; ?></td>
                </tr>
            </tfoot>
        </table>

        <div style="text-align:center; margin-top:24px; font-size:12px; color:#444;">
            This is a computer-generated report. Printed on <?php echo date('F d, Y'); ?>
        </div>
    </div>

    <div class="no-print">
        <button type="button" onclick="window.print()" class="btn btn-primary">Print Report</button>
        <button type="button" onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>
</body>
</html>