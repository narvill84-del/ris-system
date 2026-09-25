<?php
declare(strict_types=1);

/**
 * Central application configuration.
 * Values may be supplied through environment variables so the app works in
 * local, staging, and production environments without editing source code.
 */

function env_value(string $name, string $default = ''): string
{
    $value = getenv($name);
    return $value === false ? $default : $value;
}

define('DB_HOST', env_value('RIS_DB_HOST', '127.0.0.1'));
define('DB_USER', env_value('RIS_DB_USER', 'root'));
define('DB_PASSWORD', env_value('RIS_DB_PASSWORD', ''));
define('DB_NAME', env_value('RIS_DB_NAME', 'ris_system'));
define('DB_PORT', (int) env_value('RIS_DB_PORT', '3306'));

define('APP_NAME', env_value('RIS_APP_NAME', 'RIS Form System'));
define('APP_VERSION', env_value('RIS_APP_VERSION', '1.1.0'));
define('LGU_NAME', env_value('RIS_LGU_NAME', 'Margosatubig, Zamboanga del Sur'));

/*$app_url = rtrim(env_value('RIS_APP_URL', ''), '/');
if ($app_url === '') {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $app_url = $https . '://' . $host . rtrim(preg_replace('#/(api|config)$#', '', $script_dir), '/');
}
define('APP_URL', $app_url); */

define(
    'APP_URL',
    rtrim(
        env_value(
            'RIS_APP_URL',
            'http://localhost/ris-system'
        ),
        '/'
    )
);

define('ROLE_ADMIN', 'ADMIN');
define('ROLE_USER', 'USER');
define('ROLE_APPROVER', 'APPROVER');
define('DATE_FORMAT', 'Y-m-d');
define('DISPLAY_DATE_FORMAT', 'F d, Y');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('ITEMS_PER_PAGE', 10);

error_reporting(E_ALL);
ini_set('display_errors', '0');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('Database connection error: ' . $exception->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check the server configuration.');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function escape_html(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sanitize_input(mixed $data): string
{
    return trim((string) $data);
}

function is_valid_email(mixed $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function generate_ris_number(mysqli $conn): string
{
    $prefix = 'RIS-' . date('Y-m') . '-';
    $stmt = $conn->prepare(
        'SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(ris_number, \'-\', -1) AS UNSIGNED)), 0) AS last_num
         FROM ris_forms WHERE ris_number LIKE CONCAT(?, \'%\')'
    );
    $stmt->bind_param('s', $prefix);
    $stmt->execute();
    $last = (int) (($stmt->get_result()->fetch_assoc()['last_num'] ?? 0));
    $stmt->close();
    return $prefix . str_pad((string) ($last + 1), 5, '0', STR_PAD_LEFT);
}

function log_audit(?int $user_id, ?int $ris_id, string $action, string $details = ''): bool
{
    global $conn;
    try {
        $stmt = $conn->prepare('INSERT INTO audit_log (user_id, ris_id, action, details) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iiss', $user_id, $ris_id, $action, $details);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    } catch (Throwable $exception) {
        error_log('Audit log error: ' . $exception->getMessage());
        return false;
    }
}

function check_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function check_role(string $required_role): void
{
    if (($_SESSION['role'] ?? '') !== $required_role) {
        header('Location: ' . APP_URL . '/unauthorized.php');
        exit;
    }
}
