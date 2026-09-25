<?php
/**
 * View RIS Form Page
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$ris_id = (int) ($_GET['id'] ?? 0);

if ($ris_id <= 0) {
    die('Invalid RIS ID');
}

$form_stmt = $conn->prepare('SELECT * FROM ris_forms WHERE id = ?');
$form_stmt->bind_param('i', $ris_id);
$form_stmt->execute();
$form_result = $form_stmt->get_result();

if ($form_result->num_rows === 0) {
    die('Form not found');
}

$form = $form_result->fetch_assoc();
$form_stmt->close();

$items_stmt = $conn->prepare(
    'SELECT * FROM ris_line_items WHERE ris_id = ? ORDER BY id ASC'
);
$items_stmt->bind_param('i', $ris_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$page_title = 'View RIS Form';
$dashboard_layout = true;
include '../includes/header.php';

$user_name = $_SESSION['user_name'] ?? 'Administrator';
$user_initial = strtoupper(substr($user_name, 0, 1));
$status = trim((string) ($form['status'] ?? 'Pending'));
$status_class = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $status));
$status_class = trim($status_class, '-');
?>

<style>
    .view-page {
        width: 100%;
        max-width: 1350px;
        margin: 0 auto;
    }

    .view-card {
        overflow: hidden;
        background: linear-gradient(145deg, rgba(25, 25, 25, .98), rgba(12, 12, 12, .98));
        border: 1px solid rgba(212, 175, 55, .2);
        border-radius: 16px;
        box-shadow: 0 22px 45px rgba(0, 0, 0, .55);
    }

    .view-card-header {
        padding: 22px 26px;
        background: linear-gradient(90deg, rgba(212, 175, 55, .14), transparent);
        border-bottom: 1px solid rgba(212, 175, 55, .2);
    }

    .view-card-header h1 {
        margin: 0;
        color: #f3d77a;
        font-size: 1.5rem;
    }

    .view-card-body {
        padding: 26px;
    }

    .ris-header {
        margin-bottom: 22px;
        padding-bottom: 14px;
        color: #111;
        background: #fff;
        border-bottom: 2px solid #000;
        text-align: center;
    }

    .ris-header h2,
    .ris-header p {
        margin: 4px 0;
    }

    .ris-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
        padding: 16px;
        color: #111;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 8px;
    }

    .view-document {
        padding: 20px;
        background: #fff;
        color: #111;
        border-radius: 10px;
    }

    .view-document table {
        width: 100%;
        border-collapse: collapse;
    }

    .view-document th,
    .view-document td {
        padding: 9px;
        border: 1px solid #000;
        vertical-align: top;
    }

    .view-document th {
        background: #f0f0f0;
        text-align: center;
    }

    .purpose-box {
        margin: 20px 0;
        padding: 12px;
        border: 1px solid #aaa;
    }

    .signature-table {
        margin-top: 30px;
    }

    .signature-table td {
        width: 33.33%;
        text-align: center;
    }

    .signature-line {
        height: 62px;
        margin: 10px 0;
        border-bottom: 1px solid #000;
    }

    .view-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 24px;
    }

    .view-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 10px 18px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .view-actions .btn-primary {
        color: #17120a;
        background: linear-gradient(135deg, #f3d77a, #9c7c19);
    }

    .view-actions .btn-warning {
        color: #17120a;
        background: linear-gradient(135deg, #f1cb67, #b8891c);
    }

    .view-actions .btn-danger {
        color: #fff;
        background: #9b2c2c;
    }

    .view-actions .btn-secondary {
        color: #f3d77a;
        background: rgba(212, 175, 55, .08);
        border-color: rgba(212, 175, 55, .3);
    }

    @media (max-width: 768px) {
        body.dashboard-page .main-content {
            padding: 76px 14px 35px;
        }

        .view-card-body {
            padding: 14px;
        }

        .ris-info-grid {
            grid-template-columns: 1fr;
        }

        .view-document {
            padding: 10px;
            overflow-x: auto;
        }

        .view-document table {
            min-width: 760px;
        }

        .view-actions {
            flex-direction: column;
        }

        .view-actions .btn {
            width: 100%;
        }
    }

    @media print {
        .view-actions,
        .sidebar,
        .mobile-menu-button,
        .sidebar-overlay,
        .footer {
            display: none !important;
        }

        body.dashboard-page .main-content {
            width: 100%;
            margin: 0;
            padding: 0;
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
                <span>All Forms</span>
            </a>

            <a href="report.php" class="sidebar-link">
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
        <div class="view-page">
            <section class="view-card">
                <header class="view-card-header">
                    <h1>View Requisition and Issue Slip</h1>
                </header>

                <div class="view-card-body">
                    <div class="view-document">
                        <div class="ris-header">
                            <img src="../uploads/mto1.png" alt="MTO Image" style="width: 120px; height: auto;">
                            <p>Republic of the Philippines</p>
                            <p>Province of Zamboanga del Sur</p>
                            <p><strong>MUNICIPALITY OF MARGOSATUBIG</strong></p>
                            <p>Margosatubig, Zamboanga del Sur</p>
                            <h2>REQUISITION AND ISSUE SLIP FORM</h2>
                        </div>

                        <div class="ris-info-grid">
                            <div><strong>Office:</strong> <?php echo htmlspecialchars($form['office_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                            <div><strong>Responsibility Center:</strong> <?php echo htmlspecialchars($form['responsibility_center_code'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                            <div><strong>RIS No.:</strong> <?php echo htmlspecialchars($form['ris_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                            <div><strong>Date:</strong> <?php echo !empty($form['ris_date']) ? date(DISPLAY_DATE_FORMAT, strtotime($form['ris_date'])) : ''; ?></div>
                            <div><strong>SAI No.:</strong> <?php echo htmlspecialchars($form['sai_number'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                            <div><strong>Status:</strong> <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="purpose-box">
                            <strong>Purpose:</strong><br>
                            <?php echo nl2br(htmlspecialchars($form['purpose'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
                        </div>

                        <h3>REQUISITION ITEMS</h3>
                        <table>
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
                                    <tr><td colspan="6" style="text-align:center;">No items found</td></tr>
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
                                        <strong><?php echo $label; ?></strong>
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
                        <a href="edit.php?id=<?php echo $ris_id; ?>" class="btn btn-primary">Edit Form</a>
                        <a href="../print/print-form.php?id=<?php echo $ris_id; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-warning">Print</a>
                        <button type="button" onclick="deleteRISForm(<?php echo $ris_id; ?>)" class="btn btn-danger">Delete</button>
                        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

<script>
(function () {
    const sidebar = document.getElementById('sidebar');
    const menuButton = document.getElementById('mobileMenuButton');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        if (!sidebar) return;

        const open = sidebar.classList.toggle('open');
        overlay?.classList.toggle('visible', open);
        menuButton?.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (menuButton) menuButton.textContent = open ? '×' : '☰';
    }

    menuButton?.addEventListener('click', toggleSidebar);
    overlay?.addEventListener('click', toggleSidebar);
})();
</script>
