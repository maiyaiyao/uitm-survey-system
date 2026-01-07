<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';

if (!$type || !$id) {
    header("Location: index.php");
    exit;
}

$pageTitle = "Map " . ucfirst($type);
$targetItem = [];
$mappedItems = [];
$availableItems = [];

// ---------------------------------------------------------
// LOGIC HANDLER
// ---------------------------------------------------------

// 1. FETCH TARGET ISO ITEM
if ($type === 'section') {
    $targetItem = $db->fetchOne("SELECT sec_ID as id, sec_name as name, 'Section' as label FROM section WHERE sec_ID = :id", [':id' => $id]);
    
    // Fetch Mapped Domains
    $mappedItems = $db->fetchAll("SELECT domain_ID as link_id, domain_ID as display_id, domain_name as display_name FROM domain WHERE sec_ID = :id", [':id' => $id]);
    
    // Fetch Available Domains (All active domains)
    $availableItems = $db->fetchAll("SELECT domain_ID as id, domain_name as name FROM domain WHERE status = 'Active' ORDER BY domain_ID ASC");

} elseif ($type === 'requirement') {
    $targetItem = $db->fetchOne("SELECT sub_req_ID as id, sub_req_name as name, 'Requirement' as label FROM sub_req WHERE sub_req_ID = :id", [':id' => $id]);

    // Fetch Mapped Criteria (1-to-1 via sub_req.criteria_ID)
    $mappedItems = $db->fetchAll("
        SELECT c.criteria_ID as link_id, c.criteria_ID as display_id, c.criteria_name as display_name 
        FROM sub_req sr 
        JOIN criteria c ON sr.criteria_ID = c.criteria_ID 
        WHERE sr.sub_req_ID = :id", 
        [':id' => $id]
    );

    // Fetch Available Criteria
    $availableItems = $db->fetchAll("SELECT criteria_ID as id, criteria_name as name FROM criteria WHERE status = 'Active' ORDER BY criteria_ID ASC");

} elseif ($type === 'control') {
    $targetItem = $db->fetchOne("SELECT sub_con_ID as id, sub_con_name as name, 'Control' as label FROM sub_con WHERE sub_con_ID = :id", [':id' => $id]);

    // Fetch Mapped Elements (Many-to-Many via element_control)
    $mappedItems = $db->fetchAll("
        SELECT ec.id as link_id, e.element_ID as display_id, e.element_name as display_name 
        FROM element_control ec 
        JOIN element e ON ec.element_ID = e.element_ID 
        WHERE ec.sub_con_ID = :id", 
        [':id' => $id]
    );

    // Fetch Available Elements
    $availableItems = $db->fetchAll("SELECT element_ID as id, element_name as name FROM element WHERE status = 'Active' ORDER BY element_ID ASC");
}

if (!$targetItem) {
    die("Item not found.");
}

