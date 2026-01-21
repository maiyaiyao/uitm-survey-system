<?php
require_once '../../../config/config.php';
requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $element_id = $_POST['element_id'] ?? '';

    if (empty($element_id)) {
        setFlashMessage('error', 'Invalid element ID.');
        header('Location: index.php');
        exit();
    }

    $db = new Database();

    try {
        // 1. Double-Check: Ensure element is not used in any responses (Answered Surveys)
        $usage = $db->fetchOne(
            "SELECT COUNT(*) as count FROM response WHERE element_ID = :id", 
            [':id' => $element_id]
        );

        if ($usage['count'] > 0) {
            setFlashMessage('warning', 'Cannot delete: This element has already been answered in a survey.');
            header('Location: index.php');
            exit();
        }

        // 2. Perform Deletion
        // Note: Related scores in 'score_element' will cascade delete if DB is configured, 
        // but we can manually delete them first to be safe if needed.
        // Assuming database ON DELETE CASCADE is set for child tables, otherwise:
        // $db->query("DELETE FROM score_element WHERE element_ID = :id", [':id' => $element_id]);
        
        $db->query("DELETE FROM element WHERE element_ID = :id", [':id' => $element_id]);

        setFlashMessage('success', 'Element deleted successfully.');

    } catch (Exception $e) {
        error_log("Delete Element Error: " . $e->getMessage());
        setFlashMessage('error', 'An error occurred while deleting the element.');
    }

    header('Location: index.php');
    exit();
}