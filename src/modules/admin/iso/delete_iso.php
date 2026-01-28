<?php
// Path: src/modules/admin/iso/delete_iso.php
require_once '../../../config/config.php';
requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $id = $_POST['id'] ?? '';
    $redirect_to = $_POST['redirect_to'] ?? ''; // New Parameter

    if (empty($type) || empty($id)) {
        setFlashMessage('error', 'Invalid request parameters.');
        header('Location: index.php');
        exit();
    }

    $db = new Database();

    try {
        if ($type === 'section') {
            $db->query("DELETE FROM section WHERE sec_ID = :id", [':id' => $id]);
        } elseif ($type === 'requirement') {
            $db->query("DELETE FROM sub_req WHERE sub_req_ID = :id", [':id' => $id]);
        } elseif ($type === 'control') {
            $db->query("DELETE FROM sub_con WHERE sub_con_ID = :id", [':id' => $id]);
        } else {
            throw new Exception("Invalid item type.");
        }

        setFlashMessage('success', ucfirst($type) . ' deleted successfully.');

    } catch (Exception $e) {
        error_log("Delete ISO Error: " . $e->getMessage());
        
        // Check for Foreign Key Constraint violation (Code 23000)
        // Note: PDO code behavior might vary slightly depending on driver, 
        // checking the message for common constraint keywords is a safe fallback.
        if (strpos($e->getMessage(), 'Constraint violation') !== false || $e->getCode() == 23000) {
             setFlashMessage('error', 'Cannot delete this item because it is linked to other records. Please delete dependent items first.');
        } else {
             setFlashMessage('error', 'Database Error: Could not delete item. ' . $e->getMessage());
        }
    }

    // 1. Check if a specific return URL was provided
    if (!empty($redirect_to)) {
        header("Location: " . $redirect_to);
        exit();
    }

    // 2. Default Redirect Logic
    $tabMap = [
        'section' => 'sections',
        'requirement' => 'requirements',
        'control' => 'controls'
    ];
    $redirectTab = $tabMap[$type] ?? 'sections';
    
    header("Location: index.php?tab=" . $redirectTab);
    exit();
}
?>