<?php
/**
 * API - Delete RIS Form
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $ris_id = (int)($input['id'] ?? 0);

    if ($ris_id <= 0) {
        throw new Exception('Invalid RIS ID');
    }

    // Delete line items
    $delete_items = "DELETE FROM ris_line_items WHERE ris_id = ?";
    $stmt1 = $conn->prepare($delete_items);
    $stmt1->bind_param("i", $ris_id);
    $stmt1->execute();

    // Delete form
    $delete_form = "DELETE FROM ris_forms WHERE id = ?";
    $stmt2 = $conn->prepare($delete_form);
    $stmt2->bind_param("i", $ris_id);

    if (!$stmt2->execute()) {
        throw new Exception("Error deleting form: " . $stmt2->error);
    }

    // Log audit
    $user_id = $_SESSION['user_id'] ?? 1;
    log_audit($user_id, $ris_id, 'DELETE', 'Form deleted');

    $response = [
        'success' => true,
        'message' => 'Form deleted successfully'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
