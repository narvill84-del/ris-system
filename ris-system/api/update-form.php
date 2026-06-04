<?php
/**
 * API - Update RIS Form
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $ris_id = (int)($_POST['id'] ?? 0);
    if ($ris_id <= 0) {
        throw new Exception('Invalid RIS ID');
    }

    // Get form data
    $office_name = sanitize_input($_POST['office_name'] ?? '');
    $responsibility_center_code = sanitize_input($_POST['responsibility_center_code'] ?? '');
    $ris_date = sanitize_input($_POST['ris_date'] ?? '');
    $sai_number = sanitize_input($_POST['sai_number'] ?? '');
    $sai_date = sanitize_input($_POST['sai_date'] ?? '');
    $purpose = sanitize_input($_POST['purpose'] ?? '');
    $requested_by = sanitize_input($_POST['requested_by'] ?? '');
    $requested_by_designation = sanitize_input($_POST['requested_by_designation'] ?? '');
    $requested_by_date = sanitize_input($_POST['requested_by_date'] ?? '');
    $approved_by = sanitize_input($_POST['approved_by'] ?? '');
    $approved_by_designation = sanitize_input($_POST['approved_by_designation'] ?? '');
    $approved_by_date = sanitize_input($_POST['approved_by_date'] ?? '');
    $received_by = sanitize_input($_POST['received_by'] ?? '');
    $received_by_designation = sanitize_input($_POST['received_by_designation'] ?? '');
    $received_by_date = sanitize_input($_POST['received_by_date'] ?? '');

    // Update RIS Form
    $query = "UPDATE ris_forms SET
        office_name = ?, responsibility_center_code = ?, ris_date = ?, sai_number = ?, sai_date = ?,
        purpose = ?, requested_by = ?, requested_by_designation = ?, requested_by_date = ?,
        approved_by = ?, approved_by_designation = ?, approved_by_date = ?,
        received_by = ?, received_by_designation = ?, received_by_date = ?
        WHERE id = ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param(
        "sssssssssssssssi",
        $office_name, $responsibility_center_code, $ris_date, $sai_number, $sai_date,
        $purpose, $requested_by, $requested_by_designation, $requested_by_date,
        $approved_by, $approved_by_designation, $approved_by_date,
        $received_by, $received_by_designation, $received_by_date, $ris_id
    );

    if (!$stmt->execute()) {
        throw new Exception("Error updating form: " . $stmt->error);
    }

    // Delete old line items
    $delete_query = "DELETE FROM ris_line_items WHERE ris_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $ris_id);
    $delete_stmt->execute();

    // Insert new line items
    $line_items = json_decode($_POST['line_items'] ?? '[]', true);
    if (!empty($line_items)) {
        $item_query = "INSERT INTO ris_line_items (
            ris_id, stock_number, unit, description, quantity_requested, quantity_received, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $item_stmt = $conn->prepare($item_query);
        if (!$item_stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        foreach ($line_items as $item) {
            $stock_number = $item['stock_number'] ?? '';
            $unit = $item['unit'] ?? '';
            $description = $item['description'] ?? '';
            $quantity_requested = (int)($item['quantity_requested'] ?? 0);
            $quantity_received = (int)($item['quantity_received'] ?? 0);
            $remarks = $item['remarks'] ?? '';

            $item_stmt->bind_param(
                "ississs",
                $ris_id, $stock_number, $unit, $description, $quantity_requested, $quantity_received, $remarks
            );

            if (!$item_stmt->execute()) {
                throw new Exception("Error inserting line item: " . $item_stmt->error);
            }
        }
        $item_stmt->close();
    }

    // Log audit
    $user_id = $_SESSION['user_id'] ?? 1;
    log_audit($user_id, $ris_id, 'UPDATE', 'Form updated');

    $response = [
        'success' => true,
        'message' => 'Form updated successfully',
        'ris_id' => $ris_id
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
