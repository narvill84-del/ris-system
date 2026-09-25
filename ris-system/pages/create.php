<?php
/**
 * Create RIS Form Page
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$page_title = 'Create RIS Form';
$dashboard_layout = true;

include '../includes/header.php';

$ris_number = generate_ris_number($conn);

$user_name = $_SESSION['user_name'] ?? 'Administrator';
$user_initial = strtoupper(substr($user_name, 0, 1));
?>

<style>
    :root {
        --gold: #d4af37;
        --gold-light: #f3d77a;
        --gold-dark: #9c7c19;
        --luxury-bg: #080808;
        --luxury-text: #f5f1e6;
        --luxury-text-soft: #c8c1b0;
        --luxury-text-muted: #918a7a;
        --luxury-line: rgba(212, 175, 55, 0.2);
        --luxury-line-strong: rgba(212, 175, 55, 0.45);
        --sidebar-width: 270px;
        --page-max-width: 1240px;
        --form-max-width: 1180px;
        --radius: 16px;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    body.dashboard-page {
        min-height: 100vh;
        margin: 0;
        color: var(--luxury-text);
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
            var(--luxury-bg);
        overflow-x: hidden;
    }

    body.dashboard-page a {
        text-decoration: none;
    }

    .dashboard-layout {
        display: flex;
        min-height: 100vh;
    }

    .main-content {
        width: calc(100% - var(--sidebar-width));
        min-height: 100vh;
        margin-left: var(--sidebar-width);
        padding: 34px 28px 50px;
    }

    .sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        width: var(--sidebar-width);
        background:
            linear-gradient(
                180deg,
                #090909 0%,
                #11100d 55%,
                #090909 100%
            );
        border-right: 1px solid var(--luxury-line);
        box-shadow: 14px 0 35px rgba(0, 0, 0, 0.6);
        transition: transform 0.28s ease;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 92px;
        padding: 0 22px;
        border-bottom: 1px solid var(--luxury-line);
    }

    .sidebar-brand-icon {
        display: grid;
        place-items: center;
        width: 44px;
        height: 44px;
        color: #17120a;
        background: linear-gradient(
            135deg,
            var(--gold-light),
            var(--gold-dark)
        );
        border-radius: 13px;
        box-shadow: 0 12px 28px rgba(212, 175, 55, 0.25);
        font-size: 1.15rem;
        font-weight: 900;
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
        color: var(--luxury-text-muted);
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
        margin-bottom: 7px;
        padding: 13px 14px;
        color: var(--luxury-text-soft);
        border: 1px solid transparent;
        border-radius: 11px;
        font-size: 0.94rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .sidebar-link:hover {
        color: var(--gold-light);
        background: rgba(212, 175, 55, 0.08);
        border-color: var(--luxury-line);
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
        border-color: var(--luxury-line-strong);
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
        border-top: 1px solid var(--luxury-line);
    }

    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 12px;
        background: rgba(212, 175, 55, 0.04);
        border: 1px solid var(--luxury-line);
        border-radius: 13px;
    }

    .sidebar-avatar {
        display: grid;
        place-items: center;
        width: 36px;
        height: 36px;
        color: #17120a;
        background: linear-gradient(
            135deg,
            var(--gold-light),
            var(--gold-dark)
        );
        border-radius: 50%;
        font-size: 0.78rem;
        font-weight: 900;
    }

    .sidebar-user strong {
        display: block;
        color: var(--gold-light);
        font-size: 0.8rem;
    }

    .sidebar-user small {
        color: var(--luxury-text-muted);
        font-size: 0.7rem;
    }

    .create-page-content {
        width: 100%;
        max-width: var(--page-max-width);
        margin: 0 auto;
    }

    .create-card {
        width: 100%;
        max-width: var(--form-max-width);
        margin: 0 auto;
        overflow: hidden;
        background:
            linear-gradient(
                145deg,
                rgba(25, 25, 25, 0.98),
                rgba(12, 12, 12, 0.98)
            );
        border: 1px solid var(--luxury-line);
        border-radius: var(--radius);
        box-shadow: 0 22px 45px rgba(0, 0, 0, 0.55);
    }

    .create-card-header {
        padding: 22px 26px;
        background:
            linear-gradient(
                90deg,
                rgba(212, 175, 55, 0.14),
                transparent
            );
        border-bottom: 1px solid var(--luxury-line);
    }

    .create-card-header h2 {
        margin: 0;
        color: var(--gold-light);
        font-size: 1.35rem;
    }

    .create-card-body {
        padding: 26px;
        background:
            linear-gradient(
                180deg,
                rgba(20, 20, 20, 0.96),
                rgba(10, 10, 10, 0.96)
            );
    }

    .ris-form {
        width: 100%;
        margin: 0;
        padding: 30px;
        color: var(--luxury-text);
        background:
            linear-gradient(
                180deg,
                #11100d 0%,
                #090909 100%
            );
        border: 1px solid var(--luxury-line-strong);
        border-radius: 14px;
    }

    .ris-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        text-align: center;
        border-bottom: 1px solid var(--luxury-line-strong);
    }

    .ris-header h1 {
        margin: 0 0 10px;
        color: var(--gold-light);
        font-size: clamp(1.3rem, 2vw, 1.9rem);
        font-weight: 800;
        letter-spacing: 0.04em;
    }

    .ris-header p {
        margin: 4px 0;
        color: var(--luxury-text-soft);
        font-size: 0.9rem;
    }

    .ris-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--luxury-line);
    }

    .ris-info-item label,
    .form-group label,
    .signature-field > label {
        display: block;
        margin-bottom: 7px;
        color: var(--gold-light);
        font-size: 0.86rem;
        font-weight: 700;
    }

    .ris-info-item input,
    .form-group input,
    .form-group textarea,
    .form-group select,
    .signature-field input,
    .line-items-table input {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        color: var(--luxury-text);
        background: #171717;
        border: 1px solid rgba(212, 175, 55, 0.25);
        border-radius: 8px;
        outline: none;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease,
            background 0.2s ease;
    }

    .ris-info-item input:focus,
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus,
    .signature-field input:focus,
    .line-items-table input:focus {
        background: #1d1d1d;
        border-color: var(--gold);
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.12);
    }

    .ris-info-item input[readonly] {
        color: var(--gold-light);
        background: rgba(212, 175, 55, 0.08);
    }

    .form-group {
        margin-bottom: 22px;
    }

    .form-group textarea {
        min-height: 110px;
        resize: vertical;
    }

    input[type="date"] {
        /* color-scheme: dark;  */
        color: var(--luxury-text);

    }

    input::placeholder,
    textarea::placeholder {
        color: #756f63;
    }

    .section-title {
        margin: 28px 0 14px;
        padding-bottom: 10px;
        color: var(--gold-light);
        border-bottom: 1px solid var(--luxury-line);
        font-size: 1.05rem;
        letter-spacing: 0.08em;
    }

    .line-items-wrapper {
        width: 100%;
        margin-bottom: 18px;
        overflow-x: auto;
        border: 1px solid var(--luxury-line);
        border-radius: 10px;
    }

    .line-items-table {
        width: 100%;
        min-width: 1050px;
        margin: 0;
        border-collapse: collapse;
        background: #0d0d0d;
    }

    .line-items-table thead {
        color: var(--gold-light);
        background:
            linear-gradient(
                90deg,
                rgba(212, 175, 55, 0.18),
                rgba(212, 175, 55, 0.05)
            );
    }

    .line-items-table th {
        padding: 13px 10px;
        border-bottom: 1px solid var(--luxury-line-strong);
        font-size: 0.78rem;
        font-weight: 800;
        text-align: left;
        white-space: nowrap;
    }

    .line-items-table td {
        padding: 10px;
        color: var(--luxury-text-soft);
        background: #11100d;
        border-bottom: 1px solid rgba(212, 175, 55, 0.12);
    }

    .line-items-table tbody tr:hover td {
        background: rgba(212, 175, 55, 0.06);
    }

    .line-items-table input {
        min-height: 38px;
        padding: 8px 10px;
        font-size: 0.86rem;
    }

    .signature-block {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 22px;
        margin-top: 34px;
        padding-top: 24px;
        border-top: 1px solid var(--luxury-line);
    }

    .signature-field {
        padding: 18px;
        text-align: center;
        background: rgba(212, 175, 55, 0.035);
        border: 1px solid var(--luxury-line);
        border-radius: 12px;
    }

    .signature-field > label {
        margin-bottom: 14px;
        text-align: left;
    }

    .signature-field input {
        margin-bottom: 8px;
        text-align: center;
    }

    .signature-line {
        margin: 26px 0 8px;
        border-top: 1px solid var(--gold);
    }

    .signature-label {
        margin-bottom: 18px;
        color: var(--luxury-text-muted);
        font-size: 0.76rem;
        font-weight: 600;
    }

    .form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 28px;
        padding-top: 22px;
        border-top: 1px solid var(--luxury-line);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 10px 18px;
        border: 0;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn:disabled {
        cursor: wait;
        opacity: 0.65;
    }

    .btn-primary {
        color: #17120a;
        background: linear-gradient(
            135deg,
            var(--gold-light),
            var(--gold-dark)
        );
        border: 1px solid var(--gold-light);
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
        border: 1px solid var(--luxury-line);
    }

    .btn-secondary:hover {
        color: var(--gold-light);
        background: rgba(212, 175, 55, 0.13);
        border-color: var(--luxury-line-strong);
    }

    .remove-item-button {
        min-width: 82px;
        padding: 8px 10px;
        color: #fff;
        background: rgba(227, 107, 107, 0.15);
        border: 1px solid rgba(227, 107, 107, 0.35);
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .remove-item-button:hover {
        background: rgba(227, 107, 107, 0.28);
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
        border: 1px solid var(--luxury-line);
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

    @media (max-width: 900px) {
        .ris-info-grid,
        .signature-block {
            grid-template-columns: 1fr;
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
            padding: 76px 14px 35px;
        }

        .mobile-menu-button {
            display: block;
        }

        .create-card-body {
            padding: 14px;
        }

        .ris-form {
            padding: 18px 14px;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn,
        .ris-form > .btn {
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

<div
    class="sidebar-overlay"
    id="sidebarOverlay">
</div>

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

        <nav
            class="sidebar-nav"
            aria-label="Dashboard navigation">

            <p class="sidebar-section-title">
                Main Menu
            </p>

            <a
                href="index.php"
                class="sidebar-link">

                <span class="sidebar-icon">▦</span>
                <span>Dashboard</span>
            </a>

            <a
                href="create.php"
                class="sidebar-link active">

                <span class="sidebar-icon">＋</span>
                <span>Create RIS Form</span>
            </a>

            <a
                href="report.php"
                class="sidebar-link">

                <span class="sidebar-icon">▤</span>
                <span>Reports</span>
            </a>

            <p
                class="sidebar-section-title"
                style="margin-top: 28px;">

                Management
            </p>

            <a
                href="index.php"
                class="sidebar-link">

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
        <div class="create-page-content">

            <section class="create-card">
                <div class="create-card-header">
                    <h2>
                        Create New Requisition and Issue Slip
                    </h2>
                </div>

                <div class="create-card-body">

                    <!--form
                        id="ris-form"
                        method="POST"
                        action="../api/save-form.php"
                        novalidate-->

                    <form
                        id="ris-form"
                        method="POST"
                        action="../pages/create.php"
                        novalidate>
                            

                        <!-- This field is populated by form-handler.js -->
                        <input
                            type="hidden"
                            name="line_items"
                            id="line_items"
                            value="">

                        <div class="ris-form">

                            <div class="ris-header">
                                <h1>
                                    REQUISITION AND ISSUE SLIP FORM
                                </h1>

                                <p>
                                    MARGOSATUBIG, ZAMBOANGA DEL SUR
                                </p>

                                <p>LGU</p>
                            </div>

                            <div class="ris-info-grid">

                                <div class="ris-info-item">
                                    <label for="office_name">
                                        Office:
                                    </label>

                                    <input
                                        type="text"
                                        name="office_name"
                                        id="office_name"
                                        required
                                        data-validate="required"
                                        data-field-name="Office">
                                </div>

                                <div class="ris-info-item">
                                    <label for="responsibility_center_code">
                                        Responsibility Center Code:
                                    </label>

                                    <input
                                        type="text"
                                        name="responsibility_center_code"
                                        id="responsibility_center_code">
                                </div>

                                <div class="ris-info-item">
                                    <label for="ris_number">
                                        RIS No.:
                                    </label>

                                    <input
                                        type="text"
                                        name="ris_number"
                                        id="ris_number"
                                        value="<?php echo htmlspecialchars(
                                            $ris_number,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>"
                                        readonly>
                                </div>

                                <div class="ris-info-item">
                                    <label for="ris_date">
                                        RIS Date:
                                    </label>

                                    <input
                                        type="date"
                                        name="ris_date"
                                        id="ris_date"
                                        required
                                        data-validate="date"
                                        data-field-name="RIS Date">
                                </div>

                                <div class="ris-info-item">
                                    <label for="sai_number">
                                        SAI No.:
                                    </label>

                                    <input
                                        type="text"
                                        name="sai_number"
                                        id="sai_number">
                                </div>

                                <div class="ris-info-item">
                                    <label for="sai_date">
                                        SAI Date:
                                    </label>

                                    <input
                                        type="date"
                                        name="sai_date"
                                        id="sai_date">
                                </div>

                            </div>

                            <div class="form-group">
                                <label for="purpose">
                                    Purpose:
                                </label>

                                <textarea
                                    name="purpose"
                                    id="purpose"
                                    required
                                    data-validate="required"
                                    data-field-name="Purpose"
                                    placeholder="Enter the purpose of this requisition"></textarea>
                            </div>

                            <h3 class="section-title">
                                REQUISITION
                            </h3>

                            <div class="line-items-wrapper">
                                <table class="line-items-table">
                                    <thead>
                                        <tr>
                                            <th>Stock No.</th>
                                            <th>Unit</th>
                                            <th>Description</th>
                                            <th>Quantity Requested</th>
                                            <th>Quantity Received</th>
                                            <th>Remarks</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody id="line-items-body">
                                        <tr>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="stock_number_0"
                                                    placeholder="Stock No.">
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    name="unit_0"
                                                    placeholder="Unit">
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    name="description_0"
                                                    placeholder="Description"
                                                    required>
                                            </td>

                                            <td>
                                                <input
                                                    type="number"
                                                    name="quantity_requested_0"
                                                    placeholder="Qty"
                                                    min="1"
                                                    step="1"
                                                    required>
                                            </td>

                                            <td>
                                                <input
                                                    type="number"
                                                    name="quantity_received_0"
                                                    placeholder="Qty"
                                                    min="0"
                                                    step="1"
                                                    value="0">
                                            </td>

                                            <td>
                                                <input
                                                    type="text"
                                                    name="remarks_0"
                                                    placeholder="Remarks">
                                            </td>

                                            <td>
                                                <button
                                                    type="button"
                                                    class="remove-item-button"
                                                    onclick="removeLineItem(this)">
                                                    Remove
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <button
                                type="button"
                                class="btn btn-secondary"
                                onclick="addLineItem()">
                                + Add Item
                            </button>

                            <div class="signature-block">

                                <div class="signature-field">
                                    <label for="requested_by">
                                        Requested by:
                                    </label>

                                    <input
                                        type="text"
                                        name="requested_by"
                                        id="requested_by"
                                        placeholder="Name"
                                        required>

                                    <input
                                        type="text"
                                        name="requested_by_signature"
                                        id="requested_by_signature"
                                        placeholder="Signature">

                                    <div class="signature-line"></div>

                                    <div class="signature-label">
                                        Signature
                                    </div>

                                    <input
                                        type="text"
                                        name="requested_by_designation"
                                        id="requested_by_designation"
                                        placeholder="Designation"
                                        required>

                                    <div class="signature-label">
                                        Designation
                                    </div>

                                    <input
                                        type="date"
                                        name="requested_by_date"
                                        id="requested_by_date"
                                        required>

                                    <div class="signature-label">
                                        Date
                                    </div>
                                </div>

                                <div class="signature-field">
                                    <label for="approved_by">
                                        Approved by:
                                    </label>

                                    <input
                                        type="text"
                                        name="approved_by"
                                        id="approved_by"
                                        placeholder="Name"
                                        required>

                                    <input
                                        type="text"
                                        name="approved_by_signature"
                                        id="approved_by_signature"
                                        placeholder="Signature">

                                    <div class="signature-line"></div>

                                    <div class="signature-label">
                                        Signature
                                    </div>

                                    <input
                                        type="text"
                                        name="approved_by_designation"
                                        id="approved_by_designation"
                                        placeholder="Designation"
                                        required>

                                    <div class="signature-label">
                                        Designation
                                    </div>

                                    <input
                                        type="date"
                                        name="approved_by_date"
                                        id="approved_by_date"
                                        required>

                                    <div class="signature-label">
                                        Date
                                    </div>
                                </div>

                                <div class="signature-field">
                                    <label for="received_by">
                                        Received by:
                                    </label>

                                    <input
                                        type="text"
                                        name="received_by"
                                        id="received_by"
                                        placeholder="Name">

                                    <input
                                        type="text"
                                        name="received_by_signature"
                                        id="received_by_signature"
                                        placeholder="Signature">

                                    <div class="signature-line"></div>

                                    <div class="signature-label">
                                        Signature
                                    </div>

                                    <input
                                        type="text"
                                        name="received_by_designation"
                                        id="received_by_designation"
                                        placeholder="Designation">

                                    <div class="signature-label">
                                        Designation
                                    </div>

                                    <input
                                        type="date"
                                        name="received_by_date"
                                        id="received_by_date"
                                        style="color:  var(--luxury-text);">

                                    <div class="signature-label" >
                                        Date
                                    </div>
                                </div>

                            </div>

                            <div class="form-actions">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    name="save"
                                    id="save-ris-button">
                                    Save
                                </button>

                                <a
                                    href="index.php"
                                    class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>

                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {
            const sidebar =
                document.getElementById('sidebar');

            const mobileMenuButton =
                document.getElementById('mobileMenuButton');

            const sidebarOverlay =
                document.getElementById('sidebarOverlay');

            function toggleSidebar() {
                if (!sidebar) {
                    return;
                }

                const isOpen =
                    sidebar.classList.toggle('open');

                if (sidebarOverlay) {
                    sidebarOverlay.classList.toggle(
                        'visible',
                        isOpen
                    );
                }

                if (mobileMenuButton) {
                    mobileMenuButton.setAttribute(
                        'aria-expanded',
                        isOpen ? 'true' : 'false'
                    );

                    mobileMenuButton.textContent =
                        isOpen ? '×' : '☰';
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

            document
                .querySelectorAll('.sidebar-link')
                .forEach(link => {
                    link.addEventListener(
                        'click',
                        function () {
                            if (
                                window.innerWidth <= 768 &&
                                sidebar &&
                                sidebar.classList.contains('open')
                            ) {
                                toggleSidebar();
                            }
                        }
                    );
                });
        }
    );
</script>