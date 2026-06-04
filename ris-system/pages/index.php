<?php
/**
 * Index/Dashboard Page
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$page_title = 'Dashboard';
include '../includes/header.php';

// Get forms for display
$query = "SELECT * FROM ris_forms ORDER BY created_at DESC LIMIT 20";
$result = $conn->query($query);
?>

<style>
    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background-color: #f9f9f9;
        min-width: 200px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        padding: 12px 16px;
        z-index: 1;
        border-radius: 4px;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .dropdown-content a,
    .dropdown-content button {
        color: #333;
        padding: 10px 0;
        text-decoration: none;
        display: block;
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 0.9rem;
        font-family: inherit;
    }

    .dropdown-content a:hover,
    .dropdown-content button:hover {
        background-color: #f1f1f1;
        padding-left: 10px;
        border-radius: 3px;
    }

    .dropdown-btn {
        background-color: #0066cc;
        color: white;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        border: none;
        cursor: pointer;
        border-radius: 4px;
    }

    .dropdown-btn:hover {
        background-color: #0052a3;
    }
</style>

<div class="card col-full">
    <div class="card-header">
        <h2>RIS Forms Dashboard</h2>
    </div>
    <div class="card-body">
        <div class="btn-group">
            <a href="create.php" class="btn btn-primary">+ Create New Form</a>
            <a href="report.php" class="btn btn-secondary">Generate Report</a>
            <button onclick="exportFormListToCSV()" class="btn btn-secondary">Export to CSV</button>
        </div>

        <?php if ($result->num_rows > 0): ?>
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
                        <tr>
                            <td><?php echo htmlspecialchars($form['ris_number']); ?></td>
                            <td><?php echo date(DISPLAY_DATE_FORMAT, strtotime($form['ris_date'])); ?></td>
                            <td><?php echo htmlspecialchars($form['office_name']); ?></td>
                            <td><?php echo htmlspecialchars($form['requested_by'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(substr($form['purpose'], 0, 50)) . (strlen($form['purpose']) > 50 ? '...' : ''); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($form['status']); ?>">
                                    <?php echo $form['status']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropdown-btn">Actions ▼</button>
                                    <div class="dropdown-content">
                                        <a href="view.php?id=<?php echo $form['id']; ?>">View</a>
                                        <a href="edit.php?id=<?php echo $form['id']; ?>">Edit</a>
                                        <a href="javascript:void(0);" onclick="printRISForm(<?php echo $form['id']; ?>)">Print</a>
                                        <a href="javascript:void(0);" onclick="deleteRISForm(<?php echo $form['id']; ?>)" style="color: #dc3545;">Delete</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">
                No RIS forms found. <a href="create.php">Create your first form</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
