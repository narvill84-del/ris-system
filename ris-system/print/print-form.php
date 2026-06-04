<?php
/**
 * Print Single RIS Form
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$ris_id = (int)($_GET['id'] ?? 0);
if ($ris_id <= 0) {
    die('Invalid RIS ID');
}

// Get form data
$query = "SELECT * FROM ris_forms WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ris_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Form not found');
}

$form = $result->fetch_assoc();

// Get line items
$items_query = "SELECT * FROM ris_line_items WHERE ris_id = ? ORDER BY id ASC";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $ris_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIS Form - <?php echo htmlspecialchars($form['ris_number']); ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/style.css">
    <style>
        body { margin: 0; padding: 20px; }
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="ris-form" style="border: 2px solid #000; max-width: 850px; margin: 0 auto;">
        <div class="ris-header">

            <img src="../uploads/mto1.png" alt="MTO Image" style="width: 150px; height: auto; margin: 0.1rem 0;">
            <p style="margin: 0.1rem 0;">Republic of the Philippines</p>
            <p style="margin: 0.1rem 0;">Province of Zamboanga del Sur</p>
            <p style="margin: 0.1rem 0;"><strong>MUNICIPALITY OF MARGOSATUBIG</strong></p>
            <p style="margin: 0.1rem 0;">Margosatubig, Zamboanga del Sur</p>
            <h1 style="margin: 0;">REQUISITION AND ISSUE SLIP REPORT</h1>
            <p style="margin: 0.5rem 0; font-size: 0.9rem;">
                Report Generated: <?php echo date('F d, Y H:i:s'); ?>
            </p>

        </div>

        <!-- Form Info -->
        <table style="width: 100%; margin: 1.5rem 0; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; padding: 0.5rem;">
                    <P>OFFICE:<strong> <?php echo htmlspecialchars($form['office_name']); ?></strong></p>
                </td>
                <td style="width: 50%; padding: 0.5rem;">
                    <strong>Responsibility Center</strong>
                    <p>Code:<strong> <?php echo htmlspecialchars($form['responsibility_center_code'] ?? ''); ?></strong></p>
                </td>
            </tr>
            <tr>
                <td style="padding: 0.5rem;">
                    <p>RIS No.: <strong> <?php echo htmlspecialchars($form['ris_number']); ?></strong></p>
                    <p>Date: <strong> <?php echo date('m/d/Y', strtotime($form['ris_date'])); ?></strong></p>
                </td>
                <td style="padding: 0.5rem;">
                    <p>SAI No.:<strong> <?php echo htmlspecialchars($form['sai_number'] ?? ''); ?></strong></p>
                   <p> Date:  <strong> <?php echo $form['sai_date'] ? date('m/d/Y', strtotime($form['sai_date'])) : ''; ?></strong></p>
                </td>
            </tr>
        </table>

        <!-- Requisition Table -->
        <div style="margin: 1.5rem 0;">
            <!--h4 style="margin-top: 0;">REQUISITION</h4-->
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
                <thead>
                    <tr style="background: #f0f0f0;">
                        <th style="border: 1px solid #000; padding: 0.5rem; text-align: center; width: 10%;">Stock No.</th>
                        <th style="border: 1px solid #000; padding: 0.5rem; text-align: center; width: 8%;">Unit</th>
                        <th style="border: 1px solid #000; padding: 0.5rem; text-align: center; width: 30%;">Description</th>
                        <th style="border: 1px solid #000; padding: 0.5rem; text-align: center; width: 12%;">Quantity Requested</th>
                        <th style="border: 1px solid #000; padding: 0.5rem; text-align: center; width: 12%;">Quantity Received</th>
                        <th style="border: 1px solid #000; padding: 0.5rem; text-align: center; width: 28%;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($items_result->num_rows > 0):
                        while ($item = $items_result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td style="border: 1px solid #000; padding: 0.5rem; text-align: center;"><?php echo htmlspecialchars($item['stock_number'] ?? ''); ?></td>
                        <td style="border: 1px solid #000; padding: 0.5rem; text-align: center;"><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td>
                        <td style="border: 1px solid #000; padding: 0.5rem; text-align: center;"><?php echo htmlspecialchars($item['description']); ?></td>
                        <td style="border: 1px solid #000; padding: 0.5rem; text-align: center;"><?php echo $item['quantity_requested']; ?></td>
                        <td style="border: 1px solid #000; padding: 0.5rem; text-align: center;"><?php echo $item['quantity_received']; ?></td>
                        <td style="border: 1px solid #000; padding: 0.5rem; text-align: center;"><?php echo htmlspecialchars($item['remarks'] ?? ''); ?></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="6" style="border: 1px solid #000; padding: 0.5rem; text-align: center;">No items found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Purpose -->
        <div style="margin: 1rem 0; padding: 0.5rem; border-bottom: 1px solid #000;">
           <p> Purpose: <strong><?php echo nl2br(htmlspecialchars($form['purpose'] ?? '')); ?></strong></p>
        </div>

        <!-- Signature Block -->
        <table style="width: 100%; margin-top: 2rem; border-collapse: collapse;">
            <tr>
                <td style="width: 33%; text-align: center; padding: 1rem;">
                    <p style="margin-top: 0.5rem;">Requested by</p>
                    <div style="height: 60px; border-bottom: 1px solid #000; margin-bottom: 0.5rem;"></div>
                    <strong><?php echo htmlspecialchars($form['requested_by'] ?? ''); ?></strong><br>
                    <small><?php echo htmlspecialchars($form['requested_by_designation'] ?? ''); ?></small><br>
                    <small><?php echo $form['requested_by_date'] ? date('m/d/Y', strtotime($form['requested_by_date'])) : ''; ?></small>
                    
                </td>
                <td style="width: 33%; text-align: center; padding: 1rem;">
                    <p style="margin-top: 0.5rem;">Approved by</p>
                    <div style="height: 60px; border-bottom: 1px solid #000; margin-bottom: 0.5rem;"></div>
                    <strong><?php echo htmlspecialchars($form['approved_by'] ?? ''); ?></strong><br>
                    <small><?php echo htmlspecialchars($form['approved_by_designation'] ?? ''); ?></small><br>
                    <small><?php echo $form['approved_by_date'] ? date('m/d/Y', strtotime($form['approved_by_date'])) : ''; ?></small>
                    
                </td>
                <td style="width: 33%; text-align: center; padding: 1rem;">
                    <p style="margin-top: 0.5rem;">Received by</p>
                    <div style="height: 60px; border-bottom: 1px solid #000; margin-bottom: 0.5rem;"></div>
                    <strong><?php echo htmlspecialchars($form['received_by'] ?? ''); ?></strong><br>
                    <small><?php echo htmlspecialchars($form['received_by_designation'] ?? ''); ?></small><br>
                    <small><?php echo $form['received_by_date'] ? date('m/d/Y', strtotime($form['received_by_date'])) : ''; ?></small>
                    
                </td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin-top: 2rem;" class="no-print">
        <button onclick="window.print()" class="btn btn-primary">Print Form</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    <script>
        window.onload = function() {
            window.focus();
        };
    </script>
</body>
</html>
