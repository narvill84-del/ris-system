<?php
/**
 * API - Get Single RIS Form
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';
header('Content-Type: application/json');

$response = ['success' => false, 'data' => null];

try {
    $ris_id = (int)($_GET['id'] ?? 0);
    if ($ris_id <= 0) {
        throw new Exception('Invalid RIS ID');
    }

    // Get form data
    $query = "SELECT * FROM ris_forms WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ris_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Form not found');
    }

    $form = $result->fetch_assoc();

    // Get line items
    $items_query = "SELECT * FROM ris_line_items WHERE ris_id = ? ORDER BY id ASC";
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->bind_param("i", $ris_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();

    $line_items = [];
    while ($item = $items_result->fetch_assoc()) {
        $line_items[] = $item;
    }

    $response = [
        'success' => true,
        'form' => $form,
        'line_items' => $line_items
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
