<?php
/**
 * View RIS Form Page
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$page_title = 'View RIS Form';
include '../includes/header.php';

$ris_id = (int)($_GET['id'] ?? 0);
if ($ris_id <= 0) {
    die('<div class="alert alert-danger">Invalid RIS ID</div>');
}

// Get form data
$query = "SELECT * FROM ris_forms WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ris_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('<div class="alert alert-danger">Form not found</div>');
}

$form = $result->fetch_assoc();

// Get line items
$items_query = "SELECT * FROM ris_line_items WHERE ris_id = ? ORDER BY id ASC";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $ris_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>

<div class="card col-full">
    <div class="card-header">
        <h2>View Requisition and Issue Slip (RIS)</h2>
    </div>
    <div class="card-body">
        <div class="ris-form" style="border: none; padding: 0;">
            <div class="ris-header">
                <h1>REQUISITION AND ISSUE SLIP FORM</h1>
                <p>MARGOSATUBIG, ZAMBOANGA DEL SUR</p>
                <p>LGU</p>
            </div>

            <!-- Form Info Display -->
            <div class="ris-info-grid" style="background: #f8f9fa; padding: 1rem; border-radius: 4px;">
                <div>
                    <strong>Office:</strong> <?php echo htmlspecialchars($form['office_name']); ?>
                </div>
                <div>
                    <strong>Responsibility Center Code:</strong> <?php echo htmlspecialchars($form['responsibility_center_code'] ?? 'N/A'); ?>
                </div>
                <div>
                    <strong>RIS No.:</strong> <?php echo htmlspecialchars($form['ris_number']); ?>
                </div>
                <div>
                    <strong>Date:</strong> <?php echo date(DISPLAY_DATE_FORMAT, strtotime($form['ris_date'])); ?>
                </div>
                <div>
                    <strong>SAI No.:</strong> <?php echo htmlspecialchars($form['sai_number'] ?? 'N/A'); ?>
                </div>
                <div>
                    <strong>Status:</strong> <span class="badge badge-<?php echo strtolower($form['status']); ?>"><?php echo $form['status']; ?></span>
                </div>
            </div>

            <!-- Description/Purpose -->
            <div style="margin: 2rem 0; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                <strong>Description:</strong><br>
                <?php echo nl2br(htmlspecialchars($form['purpose'] ?? '')); ?>
            </div>

            <!-- Line Items Table -->
            <h3 style="margin-top: 2rem; margin-bottom: 1rem;">REQUISITION ITEMS</h3>
            <table class="line-items-table">
                <thead>
                    <tr>
                        <th>Stock No.</th>
                        <th>Unit</th>
                        <th>Description</th>
                        <th>Quantity Requested</th>
                        <th>Quantity Received</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($items_result->num_rows > 0):
                        while ($item = $items_result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['stock_number'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                        <td><?php echo $item['quantity_requested']; ?></td>
                        <td><?php echo $item['quantity_received']; ?></td>
                        <td><?php echo htmlspecialchars($item['remarks'] ?? ''); ?></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">No items found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Signature Block -->
            <div class="signature-block" style="margin-top: 3rem; text-align: center;">
                <div class="signature-field">
                    <strong>Requested by:</strong><br>
                    <div style="height: 80px; margin: 1rem 0; border-bottom: 2px solid #000;"></div>
                    <?php echo htmlspecialchars($form['requested_by'] ?? ''); ?><br>
                    <small><?php echo htmlspecialchars($form['requested_by_designation'] ?? ''); ?></small><br>
                    <small><?php echo date(DISPLAY_DATE_FORMAT, strtotime($form['requested_by_date'] ?? '')); ?></small>
                </div>

                <div class="signature-field">
                    <strong>Approved by:</strong><br>
                    <div style="height: 80px; margin: 1rem 0; border-bottom: 2px solid #000;"></div>
                    <?php echo htmlspecialchars($form['approved_by'] ?? ''); ?><br>
                    <small><?php echo htmlspecialchars($form['approved_by_designation'] ?? ''); ?></small><br>
                    <small><?php echo date(DISPLAY_DATE_FORMAT, strtotime($form['approved_by_date'] ?? '')); ?></small>
                </div>

                <div class="signature-field">
                    <strong>Received by:</strong><br>
                    <div style="height: 80px; margin: 1rem 0; border-bottom: 2px solid #000;"></div>
                    <?php echo htmlspecialchars($form['received_by'] ?? ''); ?><br>
                    <small><?php echo htmlspecialchars($form['received_by_designation'] ?? ''); ?></small><br>
                    <small><?php echo date(DISPLAY_DATE_FORMAT, strtotime($form['received_by_date'] ?? '')); ?></small>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="btn-group" style="margin-top: 2rem;">
            <a href="edit.php?id=<?php echo $ris_id; ?>" class="btn btn-primary">Edit Form</a>
            <button onclick="printRISForm(<?php echo $ris_id; ?>)" class="btn btn-warning">Print</button>
            <button onclick="deleteRISForm(<?php echo $ris_id; ?>)" class="btn btn-danger">Delete</button>
            <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
