<?php
/**
 * Header Template
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div>
                <h1><?php echo APP_NAME; ?></h1>
                <p class="subtitle"><?php echo LGU_NAME; ?></p>
            </div>
            <nav class="nav-links">
                <a href="<?php echo APP_URL; ?>/pages/index.php">Dashboard</a>
                <a href="<?php echo APP_URL; ?>/pages/create.php">Create Form</a>
                <a href="<?php echo APP_URL; ?>/pages/report.php">Reports</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span style="color: white;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="<?php echo APP_URL; ?>/logout.php">Logout</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
