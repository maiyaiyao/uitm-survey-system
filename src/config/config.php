<?php
/**
 * Application Configuration
 * UiTM ISO 27001 Audit System
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Application settings
define('APP_NAME', 'UiTM ISO 27001 Level Assessment System');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // change to 'production' when live

// URL Configuration
define('BASE_URL', 'http://localhost:8080');
define('ASSETS_URL', BASE_URL . '/assets');

// Path Configuration
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('PASSWORD_MIN_LENGTH', 8);

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', '473209254883-du60i9kkr345qpc52d7g36ph8mh29c29.apps.googleusercontent.com'); // Get from Google Cloud Console
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', BASE_URL . '/modules/auth/google-callback.php');

// UiTM Email Domain
define('UITM_EMAIL_DOMAIN', '@uitm.edu.my');

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']);

// Pagination
define('RECORDS_PER_PAGE', 10);

// Date format
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');

// Include database configuration
require_once CONFIG_PATH . '/database.php';

// Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

/**
 * Autoload helper functions
 */
function autoloadHelpers()
{
    $helpers = glob(INCLUDES_PATH . '/helpers/*.php');
    foreach ($helpers as $helper) {
        require_once $helper;
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_ID']) && !empty($_SESSION['user_ID']);
}

/**
 * Get current user ID
 */
function getCurrentUserId()
{
    return $_SESSION['user_ID'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole()
{
    return $_SESSION['user_role'] ?? null;
}

/**
 * Redirect to URL
 */
function redirect($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        echo '<script>window.location.href="' . $url . '";</script>';
        exit();
    }
}

/**
 * Check user role permission
 */
function hasPermission($allowedRoles = [])
{
    if (!isLoggedIn()) {
        return false;
    }

    $userRole = getCurrentUserRole();
    return in_array($userRole, $allowedRoles);
}

/**
 * Sanitize input
 */
function sanitize($data)
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format date
 */
function formatDate($date, $format = DATE_FORMAT)
{
    if (empty($date))
        return '-';
    return date($format, strtotime($date));
}

/**
 * Display flash message
 */
function setFlashMessage($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/** 
 * Get and clear flash message
 */
function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Function to truncate text
function truncate($string, $limit = 30, $end = '...') {
    if (mb_strlen($string) <= $limit) {
        return $string;
    }
    return mb_substr($string, 0, $limit) . $end;
}

// Load helpers
autoloadHelpers();
?>