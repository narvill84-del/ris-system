<?php
require_once '../config/database.php';

$page_title = 'Edit RIS Form';
$dashboard_layout = true;
$ris_id = (int) ($_GET['id'] ?? 0);
if ($ris_id < 1) exit('Invalid RIS ID.');

$stmt = $conn->prepare('SELECT * FROM ris_forms WHERE id = ?');
$stmt->bind_param('i', $ris_id); $stmt->execute();
$form_result = $stmt->get_result();
$form = $form_result->fetch_assoc(); $stmt->close();
if (!$form) exit('RIS form not found.');

$item_stmt = $conn->prepare('SELECT stock_number,unit,descriptions,quantity_requested,quantity_received,remarks FROM ris_line_items WHERE ris_id = ? ORDER BY id');
$item_stmt->bind_param('i', $ris_id); $item_stmt->execute();
$items = $item_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $item_stmt->close();
if (!$items) $items = [['stock_number'=>'','unit'=>'','descriptions'=>'','quantity_requested'=>'','quantity_received'=>0,'remarks'=>'']];
include '../includes/header.php';
function edit_value(array $form, string $key): string { return escape_html($form[$key] ?? ''); }
?>
<main class="container">
<h2>Edit Requisition and Issue Slip</h2>
<form id="ris-form" method="post">
<input type="hidden" name="id" value="<?php echo $ris_id; ?>">
<input type="hidden" name="line_items" id="line_items" value="">
<div class="ris-info-grid">
<label>Office<input name="office_name" id="office_name" required value="<?php echo edit_value($form,'office_name'); ?>"></label>
<label>Responsibility Center Code<input name="responsibility_center_code" id="responsibility_center_code" value="<?php echo edit_value($form,'responsibility_center_code'); ?>"></label>
<label>RIS No.<input readonly value="<?php echo edit_value($form,'ris_number'); ?>"></label>
<label>RIS Date<input type="date" name="ris_date" id="ris_date" required value="<?php echo edit_value($form,'ris_date'); ?>"></label>
<label>SAI No.<input name="sai_number" id="sai_number" value="<?php echo edit_value($form,'sai_number'); ?>"></label>
<label>SAI Date<input type="date" name="sai_date" id="sai_date" value="<?php echo edit_value($form,'sai_date'); ?>"></label>
</div>
<label>Purpose<textarea name="purpose" id="purpose" required><?php echo edit_value($form,'purpose'); ?></textarea></label>
<h3>REQUISITION ITEMS</h3>
<div class="line-items-wrapper"><table class="line-items-table"><thead><tr><th>Stock No.</th><th>Unit</th><th>Description</th><th>Requested</th><th>Received</th><th>Remarks</th><th></th></tr></thead><tbody id="line-items-body">
<?php foreach ($items as $index=>$item): ?><tr>
<td><input name="stock_number_<?php echo $index; ?>" value="<?php echo escape_html($item['stock_number']); ?>"></td>
<td><input name="unit_<?php echo $index; ?>" value="<?php echo escape_html($item['unit']); ?>"></td>
<td><input name="description_<?php echo $index; ?>" required value="<?php echo escape_html($item['descriptions']); ?>"></td>
<td><input type="number" min="1" name="quantity_requested_<?php echo $index; ?>" required value="<?php echo escape_html($item['quantity_requested']); ?>"></td>
<td><input type="number" min="0" name="quantity_received_<?php echo $index; ?>" value="<?php echo escape_html($item['quantity_received']); ?>"></td>
<td><input name="remarks_<?php echo $index; ?>" value="<?php echo escape_html($item['remarks']); ?>"></td>
<td><button type="button" class="remove-item-button" onclick="removeLineItem(this)">Remove</button></td>
</tr><?php endforeach; ?></tbody></table></div>
<button type="button" class="btn btn-secondary" onclick="addLineItem()">+ Add Item</button>
<div class="signature-block">
<div class="signature-field"><label>Requested by</label><input name="requested_by" id="requested_by" required value="<?php echo edit_value($form,'requested_by'); ?>"><input name="requested_by_signature" id="requested_by_signature" value="<?php echo edit_value($form,'requested_by_signature'); ?>"><input name="requested_by_designation" id="requested_by_designation" required value="<?php echo edit_value($form,'requested_by_designation'); ?>"><input type="date" name="requested_by_date" id="requested_by_date" required value="<?php echo edit_value($form,'requested_by_date'); ?>"></div>
<div class="signature-field"><label>Approved by</label><input name="approved_by" id="approved_by" required value="<?php echo edit_value($form,'approved_by'); ?>"><input name="approved_by_signature" id="approved_by_signature" value="<?php echo edit_value($form,'approved_by_signature'); ?>"><input name="approved_by_designation" id="approved_by_designation" required value="<?php echo edit_value($form,'approved_by_designation'); ?>"><input type="date" name="approved_by_date" id="approved_by_date" required value="<?php echo edit_value($form,'approved_by_date'); ?>"></div>
<div class="signature-field"><label>Received by</label><input name="received_by" id="received_by" value="<?php echo edit_value($form,'received_by'); ?>"><input name="received_by_signature" id="received_by_signature" value="<?php echo edit_value($form,'received_by_signature'); ?>"><input name="received_by_designation" id="received_by_designation" value="<?php echo edit_value($form,'received_by_designation'); ?>"><input type="date" name="received_by_date" id="received_by_date" value="<?php echo edit_value($form,'received_by_date'); ?>"></div>
</div>
<button type="submit" class="btn btn-primary">Update Form</button> <a class="btn btn-secondary" href="view.php?id=<?php echo $ris_id; ?>">Cancel</a>
</form></main>
<?php include '../includes/footer.php'; ?>
<script>window.RIS_UPDATE_ID=<?php echo $ris_id; ?>;</script>
