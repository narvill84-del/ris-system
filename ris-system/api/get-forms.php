<?php
/**
 * API - Get All RIS Forms
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';
header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'total' => 0];

try {
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? ITEMS_PER_PAGE);
    $offset = ($page - 1) * $limit;

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM ris_forms";
    $count_result = $conn->query($count_query);
    $count_row = $count_result->fetch_assoc();
    $total = $count_row['total'];

    // Get forms with pagination
    $query = "SELECT * FROM ris_forms ORDER BY created_at DESC LIMIT ?, ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $offset, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $forms = [];
    while ($form = $result->fetch_assoc()) {
        $forms[] = $form;
    }

    $response = [
        'success' => true,
        'data' => $forms,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'pages' => ceil($total / $limit)
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
