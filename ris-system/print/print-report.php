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

// Build query to get forms with line items
$query = "SELECT 
    rf.id,
    rf.ris_number,
    rf.ris_date,
    rf.office_name,
    rf.purpose,
    rf.requested_by,
    rf.status,
    rli.stock_number,
    rli.description,
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
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/style.css">
    <style>
        body { margin: 0; padding: 20px; }
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1px solid #000;
            font-size: 0.85rem;
        }
        th, td {
            border: 1px solid #000;
            padding: 0.5rem;
            text-align: left;
        }
        th {
            background: #003366;
            color: white;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div style="max-width: 1400px; margin: 0 auto;">
        <!-- Report Header -->
        <div style="text-align: center; margin-bottom: 2rem; border-bottom: 2px solid #000; padding-bottom: 1rem;">

            <img src="../uploads/mto1.png" alt="MTO Image" style="width: 150px; height: auto; margin: 0.1rem 0;">
            <p style="margin: 0.1rem 0;">Republic of the Philippines</p>
            <p style="margin: 0.1rem 0;">Province of Zamboanga del Sur</p>
            <p style="margin: 0.1rem 0;"><strong>MUNICIPALITY OF MARGOSATUBIG</strong></p>
            <p style="margin: 0.1rem 0;">Margosatubig, Zamboanga del Sur</p>
            <h2 style="margin: 0;">REQUISITION AND ISSUE SLIP REPORT</h2>
            <p style="margin: 0.5rem 0; font-size: 0.9rem;">
                Report Generated: <?php echo date('F d, Y H:i:s'); ?>
            </p>

        </div>

        <!-- Filter Summary -->
        <div style="margin-bottom: 0.1rem; padding: 0.1rem; background: #f8f9fa; border-radius: 4px;">
            <!--h3 style="margin-top: 0;">Filter Summary:</h3-->
            <p style="margin: 0.25rem 0;">
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
            </p>
        </div>

        <!-- Report Table -->
        <table>
            <thead>
                <tr>
                    <th>RIS NO.</th>
                    <th>Requested By</th>
                    <th>Description</th>
                    <th>Stock No.</th>
                    <th>Series Number</th>
                    <th>Date Issued</th>
                    <th>Date Consumed</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_items = 0;
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        $total_items++;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ris_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['requested_by'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['stock_number'] ?? ''); ?></td>
                    <td></td>
                    <td><?php echo date('m/d/Y', strtotime($row['ris_date'])); ?></td>
                    <td></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 1rem;">No records found</td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f8f9fa; font-weight: 600;">
                    <td colspan="6" style="text-align: right;">Total Items:</td>
                    <td><?php echo $total_items; ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Summary Stats -->
        <!--div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border: 1px solid #000; border-radius: 4px;">
            <h3 style="margin-top: 0;">Summary:</h3>
            <p style="margin: 0.5rem 0;"><strong>Total Line Items:</strong> <!?php echo $total_items; ?></p>
            <p style="margin: 0.5rem 0;"><strong>Report Period:</strong> 
                <!?php 
                if (!empty($start_date) && !empty($end_date)) {
                    echo date('F d, Y', strtotime($start_date)) . ' to ' . date('F d, Y', strtotime($end_date));
                } else {
                    echo 'All time';
                }
                ?>
            </p>
        </div-->

        <!-- Footer -->
        <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #ccc; font-size: 0.85rem; color: #666;">
            <p style="margin: 0;">This is a computer-generated report. Printed on <?php echo date('F d, Y'); ?></p>
        </div>
    </div>

    <div style="text-align: center; margin-top: 2rem;" class="no-print">
        <button onclick="window.print()" class="btn btn-primary">Print Report</button>
        <button onclick="exportTableToCSV('RIS_Report_<?php echo date('Y-m-d'); ?>.csv')" class="btn btn-secondary">Export to CSV</button>
        <button onclick="window.close()" class="btn btn-danger">Close</button>
    </div>

    <script src="<?php echo APP_URL; ?>/js/form-validation.js"></script>
    <script>
        window.onload = function() {
            window.focus();
        };
    </script>
</body>
</html>
