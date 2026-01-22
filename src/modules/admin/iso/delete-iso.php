<?php
require_once '../../../config/config.php';
requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $id = $_POST['id'] ?? '';

    if (empty($type) || empty($id)) {
        setFlashMessage('error', 'Invalid request parameters.');
        header('Location: index.php');
        exit();
    }

    $db = new Database();

    try {
        if ($type === 'section') {
            // Deleting a section will automatically:
            // 1. DELETE all child Requirements (sub_req) via Cascade
            // 2. DELETE all child Controls (sub_con) via Cascade
            // 3. Set domain.sec_ID to NULL via Set Null
            $db->query("DELETE FROM section WHERE sec_ID = :id", [':id' => $id]);

        } elseif ($type === 'requirement') {
            // Deleting a requirement just removes the row.
            // The link to Criteria is stored here, so it just disappears safely.
            $db->query("DELETE FROM sub_req WHERE sub_req_ID = :id", [':id' => $id]);

        } elseif ($type === 'control') {
            // Deleting a control will automatically:
            // 1. DELETE mappings in 'element_control' via Cascade
            $db->query("DELETE FROM sub_con WHERE sub_con_ID = :id", [':id' => $id]);

        } else {
            throw new Exception("Invalid item type.");
        }

        setFlashMessage('success', ucfirst($type) . ' deleted successfully.');

    } catch (Exception $e) {
        // This catches DB errors (like foreign key failures if Cascade isn't set up right)
        error_log("Delete ISO Error: " . $e->getMessage());
        setFlashMessage('error', 'Database Error: Could not delete item. ' . $e->getMessage());
    }

    // Redirect back to the correct tab
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