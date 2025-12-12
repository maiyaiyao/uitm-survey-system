<?php
/**
 * Handles the POST request to toggle the status of a criteria.
 * This file must be saved in the same directory as criteria/index.php
 */

require_once '../../../config/config.php';

// Only admin can access
requireRole(['admin']);

// Ensure POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method. Status change requires a POST request.');
    header('Location: index.php');
    exit();
}

// Validate required POST parameters
if (!isset($_POST['criteria_id']) || !isset($_POST['new_status'])) {
    setFlashMessage('error', 'Missing criteria ID or new status parameter.');
    header('Location: index.php');
    exit();
}

// Sanitize inputs
$criteria_id = trim($_POST['criteria_id']);
$new_status = trim($_POST['new_status']);

// Validate new status
if (!in_array($new_status, ['Active', 'Inactive'])) {
    setFlashMessage('error', 'Invalid status value provided.');
    header('Location: index.php');
    exit();
}

$db = new Database();

try {
    // Update the status of the criteria
    $updateQuery = "UPDATE criteria SET status = ? WHERE criteria_ID = ?";
    $success = $db->query($updateQuery, [$new_status, $criteria_id]);

    if ($success) {
        $action = ($new_status === 'Active') ? 'activated' : 'deactivated';
        $safe_id = htmlspecialchars($criteria_id);
        setFlashMessage('success', "Criteria has been successfully {$action}.");
    } else {
        setFlashMessage('error', 'Failed to update criteria status in the database.');
    }
} catch (Exception $e) {
    error_log("Database Error in criteria/toggle-status.php: " . $e->getMessage());
    setFlashMessage('error', 'An unexpected error occurred during the status update. Please try again.');
}

// Redirect back to criteria list
$domain_id = $_POST['domain_id'] ?? null;
header('Location: ../criteria/view-criteria.php?id=' . urlencode($domain_id));
exit();
?>