<?php
require_once '../config/database.php';
header('Content-Type: application/json; charset=UTF-8');

function delete_json(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') delete_json(['success'=>false,'message'=>'POST is required.'], 405);
    $input = json_decode(file_get_contents('php://input'), true);
    $ris_id = (int) ($input['id'] ?? $_POST['id'] ?? 0);
    if ($ris_id < 1) throw new RuntimeException('Invalid RIS ID.');

    $conn->begin_transaction();
    $check = $conn->prepare('SELECT id FROM ris_forms WHERE id = ? FOR UPDATE');
    $check->bind_param('i', $ris_id); $check->execute(); $check->store_result();
    if ($check->num_rows !== 1) throw new RuntimeException('RIS form not found.');
    $check->close();

    // Delete children explicitly so this works with both old and migrated schemas.
    $items = $conn->prepare('DELETE FROM ris_line_items WHERE ris_id = ?');
    $items->bind_param('i', $ris_id); $items->execute(); $deleted_items = $items->affected_rows; $items->close();
    $form = $conn->prepare('DELETE FROM ris_forms WHERE id = ?');
    $form->bind_param('i', $ris_id); $form->execute(); $deleted_forms = $form->affected_rows; $form->close();
    if ($deleted_forms !== 1) throw new RuntimeException('The RIS form could not be deleted.');
    $conn->commit();
    delete_json(['success'=>true,'message'=>'Form deleted successfully.','deleted_items'=>$deleted_items,'deleted_forms'=>$deleted_forms]);
} catch (Throwable $exception) {
    if (isset($conn)) { try { $conn->rollback(); } catch (Throwable $ignored) {} }
    error_log('Delete RIS form error: ' . $exception->getMessage());
    delete_json(['success'=>false,'message'=>$exception->getMessage()], 400);
}
