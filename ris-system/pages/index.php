<?php
/**
 * Index/Dashboard Page
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$page_title = 'Dashboard';
$dashboard_layout = true;

include '../includes/header.php';

/*
 * Get the latest RIS forms and their line-item descriptions.
 */
$query = "
    SELECT
        rf.id,
        rf.ris_number,
        rf.ris_date,
        rf.office_name,
        rf.requested_by,
        rf.purpose,
        rf.status,
        rf.created_at,
        GROUP_CONCAT(
            rli.descriptions
            SEPARATOR ', '
        ) AS line_descriptions
    FROM ris_forms rf
    LEFT JOIN ris_line_items rli
        ON rf.id = rli.ris_id
    GROUP BY rf.id
    ORDER BY rf.created_at DESC
    LIMIT 20
";

$result = $conn->query($query);
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

    * {
        box-sizing: border-box;
    }

    html,
    body {
        min-height: 100%;
        background:
            radial-gradient(
                circle at top right,
                rgba(212, 175, 55, 0.08),
                transparent 30%
            ),
            radial-gradient(
                circle at bottom left,
                rgba(212, 175, 55, 0.04),
                transparent 28%
            ),
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

    /*
     * Layout
     */
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

    .main-content .page-header,
    .main-content .dashboard-card {
        width: 100%;
        max-width: var(--content-max-width);
        margin-right: auto;
        margin-left: auto;
    }

    /*
     * Sidebar
     */
    .sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        z-index: 1000;
        width: var(--sidebar-width);
        display: flex;
        flex-direction: column;
        background:
            linear-gradient(
                180deg,
                #090909 0%,
                #11100d 55%,
                #090909 100%
            );
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
        background: linear-gradient(
            135deg,
            var(--gold-light),
            var(--gold-dark)
        );
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
        background:
            linear-gradient(
                135deg,
                rgba(212, 175, 55, 0.2),
                rgba(212, 175, 55, 0.06)
            );
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
        background: linear-gradient(
            135deg,
            var(--gold-light),
            var(--gold-dark)
        );
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

    /*
     * Page header
     */
    .page-header {
        margin-bottom: 24px;
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

    /*
     * Dashboard card
     */
    .dashboard-card {
        overflow: hidden;
        background:
            linear-gradient(
                145deg,
                rgba(25, 25, 25, 0.98),
                rgba(12, 12, 12, 0.98)
            );
        border: 1px solid var(--line);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
    }

    .card-header {
        padding: 21px 24px;
        background:
            linear-gradient(
                90deg,
                rgba(212, 175, 55, 0.12),
                transparent
            );
        border-bottom: 1px solid var(--line);
    }

    .card-header h2 {
        margin: 0;
        color: var(--gold-light);
        font-size: 1.38rem;
        font-weight: 700;
    }

    .card-body {
        padding: 24px;
    }

    /*
     * Buttons
     */
    .btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 22px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.8rem 1.15rem;
        border: 1px solid transparent;
        border-radius: 12px;
        cursor: pointer;
        font-size: 0.92rem;
        font-weight: 700;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }

    .btn-primary {
        color: #17120a;
        background: linear-gradient(
            135deg,
            var(--gold-light),
            var(--gold-dark)
        );
        border-color: var(--gold-light);
        box-shadow: 0 12px 24px rgba(212, 175, 55, 0.18);
    }

    .btn-primary:hover {
        color: #0c0c0c;
        background: linear-gradient(
            135deg,
            #ffe99b,
            var(--gold)
        );
    }

    .btn-secondary {
        color: var(--gold-light);
        background: rgba(212, 175, 55, 0.05);
        border-color: var(--line);
    }

    .btn-secondary:hover {
        background: rgba(212, 175, 55, 0.13);
        border-color: var(--line-strong);
    }

    /*
     * Table
     */
    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        border: 1px solid var(--report-line);
        border-radius: 12px;
    }


    .table {
        width: 100%;
        border-collapse: collapse;
        /*background:
            linear-gradient(
                180deg,
                #090909 0%,
                #11100d 55%,
                #090909 100%
            ); */
        background: transparent;
    }


    .table thead th {
        padding: 14px 12px;
        color: var(--report-gold-light);
        background:
            linear-gradient(
                90deg,
                rgba(212, 175, 55, 0.16),
                rgba(212, 175, 55, 0.04)
            );
        border-bottom: 1px solid var(--report-line-strong);
        color: var(--gold);
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.09em;
        text-align: center;
        text-transform: uppercase;
        white-space: nowrap;

    }

    .table tbody td {
        padding: 14px 12px;
        color: var(--report-text-soft);
        border-bottom: 1px solid rgba(212, 175, 55, 0.12);
        font-size: 0.9rem;
        vertical-align: middle;
        text-align: center;
    }

    .table tbody tr {
        background:
            linear-gradient(
                180deg,
                rgba(9, 9, 9, 0.98),
                rgba(17, 16, 13, 0.98)
            );
        transition: background 0.2s ease, box-shadow 0.2s ease;
    }

    .table tbody tr:hover {
        background:
            linear-gradient(
                90deg,
                rgba(212, 175, 55, 0.13),
                rgba(212, 175, 55, 0.04)
            );
        box-shadow: inset 4px 0 0 var(--report-gold);
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /*
     * Status badges
     */
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

    /*
     * Actions dropdown
     */
    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-btn {
        padding: 0.7rem 1rem;
        color: white;
        background: linear-gradient(135deg, #171717, #292929);
        border: 1px solid rgba(212, 175, 55, 0.3);
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.86rem;
        font-weight: 700;
    }

    .dropdown-btn:hover {
        color: var(--gold-light);
        border-color: var(--line-strong);
    }

    .dropdown-content {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        z-index: 20;
        display: none;
        min-width: 190px;
        padding: 8px;
        background: #171717;
        border: 1px solid var(--line-strong);
        border-radius: 12px;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.55);
    }

    .dropdown:hover .dropdown-content,
    .dropdown:focus-within .dropdown-content {
        display: block;
    }

    .dropdown-content a {
        display: block;
        padding: 10px 12px;
        color: var(--text-soft);
        border-radius: 8px;
        font-size: 0.88rem;
    }

    .dropdown-content a:hover {
        color: var(--gold-light);
        background: rgba(212, 175, 55, 0.08);
    }

    .dropdown-content .delete-link {
        color: var(--danger);
    }

    /*
     * Empty state
     */
    .alert {
        padding: 16px 18px;
        color: #bae6fd;
        background: rgba(56, 189, 248, 0.08);
        border: 1px solid rgba(56, 189, 248, 0.2);
        border-radius: 14px;
        font-size: 0.95rem;
    }

    .alert a {
        color: #7dd3fc;
        font-weight: 700;
    }

    /*
     * Mobile menu
     */
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

        .main-content .page-header,
        .main-content .dashboard-card {
            max-width: 100%;
        }

        .mobile-menu-button {
            display: block;
        }

        .btn-group {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
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
        color: #f1cb67;
        background: rgba(228, 184, 76, 0.12);
        border: 1px solid rgba(228, 184, 76, 0.3);
    }

    .badge-approved,
    .badge-completed {
        color: #79d895;
        background: rgba(95, 207, 128, 0.1);
        border: 1px solid rgba(95, 207, 128, 0.25);
    }

    .badge-rejected {
        color: #f18b8b;
        background: rgba(227, 107, 107, 0.1);
        border: 1px solid rgba(227, 107, 107, 0.25);
    }

    .badge-processing,
    .badge-in-progress {
        color: #8fc9e6;
        background: rgba(125, 185, 216, 0.1);
        border: 1px solid rgba(125, 185, 216, 0.25);
    }

    .badge-archived {
        color: #b8b1a1;
        background: rgba(184, 177, 161, 0.1);
        border: 1px solid rgba(184, 177, 161, 0.2);
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
                <small>
                    <?php echo htmlspecialchars(
                        LGU_NAME,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </small>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Dashboard navigation">
            <p class="sidebar-section-title">Main Menu</p>

            <a href="index.php" class="sidebar-link active">
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

            <p class="sidebar-section-title" style="margin-top: 28px;">
                Management
            </p>

            <a href="index.php" class="sidebar-link">
                <span class="sidebar-icon">◷</span>
                <span>Recent Forms</span>
            </a>

            <a
                href="javascript:void(0);"
                class="sidebar-link"
                onclick="exportFormListToCSV()">
                <span class="sidebar-icon">⇩</span>
                <span>Export Forms</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    <?php
                        $user_name = $_SESSION['user_name'] ?? 'Administrator';

                        echo htmlspecialchars(
                            strtoupper(substr($user_name, 0, 1)),
                            ENT_QUOTES,
                            'UTF-8'
                        );
                    ?>
                </div>

                <div>
                    <strong>
                        <?php echo htmlspecialchars(
                            $user_name,
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </strong>

                    <small>RIS Management</small>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Overview of all RIS forms and requests</p>
        </div>

        <section class="dashboard-card">
            <div class="card-header">
                <h2>RIS Forms Dashboard</h2>
            </div>

            <div class="card-body">

                <?php if ($result && $result->num_rows > 0): ?>

                    <div class="table-wrapper">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>RIS No.</th>
                                    <th>Date</th>
                                    <th>Office</th>
                                    <th>Requested By</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($form = $result->fetch_assoc()): ?>

                                    <?php
                                        $descriptions = !empty(
                                            $form['line_descriptions']
                                        )
                                            ? $form['line_descriptions']
                                            : ($form['purpose'] ?? '');

                                        $display_text = substr(
                                            $descriptions,
                                            0,
                                            50
                                        );

                                        if (strlen($descriptions) > 50) {
                                            $display_text .= '...';
                                        }

                                        $status = trim(
                                            (string) (
                                                $form['status'] ?? 'Pending'
                                            )
                                        );

                                        $status_class = strtolower($status);

                                        $status_class = preg_replace(
                                            '/[^a-z0-9]+/',
                                            '-',
                                            $status_class
                                        );

                                        $status_class = trim(
                                            $status_class,
                                            '-'
                                        );
                                    ?>

                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars(
                                                $form['ris_number'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>

                                        <td>
                                            <?php echo date(
                                                DISPLAY_DATE_FORMAT,
                                                strtotime($form['ris_date'])
                                            ); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars(
                                                $form['office_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars(
                                                $form['requested_by'] ?? 'N/A',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars(
                                                $display_text,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>

                                        <td>
                                            <span class="badge badge-<?php echo htmlspecialchars(
                                                $status_class,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>">
                                                <?php echo htmlspecialchars(
                                                    $status,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="dropdown">
                                                <button
                                                    type="button"
                                                    class="dropdown-btn">
                                                    Actions ▼
                                                </button>

                                                <div class="dropdown-content">
                                                    <a href="view.php?id=<?php echo (int) $form['id']; ?>">
                                                        View
                                                    </a>

                                                    <a href="edit.php?id=<?php echo (int) $form['id']; ?>">
                                                        Edit
                                                    </a>

                                                    <a
                                                        href="javascript:void(0);"
                                                        onclick="printRISForm(<?php echo (int) $form['id']; ?>)">
                                                        Print
                                                    </a>

                                                    <a
                                                        href="javascript:void(0);"
                                                        class="delete-link"
                                                        onclick="deleteRISForm(<?php echo (int) $form['id']; ?>)">
                                                        Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <div class="alert">
                        No RIS forms found.
                        <a href="create.php">
                            Create your first form
                        </a>
                    </div>

                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

<script>
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
            mobileMenuButton.setAttribute(
                'aria-expanded',
                isOpen ? 'true' : 'false'
            );

            mobileMenuButton.textContent = isOpen ? '×' : '☰';
        }
    }

    if (mobileMenuButton) {
        mobileMenuButton.addEventListener(
            'click',
            toggleSidebar
        );
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener(
            'click',
            toggleSidebar
        );
    }

    document.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', () => {
            if (
                window.innerWidth <= 768 &&
                sidebar &&
                sidebar.classList.contains('open')
            ) {
                toggleSidebar();
            }
        });
    });
</script>