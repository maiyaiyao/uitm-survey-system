<?php

require_once '../../../config/config.php';
requireRole(['admin']);

// 1. Determine the Redirect URL
$redirect_url = $_SERVER['HTTP_REFERER'] ?? null;
$criteria_id = $_POST['criteria_id'] ?? '';

if (!$redirect_url) {
    if (!empty($criteria_id)) {
        $redirect_url = 'view-element.php?id=' . urlencode($criteria_id);
    } else {
        $redirect_url = 'index.php';
    }
}

// 2. Validate Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method.');
    header('Location: ' . $redirect_url);
    exit();
}

// 3. Validate Parameters
if (!isset($_POST['element_id']) || !isset($_POST['new_status'])) {
    setFlashMessage('error', 'Missing parameters.');
    header('Location: ' . $redirect_url);
    exit();
}

$element_id = trim($_POST['element_id']);
$new_status = trim($_POST['new_status']);

if (!in_array($new_status, ['Active', 'Inactive'])) {
    setFlashMessage('error', 'Invalid status value.');
    header('Location: ' . $redirect_url);
    exit();
}

$db = new Database();

try {

    if ($new_status === 'Active') {
        $parent = $db->fetchOne(
            "SELECT c.status FROM criteria c 
             JOIN element e ON c.criteria_ID = e.criteria_ID 
             WHERE e.element_ID = ?", 
            [$element_id]
        );
        
        if ($parent && $parent['status'] !== 'Active') {
            setFlashMessage('danger', 'Cannot activate this Element because its Criteria is Inactive.');
            header('Location: ' . $redirect_url);
            exit();
        }
    }

    // 4. Update the Element status
    $updateQuery = "UPDATE element SET status = ?, updated_at = NOW() WHERE element_ID = ?";
    $success = $db->query($updateQuery, [$new_status, $element_id]);

    if ($success) {
        $action = ($new_status === 'Active') ? 'activated' : 'deactivated';
        setFlashMessage('success', "Element has been successfully {$action}.");
    } else {
        setFlashMessage('error', 'Failed to update element status.');
    }
} catch (Exception $e) {
    error_log("Database Error in element/toggle-status.php: " . $e->getMessage());
    setFlashMessage('danger', 'An unexpected error occurred.');
}

header('Location: ' . $redirect_url);
exit();
?>