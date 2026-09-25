<?php
/**
 * Report Generation Page
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$page_title = 'Generate Report';
$dashboard_layout = true;

include '../includes/header.php';

$start_date  = trim($_GET['start_date'] ?? '');
$end_date    = trim($_GET['end_date'] ?? '');
$office_name = trim($_GET['office_name'] ?? '');
$status      = trim($_GET['status'] ?? '');

$allowed_statuses = [
    'DRAFT',
    'SUBMITTED',
    'APPROVED',
    'RECEIVED',
    'ARCHIVED'
];

if (!in_array($status, $allowed_statuses, true)) {
    $status = '';
}

/*
|--------------------------------------------------------------------------
| Build filters
|--------------------------------------------------------------------------
*/

$where_conditions = [];
$query_params = [];
$query_types = '';

if ($start_date !== '') {
    $where_conditions[] = 'rf.ris_date >= ?';
    $query_params[] = $start_date;
    $query_types .= 's';
}

if ($end_date !== '') {
    $where_conditions[] = 'rf.ris_date <= ?';
    $query_params[] = $end_date;
    $query_types .= 's';
}

if ($office_name !== '') {
    $where_conditions[] = 'rf.office_name LIKE ?';
    $query_params[] = '%' . $office_name . '%';
    $query_types .= 's';
}

if ($status !== '') {
    $where_conditions[] = 'rf.status = ?';
    $query_params[] = $status;
    $query_types .= 's';
}

$where_sql = '';

if (!empty($where_conditions)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
}

