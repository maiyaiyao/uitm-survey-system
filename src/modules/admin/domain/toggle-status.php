<?php
require_once '../../../config/config.php';

// Ensure only admins can access this script
requireRole(['admin']);

// Check if the request method is POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method. Status change requires a POST request.');
    header('Location: index.php');
    exit();
}

// Validate input
if (!isset($_POST['domain_id']) || !isset($_POST['new_status'])) {
    setFlashMessage('error', 'Missing domain ID or new status parameter.');
    header('Location: index.php');
    exit();
}

$domain_id = trim($_POST['domain_id']); 
$new_status = trim($_POST['new_status']);

if (!in_array($new_status, ['Active', 'Inactive'])) {
    setFlashMessage('error', 'Invalid status value provided.');
    header('Location: index.php');
    exit();
}

$db = new Database();

try {
    // 1. Update the Domain status
    $updateQuery = "UPDATE domain SET status = ? WHERE domain_ID = ?";
    $success = $db->query($updateQuery, [$new_status, $domain_id]);

    if ($success) {
        // === CASCADING LOGIC ===
        if ($new_status === 'Inactive') {
            // A. Deactivate all Criteria under this Domain
            $db->query("UPDATE criteria SET status = 'Inactive' WHERE domain_ID = ?", [$domain_id]);
            
            // B. Deactivate all Elements under this Domain
            $cascadeElements = "UPDATE element e 
                                INNER JOIN criteria c ON e.criteria_ID = c.criteria_ID 
                                SET e.status = 'Inactive' 
                                WHERE c.domain_ID = ?";
            $db->query($cascadeElements, [$domain_id]);

        } elseif ($new_status === 'Active') {
            // A. Activate all Criteria under this Domain
            $db->query("UPDATE criteria SET status = 'Active' WHERE domain_ID = ?", [$domain_id]);
            
            // B. Activate all Elements under this Domain
            $cascadeElements = "UPDATE element e 
                                INNER JOIN criteria c ON e.criteria_ID = c.criteria_ID 
                                SET e.status = 'Active' 
                                WHERE c.domain_ID = ?";
            $db->query($cascadeElements, [$domain_id]);
        }
        // =======================

        $action = ($new_status === 'Active') ? 'activated' : 'deactivated';
        setFlashMessage('success', "Domain has been successfully {$action}. " . 
                        "All related criteria and elements have also been {$action}.");
    } else {
        setFlashMessage('error', 'Failed to update domain status.');
    }
} catch (Exception $e) {
    error_log("Database Error in domain/toggle-status.php: " . $e->getMessage());
    setFlashMessage('error', 'An unexpected error occurred during the status update.');
}

header('Location: index.php');
exit();
?>