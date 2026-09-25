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
<!-- existing dashboard content -->
<a href="../print/print-invoice.php?id=<?php echo (int) $form['id']; ?>" target="_blank" rel="noopener noreferrer">Invoice</a>
<?php include '../includes/footer.php'; ?>
