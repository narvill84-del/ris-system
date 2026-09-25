<?php
require_once '../config/database.php';
header('Content-Type: application/json; charset=UTF-8');

function update_json(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function update_date(?string $value, bool $required = false): bool
{
    if ($value === null || $value === '') return !$required;
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') update_json(['success'=>false,'message'=>'POST is required.'], 405);
    $ris_id = (int) ($_POST['id'] ?? 0);
    if ($ris_id < 1) throw new RuntimeException('Invalid RIS ID.');

    $value = static fn(string $key): string => trim((string) ($_POST[$key] ?? ''));
    $nullable = static function (string $key) use ($value): ?string {
        $v = $value($key); return $v === '' ? null : $v;
    };

    $office = $value('office_name');
    $center = $nullable('responsibility_center_code');
    $ris_date = $value('ris_date');
    $sai_number = $nullable('sai_number');
    $sai_date = $nullable('sai_date');
    $purpose = $value('purpose');
    $requested_by = $value('requested_by');
    $requested_sig = $nullable('requested_by_signature');
    $requested_designation = $value('requested_by_designation');
    $requested_date = $value('requested_by_date');
    $approved_by = $value('approved_by');
    $approved_sig = $nullable('approved_by_signature');
    $approved_designation = $value('approved_by_designation');
    $approved_date = $value('approved_by_date');
    $received_by = $nullable('received_by');
    $received_sig = $nullable('received_by_signature');
    $received_designation = $nullable('received_by_designation');
    $received_date = $nullable('received_by_date');

    foreach (['Office'=>$office,'RIS date'=>$ris_date,'Purpose'=>$purpose,'Requested by'=>$requested_by,'Requested-by designation'=>$requested_designation,'Requested-by date'=>$requested_date,'Approved by'=>$approved_by,'Approved-by designation'=>$approved_designation,'Approved-by date'=>$approved_date] as $name=>$field) {
        if ($field === '') throw new RuntimeException($name . ' is required.');
    }
    foreach ([$ris_date, $requested_date, $approved_date] as $date) if (!update_date($date, true)) throw new RuntimeException('A required date is invalid.');
    foreach ([$sai_date, $received_date] as $date) if (!update_date($date)) throw new RuntimeException('An optional date is invalid.');

    $raw = $_POST['line_items'] ?? '';
    $items = is_array($raw) ? $raw : json_decode((string) $raw, true);
    if (!is_array($items)) throw new RuntimeException('Line-item data is invalid.');
    $valid_items = [];
    foreach ($items as $index=>$item) {
        if (!is_array($item)) continue;
        $description = trim((string) ($item['descriptions'] ?? $item['description'] ?? ''));
        $requested = filter_var($item['quantity_requested'] ?? null, FILTER_VALIDATE_INT);
        $received = filter_var($item['quantity_received'] ?? 0, FILTER_VALIDATE_INT);
        if ($description === '' && ($item['quantity_requested'] ?? '') === '') continue;
        if ($description === '' || $requested === false || $requested < 1) throw new RuntimeException('Invalid line item ' . ((int)$index + 1) . '.');
        if ($received === false || $received < 0 || $received > $requested) throw new RuntimeException('Invalid received quantity for line item ' . ((int)$index + 1) . '.');
        $valid_items[] = [trim((string)($item['stock_number'] ?? '')),trim((string)($item['unit'] ?? '')),$description,(int)$requested,(int)$received,trim((string)($item['remarks'] ?? ''))];
    }
    if (!$valid_items) throw new RuntimeException('Please add at least one valid line item.');

    $conn->begin_transaction();
    $exists = $conn->prepare('SELECT id FROM ris_forms WHERE id = ? FOR UPDATE');
    $exists->bind_param('i', $ris_id); $exists->execute(); $exists->store_result();
    if ($exists->num_rows !== 1) throw new RuntimeException('RIS form not found.');
    $exists->close();

    $stmt = $conn->prepare('UPDATE ris_forms SET sai_number=?,office_name=?,responsibility_center_code=?,ris_date=?,sai_date=?,purpose=?,requested_by=?,requested_by_signature=?,requested_by_designation=?,requested_by_date=?,approved_by=?,approved_by_signature=?,approved_by_designation=?,approved_by_date=?,received_by=?,received_by_signature=?,received_by_designation=?,received_by_date=? WHERE id=?');
    $stmt->bind_param('ssssssssssssssssssi',$sai_number,$office,$center,$ris_date,$sai_date,$purpose,$requested_by,$requested_sig,$requested_designation,$requested_date,$approved_by,$approved_sig,$approved_designation,$approved_date,$received_by,$received_sig,$received_designation,$received_date,$ris_id);
    $stmt->execute(); $stmt->close();

    $delete = $conn->prepare('DELETE FROM ris_line_items WHERE ris_id = ?');
    $delete->bind_param('i', $ris_id); $delete->execute(); $delete->close();
    $item_stmt = $conn->prepare('INSERT INTO ris_line_items (ris_id,stock_number,unit,descriptions,quantity_requested,quantity_received,remarks) VALUES (?,?,?,?,?,?,?)');
    foreach ($valid_items as [$stock,$unit,$description,$requested_qty,$received_qty,$remarks]) {
        $item_stmt->bind_param('isssiis',$ris_id,$stock,$unit,$description,$requested_qty,$received_qty,$remarks); $item_stmt->execute();
    }
    $item_stmt->close();
    log_audit((int)($_SESSION['user_id'] ?? 1), $ris_id, 'UPDATE', 'Form updated');
    $conn->commit();
    update_json(['success'=>true,'message'=>'Form updated successfully.','ris_id'=>$ris_id]);
} catch (Throwable $exception) {
    if (isset($conn)) { try { $conn->rollback(); } catch (Throwable $ignored) {} }
    error_log('Update RIS form error: ' . $exception->getMessage());
    update_json(['success'=>false,'message'=>$exception->getMessage()], 400);
}