/*
|--------------------------------------------------------------------------
| Get report data
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        rf.id,
        rf.ris_number,
        rf.ris_date,
        rf.office_name,
        rf.purpose,
        rf.status,
        rf.created_at,
        COUNT(rli.id) AS item_count
    FROM ris_forms rf
    LEFT JOIN ris_line_items rli
        ON rf.id = rli.ris_id
    {$where_sql}
    GROUP BY
        rf.id,
        rf.ris_number,
        rf.ris_date,
        rf.office_name,
        rf.purpose,
        rf.status,
        rf.created_at
    ORDER BY rf.ris_date DESC, rf.created_at DESC
";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die('Unable to prepare report query.');
}

if (!empty($query_params)) {
    $bind_values = [$query_types];

    foreach ($query_params as $key => $value) {
        $bind_values[] = &$query_params[$key];
    }

    call_user_func_array(
        [$stmt, 'bind_param'],
        $bind_values
    );
}

$stmt->execute();

$report_result = $stmt->get_result();
$forms = [];

$stats = [
    'total' => 0,
    'draft' => 0,
    'submitted' => 0,
    'approved' => 0,
    'received' => 0,
    'archived' => 0
];

while ($form = $report_result->fetch_assoc()) {
    $forms[] = $form;

    $stats['total']++;

    $form_status = strtoupper(trim($form['status'] ?? ''));

    if ($form_status === 'DRAFT') {
        $stats['draft']++;
    } elseif ($form_status === 'SUBMITTED') {
        $stats['submitted']++;
    } elseif ($form_status === 'APPROVED') {
        $stats['approved']++;
    } elseif ($form_status === 'RECEIVED') {
        $stats['received']++;
    } elseif ($form_status === 'ARCHIVED') {
        $stats['archived']++;
    }
}

$stmt->close();

$report_filters = [
    'start_date' => $start_date,
    'end_date' => $end_date,
    'office_name' => $office_name,
    'status' => $status
];

$user_name = $_SESSION['user_name'] ?? 'Administrator';
$user_initial = strtoupper(substr($user_name, 0, 1));
?>

<style>
    :root {
        --report-gold: #d4af37;
        --report-gold-light: #f3d77a;
        --report-gold-dark: #9c7c19;
        --report-bg: #080808;
        --report-panel: #151515;
        --report-panel-dark: #10100e;
        --report-text: #f5f1e6;
        --report-text-soft: #c8c1b0;
        --report-text-muted: #918a7a;
        --report-line: rgba(212, 175, 55, 0.2);
        --report-line-strong: rgba(212, 175, 55, 0.45);
        --report-sidebar-width: 270px;
        --report-max-width: 1350px;
    }

    * {
        box-sizing: border-box;
    }

    body.dashboard-page {
        min-height: 100vh;
        margin: 0;
        color: var(--report-text);
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
            var(--report-bg);
        overflow-x: hidden;
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard layout
    |--------------------------------------------------------------------------
    */

    body.dashboard-page .dashboard-layout {
        display: flex;
        min-height: 100vh;
    }

    body.dashboard-page .main-content {
        width: calc(100% - var(--report-sidebar-width));
        min-height: 100vh;
        margin-left: var(--report-sidebar-width);
        padding: 34px 28px 50px;
    }

    .report-page-content {
        width: 100%;
        max-width: var(--report-max-width);
        margin: 0 auto;
    }

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */

    body.dashboard-page .sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        width: var(--report-sidebar-width);
        background:
            linear-gradient(
                180deg,
                #090909 0%,
                #11100d 55%,
                #090909 100%
            );
        border-right: 1px solid var(--report-line);
        box-shadow: 14px 0 35px rgba(0, 0, 0, 0.6);
        transition: transform 0.28s ease;
    }

    body.dashboard-page .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 92px;
        padding: 0 22px;
        border-bottom: 1px solid var(--report-line);
    }

    body.dashboard-page .sidebar-brand-icon {
        display: grid;
        place-items: center;
        width: 44px;
        height: 44px;
        color: #17120a;
        background: linear-gradient(
            135deg,
            var(--report-gold-light),
            var(--report-gold-dark)
        );
        border-radius: 13px;
        box-shadow: 0 12px 28px rgba(212, 175, 55, 0.25);
        font-size: 1.15rem;
        font-weight: 900;
    }

    body.dashboard-page .sidebar-brand-text {
        color: var(--report-gold-light);
        font-size: 1.04rem;
        font-weight: 800;
        line-height: 1.2;
    }

    body.dashboard-page .sidebar-brand-text small {
        display: block;
        margin-top: 3px;
        color: var(--report-text-muted);
        font-size: 0.7rem;
        font-weight: 500;
    }

    body.dashboard-page .sidebar-nav {
        flex: 1;
        padding: 22px 14px 18px;
        overflow-y: auto;
    }

    body.dashboard-page .sidebar-section-title {
        margin: 0 10px 12px;
        color: var(--report-gold-dark);
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    body.dashboard-page .sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 7px;
        padding: 13px 14px;
        color: var(--report-text-soft);
        border: 1px solid transparent;
        border-radius: 11px;
        font-size: 0.94rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    body.dashboard-page .sidebar-link:hover {
        color: var(--report-gold-light);
        background: rgba(212, 175, 55, 0.08);
        border-color: var(--report-line);
        text-decoration: none;
        transform: translateX(3px);
    }

    body.dashboard-page .sidebar-link.active {
        color: var(--report-gold-light);
        background:
            linear-gradient(
                135deg,
                rgba(212, 175, 55, 0.2),
                rgba(212, 175, 55, 0.06)
            );
        border-color: var(--report-line-strong);
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.25);
    }

    body.dashboard-page .sidebar-icon {
        width: 22px;
        color: var(--report-gold);
        font-size: 1.05rem;
        text-align: center;
    }

    body.dashboard-page .sidebar-footer {
        padding: 16px 14px;
        border-top: 1px solid var(--report-line);
    }

    body.dashboard-page .sidebar-user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 12px;
        background: rgba(212, 175, 55, 0.04);
        border: 1px solid var(--report-line);
        border-radius: 13px;
    }

    body.dashboard-page .sidebar-avatar {
        display: grid;
        place-items: center;
        width: 36px;
        height: 36px;
        color: #17120a;
        background: linear-gradient(
            135deg,
            var(--report-gold-light),
            var(--report-gold-dark)
        );
        border-radius: 50%;
        font-size: 0.78rem;
        font-weight: 900;
    }

    body.dashboard-page .sidebar-user strong {
        display: block;
        color: var(--report-gold-light);
        font-size: 0.8rem;
    }

    body.dashboard-page .sidebar-user small {
        color: var(--report-text-muted);
        font-size: 0.7rem;
    }

    /*
    |--------------------------------------------------------------------------
    | Page heading
    |--------------------------------------------------------------------------
    */

    .report-page-header {
        max-width: var(--report-max-width);
        margin: 0 auto 24px;
    }

    .report-page-header h1 {
        margin: 0;
        color: var(--report-gold-light);
        font-size: 2rem;
        font-weight: 800;
    }

    .report-page-header p {
        margin: 8px 0 0;
        color: var(--report-text-muted);
        font-size: 0.95rem;
    }

    /*
    |--------------------------------------------------------------------------
    | Report card
    |--------------------------------------------------------------------------
    */

    .report-card {
        overflow: hidden;
        background:
            linear-gradient(
                145deg,
                rgba(25, 25, 25, 0.98),
                rgba(12, 12, 12, 0.98)
            );
        border: 1px solid var(--report-line);
        border-radius: 16px;
        box-shadow: 0 22px 45px rgba(0, 0, 0, 0.55);
    }

    .report-card-header {
        padding: 22px 26px;
        background:
            linear-gradient(
                90deg,
                rgba(212, 175, 55, 0.14),
                transparent
            );
        border-bottom: 1px solid var(--report-line);
    }

    .report-card-header h2 {
        margin: 0;
        color: var(--report-gold-light);
        font-size: 1.4rem;
    }

    .report-card-body {
        padding: 26px;
        background:
            linear-gradient(
                180deg,
                rgba(20, 20, 20, 0.96),
                rgba(10, 10, 10, 0.96)
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    .report-filter-form {
        margin-bottom: 28px;
        padding: 22px;
        background:
            linear-gradient(
                145deg,
                rgba(212, 175, 55, 0.08),
                rgba(17, 16, 13, 0.96)
            );
        border: 1px solid var(--report-line);
        border-radius: 14px;
    }

    .report-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .report-filter-form .form-group {
        margin: 0;
    }

    .report-filter-form label {
        display: block;
        margin-bottom: 7px;
        color: var(--report-gold-light);
        font-size: 0.84rem;
        font-weight: 700;
    }

    .report-filter-form input,
    .report-filter-form select {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        color: var(--report-text);
        background: #171717;
        border: 1px solid rgba(212, 175, 55, 0.25);
        border-radius: 8px;
        outline: none;
        color-scheme: dark;
    }

    .report-filter-form input:focus,
    .report-filter-form select:focus {
        border-color: var(--report-gold);
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.12);
    }

    .report-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--report-line);
    }

    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */

    .report-page-content .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 10px 18px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .report-page-content .btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }

    .report-page-content .btn-primary {
        color: #17120a;
        background: linear-gradient(
            135deg,
            var(--report-gold-light),
            var(--report-gold-dark)
        );
        border: 1px solid var(--report-gold-light);
    }

    .report-page-content .btn-primary:hover {
        color: #0c0c0c;
        background: linear-gradient(
            135deg,
            #ffe99b,
            var(--report-gold)
        );
    }

    .report-page-content .btn-secondary {
        color: var(--report-gold-light);
        background: rgba(212, 175, 55, 0.05);
        border: 1px solid var(--report-line);
    }

    .report-page-content .btn-secondary:hover {
        background: rgba(212, 175, 55, 0.13);
        border-color: var(--report-line-strong);
    }

    .report-page-content .btn-warning {
        color: #17120a;
        background: linear-gradient(
            135deg,
            #f1cb67,
            #b8891c
        );
        border: 1px solid #f1cb67;
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    .report-statistics {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 28px;
    }

    .report-stat-card {
        min-height: 112px;
        padding: 18px 14px;
        text-align: center;
        background:
            linear-gradient(
                145deg,
                #171717,
                #0d0d0d
            );
        border: 1px solid var(--report-line);
        border-radius: 13px;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
    }

    .report-stat-card h3 {
        margin: 0;
        color: var(--report-gold-light);
        font-size: 1.8rem;
        line-height: 1.2;
    }

    .report-stat-card p {
        margin: 9px 0 0;
        color: var(--report-text-muted);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .report-stat-total {
        border-top: 3px solid var(--report-gold);
    }

    .report-stat-draft {
        border-top: 3px solid #a7a7a7;
    }

    .report-stat-submitted {
        border-top: 3px solid #d9a441;
    }

    .report-stat-approved {
        border-top: 3px solid #65c982;
    }

    .report-stat-received {
        border-top: 3px solid #70b9d8;
    }

    /*
    |--------------------------------------------------------------------------
    | Report table
    |--------------------------------------------------------------------------
    */

    .report-table-wrapper {
        width: 100%;
        overflow-x: auto;
        border: 1px solid var(--report-line);
        border-radius: 12px;
    }

    .report-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
        background:
            linear-gradient(
                180deg,
                #090909 0%,
                #11100d 55%,
                #090909 100%
            );
    }

    .report-table thead th {
        padding: 14px 12px;
        color: var(--report-gold-light);
        background:
            linear-gradient(
                90deg,
                rgba(212, 175, 55, 0.16),
                rgba(212, 175, 55, 0.04)
            );
        border-bottom: 1px solid var(--report-line-strong);
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .report-table tbody tr {
        background:
            linear-gradient(
                180deg,
                rgba(9, 9, 9, 0.98),
                rgba(17, 16, 13, 0.98)
            );
        transition: background 0.2s ease, box-shadow 0.2s ease;
    }

    .report-table tbody tr:hover {
        background:
            linear-gradient(
                90deg,
                rgba(212, 175, 55, 0.13),
                rgba(212, 175, 55, 0.04)
            );
        box-shadow: inset 4px 0 0 var(--report-gold);
    }

    .report-table tbody td {
        padding: 14px 12px;
        color: var(--report-text-soft);
        border-bottom: 1px solid rgba(212, 175, 55, 0.12);
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .report-table tbody tr:last-child td {
        border-bottom: none;
    }

    .report-item-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        padding: 5px 9px;
        color: var(--report-gold-light);
        background: rgba(212, 175, 55, 0.1);
        border: 1px solid var(--report-line);
        border-radius: 999px;
        font-weight: 800;
    }

    /*
    |--------------------------------------------------------------------------
    | Empty state
    |--------------------------------------------------------------------------
    */

    .report-empty-state {
        padding: 28px;
        color: var(--report-text-muted);
        background: rgba(212, 175, 55, 0.04);
        border: 1px solid var(--report-line);
        border-radius: 12px;
        text-align: center;
    }

    /*
    |--------------------------------------------------------------------------
    | Mobile menu
    |--------------------------------------------------------------------------
    */

    body.dashboard-page .mobile-menu-button {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1100;
        width: 44px;
        height: 44px;
        color: var(--report-gold-light);
        background: #151515;
        border: 1px solid var(--report-line);
        border-radius: 12px;
        cursor: pointer;
        font-size: 1.15rem;
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.4);
    }

    body.dashboard-page .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 999;
        background: rgba(0, 0, 0, 0.72);
    }

    body.dashboard-page .sidebar-overlay.visible {
        display: block;
    }

    @media (max-width: 1100px) {
        .report-statistics {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .report-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        body.dashboard-page .sidebar {
            transform: translateX(-100%);
        }

        body.dashboard-page .sidebar.open {
            transform: translateX(0);
        }

        body.dashboard-page .main-content {
            width: 100%;
            margin-left: 0;
            padding: 76px 14px 35px;
        }

        body.dashboard-page .mobile-menu-button {
            display: block;
        }

        .report-page-header h1 {
            font-size: 1.65rem;
        }

        .report-card-body {
            padding: 14px;
        }

        .report-filter-form {
            padding: 16px;
        }

        .report-filter-grid,
        .report-statistics {
            grid-template-columns: 1fr;
        }

        .report-filter-actions {
            flex-direction: column;
        }

        .report-filter-actions .btn {
            width: 100%;
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

            <a href="index.php" class="sidebar-link">
                <span class="sidebar-icon">▦</span>
                <span>Dashboard</span>
            </a>

            <a href="create.php" class="sidebar-link">
                <span class="sidebar-icon">＋</span>
                <span>Create RIS Form</span>
            </a>

            <a href="report.php" class="sidebar-link active">
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
                    <?php echo htmlspecialchars(
                        $user_initial,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
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
        <div class="report-page-content">

            <!--header class="report-page-header">
                <h1>Generate Report</h1>
                <p>Filter, review, and print RIS form reports</p>
            </header-->

            <section class="report-card">
                <div class="report-card-header">
                    <h2>RIS Generate Report</h2>
                </div>

                <div class="report-card-body">

                    <form
                        method="GET"
                        id="filter-form"
                        class="report-filter-form">

                        <div class="report-filter-grid">
                            <div class="form-group">
                                <label for="start_date">
                                    Start Date:
                                </label>

                                <input
                                    type="date"
                                    name="start_date"
                                    id="start_date"
                                    value="<?php echo htmlspecialchars(
                                        $start_date,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>">
                            </div>

                            <div class="form-group">
                                <label for="end_date">
                                    End Date:
                                </label>

                                <input
                                    type="date"
                                    name="end_date"
                                    id="end_date"
                                    value="<?php echo htmlspecialchars(
                                        $end_date,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>">
                            </div>

                            <div class="form-group">
                                <label for="office_name">
                                    Office:
                                </label>

                                <input
                                    type="text"
                                    name="office_name"
                                    id="office_name"
                                    placeholder="Enter office name"
                                    value="<?php echo htmlspecialchars(
                                        $office_name,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>">
                            </div>

                            <div class="form-group">
                                <label for="status">
                                    Status:
                                </label>

                                <select name="status" id="status">
                                    <option value="">All Status</option>

                                    <?php foreach ($allowed_statuses as $option_status): ?>
                                        <option
                                            value="<?php echo htmlspecialchars(
                                                $option_status,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>"
                                            <?php echo $status === $option_status
                                                ? 'selected'
                                                : ''; ?>>
                                            <?php echo htmlspecialchars(
                                                ucfirst(strtolower($option_status)),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="report-filter-actions">
                            <button
                                type="submit"
                                class="btn btn-primary">
                                Apply Filters
                            </button>

                            <a
                                href="report.php"
                                class="btn btn-secondary">
                                Clear Filters
                            </a>

                            <button
                                type="button"
                                class="btn btn-warning"
                                onclick="generateReport(<?php echo htmlspecialchars(
                                    json_encode(
                                        $report_filters,
                                        JSON_HEX_TAG |
                                        JSON_HEX_APOS |
                                        JSON_HEX_QUOT |
                                        JSON_HEX_AMP
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>)">
                                Generate Printable Report
                            </button>
                        </div>
                    </form>

                    <div class="report-statistics">
                        <div class="report-stat-card report-stat-total">
                            <h3><?php echo (int) $stats['total']; ?></h3>
                            <p>Total Forms</p>
                        </div>

                        <div class="report-stat-card report-stat-draft">
                            <h3><?php echo (int) $stats['draft']; ?></h3>
                            <p>Draft</p>
                        </div>

                        <div class="report-stat-card report-stat-submitted">
                            <h3><?php echo (int) $stats['submitted']; ?></h3>
                            <p>Submitted</p>
                        </div>

                        <div class="report-stat-card report-stat-approved">
                            <h3><?php echo (int) $stats['approved']; ?></h3>
                            <p>Approved</p>
                        </div>

                        <div class="report-stat-card report-stat-received">
                            <h3><?php echo (int) $stats['received']; ?></h3>
                            <p>Received</p>
                        </div>
                    </div>

                    <?php if (!empty($forms)): ?>

                        <div class="report-table-wrapper">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>RIS No.</th>
                                        <th>Date</th>
                                        <th>Office</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Items</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($forms as $form): ?>

                                        <?php
                                            $form_status = trim(
                                                (string) ($form['status'] ?? 'Pending')
                                            );

                                            $status_class = strtolower(
                                                $form_status
                                            );

                                            $status_class = preg_replace(
                                                '/[^a-z0-9]+/',
                                                '-',
                                                $status_class
                                            );

                                            $status_class = trim(
                                                $status_class,
                                                '-'
                                            );

                                            $purpose = trim(
                                                (string) ($form['purpose'] ?? '')
                                            );

                                            $short_purpose = mb_substr(
                                                $purpose,
                                                0,
                                                80
                                            );

                                            if (mb_strlen($purpose) > 80) {
                                                $short_purpose .= '...';
                                            }
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
                                                    $short_purpose,
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
                                                        $form_status,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <span class="report-item-count">
                                                    <?php echo (int) $form['item_count']; ?>
                                                </span>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>

                        <div class="report-empty-state">
                            No forms found matching the selected filters.
                        </div>

                    <?php endif; ?>

                </div>
            </section>
        </div>
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