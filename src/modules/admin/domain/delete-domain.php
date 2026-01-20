<?php
// modules/admin/domain/delete-domain.php
require_once '../../../config/config.php';
requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain_id = $_POST['domain_id'] ?? null;

    if ($domain_id) {
        $db = new Database();

        try {
            // 1. SECURITY CHECK
            // Use your actual table name for survey results (e.g., 'audit_results', 'answers')
            $checkSql = "
                SELECT COUNT(*) as usage_count 
                FROM response r
                JOIN element e ON r.element_id = e.element_ID
                JOIN criteria c ON e.criteria_ID = c.criteria_ID
                WHERE c.domain_ID = :id
            ";
            
            $result = $db->fetchOne($checkSql, [':id' => $domain_id]);

            if ($result['usage_count'] > 0) {
                setFlashMessage('error', "Cannot delete Domain. It contains " . $result['usage_count'] . " survey records.");
            } else {
                // 2. SAFE TO DELETE
                // Start Transaction
                $db->beginTransaction();

                // Delete Elements belonging to this Domain (via Criteria)
                $db->query("DELETE e FROM element e 
                            INNER JOIN criteria c ON e.criteria_ID = c.criteria_ID 
                            WHERE c.domain_ID = :id", [':id' => $domain_id]);

                // Delete Criteria belonging to this Domain
                $db->query("DELETE FROM criteria WHERE domain_ID = :id", [':id' => $domain_id]);

                // Delete the Domain
                $db->query("DELETE FROM domain WHERE domain_ID = :id", [':id' => $domain_id]);

                // Commit changes
                $db->commit(); 
                
                setFlashMessage('success', "Domain and its structure deleted successfully.");
            }

        } catch (Exception $e) {
            // --- FIX STARTS HERE ---
            // We removed 'if ($db->inTransaction())' because your class doesn't support it.
            // Instead, we try to rollback and ignore the error if no transaction was active.
            try {
                $db->rollBack();
            } catch (Exception $rollbackEx) {
                // Silently ignore "no active transaction" errors
            }
            // --- FIX ENDS HERE ---

            setFlashMessage('error', "Deletion failed: " . $e->getMessage());
        }
    }
}

$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';
// Sanitize header to prevent injection
$redirect_url = filter_var($redirect_url, FILTER_SANITIZE_URL);

header("Location: " . $redirect_url);
exit();
?>