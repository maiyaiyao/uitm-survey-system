<?php
// get_iso_details.php
require_once '../../../config/config.php'; // Adjust path as needed
requireRole(['admin']);

$db = new Database();
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';

if (!$type || !$id) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$data = [];
$mappings = [];

try {
    if ($type === 'section') {
        // Fetch Section Details
        $data = $db->fetchOne("SELECT sec_ID as id, sec_name as name FROM section WHERE sec_ID = :id", [':id' => $id]);
        
        // Fetch Mapped Domains
        $mappings = $db->fetchAll("SELECT domain_ID as id, domain_name as name FROM domain WHERE sec_ID = :id", [':id' => $id]);

    } elseif ($type === 'requirement') {
        // Fetch Requirement Details
        $data = $db->fetchOne("SELECT sub_req_ID as id, sub_req_name as name, criteria_ID FROM sub_req WHERE sub_req_ID = :id", [':id' => $id]);
        
        // Fetch Mapped Criteria (Single)
        if ($data['criteria_ID']) {
            $mappings = $db->fetchAll("SELECT criteria_ID as id, criteria_name as name FROM criteria WHERE criteria_ID = :mid", [':mid' => $data['criteria_ID']]);
        }

    } elseif ($type === 'control') {
        // Fetch Control Details
        $data = $db->fetchOne("SELECT sub_con_ID as id, sub_con_name as name FROM sub_con WHERE sub_con_ID = :id", [':id' => $id]);
        
        // Fetch Mapped Elements (Many-to-Many)
        $mappings = $db->fetchAll("
            SELECT e.element_ID as id, e.element_name as name 
            FROM element_control ec 
            JOIN element e ON ec.element_ID = e.element_ID 
            WHERE ec.sub_con_ID = :id", 
            [':id' => $id]
        );
    }

    echo json_encode([
        'item' => $data,
        'mappings' => $mappings,
        'type' => ucfirst($type)
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>