<?php
// sidebar.php
// Include this file near the top of your page layout (before main content).
// It uses $current_page to mark the active item. Set $current_page = basename($_SERVER['PHP_SELF']);

if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF']);
}

// Helper to mark active pages
function is_active($names, $current_page) {
    $names = (array)$names;
    foreach ($names as $n) {
        if (strpos($current_page, $n) !== false) return 'active';
    }
    return '';
}
?>
<aside class="app-sidebar" id="app-sidebar" aria-label="Main navigation">
  <div class="sidebar-header">
    <a class="brand" href="index.php" title="RIS Dashboard">
      <!-- simple SVG icon -->
      <svg class="brand-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
        <path fill="currentColor" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zM13 21h8V11h-8v10zm0-18v6h8V3h-8z"/>
      </svg>
      <span class="brand-name">RIS System</span>
    </a>
    <button class="sidebar-toggle" id="sidebar-toggle" aria-expanded="true" aria-controls="app-sidebar" aria-label="Toggle sidebar">
      <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path fill="currentColor" d="M4 6h16v2H4zM4 11h16v2H4zM4 16h16v2H4z"/></svg>
    </button>
  </div>

  <div class="sidebar-search" role="search">
    <input id="sidebar-search" type="search" placeholder="Search menu..." aria-label="Search menu">
  </div>

  <nav class="sidebar-nav" role="navigation" aria-label="Sidebar">
    <a class="nav-item <?php echo is_active('index.php', $current_page); ?>" href="index.php">
      <span class="nav-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
      </span>
      <span class="nav-text">Dashboard</span>
    </a>

    <a class="nav-item <?php echo is_active('create.php', $current_page); ?>" href="create.php">
      <span class="nav-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M13 11h8v2h-8v8h-2v-8H3v-2h8V3h2z"/></svg>
      </span>
      <span class="nav-text">Create New Form</span>
    </a>

    <a class="nav-item <?php echo is_active('report.php', $current_page); ?>" href="report.php">
      <span class="nav-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 8h14v-2H7v2zm0-4h14v-2H7v2zm0-6v2h14V7H7z"/></svg>
      </span>
      <span class="nav-text">Reports</span>
    </a>

    <button class="nav-item nav-button" type="button" onclick="exportFormListToCSV();">
      <span class="nav-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M5 20h14v-2H5v2zM7 4h10v8h3L12 21 4 12h3V4z"/></svg>
      </span>
      <span class="nav-text">Export to CSV</span>
    </button>

    <div class="nav-divider" role="separator" aria-hidden="true"></div>

    <a class="nav-item <?php echo is_active('view.php', $current_page); ?>" href="index.php#list">
      <span class="nav-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M3 5h18v2H3zm0 6h18v2H3zm0 6h18v2H3z"/></svg>
      </span>
      <span class="nav-text">All Forms</span>
    </a>

    <a class="nav-item" href="javascript:void(0);" onclick="window.location.href='help.php'">
      <span class="nav-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm1.07-7.75c-.9.92-1.07 1.23-1.07 2.25h-2v-.5c0-1 .27-1.64 1.17-2.55.84-.86 1.83-1.25 1.83-2.45 0-1.31-1.06-2.25-2.5-2.25S9.5 7.69 9.5 9H7.5c0-2.5 2.01-4.5 4.5-4.5S16.5 6.5 16.5 8.25c0 1.1-.6 1.87-2.43 3.0z"/></svg>
      </span>
      <span class="nav-text">Help</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="profile">
      <div class="avatar" aria-hidden="true"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'USER', 0, 1)); ?></div>
      <div class="profile-info">
        <div class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></div>
        <a class="profile-link" href="profile.php">Profile</a>
      </div>
    </div>
  </div>
</aside>