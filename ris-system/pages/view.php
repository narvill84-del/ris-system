<?php
/**
 * View RIS Form Page
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$page_title = 'View RIS Form';
$dashboard_layout = true;

$ris_id = (int) ($_GET['id'] ?? 0);
if ($ris_id <= 0) {
    die('<div class="alert alert-danger">Invalid RIS ID</div>');
}

$form_stmt = $conn->prepare('SELECT * FROM ris_forms WHERE id = ?');
$form_stmt->bind_param('i', $ris_id);
$form_stmt->execute();
$form_result = $form_stmt->get_result();

if ($form_result->num_rows === 0) {
    die('<div class="alert alert-danger">Form not found</div>');
}

$form = $form_result->fetch_assoc();
$form_stmt->close();

$items_stmt = $conn->prepare(
    'SELECT * FROM ris_line_items WHERE ris_id = ? ORDER BY id ASC'
);
$items_stmt->bind_param('i', $ris_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

include '../includes/header.php';

$user_name = $_SESSION['user_name'] ?? 'Administrator';
$user_initial = strtoupper(substr($user_name, 0, 1));

$status = trim((string) ($form['status'] ?? 'Pending'));
$status_class = strtolower($status);
$status_class = preg_replace('/[^a-z0-9]+/', '-', $status_class);
$status_class = trim($status_class, '-');
?>

<style>
    :root {
        --background: #080808;
        --sidebar-background: #0d0d0d;
        --panel: #151515;
        --panel-hover: #1d1d1d;

        --gold: #d4af37;
        --gold-light: #f3d77a;
        --gold-dark: #9c7c19;

        --line: rgba(212, 175, 55, 0.2);
        --line-strong: rgba(212, 175, 55, 0.45);

        --text: #f5f1e6;
        --text-soft: #c8c1b0;
        --text-muted: #918a7a;

        --success: #79d895;
        --warning: #f1cb67;
        --danger: #f18b8b;
        --info: #8fc9e6;

        --sidebar-width: 270px;
        --content-max-width: 1350px;
        --radius: 16px;
        --shadow: 0 22px 45px rgba(0, 0, 0, 0.55);
    }

    * { box-sizing: border-box; }

    html, body {
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
        margin: 0;
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
    .view-card {
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

    .view-card {
        overflow: hidden;
        background: linear-gradient(145deg, rgba(25, 25, 25, 0.98), rgba(12, 12, 12, 0.98));
        border: 1px solid var(--line);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
    }

    .view-card-header {
        padding: 21px 24px;
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.12), transparent);
        border-bottom: 1px solid var(--line);
    }

    .view-card-header h2 {
        margin: 0;
        color: var(--gold-light);
        font-size: 1.4rem;
        font-weight: 700;
    }

    .view-card-body {
        padding: 24px;
    }

    .ris-document {
        padding: 22px;
        color: #111;
        background: #fff;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
    }

    .ris-header {
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 2px solid #000;
        text-align: center;
    }

    .ris-header img {
        width: 130px;
        height: auto;
        margin-bottom: 10px;
    }

    .ris-header h3,
    .ris-header p {
        margin: 4px 0;
    }

    .ris-header h3 {
        font-size: 1.4rem;
        letter-spacing: 0.04em;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
        padding: 14px;
        background: #f8f9fa;
        border: 1px solid #dfe3e8;
        border-radius: 8px;
    }

    .info-grid div {
        font-size: 0.97rem;
    }

    .purpose-box {
        margin: 18px 0;
        padding: 14px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fafafa;
    }

    .ris-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
        border: 1px solid #000;
    }

    .ris-table th,
    .ris-table td {
        border: 1px solid #000;
        padding: 9px;
        text-align: left;
        vertical-align: top;
    }

    .ris-table th {
        background: #f0f0f0;
        font-size: 0.82rem;
        text-transform: uppercase;
        text-align: center;
    }

    .signature-table {
        width: 100%;
        margin-top: 30px;
        border-collapse: collapse;
    }

    .signature-table td {
        width: 33.33%;
        padding: 10px 12px;
        text-align: center;
        vertical-align: top;
    }

    .signature-line {
        height: 62px;
        margin: 12px 0 8px;
        border-bottom: 2px solid #000;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 96px;
        padding: 0.5rem 0.75rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 800;
    }

    .badge-pending,
    .badge-pending-approval {
        color: var(--warning);
        background: rgba(228, 184, 76, 0.12);
        border: 1px solid rgba(228, 184, 76, 0.3);
    }

    .badge-approved,
    .badge-completed {
        color: var(--success);
        background: rgba(95, 207, 128, 0.1);
        border: 1px solid rgba(95, 207, 128, 0.25);
    }

    .badge-rejected {
        color: var(--danger);
        background: rgba(227, 107, 107, 0.1);
        border: 1px solid rgba(227, 107, 107, 0.25);
    }

    .badge-processing,
    .badge-in-progress {
        color: var(--info);
        background: rgba(125, 185, 216, 0.1);
        border: 1px solid rgba(125, 185, 216, 0.25);
    }

    .badge-archived {
        color: #b8b1a1;
        background: rgba(184, 177, 161, 0.1);
        border: 1px solid rgba(184, 177, 161, 0.2);
    }

    .view-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 22px;
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
        text-decoration: none;
    }

    .btn-primary {
        color: #17120a;
        background: linear-gradient(135deg, var(--gold-light), var(--gold-dark));
        border-color: var(--gold-light);
    }

    .btn-warning {
        color: #17120a;
        background: linear-gradient(135deg, #f1cb67, #b8891c);
        border-color: #f1cb67;
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
            max-width: 100%;
            margin-left: 0;
            padding: 76px 14px 24px;
        }

        .mobile-menu-button {
            display: block;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .view-actions {
            flex-direction: column;
        }

        .view-actions .btn {
            width: 100%;
        }
    }

    @media print {
        .sidebar,
        .mobile-menu-button,
        .sidebar-overlay,
        .footer,
        .view-actions {
            display: none !important;
        }

        .main-content {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .view-card {
            box-shadow: none;
            border: none;
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

            <a href="index.php" class="sidebar-link active">
                <span class="sidebar-icon">◷</span>
                <span>View Form</span>
            </a>

            <a href="javascript:void(0);" class="sidebar-link" onclick="if (typeof exportFormListToCSV === 'function') { exportFormListToCSV(); }">
                <span class="sidebar-icon">⇩</span>
                <span>Export Forms</span>
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
        <div class="page-header">
            <h1>View RIS Form</h1>
            <p>Detailed record and printable form</p>
        </div>

        <section class="view-card">
            <div class="view-card-header">
                <h2>Requisition and Issue Slip (RIS)</h2>
            </div>

            <div class="view-card-body">
                <div class="ris-document">
                    <div class="ris-header">
                        <img src="../uploads/mto1.png" alt="MTO Image">
                        <p>Republic of the Philippines</p>
                        <p>Province of Zamboanga del Sur</p>
                        <p><strong>MUNICIPALITY OF MARGOSATUBIG</strong></p>
                        <p>Margosatubig, Zamboanga del Sur</p>
                        <h3>REQUISITION AND ISSUE SLIP FORM</h3>
                    </div>

                    <div class="info-grid">
                        <div>
                            <strong>Office:</strong>
                            <?php echo htmlspecialchars($form['office_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div>
                            <strong>Responsibility Center:</strong>
                            <?php echo htmlspecialchars($form['responsibility_center_code'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div>
                            <strong>RIS No.:</strong>
                            <?php echo htmlspecialchars($form['ris_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div>
                            <strong>Date:</strong>
                            <?php echo !empty($form['ris_date']) ? date(DISPLAY_DATE_FORMAT, strtotime($form['ris_date'])) : ''; ?>
                        </div>
                        <div>
                            <strong>SAI No.:</strong>
                            <?php echo htmlspecialchars($form['sai_number'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div>
                            <strong>Status:</strong>
                            <span class="badge badge-<?php echo htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>

                    <div class="purpose-box">
                        <strong>Purpose:</strong><br>
                        <?php echo nl2br(htmlspecialchars($form['purpose'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
                    </div>

                    <h3>REQUISITION ITEMS</h3>
                    <table class="ris-table">
                        <thead>
                            <tr>
                                <th>Stock No.</th>
                                <th>Unit</th>
                                <th>Description</th>
                                <th>Quantity Requested</th>
                                <th>Quantity Received</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($items_result->num_rows > 0): ?>
                                <?php while ($item = $items_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['stock_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($item['unit'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($item['descriptions'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($item['quantity_requested'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($item['quantity_received'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($item['remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 1rem;">No items found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <table class="signature-table">
                        <tr>
                            <?php foreach ([
                                ['Requested by', 'requested_by', 'requested_by_designation', 'requested_by_date'],
                                ['Approved by', 'approved_by', 'approved_by_designation', 'approved_by_date'],
                                ['Received by', 'received_by', 'received_by_designation', 'received_by_date']
                            ] as [$label, $name_key, $designation_key, $date_key]): ?>
                                <td>
                                    <strong><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <div class="signature-line"></div>
                                    <?php echo htmlspecialchars($form[$name_key] ?? '', ENT_QUOTES, 'UTF-8'); ?><br>
                                    <small><?php echo htmlspecialchars($form[$designation_key] ?? '', ENT_QUOTES, 'UTF-8'); ?></small><br>
                                    <small><?php echo !empty($form[$date_key]) ? date(DISPLAY_DATE_FORMAT, strtotime($form[$date_key])) : ''; ?></small>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </table>
                </div>

                <div class="view-actions">
                    <a href="edit.php?id=<?php echo (int) $ris_id; ?>" class="btn btn-primary">Edit Form</a>
                    <a href="../print/print-form.php?id=<?php echo (int) $ris_id; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-warning">Print</a>
                    <button type="button" class="btn btn-danger" onclick="if (typeof deleteRISForm === 'function') { deleteRISForm(<?php echo (int) $ris_id; ?>); } else { if (confirm('Delete this form?')) { window.location.href = 'index.php'; } }">Delete</button>
                    <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </section>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

<script>
    (function () {
        const sidebar = document.getElementById('sidebar');
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            if (!sidebar) {
                return;
            }

            const isOpen = sidebar.classList.toggle('open');

            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('visible', isOpen);
            }

            if (mobileMenuButton) {
                mobileMenuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                mobileMenuButton.textContent = isOpen ? '×' : '☰';
            }
        }

        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', toggleSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }
    })();
</script>
