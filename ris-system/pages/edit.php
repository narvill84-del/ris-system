<?php
require_once '../config/database.php';

$page_title = 'Edit RIS Form';
$dashboard_layout = true;

$ris_id = (int) ($_GET['id'] ?? 0);
if ($ris_id < 1) {
    exit('Invalid RIS ID.');
}

$stmt = $conn->prepare('SELECT * FROM ris_forms WHERE id = ?');
$stmt->bind_param('i', $ris_id);
$stmt->execute();
$form_result = $stmt->get_result();
$form = $form_result->fetch_assoc();
$stmt->close();

if (!$form) {
    exit('RIS form not found.');
}

$item_stmt = $conn->prepare(
    'SELECT stock_number, unit, descriptions, quantity_requested, quantity_received, remarks
     FROM ris_line_items
     WHERE ris_id = ?
     ORDER BY id ASC'
);
$item_stmt->bind_param('i', $ris_id);
$item_stmt->execute();
$items = $item_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$item_stmt->close();

if (!$items) {
    $items = [[
        'stock_number' => '',
        'unit' => '',
        'descriptions' => '',
        'quantity_requested' => '',
        'quantity_received' => 0,
        'remarks' => ''
    ]];
}

include '../includes/header.php';

$user_name = $_SESSION['user_name'] ?? 'Administrator';
$user_initial = strtoupper(substr($user_name, 0, 1));