// ---------------------------------------------------------
// FORM SUBMISSION HANDLER
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ADD / UPDATE MAPPING
    if (isset($_POST['add_mapping'])) {
        $selectedID = $_POST['selected_id'];

        if ($type === 'section') {
            // Update Domain to point to this Section
            $db->query("UPDATE domain SET sec_ID = :sec WHERE domain_ID = :dom", [':sec' => $id, ':dom' => $selectedID]);
            setFlashMessage("success", "Domain linked successfully.");

        } elseif ($type === 'requirement') {
            // Update Sub Req to point to Criteria
            // Note: Since sub_req has the FK, we update the current ISO row
            $db->query("UPDATE sub_req SET criteria_ID = :crit WHERE sub_req_ID = :req", [':crit' => $selectedID, ':req' => $id]);
            setFlashMessage("success", "Criteria linked successfully.");

        } elseif ($type === 'control') {
            // Insert into link table
            // Check duplicate first
            $exists = $db->fetchOne("SELECT id FROM element_control WHERE sub_con_ID = :con AND element_ID = :elem", [':con' => $id, ':elem' => $selectedID]);
            if (!$exists) {
                $db->query("INSERT INTO element_control (sub_con_ID, element_ID) VALUES (:con, :elem)", [':con' => $id, ':elem' => $selectedID]);
                setFlashMessage("success", "Element linked successfully.");
            } else {
                setFlashMessage("error", "Mapping already exists.");
            }
        }
        header("Location: map_iso.php?type=$type&id=" . urlencode($id));
        exit;
    }

    // REMOVE MAPPING
    if (isset($_POST['remove_mapping'])) {
        $linkID = $_POST['link_id']; // For Control, this is the PK. For others, it's the ID of the mapped item.

        if ($type === 'section') {
            // Unlink Domain
            $db->query("UPDATE domain SET sec_ID = NULL WHERE domain_ID = :dom", [':dom' => $linkID]);
        } elseif ($type === 'requirement') {
            // Unlink Criteria (Set FK in sub_req to NULL)
            $db->query("UPDATE sub_req SET criteria_ID = NULL WHERE sub_req_ID = :req", [':req' => $id]);
        } elseif ($type === 'control') {
            // Delete from link table
            $db->query("DELETE FROM element_control WHERE id = :pk", [':pk' => $linkID]);
        }
        setFlashMessage("success", "Mapping removed.");
        header("Location: map_iso.php?type=$type&id=" . urlencode($id));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { background-color: #f8f9fa; height: 100%; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-auto">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>

            <div class="col main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php?tab=<?php echo $type; ?>s">ISO List</a></li>
                                <li class="breadcrumb-item active"><?php echo $pageTitle; ?></li>
                            </ol>
                        </nav>
                        <a href="index.php?tab=<?php echo $type; ?>s" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                    </div>

                    <?php 
                    $msg = getFlashMessage();
                    if ($msg): 
                        $alertClass = ($msg['type'] === 'error') ? 'danger' : $msg['type'];
                    ?>
                        <div class="alert alert-<?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
                            <?php echo $msg['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card mb-4 border-start border-4 border-primary">
                        <div class="card-body">
                            <h6 class="text-uppercase text-muted fw-bold small">ISO <?php echo $type; ?></h6>
                            <h3 class="mb-2 text-primary"><?php echo htmlspecialchars($targetItem['id']); ?></h3>
                            <h5 class="mb-0 text-dark"><?php echo htmlspecialchars($targetItem['name']); ?></h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-5 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0 fw-bold">Link to Internal Structure</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="add_mapping" value="1">
                                        
                                        <div class="mb-3">
                                            <label class="form-label text-muted small fw-bold">Select 
                                                <?php 
                                                    if($type == 'section') echo 'Domain';
                                                    elseif($type == 'requirement') echo 'Criteria';
                                                    else echo 'Element'; 
                                                ?>
                                            </label>
                                            <select name="selected_id" class="form-select" required>
                                                <option value="">-- Choose --</option>
                                                <?php foreach ($availableItems as $item): ?>
                                                    <option value="<?php echo $item['id']; ?>">
                                                        <?php echo htmlspecialchars($item['id'] . ' - ' . $item['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">
                                                Select the internal item to link with this ISO <?php echo $type; ?>.
                                            </div>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-link-45deg"></i> Add Mapping
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0 fw-bold">Current Links</h6>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Internal Name</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($mappedItems)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center py-4 text-muted small">
                                                        No mappings found.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($mappedItems as $map): ?>
                                                    <tr>
                                                        <td class="fw-bold"><?php echo htmlspecialchars($map['display_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($map['display_name']); ?></td>
                                                        <td class="text-end">
                                                            <form method="POST" onsubmit="return confirm('Remove this link?');">
                                                                <input type="hidden" name="remove_mapping" value="1">
                                                                <input type="hidden" name="link_id" value="<?php echo $map['link_id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                                    <i class="bi bi-x-circle-fill"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>