<?php
/**
 * Edit RIS Form Page
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$page_title = 'Edit RIS Form';
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
        <h2>Edit Requisition and Issue Slip (RIS)</h2>
    </div>
    <div class="card-body">
        <form id="ris-form" method="POST">
            <!-- RIS Header -->
            <div class="ris-form">
                <div class="ris-header">
                    <h1>REQUISITION AND ISSUE SLIP FORM</h1>
                    <p>MARGOSATUBIG, ZAMBOANGA DEL SUR</p>
                    <p>LGU</p>
                </div>

                <!-- Form Info Section -->
                <div class="ris-info-grid">
                    <div class="ris-info-item">
                        <label>Office:</label>
                        <input type="text" name="office_name" id="office_name" value="<?php echo htmlspecialchars($form['office_name']); ?>" required>
                    </div>
                    <div class="ris-info-item">
                        <label>Responsibility Center Code:</label>
                        <input type="text" name="responsibility_center_code" id="responsibility_center_code" value="<?php echo htmlspecialchars($form['responsibility_center_code'] ?? ''); ?>">
                    </div>
                    <div class="ris-info-item">
                        <label>RIS No.:</label>
                        <input type="text" name="ris_number" id="ris_number" value="<?php echo htmlspecialchars($form['ris_number']); ?>" readonly>
                    </div>
                    <div class="ris-info-item">
                        <label>Date:</label>
                        <input type="date" name="ris_date" id="ris_date" value="<?php echo htmlspecialchars($form['ris_date']); ?>" required>
                    </div>
                    <div class="ris-info-item">
                        <label>SAI No.:</label>
                        <input type="text" name="sai_number" id="sai_number" value="<?php echo htmlspecialchars($form['sai_number'] ?? ''); ?>">
                    </div>
                    <div class="ris-info-item">
                        <label>Date:</label>
                        <input type="date" name="sai_date" id="sai_date" value="<?php echo htmlspecialchars($form['sai_date'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Purpose Section -->
                <div class="form-group">
                    <label>Purpose:</label>
                    <textarea name="purpose" id="purpose" required><?php echo htmlspecialchars($form['purpose'] ?? ''); ?></textarea>
                </div>

                <!-- Line Items Table -->
                <h3 style="margin-top: 2rem; margin-bottom: 1rem;">REQUISITION</h3>
                <table class="line-items-table">
                    <thead>
                        <tr>
                            <th>Stock No.</th>
                            <th>Unit</th>
                            <th>Description</th>
                            <th>Quantity Requested</th>
                            <th>Quantity Received</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $index = 0;
                        while ($item = $items_result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><input type="text" name="stock_number_<?php echo $index; ?>" class="form-control" value="<?php echo htmlspecialchars($item['stock_number'] ?? ''); ?>"></td>
                            <td><input type="text" name="unit_<?php echo $index; ?>" class="form-control" value="<?php echo htmlspecialchars($item['unit'] ?? ''); ?>"></td>
                            <td><input type="text" name="description_<?php echo $index; ?>" class="form-control" value="<?php echo htmlspecialchars($item['description']); ?>" required></td>
                            <td><input type="number" name="quantity_requested_<?php echo $index; ?>" class="form-control" value="<?php echo $item['quantity_requested']; ?>" required min="1"></td>
                            <td><input type="number" name="quantity_received_<?php echo $index; ?>" class="form-control" value="<?php echo $item['quantity_received']; ?>" min="0"></td>
                            <td><input type="text" name="remarks_<?php echo $index; ?>" class="form-control" value="<?php echo htmlspecialchars($item['remarks'] ?? ''); ?>"></td>
                            <td><button type="button" class="btn btn-danger btn-small" onclick="removeLineItem(this)">Remove</button></td>
                        </tr>
                        <?php $index++; endwhile; ?>
                    </tbody>
                </table>

                <button type="button" class="btn btn-secondary" onclick="addLineItem()" style="margin-top: 1rem;">+ Add Item</button>

                <!-- Signature Block -->
                <div class="signature-block">
                    <div class="signature-field">
                        <label>Requested by:</label>
                        <input type="text" name="requested_by" id="requested_by" value="<?php echo htmlspecialchars($form['requested_by'] ?? ''); ?>" placeholder="Name" required>
                        <div class="signature-line"></div>
                        <div class="signature-label">Signature</div>

                        <input type="text" name="requested_by_designation" id="requested_by_designation" value="<?php echo htmlspecialchars($form['requested_by_designation'] ?? ''); ?>" placeholder="Designation" required>
                        <div class="signature-label">Designation</div>

                        <input type="date" name="requested_by_date" id="requested_by_date" value="<?php echo htmlspecialchars($form['requested_by_date'] ?? ''); ?>" required>
                        <div class="signature-label">Date</div>
                    </div>

                    <div class="signature-field">
                        <label>Approved by:</label>
                        <input type="text" name="approved_by" id="approved_by" value="<?php echo htmlspecialchars($form['approved_by'] ?? ''); ?>" placeholder="Name" required>
                        <div class="signature-line"></div>
                        <div class="signature-label">Signature</div>

                        <input type="text" name="approved_by_designation" id="approved_by_designation" value="<?php echo htmlspecialchars($form['approved_by_designation'] ?? ''); ?>" placeholder="Designation" required>
                        <div class="signature-label">Designation</div>

                        <input type="date" name="approved_by_date" id="approved_by_date" value="<?php echo htmlspecialchars($form['approved_by_date'] ?? ''); ?>" required>
                        <div class="signature-label">Date</div>
                    </div>

                    <div class="signature-field">
                        <label>Received by:</label>
                        <input type="text" name="received_by" id="received_by" value="<?php echo htmlspecialchars($form['received_by'] ?? ''); ?>" placeholder="Name">
                        <div class="signature-line"></div>
                        <div class="signature-label">Signature</div>

                        <input type="text" name="received_by_designation" id="received_by_designation" value="<?php echo htmlspecialchars($form['received_by_designation'] ?? ''); ?>" placeholder="Designation">
                        <div class="signature-label">Designation</div>

                        <input type="date" name="received_by_date" id="received_by_date" value="<?php echo htmlspecialchars($form['received_by_date'] ?? ''); ?>">
                        <div class="signature-label">Date</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="btn-group">
                    <button type="button" class="btn btn-success" name="update" onclick="updateRISForm(<?php echo $ris_id; ?>)">Update Form</button>
                    <a href="view.php?id=<?php echo $ris_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
