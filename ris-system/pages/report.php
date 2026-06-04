<?php
/**
 * Report Generation Page
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

require_once '../config/database.php';

$page_title = 'Generate Report';
include '../includes/header.php';

// Get filter parameters
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$office_name = $_GET['office_name'] ?? '';
$status = $_GET['status'] ?? '';
?>

<div class="card col-full">
    <div class="card-header">
        <h2>Generate RIS Report</h2>
    </div>
    <div class="card-body">
        <!-- Filter Form -->
        <form method="GET" id="filter-form" style="background: #f8f9fa; padding: 1.5rem; border-radius: 4px; margin-bottom: 2rem;">
            <div class="form-row">
                <div class="form-group">
                    <label>Start Date:</label>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="form-group">
                    <label>End Date:</label>
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div class="form-group">
                    <label>Office:</label>
                    <input type="text" name="office_name" placeholder="Enter office name" value="<?php echo htmlspecialchars($office_name); ?>">
                </div>
                <div class="form-group">
                    <label>Status:</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="DRAFT" <?php echo $status === 'DRAFT' ? 'selected' : ''; ?>>Draft</option>
                        <option value="SUBMITTED" <?php echo $status === 'SUBMITTED' ? 'selected' : ''; ?>>Submitted</option>
                        <option value="APPROVED" <?php echo $status === 'APPROVED' ? 'selected' : ''; ?>>Approved</option>
                        <option value="RECEIVED" <?php echo $status === 'RECEIVED' ? 'selected' : ''; ?>>Received</option>
                        <option value="ARCHIVED" <?php echo $status === 'ARCHIVED' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="report.php" class="btn btn-secondary">Clear Filters</a>
                <button type="button" class="btn btn-warning" onclick="generateReport({start_date: '<?php echo $start_date; ?>', end_date: '<?php echo $end_date; ?>', office_name: '<?php echo $office_name; ?>', status: '<?php echo $status; ?>'})">Generate Printable Report</button>
            </div>
        </form>

        <!-- Report Data -->
        <?php
        $query = "SELECT * FROM ris_forms WHERE 1=1";
        
        if (!empty($start_date)) {
            $query .= " AND ris_date >= '" . $conn->real_escape_string($start_date) . "'";
        }
        if (!empty($end_date)) {
            $query .= " AND ris_date <= '" . $conn->real_escape_string($end_date) . "'";
        }
        if (!empty($office_name)) {
            $query .= " AND office_name LIKE '%" . $conn->real_escape_string($office_name) . "%'";
        }
        if (!empty($status)) {
            $query .= " AND status = '" . $conn->real_escape_string($status) . "'";
        }
        
        $query .= " ORDER BY ris_date DESC";
        
        $result = $conn->query($query);
        $total_forms = $result->num_rows;
        
        // Calculate statistics
        $stats_query = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'DRAFT' THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN status = 'SUBMITTED' THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'RECEIVED' THEN 1 ELSE 0 END) as received
            FROM ris_forms WHERE 1=1";
        
        if (!empty($start_date)) {
            $stats_query .= " AND ris_date >= '" . $conn->real_escape_string($start_date) . "'";
        }
        if (!empty($end_date)) {
            $stats_query .= " AND ris_date <= '" . $conn->real_escape_string($end_date) . "'";
        }
        if (!empty($office_name)) {
            $stats_query .= " AND office_name LIKE '%" . $conn->real_escape_string($office_name) . "%'";
        }
        if (!empty($status)) {
            $stats_query .= " AND status = '" . $conn->real_escape_string($status) . "'";
        }
        
        $stats_result = $conn->query($stats_query);
        $stats = $stats_result->fetch_assoc();
        ?>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="background: #e3f2fd; padding: 1rem; border-radius: 4px; text-align: center;">
                <h3 style="margin: 0; color: #1976d2;"><?php echo $stats['total'] ?? 0; ?></h3>
                <p style="margin: 0.5rem 0 0 0;">Total Forms</p>
            </div>
            <div style="background: #f3e5f5; padding: 1rem; border-radius: 4px; text-align: center;">
                <h3 style="margin: 0; color: #7b1fa2;"><?php echo $stats['draft'] ?? 0; ?></h3>
                <p style="margin: 0.5rem 0 0 0;">Draft</p>
            </div>
            <div style="background: #fff3e0; padding: 1rem; border-radius: 4px; text-align: center;">
                <h3 style="margin: 0; color: #f57c00;"><?php echo $stats['submitted'] ?? 0; ?></h3>
                <p style="margin: 0.5rem 0 0 0;">Submitted</p>
            </div>
            <div style="background: #e8f5e9; padding: 1rem; border-radius: 4px; text-align: center;">
                <h3 style="margin: 0; color: #388e3c;"><?php echo $stats['approved'] ?? 0; ?></h3>
                <p style="margin: 0.5rem 0 0 0;">Approved</p>
            </div>
            <div style="background: #e0f2f1; padding: 1rem; border-radius: 4px; text-align: center;">
                <h3 style="margin: 0; color: #00796b;"><?php echo $stats['received'] ?? 0; ?></h3>
                <p style="margin: 0.5rem 0 0 0;">Received</p>
            </div>
        </div>

        <!-- Report Table -->
        <?php if ($total_forms > 0): ?>
            <table class="table">
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
                    <?php 
                    while ($form = $result->fetch_assoc()):
                        // Count items for this form
                        $item_count_query = "SELECT COUNT(*) as count FROM ris_line_items WHERE ris_id = " . $form['id'];
                        $item_count_result = $conn->query($item_count_query);
                        $item_count = $item_count_result->fetch_assoc()['count'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($form['ris_number']); ?></td>
                        <td><?php echo date(DISPLAY_DATE_FORMAT, strtotime($form['ris_date'])); ?></td>
                        <td><?php echo htmlspecialchars($form['office_name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($form['purpose'], 0, 40)) . '...'; ?></td>
                        <td><span class="badge badge-<?php echo strtolower($form['status']); ?>"><?php echo $form['status']; ?></span></td>
                        <td><?php echo $item_count; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">
                No forms found matching the selected filters.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
