<?php
require_once '../../../config/config.php';
requireRole(['admin']);

header('Content-Type: application/json');

$db = new Database();

// Check if we want ALL active users
if (isset($_GET['all']) && $_GET['all'] == '1') {
    $sql = "SELECT primary_email, full_name FROM user WHERE status = 'Active' ORDER BY full_name ASC";
    $results = $db->fetchAll($sql);
    echo json_encode($results);
    exit;
}

// Normal search behavior
$query = $_GET['q'] ?? '';

if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT primary_email, full_name FROM user 
        WHERE (primary_email LIKE :q1 OR full_name LIKE :q2) AND status = 'Active'
        LIMIT 10";

$results = $db->fetchAll($sql, [
    ':q1' => "%$query%",
    ':q2' => "%$query%"
]);

echo json_encode($results);
?>