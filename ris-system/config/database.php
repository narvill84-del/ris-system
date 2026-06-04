<?php
/**
 * Database Configuration
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ris_system');
define('DB_PORT', 3306);

// Application Settings
define('APP_NAME', 'RIS Form System');
define('APP_VERSION', '1.0.0');
define('LGU_NAME', 'Margosatubig, Zamboanga del Sur');
define('APP_URL', 'http://localhost/ris-system');

// Create Database Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

// Check Connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set Charset to UTF-8
$conn->set_charset("utf8mb4");

// Session Configuration
session_start();

// Define User Roles
define('ROLE_ADMIN', 'ADMIN');
define('ROLE_USER', 'USER');
define('ROLE_APPROVER', 'APPROVER');

// Date Format
define('DATE_FORMAT', 'Y-m-d');
define('DISPLAY_DATE_FORMAT', 'F d, Y');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// Pagination
define('ITEMS_PER_PAGE', 10);

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper Function to Sanitize Input
function sanitize_input($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

// Helper Function to Validate Email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper Function to Generate RIS Number
function generate_ris_number($conn) {
    $year = date('Y');
    $month = date('m');
    
    $query = "SELECT MAX(CAST(SUBSTRING(ris_number, -5) AS UNSIGNED)) as last_num 
              FROM ris_forms 
              WHERE ris_number LIKE 'RIS-$year-$month%'";
    
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $last_num = isset($row['last_num']) && $row['last_num'] ? $row['last_num'] : 0;
    
    $new_num = str_pad($last_num + 1, 5, '0', STR_PAD_LEFT);
    return "RIS-$year-$month-$new_num";
}

// Helper Function to Log Audit
function log_audit($user_id, $ris_id, $action, $details = '') {
    global $conn;
    $query = "INSERT INTO audit_log (user_id, ris_id, action, details) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $user_id, $ris_id, $action, $details);
    return $stmt->execute();
}

// Helper Function to Check User Session
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . APP_URL . "/login.php");
        exit;
    }
}

// Helper Function to Check User Role
function check_role($required_role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        header("Location: " . APP_URL . "/unauthorized.php");
        exit;
    }
}

?>
