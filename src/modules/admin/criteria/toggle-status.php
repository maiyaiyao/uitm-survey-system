<?php
/**
 * Handles the POST request to toggle the status of a criteria.
 * This file must be saved in the same directory as criteria/index.php
 */

require_once '../../../config/config.php';
requireRole(['admin']);

// 1. Determine the Redirect URL immediately (defaults to Referer "Go Back")
$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';

// 2. Validate Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method. Status change requires a POST request.');
    header("Location: " . $redirect_url);
    exit();
}

// 3. Validate & Sanitize Inputs
if (!isset($_POST['criteria_id']) || !isset($_POST['new_status'])) {
    setFlashMessage('error', 'Missing criteria ID or new status parameter.');
    header("Location: " . $redirect_url);
    exit();
}

$criteria_id = trim($_POST['criteria_id']);
$new_status  = trim($_POST['new_status']);
$domain_id   = $_POST['domain_id'] ?? ''; 

// Validate status value
if (!in_array($new_status, ['Active', 'Inactive'])) {
    setFlashMessage('error', 'Invalid status value provided.');
    header("Location: " . $redirect_url);
    exit();
}

$db = new Database();

try {
    // 4. Logic Validation: Check Parent Domain Status
    // Even if we want to cascade Activate, we MUST ensuring the Parent Domain is Active first.
    if ($new_status === 'Active') {
        $parent = $db->fetchOne(
            "SELECT d.status FROM domain d 
             JOIN criteria c ON d.domain_ID = c.domain_ID 
             WHERE c.criteria_ID = ?", 
            [$criteria_id]
        );
        
        if ($parent && $parent['status'] !== 'Active') {
            setFlashMessage('danger', 'Cannot activate this Criteria because its Domain is Inactive.');
            header("Location: " . $redirect_url);
            exit();
        }
    }

    // 5. Update Status in Database
    $updateQuery = "UPDATE criteria SET status = ? WHERE criteria_ID = ?";
    $success = $db->query($updateQuery, [$new_status, $criteria_id]);

    if ($success) {
        // === CASCADING LOGIC ===
        if ($new_status === 'Inactive') {
            // Deactivate all its Elements
            $db->query("UPDATE element SET status = 'Inactive' WHERE criteria_ID = ?", [$criteria_id]);
            
        } elseif ($new_status === 'Active') {
            // === NEW: ACTIVATION CASCADE ===
            // Activate all its Elements
            $db->query("UPDATE element SET status = 'Active' WHERE criteria_ID = ?", [$criteria_id]);
        }
        // =======================

        $action = ($new_status === 'Active') ? 'activated' : 'deactivated';
        setFlashMessage('success', "Criteria has been successfully {$action}. " . 
                        "All related elements have also been {$action}.");
    } else {
        setFlashMessage('error', 'Failed to update criteria status in the database.');
    }

} catch (Exception $e) {
    error_log("Database Error in criteria/toggle-status.php: " . $e->getMessage());
    setFlashMessage('error', 'An unexpected error occurred during the status update.');
}

// 6. Final Redirect
if (empty($_SERVER['HTTP_REFERER'])) {
    if (!empty($domain_id)) {
        header('Location: ../criteria/view-criteria.php?id=' . urlencode($domain_id));
    } else {
        header('Location: index.php');
    }
} else {
    header("Location: " . $redirect_url);
}
exit();
?>