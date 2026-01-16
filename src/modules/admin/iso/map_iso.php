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
    
    // Fetch Available Domains (Active & Not assigned to any section)
    $availableItems = $db->fetchAll("SELECT domain_ID as id, domain_name as name FROM domain WHERE status = 'Active' AND sec_ID IS NULL ORDER BY domain_ID ASC");

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
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

// Build a reusable query string
$queryParams = "";
if (!empty($search)) $queryParams .= "&search=" . urlencode($search);
if (!empty($filter)) $queryParams .= "&filter=" . urlencode($filter);

// ---------------------------------------------------------
// DETERMINE LOCK STATUS
// ---------------------------------------------------------
$isLocked = false;
if ($type === 'requirement' && count($mappedItems) > 0) {
    $isLocked = true;
}

// ---------------------------------------------------------
// FORM SUBMISSION HANDLER
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ADD / UPDATE MAPPING
    if (isset($_POST['add_mapping'])) {
        if ($isLocked) {
            setFlashMessage("error", "Action blocked: This item is already mapped.");
            header("Location: map_iso.php?type=$type&id=" . urlencode($id) . $queryParams);
            exit;
        }

        $selectedID = $_POST['selected_id'];

        if ($type === 'section') {
            $db->query("UPDATE domain SET sec_ID = :sec WHERE domain_ID = :dom", [':sec' => $id, ':dom' => $selectedID]);
            setFlashMessage("success", "Domain linked successfully.");

        } elseif ($type === 'requirement') {
            $db->query("UPDATE sub_req SET criteria_ID = :crit WHERE sub_req_ID = :req", [':crit' => $selectedID, ':req' => $id]);
            setFlashMessage("success", "Criteria linked successfully.");

        } elseif ($type === 'control') {
            $exists = $db->fetchOne("SELECT id FROM element_control WHERE sub_con_ID = :con AND element_ID = :elem", [':con' => $id, ':elem' => $selectedID]);
            if (!$exists) {
                $db->query("INSERT INTO element_control (sub_con_ID, element_ID) VALUES (:con, :elem)", [':con' => $id, ':elem' => $selectedID]);
                setFlashMessage("success", "Element linked successfully.");
            } else {
                setFlashMessage("error", "Mapping already exists.");
            }
        }
        header("Location: map_iso.php?type=$type&id=" . urlencode($id) . $queryParams);
        exit;
    }

    // REMOVE MAPPING
    if (isset($_POST['remove_mapping'])) {
        $linkID = $_POST['link_id']; 

        if ($type === 'section') {
            $db->query("UPDATE domain SET sec_ID = NULL WHERE domain_ID = :dom", [':dom' => $linkID]);
        } elseif ($type === 'requirement') {
            $db->query("UPDATE sub_req SET criteria_ID = NULL WHERE sub_req_ID = :req", [':req' => $id]);
        } elseif ($type === 'control') {
            $db->query("DELETE FROM element_control WHERE id = :pk", [':pk' => $linkID]);
        }
        setFlashMessage("success", "Mapping removed.");
        header("Location: map_iso.php?type=$type&id=" . urlencode($id) . $queryParams);
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
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .btn-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; }
        .btn-gradient-primary:hover { opacity: 0.9; color: white; }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            <div class="col-auto">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>

            <div class="col main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-secondary text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a></li>
                            <li class="breadcrumb-item"><a href="index.php?tab=<?php echo $type; ?>s" class="text-secondary text-decoration-none">ISO Standards</a></li>
                            <li class="breadcrumb-item active text-dark"><?php echo $pageTitle; ?></li>
                        </ol>
                    </nav>

                    <?php if ($msg = getFlashMessage()): ?>
                        <div class="alert alert-<?php echo ($msg['type'] === 'error') ? 'danger' : $msg['type']; ?> alert-dismissible fade show">
                            <?php echo $msg['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-3 me-3 text-primary">
                                    <i class="bi bi-diagram-3 fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="text-uppercase text-muted fw-bold small mb-1">Mapping ISO <?php echo ucfirst($type); ?></h6>
                                    <h4 class="mb-1 text-dark fw-bold"><?php echo htmlspecialchars($targetItem['id']); ?></h4>
                                    <p class="mb-0 text-secondary"><?php echo htmlspecialchars($targetItem['name']); ?></p>
                                </div>
                                <div class="ms-auto">
                                    <a href="index.php?tab=<?php echo $type; ?>s<?php echo $queryParams; ?>" class="btn btn-light text-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Back to List
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="card h-100 shadow-sm rounded-4">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-link me-2 text-primary"></i>Link Internal Item</h6>
                                </div>
                                <div class="card-body p-4 d-flex flex-column">
                                    
                                    <?php if ($isLocked): ?>
                                        <div class="text-center py-5 my-auto">
                                            <div class="mb-3 text-warning opacity-75">
                                                <i class="bi bi-lock-fill display-3"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark">Mapping Locked</h5>
                                            <p class="text-muted small px-3">
                                                This requirement is already mapped to a Criteria.<br>
                                                Please remove the existing link from the list on the right to assign a new one.
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <form method="POST">
                                            <input type="hidden" name="add_mapping" value="1">
                                            
                                            <div class="mb-4">
                                                <label class="form-label fw-bold small text-uppercase text-muted">Select 
                                                    <?php 
                                                        if($type == 'section') echo 'Domain';
                                                        elseif($type == 'requirement') echo 'Criteria';
                                                        else echo 'Element'; 
                                                    ?>
                                                </label>
                                                <select name="selected_id" class="form-select" required>
                                                    <option value="">-- Choose Item --</option>
                                                    <?php foreach ($availableItems as $item): ?>
                                                        <option value="<?php echo $item['id']; ?>">
                                                            <?php echo htmlspecialchars($item['id'] . ' - ' . $item['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="form-text mt-2">
                                                    Select the internal compliance item to map with this ISO <?php echo $type; ?>.
                                                </div>
                                            </div>

                                            <div class="d-grid mt-auto">
                                                <button type="submit" class="btn btn-gradient-primary py-2">
                                                    <i class="bi bi-plus-lg me-2"></i> Add Mapping
                                                </button>
                                            </div>
                                        </form>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card h-100 shadow-sm rounded-4">
                                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold">Current Links</h6>
                                    <span class="badge bg-light text-dark border"><?php echo count($mappedItems); ?> Item(s)</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 text-muted small fw-bold text-uppercase" style="width: 20%">ID</th>
                                                <th class="text-muted small fw-bold text-uppercase">Internal Name</th>
                                                <th class="text-end pe-4 text-muted small fw-bold text-uppercase" style="width: 15%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($mappedItems)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center py-5 text-muted">
                                                        <i class="bi bi-link-45deg fs-1 d-block mb-2 text-light-emphasis"></i>
                                                        No mappings found for this item.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($mappedItems as $map): ?>
                                                    <tr>
                                                        <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($map['display_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($map['display_name']); ?></td>
                                                        <td class="text-end pe-4">
                                                            <form method="POST" onsubmit="return confirm('Are you sure you want to remove this link?');">
                                                                <input type="hidden" name="remove_mapping" value="1">
                                                                <input type="hidden" name="link_id" value="<?php echo $map['link_id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-light text-danger border hover-shadow" title="Remove Link">
                                                                    <i class="bi bi-trash"></i>
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