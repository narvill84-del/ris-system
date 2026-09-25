<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_dashboard = isset($dashboard_layout) && $dashboard_layout === true;
$app_name = defined('APP_NAME') ? APP_NAME : 'RIS Form System';
$lgu_name = defined('LGU_NAME') ? LGU_NAME : 'Local Government Unit';
$app_url = defined('APP_URL') ? rtrim(APP_URL, '/') : '';
$title = isset($page_title) ? escape_html($page_title) . ' - ' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title . escape_html($app_name); ?></title>
    <link rel="stylesheet" href="<?php echo escape_html($app_url); ?>/css/style.css">
    <?php if ($is_dashboard): ?>
        <style>html,body{margin:0;padding:0}body.dashboard-page{background:#080808}</style>
    <?php endif; ?>
</head>
<body class="<?php echo $is_dashboard ? 'dashboard-page' : ''; ?>">
<?php if (!$is_dashboard): ?>
<header class="header">
    <div class="header-content">
        <div><h1><?php echo escape_html($app_name); ?></h1><p class="subtitle"><?php echo escape_html($lgu_name); ?></p></div>
        <nav class="nav-links" aria-label="Main navigation">
            <a href="<?php echo escape_html($app_url); ?>/pages/index.php">Dashboard</a>
            <a href="<?php echo escape_html($app_url); ?>/pages/create.php">Create Form</a>
            <a href="<?php echo escape_html($app_url); ?>/pages/report.php">Reports</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="welcome-user">Welcome, <?php echo escape_html($_SESSION['user_name'] ?? 'User'); ?></span>
                <a href="<?php echo escape_html($app_url); ?>/logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
<?php endif; ?>
