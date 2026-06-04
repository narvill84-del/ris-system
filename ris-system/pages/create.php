<?php
/**
 * Create RIS Form Page
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$page_title = 'Create RIS Form';
include '../includes/header.php';

// Generate RIS Number
$ris_number = generate_ris_number($conn);
?>

<div class="card col-full">
    <div class="card-header">
        <h2>Create New Requisition and Issue Slip (RIS)</h2>
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
                        <input type="text" name="office_name" id="office_name" required data-validate="required" data-field-name="Office">
                    </div>
                    <div class="ris-info-item">
                        <label>Responsibility Center Code:</label>
                        <input type="text" name="responsibility_center_code" id="responsibility_center_code">
                    </div>
                    <div class="ris-info-item">
                        <label>RIS No.:</label>
                        <input type="text" name="ris_number" id="ris_number" value="<?php echo htmlspecialchars($ris_number); ?>" readonly>
                    </div>
                    <div class="ris-info-item">
                        <label>Date:</label>
                        <input type="date" name="ris_date" id="ris_date" required data-validate="date" data-field-name="RIS Date">
                    </div>
                    <div class="ris-info-item">
                        <label>SAI No.:</label>
                        <input type="text" name="sai_number" id="sai_number">
                    </div>
                    <div class="ris-info-item">
                        <label>Date:</label>
                        <input type="date" name="sai_date" id="sai_date">
                    </div>
                </div>

                <!-- Purpose Section -->
                <div class="form-group">
                    <label>Purpose:</label>
                    <textarea name="purpose" id="purpose" required data-validate="required" data-field-name="Purpose"></textarea>
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
                        <tr>
                            <td><input type="text" name="stock_number_0" class="form-control" placeholder="Stock No."></td>
                            <td><input type="text" name="unit_0" class="form-control" placeholder="Unit"></td>
                            <td><input type="text" name="description_0" class="form-control" placeholder="Description" required></td>
                            <td><input type="number" name="quantity_requested_0" class="form-control" placeholder="Qty" required min="1"></td>
                            <td><input type="number" name="quantity_received_0" class="form-control" placeholder="Qty" min="0"></td>
                            <td><input type="text" name="remarks_0" class="form-control" placeholder="Remarks"></td>
                            <td><button type="button" class="btn btn-danger btn-small" onclick="removeLineItem(this)">Remove</button></td>
                        </tr>
                    </tbody>
                </table>

                <button type="button" class="btn btn-secondary" onclick="addLineItem()" style="margin-top: 1rem;">+ Add Item</button>

                <!-- Signature Block -->
                <div class="signature-block">
                    <div class="signature-field">
                        <label>Requested by:</label>
                        <input type="text" name="requested_by" id="requested_by" placeholder="Name" required>
                        <div class="signature-line"></div>
                        <div class="signature-label">Signature</div>

                        <input type="text" name="requested_by_designation" id="requested_by_designation" placeholder="Designation" required>
                        <div class="signature-label">Designation</div>

                        <input type="date" name="requested_by_date" id="requested_by_date" required>
                        <div class="signature-label">Date</div>
                    </div>

                    <div class="signature-field">
                        <label>Approved by:</label>
                        <input type="text" name="approved_by" id="approved_by" placeholder="Name" required>
                        <div class="signature-line"></div>
                        <div class="signature-label">Signature</div>

                        <input type="text" name="approved_by_designation" id="approved_by_designation" placeholder="Designation" required>
                        <div class="signature-label">Designation</div>

                        <input type="date" name="approved_by_date" id="approved_by_date" required>
                        <div class="signature-label">Date</div>
                    </div>

                    <div class="signature-field">
                        <label>Received by:</label>
                        <input type="text" name="received_by" id="received_by" placeholder="Name">
                        <div class="signature-line"></div>
                        <div class="signature-label">Signature</div>

                        <input type="text" name="received_by_designation" id="received_by_designation" placeholder="Designation">
                        <div class="signature-label">Designation</div>

                        <input type="date" name="received_by_date" id="received_by_date">
                        <div class="signature-label">Date</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="btn-group">
                    <button type="button" class="btn btn-success" name="save" onclick="saveRISForm()">Save Form</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="<?php echo APP_URL; ?>/js/form-validation.js"></script>
<script src="<?php echo APP_URL; ?>/js/form-handler.js"></script>
