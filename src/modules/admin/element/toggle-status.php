<?php
/**
 * Handles the POST request to toggle the status of an element.
 * This file should be saved in the same directory as element/index.php (likely modules/admin/criteria/)
 */

require_once '../../../config/config.php';

// Only admin can access
requireRole(['admin']);

// Ensure POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method. Status change requires a POST request.');
    // Attempt to redirect back, if criteria_id is available, otherwise fall back
    $criteria_id = $_POST['criteria_id'] ?? null;
    $redirect_url = $criteria_id ? 'view-elements.php?id=' . urlencode($criteria_id) : '../domain/index.php';
    header('Location: ' . $redirect_url);
    exit();
}

// Validate required POST parameters (element_id, new_status, and criteria_id for redirection)
if (!isset($_POST['element_id']) || !isset($_POST['new_status']) || !isset($_POST['criteria_id'])) {
    setFlashMessage('error', 'Missing element ID, new status, or criteria ID parameter.');
    header('Location: ../domain/index.php'); // Fallback redirect
    exit();
}

// Sanitize inputs
$element_id = trim($_POST['element_id']);
$new_status = trim($_POST['new_status']);
$criteria_id = trim($_POST['criteria_id']); // Used for redirection

// Validate new status
if (!in_array($new_status, ['Active', 'Inactive'])) {
    setFlashMessage('error', 'Invalid status value provided.');
    header('Location: view-elements.php?id=' . urlencode($criteria_id));
    exit();
}

$db = new Database();

try {
    // Update the status of the element
    $updateQuery = "UPDATE element SET status = ?, updated_at = NOW() WHERE element_ID = ?";
    $success = $db->query($updateQuery, [$new_status, $element_id]);

    if ($success) {
        $action = ($new_status === 'Active') ? 'activated' : 'deactivated';
        $safe_id = htmlspecialchars($element_id);
        setFlashMessage('success', "Element has been successfully {$action}.");
    } else {
        setFlashMessage('error', 'Failed to update element status in the database.');
    }
} catch (Exception $e) {
    error_log("Database Error in criteria/toggle-element-status.php: " . $e->getMessage());
    setFlashMessage('danger', 'An unexpected error occurred during the status update. Please try again.');
}

// Redirect back to the element view page using the criteria_id
header('Location: view-element.php?id=' . urlencode($criteria_id));
exit();
?>