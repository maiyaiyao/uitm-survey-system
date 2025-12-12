<?php
/**
 * Handles the POST request to toggle the status of a domain.
 * This file must be saved in the same directory as index.php
 */

// 1. Include the configuration file for database connection and roles
require_once '../../../config/config.php';

// Ensure only admins can access this script
requireRole(['admin']);

// Check if the request method is POST. If not, redirect immediately.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method. Status change requires a POST request.');
    header('Location: index.php');
    exit();
}

// 2. Validate and sanitize input
if (!isset($_POST['domain_id']) || !isset($_POST['new_status'])) {
    setFlashMessage('error', 'Missing domain ID or new status parameter.');
    header('Location: index.php');
    exit();
}

// Sanitize input
// IMPORTANT: domain_ID is now treated as a string (VARCHAR) based on the domain.sql schema
$domain_id = trim($_POST['domain_id']); 
$new_status = trim($_POST['new_status']);

// Basic status validation
if (!in_array($new_status, ['Active', 'Inactive'])) {
    setFlashMessage('error', 'Invalid status value provided.');
    header('Location: index.php');
    exit();
}

$db = new Database();

try {
    // 3. Prepare and execute the database update query
    // The query uses the correct column name (status) and primary key (domain_ID)
    $updateQuery = "UPDATE domain SET status = ? WHERE domain_ID = ?";

    // Pass parameters as an array. The Database class should handle prepared statements.
    $success = $db->query($updateQuery, [$new_status, $domain_id]);

    if ($success) {
        $action = ($new_status === 'Active') ? 'activated' : 'deactivated';
        // Using htmlspecialchars to safely display the ID in the message
        $safe_domain_id = htmlspecialchars($domain_id); 
        setFlashMessage('success', "Domain has been successfully {$action}.");
    } else {
        setFlashMessage('error', 'Failed to update domain status in the database. Check your database connection and query.');
    }
} catch (Exception $e) {
    // Log the error and set a general user message
    error_log("Database Error in toggle-status.php: " . $e->getMessage());
    setFlashMessage('error', 'An unexpected error occurred during the status update. Please try again.');
}

// 4. Redirect back to the domain list
header('Location: index.php');
exit();
?>
