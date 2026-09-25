<?php
require_once '../config/database.php';
header('Content-Type: application/json; charset=UTF-8');

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function valid_date_value(?string $value, bool $required = false): bool
{
    if ($value === null || $value === '') return !$required;
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function normalize_line_items_from_post(array $post): array
{
    if (isset($post['line_items'])) {
        if (is_array($post['line_items'])) {
            return $post['line_items'];
        }

        if (is_string($post['line_items']) && $post['line_items'] !== '') {
            $decoded = json_decode($post['line_items'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
    }

    $groups = [];
    foreach ($post as $key => $value) {
        if (!preg_match('/^(stock_number|unit|description|descriptions|quantity_requested|quantity_received|remarks)_(\d+)$/', $key, $matches)) {
            continue;
        }

        $group = $matches[2];
        $field = $matches[1];
        $groups[$group][$field] = $value;
    }

    $lineItems = [];
    foreach ($groups as $item) {
        $lineItems[] = [
            'stock_number' => trim((string) ($item['stock_number'] ?? $item['stock_no'] ?? '')),
            'unit' => trim((string) ($item['unit'] ?? '')),
            'descriptions' => trim((string) ($item['descriptions'] ?? $item['description'] ?? '')),
            'quantity_requested' => trim((string) ($item['quantity_requested'] ?? '')),
            'quantity_received' => trim((string) ($item['quantity_received'] ?? '0')),
            'remarks' => trim((string) ($item['remarks'] ?? '')),
        ];
    }

    return $lineItems;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['success' => false, 'message' => 'POST is required.'], 405);
    }

    $string = static fn(string $key): string => trim((string) ($_POST[$key] ?? ''));
    $nullable = static function (string $key) use ($string): ?string {
        $value = $string($key);
        return $value === '' ? null : $value;
    };

    $fields = [
        'sai_number' => $nullable('sai_number'),
        'office_name' => $string('office_name'),
        'responsibility_center_code' => $nullable('responsibility_center_code'),
        'ris_date' => $string('ris_date'),
        'sai_date' => $nullable('sai_date'),
        'purpose' => $string('purpose'),
        'requested_by' => $string('requested_by'),
        'requested_by_signature' => $nullable('requested_by_signature'),
        'requested_by_designation' => $string('requested_by_designation'),
        'requested_by_date' => $string('requested_by_date'),
        'approved_by' => $string('approved_by'),
        'approved_by_signature' => $nullable('approved_by_signature'),
        'approved_by_designation' => $string('approved_by_designation'),
        'approved_by_date' => $string('approved_by_date'),
        'received_by' => $nullable('received_by'),
        'received_by_signature' => $nullable('received_by_signature'),
        'received_by_designation' => $nullable('received_by_designation'),
        'received_by_date' => $nullable('received_by_date'),
    ];

    foreach (
        ['office_name', 'ris_date', 'purpose', 'requested_by', 'requested_by_designation', 'requested_by_date', 'approved_by', 'approved_by_designation', 'approved_by_date']
        as $key
    ) {
        if ($fields[$key] === '') {
            throw new RuntimeException(ucwords(str_replace('_', ' ', $key)) . ' is required.');
        }
    }

    foreach (['ris_date', 'requested_by_date', 'approved_by_date'] as $key) {
        if (!valid_date_value($fields[$key], true)) {
            throw new RuntimeException(ucwords(str_replace('_', ' ', $key)) . ' is invalid.');
        }
    }

    foreach (['sai_date', 'received_by_date'] as $key) {
        if (!valid_date_value($fields[$key])) {
            throw new RuntimeException(ucwords(str_replace('_', ' ', $key)) . ' is invalid.');
        }
    }

    $items = normalize_line_items_from_post($_POST);
    if (!is_array($items) || empty($items)) {
        throw new RuntimeException('Line-item data is invalid.');
    }

    $valid_items = [];
    foreach ($items as $index => $item) {
        if (!is_array($item)) {
            continue;
        }

        $description = trim((string) ($item['descriptions'] ?? $item['description'] ?? ''));
        $requested = filter_var($item['quantity_requested'] ?? null, FILTER_VALIDATE_INT);
        $received = filter_var($item['quantity_received'] ?? 0, FILTER_VALIDATE_INT);

        if ($description === '' && ($item['quantity_requested'] ?? '') === '') {
            continue;
        }

        if ($description === '' || $requested === false || $requested < 1) {
            throw new RuntimeException('Invalid line item ' . ((int) $index + 1) . '.');
        }

        if ($received === false || $received < 0 || $received > $requested) {
            throw new RuntimeException('Invalid received quantity for line item ' . ((int) $index + 1) . '.');
        }

        $valid_items[] = [
            trim((string) ($item['stock_number'] ?? '')),
            trim((string) ($item['unit'] ?? '')),
            $description,
            (int) $requested,
            (int) $received,
            trim((string) ($item['remarks'] ?? '')),
        ];
    }

    if (!$valid_items) {
        throw new RuntimeException('Please add at least one valid line item.');
    }

    $conn->begin_transaction();
    $ris_number = generate_ris_number($conn);
    $created_by = (int) ($_SESSION['user_id'] ?? 1);
    $status = 'DRAFT';
    $query = 'INSERT INTO ris_forms (ris_number,sai_number,office_name,responsibility_center_code,ris_date,sai_date,purpose,requested_by,requested_by_signature,requested_by_designation,requested_by_date,approved_by,approved_by_signature,approved_by_designation,approved_by_date,received_by,received_by_signature,received_by_designation,received_by_date,created_by,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
    $stmt = $conn->prepare($query);
    $types = str_repeat('s', 19) . 'is';
    $stmt->bind_param(
        $types,
        $ris_number,
        $fields['sai_number'],
        $fields['office_name'],
        $fields['responsibility_center_code'],
        $fields['ris_date'],
        $fields['sai_date'],
        $fields['purpose'],
        $fields['requested_by'],
        $fields['requested_by_signature'],
        $fields['requested_by_designation'],
        $fields['requested_by_date'],
        $fields['approved_by'],
        $fields['approved_by_signature'],
        $fields['approved_by_designation'],
        $fields['approved_by_date'],
        $fields['received_by'],
        $fields['received_by_signature'],
        $fields['received_by_designation'],
        $fields['received_by_date'],
        $created_by,
        $status
    );
    $stmt->execute();
    $ris_id = (int) $conn->insert_id;
    $stmt->close();

    $item_stmt = $conn->prepare('INSERT INTO ris_line_items (ris_id,stock_number,unit,descriptions,quantity_requested,quantity_received,remarks) VALUES (?,?,?,?,?,?,?)');
    foreach ($valid_items as [$stock, $unit, $description, $requested, $received, $remarks]) {
        $item_stmt->bind_param('isssiis', $ris_id, $stock, $unit, $description, $requested, $received, $remarks);
        $item_stmt->execute();
    }
    $item_stmt->close();

    log_audit($created_by, $ris_id, 'CREATE', 'Form created with RIS Number: ' . $ris_number);
    $conn->commit();

    json_response([
        'success' => true,
        'message' => 'RIS form saved successfully.',
        'ris_id' => $ris_id,
        'ris_number' => $ris_number,
    ]);
} catch (Throwable $exception) {
    if (isset($conn) && $conn->connect_errno === 0 && $conn->errno === 0) {
        try {
            $conn->rollback();
        } catch (Throwable $ignored) {
        }
    }

    error_log('Save RIS form error: ' . $exception->getMessage());
    json_response(['success' => false, 'message' => $exception->getMessage()], 400);
}
