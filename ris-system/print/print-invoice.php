<?php
/**
 * Print Invoice and Receipt of Accountable Forms
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$ris_id = (int) ($_GET['id'] ?? 0);
if ($ris_id <= 0) {
    die('Invalid RIS ID.');
}

$form_stmt = $conn->prepare('SELECT * FROM ris_forms WHERE id = ?');
$form_stmt->bind_param('i', $ris_id);
$form_stmt->execute();
$form_result = $form_stmt->get_result();

if ($form_result->num_rows === 0) {
    die('RIS form not found.');
}

$form = $form_result->fetch_assoc();
$form_stmt->close();

$items_stmt = $conn->prepare(
    'SELECT stock_number, unit, descriptions, quantity_requested,
            quantity_received, remarks
     FROM ris_line_items
     WHERE ris_id = ?
     ORDER BY id ASC'
);
$items_stmt->bind_param('i', $ris_id);
$items_stmt->execute();
$items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();

$h = static function ($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
};

$date = static function ($value, string $format = 'm/d/Y'): string {
    if (empty($value) || $value === '0000-00-00') {
        return '';
    }

    $timestamp = strtotime((string) $value);
    return $timestamp === false ? '' : date($format, $timestamp);
};

$total_quantity = 0;
foreach ($items as $item) {
    $total_quantity += (int) ($item['quantity_requested'] ?? 0);
}

$generated_at = date('F d, Y H:i:s');
$office = $form['office_name'] ?? '';
$municipality = 'Margosatubig';
$province = 'Zamboanga del Sur';
$requested_by = $form['requested_by'] ?? '';
$requested_designation = $form['requested_by_designation'] ?? '';
$approved_by = $form['approved_by'] ?? '';
$approved_designation = $form['approved_by_designation'] ?? 'Acting Municipal Treasurer';
$received_by = $form['received_by'] ?? '';
$received_designation = $form['received_by_designation'] ?? 'Receiving Officer';

/* Checked By and Witness remain independent and blank by default. */
$checked_by = trim((string) ($_GET['checked_by'] ?? ''));
$witness_name = trim((string) ($_GET['witness_name'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice - <?php echo $h($form['ris_number'] ?? ''); ?></title>
<style>
@page { size: A4 portrait; margin: 10mm 9mm; }
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; }
.page { width: 100%; max-width: 794px; margin: 0 auto; }
.header { position: relative; min-height: 39mm; text-align: center; padding-top: 2mm; }
.header p { margin: 0; line-height: 1.2; font-size: 10pt; font-weight: 700; }
.header .title { margin-top: 3mm; font-family: Georgia, 'Times New Roman', serif; font-size: 15pt; }
.header .generated { margin-top: 1.5mm; font-size: 7pt; font-weight: 400; }
.meta { position: absolute; top: 0; right: 0; font-size: 6.5pt; font-weight: 700; text-align: right; }
.rule { border-top: 1px solid #000; margin: 2mm 0; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.reference td { padding: 1mm; font-size: 7.5pt; vertical-align: bottom; }
.reference .right { width: 28%; text-align: right; font-size: 6.5pt; font-weight: 700; }
.line { display: inline-block; min-width: 42mm; border-bottom: 1px solid #000; }
.office { margin-top: 1mm; border-top: 1px solid #000; border-bottom: 1px solid #000; }
.office td { padding: 1.5mm 1mm; text-align: center; font-size: 7.5pt; font-weight: 700; }
.office small, .recipient small { display: block; font-size: 6pt; font-weight: 400; font-style: italic; }
.recipient { margin-top: 1.5mm; border-bottom: 1px solid #000; }
.recipient td { height: 8mm; padding: 1mm 2mm; font-size: 7.5pt; font-weight: 700; vertical-align: middle; }
.recipient .recipient-name { width: 30%; text-align: left; }
.recipient .recipient-title { width: 28%; text-align: center; }
.recipient .recipient-office { width: 25%; text-align: center; }
.recipient .recipient-location { width: 17%; text-align: center; }
.recipient .recipient-name strong { text-decoration: underline; text-transform: uppercase; }
.forms { margin-top: 2mm; border: 1px solid #000; }
.forms th, .forms td { height: 7mm; padding: 1mm; border: 1px solid #000; font-size: 7pt; text-align: center; vertical-align: middle; overflow-wrap: anywhere; }
.forms th { background: #f3f3f3; font-size: 6.5pt; text-transform: uppercase; }
.forms .left { text-align: left; }
.forms .blank td { height: 7mm; }
.certification { padding: 3mm 8mm 1mm; text-align: center; font-size: 7.5pt; line-height: 1.4; }
.underline { display: inline-block; min-width: 16mm; border-bottom: 1px solid #000; }
.certification-table { margin-top: 1mm; border: 1px solid #000; }
.certification-table td { height: 11mm; padding: 1mm; border: 1px solid #000; text-align: center; font-size: 7pt; }
.total { width: 25%; font-weight: 700; }
.signatures { margin-top: 7mm; }
.signatures td { width: 50%; padding: 1mm 7mm; text-align: center; vertical-align: top; font-size: 7pt; }
.signature-line { height: 8mm; margin-bottom: 2mm; border-bottom: 1px solid #000; }
.signature-name { display: block; font-size: 8pt; font-weight: 700; text-decoration: underline; text-transform: uppercase; }
.signature-role { display: block; margin-top: 1mm; font-size: 6.5pt; font-weight: 700; text-transform: uppercase; }
.no-print { margin-top: 15px; text-align: center; }
.no-print .controls { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; }
.no-print label { display: inline-flex; flex-direction: column; font-size: 11px; font-weight: 600; text-align: left; }
.no-print input { width: 180px; padding: 7px 10px; border: 1px solid #bbb; border-radius: 6px; }
.no-print button { margin: 0 4px; padding: 8px 14px; cursor: pointer; }
@media print { .no-print { display: none !important; } }
</style>
</head>
<body>
<form id="invoiceForm" method="get" class="no-print">
    <input type="hidden" name="id" value="<?php echo (int) $ris_id; ?>">
    <div class="controls">
        <label>Checked by<input type="text" name="checked_by" value="<?php echo $h($checked_by); ?>" placeholder="Leave blank"></label>
        <label>Witness<input type="text" name="witness_name" value="<?php echo $h($witness_name); ?>" placeholder="Leave blank"></label>
        <button type="submit">Apply</button>
        <button type="button" onclick="window.print()">Print Invoice</button>
        <button type="button" onclick="window.close()">Close</button>
    </div>
</form>

<main class="page">
    <header class="header">
        <div class="meta">General Form No.<br>(Revised September 2026)</div>
        <p>Republic of the Philippines</p>
        <p>Province of <?php echo $h($province); ?></p>
        <p>MUNICIPALITY OF <?php echo strtoupper($h($municipality)); ?></p>
        <div class="title">INVOICE AND RECEIPT OF ACCOUNTABLE FORMS</div>
        <div class="generated">Generated: <?php echo $h($generated_at); ?></div>
    </header>

    <div class="rule"></div>

    <table class="reference">
        <tr><td>Officer's Requisition No. <span class="line"><?php echo $h($form['ris_number'] ?? ''); ?></span></td><td class="right">Requisition for<br>Invoice of<br>Receipt for</td></tr>
        <tr><td>Issuing Officer's Invoice No. <span class="line"><?php echo $h($form['sai_number'] ?? ''); ?></span></td><td></td></tr>
        <tr><td>Receiving Officer's Receipt No. <span class="line"></span></td><td></td></tr>
    </table>

    <table class="office">
        <tr>
            <td><?php echo $h($office); ?><small>(Bureau or Office)</small></td>
            <td><?php echo $h($municipality); ?><small>(Municipality)</small></td>
            <td><?php echo $h($province); ?><small>(Province or City)</small></td>
            <td><?php echo $h($date($form['ris_date'] ?? null, 'F d, Y')); ?><small>(Date)</small></td>
        </tr>
    </table>

    <!-- Recipient block shown in the red rectangle of the reference image. -->
    <table class="recipient">
        <tr>
            <td class="recipient-name">
                To Mr./Ms.<br>
                <strong><?php echo $h($requested_by); ?></strong>
            </td>
            <td class="recipient-title">
                <?php echo $h($requested_designation ?: 'ACTING MUNICIPAL TREASURER'); ?>
            </td>
            <td class="recipient-office">
                Municipal Treasurer's Office<br>
                <small>(Bureau of Office)</small>
            </td>
            <td class="recipient-location">
                <?php echo $h($municipality); ?><br>
                <small>(Municipality)</small>
            </td>
        </tr>
    </table>

    <table class="forms">
        <thead><tr><th style="width:10%">Quantity</th><th style="width:22%">Designation of Forms</th><th style="width:26%">Denomination / Value of Forms</th><th style="width:12%">Serial Number From</th><th style="width:12%">Serial Number To</th><th style="width:18%">Remarks / Office</th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo $h($item['quantity_requested'] ?? 0); ?></td>
                <td class="left"><?php echo $h($item['unit'] ?? ''); ?></td>
                <td class="left"><?php echo $h($item['descriptions'] ?? ''); ?></td>
                <td><?php echo $h($item['stock_number'] ?? ''); ?></td>
                <td><?php echo $h($item['stock_number'] ?? ''); ?></td>
                <td class="left"><?php echo $h($item['remarks'] ?? $office); ?></td>
            </tr>
        <?php endforeach; ?>
        <?php for ($row = count($items); $row < 7; $row++): ?><tr class="blank"><td></td><td></td><td></td><td></td><td></td><td></td></tr><?php endfor; ?>
        </tbody>
    </table>

    <div class="certification">
        THIS IS TO CERTIFY THAT I HAVE THIS
        <span class="underline"><?php echo $h($date($form['ris_date'] ?? null, 'd')); ?></span>
        DAY OF <span class="underline"><?php echo $h($date($form['ris_date'] ?? null, 'F Y')); ?></span>,<br>
        RECEIVED THE ABOVE <span class="underline"><?php echo $h($total_quantity); ?></span>
        (No. of Pads) ACCOUNTABLE FORMS,<br>
        ALL ARE NUMBERED CONSECUTIVELY.<br><br>
        <strong><?php echo $h($received_by ?: '________________'); ?></strong><br>
        <?php echo $h($received_designation ?: 'Receiving Officer'); ?>
    </div>

    <table class="certification-table">
        <tr>
            <td>Checked by:<br><br><strong><?php echo $h($checked_by ?: '________________'); ?></strong></td>
            <td><strong><?php echo $h($approved_by ?: '________________'); ?></strong><br><?php echo $h($approved_designation ?: 'Acting Municipal Treasurer'); ?></td>
            <td class="total">TOTAL VALUE<br>₱ 0.00</td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>I hereby acknowledge receipt of the accountable forms above specified.<br><br><div class="signature-line"></div><span class="signature-name"><?php echo $h($witness_name ?: '________________'); ?></span><span class="signature-role">Witness</span></td>
            <td>Requisition filed and received in the presence of the undersigned.<br><br><div class="signature-line"></div><span class="signature-name"><?php echo $h($approved_by ?: '________________'); ?></span><span class="signature-role"><?php echo $h($approved_designation ?: 'Acting Municipal Treasurer'); ?></span></td>
        </tr>
    </table>
</main>
</body>
</html>