function edit_value(array $form, string $key): string
{
    return htmlspecialchars((string) ($form[$key] ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<style>
    :root {
        --background: #080808;
        --panel: #151515;
        --panel-alt: #1a1a1a;
        --sidebar: #0d0d0d;
        --gold: #d4af37;
        --gold-light: #f3d77a;
        --gold-dark: #9c7c19;
        --line: rgba(212, 175, 55, 0.22);
        --line-strong: rgba(212, 175, 55, 0.45);
        --text: #f5f1e6;
        --text-soft: #c8c1b0;
        --text-muted: #918a7a;
        --danger: #f18b8b;
        --success: #79d895;
        --info: #8fc9e6;
        --sidebar-width: 270px;
        --content-max-width: 1350px;
        --radius: 16px;
        --shadow: 0 22px 45px rgba(0, 0, 0, 0.55);
    }

    * { box-sizing: border-box; }

    html, body {
        margin: 0;
        min-height: 100%;
        background:
            radial-gradient(circle at top right, rgba(212, 175, 55, 0.08), transparent 30%),
            radial-gradient(circle at bottom left, rgba(212, 175, 55, 0.04), transparent 28%),
            var(--background);
        color: var(--text);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        overflow-x: hidden;
    }

    a {
        text-decoration: none;
    }

    .dashboard-layout {
        display: flex;
        min-height: 100vh;
    }

    .main-content {
        width: calc(100% - var(--sidebar-width));
        max-width: 1600px;
        min-height: 100vh;
        margin-left: var(--sidebar-width);
        padding: 32px;
    }

    .page-header,
    .edit-card {
        width: 100%;
        max-width: var(--content-max-width);
        margin: 0 auto;
    }

    .sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        z-index: 1000;
        width: var(--sidebar-width);
        display: flex;
        flex-direction: column;
        background: linear-gradient(180deg, #090909 0%, #11100d 55%, #090909 100%);
        border-right: 1px solid var(--line);
        box-shadow: 14px 0 35px rgba(0, 0, 0, 0.6);
        transition: transform 0.28s ease;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 92px;
        padding: 0 22px;
        border-bottom: 1px solid var(--line);
    }

    .sidebar-brand-icon {
        display: grid;
        place-items: center;
        width: 44px;
        height: 44px;
        border-radius: 13px;
        background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
        color: #17120a;
        font-size: 1.15rem;
        font-weight: 900;
        box-shadow: 0 12px 28px rgba(212, 175, 55, 0.25);
    }

    .sidebar-brand-text {
        color: var(--gold-light);
        font-size: 1.04rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .sidebar-brand-text small {
        display: block;
        margin-top: 3px;
        color: var(--text-muted);
        font-size: 0.7rem;
        font-weight: 500;
    }

    .sidebar-nav {
        flex: 1;
        padding: 22px 14px 18px;
        overflow-y: auto;
    }

    .sidebar-section-title {
        margin: 0 10px 12px;
        color: var(--gold-dark);
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 14px;
        margin-bottom: 7px;
        border: 1px solid transparent;
        border-radius: 11px;
        color: var(--text-soft);
        font-size: 0.94rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .sidebar-link:hover {
        color: var(--gold-light);
        background: rgba(212, 175, 55, 0.08);
        border-color: var(--line);
        transform: translateX(3px);
    }

    .sidebar-link.active {
        color: var(--gold-light);
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.2), rgba(212, 175, 55, 0.06));
        border-color: var(--line-strong);
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.25);
    }

    .sidebar-icon {
        width: 22px;
        color: var(--gold);
        font-size: 1.05rem;
        text-align: center;
    }

    .sidebar-footer {
        padding: 16px 14px;
        border-top: 1px solid var(--line);
    }

    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 12px;
        border: 1px solid var(--line);
        border-radius: 13px;
        background: rgba(212, 175, 55, 0.04);
    }

    .sidebar-avatar {
        display: grid;
        place-items: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
        color: #17120a;
        font-size: 0.78rem;
        font-weight: 900;
    }

    .sidebar-user strong {
        display: block;
        color: var(--gold-light);
        font-size: 0.8rem;
    }

    .sidebar-user small {
        color: var(--text-muted);
        font-size: 0.7rem;
    }

    .page-header {
        margin-bottom: 22px;
    }

    .page-header h1 {
        margin: 0;
        color: var(--gold-light);
        font-size: 2rem;
        font-weight: 800;
    }

    .page-header p {
        margin: 8px 0 0;
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    .edit-card {
        overflow: hidden;
        background: linear-gradient(145deg, rgba(25, 25, 25, 0.98), rgba(12, 12, 12, 0.98));
        border: 1px solid var(--line);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
    }

    .edit-card-header {
        padding: 21px 24px;
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.12), transparent);
        border-bottom: 1px solid var(--line);
    }

    .edit-card-header h2 {
        margin: 0;
        color: var(--gold-light);
        font-size: 1.4rem;
        font-weight: 700;
    }

    .edit-card-body {
        padding: 24px;
        /*background: linear-gradient(
            135deg,
            #f8e7b7 0%,
            #e7c870 18%,
            #d4a937 40%,
            #b8871d 68%,
            #f3df9b 100%
        ); */
        background: transparent;
        border-top: 1px solid rgba(72, 49, 8, 0.25);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.45);
    }

    .form-shell {
        /* background: rgba(255, 255, 255, 0.22); */
        background: transparent;
        color: var(--luxury-text-hard);
        border: 1px solid rgba(75, 54, 12, 0.28);
        border-radius: 12px;
        padding: 20px;
        backdrop-filter: blur(2px);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.25);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }

    .field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .field label {
        color: var(--gold-light);
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .field input,
    .field textarea,
    .field select {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        color: var(--luxury-text);
        background: #171717;
        border: 1px solid rgba(212, 175, 55, 0.25);
        border-radius: 8px;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .field textarea {
        min-height: 120px;
        resize: vertical;
    }

    .field input[readonly] {
        color: var(--gold-light);
        background: rgba(212, 175, 55, 0.08);
    }

    .items-wrap {
        margin-top: 18px;
    }

    .items-title {
        margin: 0 0 12px;
        color: var(--gold-light);
        font-size: 1.1rem;
        font-weight: 800;
    }

    .table-wrap {
        overflow-x: auto;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 780px;
        border: 1px solid #000;

    }

    .items-table th,
    .items-table td {
        
        padding: 10px;
        color: var(--luxury-text-soft);
        background: #11100d;
        border-bottom: 1px solid rgba(212, 175, 55, 0.12);

    }

    .items-table thead th {
        color: var(--gold-light);
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.18), rgba(212, 175, 55, 0.05));
    }

    .items-table input {
        width: 100%;
        padding: 8px 10px;

        color: var(--luxury-text);
        background: #171717;
        border: 1px solid rgba(212, 175, 55, 0.25);
        border-radius: 8px;

    }

    .actions-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 16px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 10px 18px;
        border: 1px solid transparent;
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        color: #17120a;
        background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
        border-color: var(--gold-light);
    }

    .btn-secondary {
        color: var(--gold-light);
        background: rgba(212, 175, 55, 0.05);
        border-color: var(--line);
    }

    .btn-danger {
        color: #fff;
        background: #9b2c2c;
        border-color: #9b2c2c;
    }

    .signature-block {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-top: 24px;
    }

    .mobile-menu-button {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1100;
        width: 44px;
        height: 44px;
        color: var(--gold-light);
        background: #151515;
        border: 1px solid var(--line);
        border-radius: 12px;
        cursor: pointer;
        font-size: 1.15rem;
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.4);
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 999;
        background: rgba(0, 0, 0, 0.72);
    }

    .sidebar-overlay.visible {
        display: block;
    }

    @media (max-width: 992px) {
        .main-content {
            padding: 24px 18px;
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .main-content {
            width: 100%;
            margin-left: 0;
            padding: 76px 14px 24px;
        }

        .mobile-menu-button {
            display: block;
        }

        .form-grid,
        .signature-block {
            grid-template-columns: 1fr;
        }
    }
</style>

<button
    type="button"
    class="mobile-menu-button"
    id="mobileMenuButton"
    aria-label="Open navigation menu"
    aria-expanded="false">
    ☰
</button>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="dashboard-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">R</div>
            <div class="sidebar-brand-text">
                RIS System
                <small><?php echo htmlspecialchars(LGU_NAME, ENT_QUOTES, 'UTF-8'); ?></small>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Dashboard navigation">
            <p class="sidebar-section-title">Main Menu</p>

            <a href="index.php" class="sidebar-link">
                <span class="sidebar-icon">▦</span>
                <span>Dashboard</span>
            </a>

            <a href="create.php" class="sidebar-link">
                <span class="sidebar-icon">＋</span>
                <span>Create RIS Form</span>
            </a>

            <a href="report.php" class="sidebar-link">
                <span class="sidebar-icon">▤</span>
                <span>Reports</span>
            </a>

            <p class="sidebar-section-title" style="margin-top: 28px;">Management</p>

            <a href="view.php?id=<?php echo (int) $ris_id; ?>" class="sidebar-link">
                <span class="sidebar-icon">◷</span>
                <span>View Form</span>
            </a>

            <a href="index.php" class="sidebar-link active">
                <span class="sidebar-icon">✎</span>
                <span>Edit Form</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    <?php echo htmlspecialchars($user_initial, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <small>RIS Management</small>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">

        <section class="edit-card">
            <div class="edit-card-header">
                <h2>Edit Requisition and Issue Slip Form (RIS)</h2>
            </div>

            <div class="edit-card-body">
                <form id="ris-form" method="post" action="update.php">
                    <input type="hidden" name="id" value="<?php echo (int) $ris_id; ?>">
                    <input type="hidden" name="line_items" id="line_items" value="">

                    <div class="form-shell">
                        <div class="form-grid">
                            <div class="field">
                                <label for="office_name">Office</label>
                                <input
                                    id="office_name"
                                    name="office_name"
                                    type="text"
                                    required
                                    value="<?php echo edit_value($form, 'office_name'); ?>">
                            </div>

                            <div class="field">
                                <label for="responsibility_center_code">Responsibility Center</label>
                                <input
                                    id="responsibility_center_code"
                                    name="responsibility_center_code"
                                    type="text"
                                    value="<?php echo edit_value($form, 'responsibility_center_code'); ?>">
                            </div>

                            <div class="field">
                                <label for="ris_number">RIS No.</label>
                                <input
                                    id="ris_number"
                                    type="text"
                                    readonly
                                    value="<?php echo edit_value($form, 'ris_number'); ?>">
                            </div>

                            <div class="field">
                                <label for="ris_date">RIS Date</label>
                                <input
                                    id="ris_date"
                                    name="ris_date"
                                    type="date"
                                    required
                                    value="<?php echo htmlspecialchars($form['ris_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="field">
                                <label for="sai_number">SAI No.</label>
                                <input
                                    id="sai_number"
                                    name="sai_number"
                                    type="text"
                                    value="<?php echo edit_value($form, 'sai_number'); ?>">
                            </div>

                            <div class="field">
                                <label for="sai_date">SAI Date</label>
                                <input
                                    id="sai_date"
                                    name="sai_date"
                                    type="date"
                                    value="<?php echo htmlspecialchars($form['sai_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="field">
                            <label for="purpose">Purpose</label>
                            <textarea id="purpose" name="purpose" required><?php echo edit_value($form, 'purpose'); ?></textarea>
                        </div>

                        <div class="items-wrap">
                            <h3 class="items-title">Requisition Items</h3>

                            <div class="table-wrap">
                                <table class="items-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 12%;">Stock No.</th>
                                            <th style="width: 10%;">Unit</th>
                                            <th style="width: 28%;">Description</th>
                                            <th style="width: 12%;">Qty Requested</th>
                                            <th style="width: 12%;">Qty Received</th>
                                            <th style="width: 18%;">Remarks</th>
                                            <th style="width: 8%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="line-items-body">
                                        <?php foreach ($items as $index => $item): ?>
                                            <tr>
                                                <td><input type="text" name="stock_number_<?php echo $index; ?>" value="<?php echo htmlspecialchars($item['stock_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
                                                <td><input type="text" name="unit_<?php echo $index; ?>" value="<?php echo htmlspecialchars($item['unit'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
                                                <td><input type="text" name="description_<?php echo $index; ?>" value="<?php echo htmlspecialchars($item['descriptions'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
                                                <td><input type="number" min="1" name="quantity_requested_<?php echo $index; ?>" value="<?php echo htmlspecialchars((string) ($item['quantity_requested'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>"></td>
                                                <td><input type="number" min="0" name="quantity_received_<?php echo $index; ?>" value="<?php echo htmlspecialchars((string) ($item['quantity_received'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>"></td>
                                                <td><input type="text" name="remarks_<?php echo $index; ?>" value="<?php echo htmlspecialchars($item['remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"></td>
                                                <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="actions-row">
                                <button type="button" class="btn btn-secondary" onclick="addRow()">+ Add Item</button>
                            </div>
                        </div>

                        <div class="signature-block">
                            <div class="field">
                                <label for="requested_by">Requested by</label>
                                <input id="requested_by" name="requested_by" type="text" value="<?php echo edit_value($form, 'requested_by'); ?>">
                            </div>

                            <div class="field">
                                <label for="approved_by">Approved by</label>
                                <input id="approved_by" name="approved_by" type="text" value="<?php echo edit_value($form, 'approved_by'); ?>">
                            </div>

                            <div class="field">
                                <label for="received_by">Received by</label>
                                <input id="received_by" name="received_by" type="text" value="<?php echo edit_value($form, 'received_by'); ?>">
                            </div>
                        </div>

                        <div class="actions-row">
                            <button type="submit" class="btn btn-primary">Update Form</button>
                            <a href="view.php?id=<?php echo (int) $ris_id; ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<script>
    (function () {
        const sidebar = document.getElementById('sidebar');
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            if (!sidebar) return;

            const isOpen = sidebar.classList.toggle('open');
            overlay?.classList.toggle('visible', isOpen);

            if (mobileMenuButton) {
                mobileMenuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                mobileMenuButton.textContent = isOpen ? '×' : '☰';
            }
        }

        mobileMenuButton?.addEventListener('click', toggleSidebar);
        overlay?.addEventListener('click', toggleSidebar);
    })();

    function addRow() {
        const tableBody = document.getElementById('line-items-body');
        const rows = tableBody.querySelectorAll('tr');

        const rowIndex = rows.length;

        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td><input type="text" name="stock_number_${rowIndex}" value=""></td>
            <td><input type="text" name="unit_${rowIndex}" value=""></td>
            <td><input type="text" name="description_${rowIndex}" value=""></td>
            <td><input type="number" min="1" name="quantity_requested_${rowIndex}" value=""></td>
            <td><input type="number" min="0" name="quantity_received_${rowIndex}" value=""></td>
            <td><input type="text" name="remarks_${rowIndex}" value=""></td>
            <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>
        `;

        tableBody.appendChild(tr);
    }

    function removeRow(button) {
        const row = button.closest('tr');
        if (!row) return;

        const rows = document.querySelectorAll('#line-items-body tr');
        if (rows.length > 1) {
            row.remove();
        } else {
            alert('At least one item row is required.');
        }
    }

    document.getElementById('ris-form')?.addEventListener('submit', function () {
        const payload = [];
        const rows = document.querySelectorAll('#line-items-body tr');

        rows.forEach((row, index) => {
            const inputs = row.querySelectorAll('input');
            const data = {
                stock_number: inputs[0]?.value || '',
                unit: inputs[1]?.value || '',
                descriptions: inputs[2]?.value || '',
                quantity_requested: inputs[3]?.value || '',
                quantity_received: inputs[4]?.value || '',
                remarks: inputs[5]?.value || ''
            };

            if (
                data.stock_number ||
                data.unit ||
                data.descriptions ||
                data.quantity_requested ||
                data.quantity_received ||
                data.remarks
            ) {
                payload.push(data);
            }
        });

        document.getElementById('line_items').value = JSON.stringify(payload);
    });
</script>

<?php include '../includes/footer.php'; ?>