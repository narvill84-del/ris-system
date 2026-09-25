<?php
/**
 * API - Save RIS Form
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
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

    // Generate RIS Number
    $ris_number = generate_ris_number($conn);

    // Insert RIS Form
    $query = "INSERT INTO ris_forms (
        ris_number, office_name, responsibility_center_code, ris_date, sai_number, sai_date,
        purpose, requested_by, requested_by_designation, requested_by_date,
        approved_by, approved_by_designation, approved_by_date,
        received_by, received_by_designation, received_by_date, created_by, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $created_by = $_SESSION['user_id'] ?? 1;
    $status = 'DRAFT';

    $stmt->bind_param(
        "ssssssssssssssssss",
        $ris_number, $office_name, $responsibility_center_code, $ris_date, $sai_number, $sai_date,
        $purpose, $requested_by, $requested_by_designation, $requested_by_date,
        $approved_by, $approved_by_designation, $approved_by_date,
        $received_by, $received_by_designation, $received_by_date, $created_by, $status
    );

    if (!$stmt->execute()) {
        throw new Exception("Error inserting form: " . $stmt->error);
    }

    $ris_id = $conn->insert_id;

    // Insert line items
    $line_items = json_decode($_POST['line_items'] ?? '[]', true);
    error_log("DEBUG: Received line_items: " . print_r($line_items, true));
    
    if (!empty($line_items)) {
        $item_query = "INSERT INTO ris_line_items (
            ris_id, stock_number, unit, descriptions, quantity_requested, quantity_received, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $item_stmt = $conn->prepare($item_query);
        if (!$item_stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $inserted_count = 0;
        foreach ($line_items as $index => $item) {
            // Skip empty rows (rows with no description)
            if (empty($item['descriptions'])) {
                error_log("DEBUG: Skipping empty row at index $index");
                continue;
            }

            $stock_number = sanitize_input($item['stock_number'] ?? '');
            $unit = sanitize_input($item['unit'] ?? '');
            $descriptions = sanitize_input($item['descriptions'] ?? '');
            $quantity_requested = (int)($item['quantity_requested'] ?? 0);
            $quantity_received = (int)($item['quantity_received'] ?? 0);
            $remarks = sanitize_input($item['remarks'] ?? '');

            error_log("DEBUG: Inserting item - Descriptions: $descriptions, Qty Req: $quantity_requested");

            $item_stmt->bind_param(
                "ississs",
                $ris_id, $stock_number, $unit, $descriptions, $quantity_requested, $quantity_received, $remarks
            );

            if (!$item_stmt->execute()) {
                throw new Exception("Error inserting line item at index $index: " . $item_stmt->error);
            }
            
            $inserted_count++;
        }
        
        error_log("DEBUG: Successfully inserted $inserted_count line items for RIS ID: $ris_id");
        $item_stmt->close();
        
        if ($inserted_count === 0) {
            throw new Exception("No valid line items to save. Please add at least one item with a description.");
        }
    } else {
        throw new Exception("No line items received from form");
    }

    // Log audit
    log_audit($created_by, $ris_id, 'CREATE', "Form created with RIS Number: $ris_number");

    $response = [
        'success' => true,
        'message' => 'Form saved successfully',
        'ris_id' => $ris_id,
        'ris_number' => $ris_number
    ];

} catch (Exception $e) {
    error_log("ERROR in save-form.php: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>


