<?php
require_once '../../../config/config.php';
requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $criteria_id = $_POST['criteria_id'] ?? null;

    if ($criteria_id) {
        $db = new Database();

        try {
            // 1. SECURITY CHECK: Check if this Criteria is used in any survey answers.
            $checkSql = "
                SELECT COUNT(*) as usage_count 
                FROM response r
                JOIN element e ON r.element_ID = e.element_ID
                WHERE e.criteria_ID = :id
            ";
            
            $result = $db->fetchOne($checkSql, [':id' => $criteria_id]);

            if ($result['usage_count'] > 0) {
                setFlashMessage('error', "Cannot delete Criteria. It contains " . $result['usage_count'] . " survey records.");
            } else {
                // 2. SAFE TO DELETE
                // Start Transaction
                $db->beginTransaction();

                // Delete child Elements first
                $db->query("DELETE FROM element WHERE criteria_ID = :id", [':id' => $criteria_id]);

                // Delete the Criteria itself
                $db->query("DELETE FROM criteria WHERE criteria_ID = :id", [':id' => $criteria_id]);

                // Commit
                try {
                    $db->commit();
                    setFlashMessage('success', "Criteria and its elements deleted successfully.");
                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            }

        } catch (Exception $e) {
            // Safe Rollback if transaction exists
            try { $db->rollBack(); } catch (Exception $ex) {}
            setFlashMessage('error', "Deletion failed: " . $e->getMessage());
        }
    }
}

$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';
$redirect_url = filter_var($redirect_url, FILTER_SANITIZE_URL);
header("Location: " . $redirect_url);
exit();
?>