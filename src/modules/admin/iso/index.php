<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$active_tab = $_GET['tab'] ?? 'sections';
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

// --- Fetch Filter Options (All Sections) ---
$all_sections = $db->fetchAll("SELECT * FROM section ORDER BY 
    CASE WHEN sec_ID REGEXP '^[0-9]+$' THEN 0 ELSE 1 END,
    CAST(sec_ID AS UNSIGNED), sec_ID ASC");

$data = [];
$params = [];

// --- Logic for Fetching Data based on Tab ---
if ($active_tab === 'sections') {
    $sql = "SELECT * FROM section WHERE 1=1";
    if ($search) {
        $sql .= " AND (sec_name LIKE :s1 OR sec_ID LIKE :s2)";
        $params[':s1'] = "%$search%";
        $params[':s2'] = "%$search%";
    }
    if ($filter) {
        $sql .= " AND type = :filter";
        $params[':filter'] = $filter;
    }
    $sql .= " ORDER BY CASE WHEN sec_ID REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, CAST(sec_ID AS UNSIGNED), sec_ID ASC";
    $data = $db->fetchAll($sql, $params);

} elseif ($active_tab === 'requirements') {
    $sql = "SELECT sr.*, s.sec_name FROM sub_req sr 
            JOIN section s ON sr.sec_ID = s.sec_ID WHERE 1=1";
    if ($search) {
        $sql .= " AND (sr.sub_req_name LIKE :s1 OR sr.sub_req_ID LIKE :s2)";
        $params[':s1'] = "%$search%";
        $params[':s2'] = "%$search%";
    }
    if ($filter) {
        $sql .= " AND sr.sec_ID = :filter";
        $params[':filter'] = $filter;
    }
    $sql .= " ORDER BY CASE WHEN sr.sub_req_ID REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, CAST(sr.sub_req_ID AS UNSIGNED), sr.sub_req_ID ASC";
    $data = $db->fetchAll($sql, $params);

} elseif ($active_tab === 'controls') {
    $sql = "SELECT sc.*, s.sec_name FROM sub_con sc 
            JOIN section s ON sc.sec_ID = s.sec_ID WHERE 1=1";
    if ($search) {
        $sql .= " AND (sc.sub_con_name LIKE :s1 OR sc.sub_con_ID LIKE :s2)";
        $params[':s1'] = "%$search%";
        $params[':s2'] = "%$search%";
    }
    if ($filter) {
        $sql .= " AND sc.sec_ID = :filter";
        $params[':filter'] = $filter;
    }
    $sql .= " ORDER BY 
              SUBSTRING_INDEX(sc.sub_con_ID, '.', 1),
              CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(sc.sub_con_ID, '.', 2), '.', -1) AS UNSIGNED),
              CAST(SUBSTRING_INDEX(sc.sub_con_ID, '.', -1) AS UNSIGNED) ASC";
    $data = $db->fetchAll($sql, $params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISO Standards Management - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Layout & General */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        /* Table Styles */
        .table th { font-weight: 700; background-color: #9d83b7ff; border-bottom: 2px solid #f0f2f5; color: black; text-transform: uppercase; font-size: 0.75rem; padding: 1rem; }
        .table td { padding: 1rem; vertical-align: middle; color: #67748e; font-size: 0.875rem; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }

        /* Custom Tabs Styling */
        .nav-tabs { border-bottom: 2px solid #e9ecef; }
        .nav-tabs .nav-link { border: none; color: #6c757d; font-weight: 600; padding: 1rem 1.5rem; transition: all 0.2s; }
        .nav-tabs .nav-link:hover { color: #5e72e4; }
        .nav-tabs .nav-link.active { color: #5e72e4; border-bottom: 3px solid #5e72e4; background: transparent; }

        /* Card & Button Styles */
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .btn-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; }
        .btn-gradient-primary:hover { color: white; opacity: 0.9; }

        /* Helper for Clickable Rows */
        .cursor-pointer { cursor: pointer; }
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
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a></li>
                            <li class="breadcrumb-item active text-dark">ISO Standards</li>
                        </ol>
                    </nav>

                    <?php 
                    $msg = getFlashMessage();
                    if ($msg): 
                        $alertClass = ($msg['type'] === 'error') ? 'danger' : $msg['type'];
                    ?>
                        <div class="alert alert-<?php echo $alertClass; ?> alert-dismissible fade show shadow-sm" role="alert">
                            <?php if($msg['type'] === 'success'): ?>
                                <i class="bi bi-check-circle-fill me-2"></i>
                            <?php else: ?>
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php endif; ?>
                            <?php echo $msg['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">ISO 27001 Standards</h3>
                            <p class="text-muted mb-0">Manage Sections, Requirements (Clauses), and Annex A Controls.</p>
                        </div>
                        
                        <div class="d-flex gap-2 align-items-center">
                            <form method="GET" class="d-flex gap-2">
                                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($active_tab); ?>">
                                <select name="filter" class="form-select border-0 shadow-sm" style="max-width: 200px;" onchange="this.form.submit()">
                                    <option value="">All <?php echo ($active_tab === 'sections') ? 'Types' : 'Sections'; ?></option>
                                    <?php if ($active_tab === 'sections'): ?>
                                        <option value="Requirement" <?php echo ($filter === 'Requirement') ? 'selected' : ''; ?>>Requirement</option>
                                        <option value="Control" <?php echo ($filter === 'Control') ? 'selected' : ''; ?>>Control</option>
                                    <?php else: ?>
                                        <?php foreach ($all_sections as $sec): ?>
                                            <?php 
                                            $showOption = false;
                                            if ($active_tab === 'requirements' && $sec['type'] === 'Requirement') $showOption = true;
                                            if ($active_tab === 'controls' && $sec['type'] === 'Control') $showOption = true;
                                            if ($showOption): 
                                            ?>
                                                <option value="<?php echo $sec['sec_ID']; ?>" <?php echo ($filter === $sec['sec_ID']) ? 'selected' : ''; ?>>
                                                    <?php echo $sec['sec_ID'] . ' - ' . $sec['sec_name']; ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="input-group shadow-sm">
                                    <input type="text" name="search" class="form-control border-0" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-white bg-white border-0" type="submit"><i class="bi bi-search text-primary"></i></button>
                                    <?php if(!empty($search) || !empty($filter)): ?>
                                        <a href="index.php?tab=<?php echo $active_tab; ?>" class="btn btn-white bg-white border-0 text-danger" title="Clear Filters"><i class="bi bi-x-circle"></i></a>
                                    <?php endif; ?>
                                </div>
                            </form>
                            <a href="add-<?php echo substr($active_tab, 0, -1); ?>.php" class="btn btn-gradient-primary shadow-sm px-4 py-2 rounded-3">
                                <i class="bi bi-plus-lg me-2"></i>Add
                            </a>
                        </div>
                    </div>

                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item"><a class="nav-link <?php echo $active_tab === 'sections' ? 'active' : ''; ?>" href="?tab=sections"><i class="bi bi-folder2-open me-2"></i>Sections</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo $active_tab === 'requirements' ? 'active' : ''; ?>" href="?tab=requirements"><i class="bi bi-list-task me-2"></i>Requirements</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo $active_tab === 'controls' ? 'active' : ''; ?>" href="?tab=controls"><i class="bi bi-shield-lock me-2"></i>Controls</a></li>
                    </ul>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo ucfirst($active_tab); ?> List</h5>
                            <small class="text-muted"><?php echo count($data); ?> records found</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="width: 15%;">ID</th>
                                        <th style="width: 50%;">Name</th>
                                        <?php if($active_tab !== 'sections'): ?>
                                            <th>Section</th>
                                        <?php endif; ?>
                                        <?php if($active_tab == 'sections'): ?>
                                            <th>Type</th>
                                        <?php endif; ?>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($data)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox display-4 d-block mb-2"></i> No records found for this tab.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($data as $row): 
                                            // 1. ENCODE ROW DATA TO JSON
                                            $jsonData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                                        ?>
                                            <tr class="cursor-pointer" onclick="openViewModal(this)" data-row="<?php echo $jsonData; ?>">
                                                <td class="ps-4 fw-bold text-dark">
                                                    <?php 
                                                    if ($active_tab === 'sections') echo htmlspecialchars($row['sec_ID']);
                                                    elseif ($active_tab === 'requirements') echo htmlspecialchars($row['sub_req_ID']);
                                                    else echo htmlspecialchars($row['sub_con_ID']);
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-secondary">
                                                            <?php 
                                                            if ($active_tab === 'sections') echo htmlspecialchars($row['sec_name']);
                                                            elseif ($active_tab === 'requirements') echo htmlspecialchars($row['sub_req_name']);
                                                            else echo htmlspecialchars($row['sub_con_name']);
                                                            ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($active_tab !== 'sections'): ?>
                                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                                            <?php echo htmlspecialchars($row['sec_ID'] . ' - ' . $row['sec_name']); ?>
                                                        </span>
                                                    <?php elseif ($active_tab === 'sections'): ?>
                                                        <?php echo '<span class="badge bg-light text-secondary border ms-2">'.htmlspecialchars($row['type']).'</span>'; ?>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <td class="text-end pe-4" onclick="event.stopPropagation()">
                                                    <a href="<?php 
                                                        if ($active_tab === 'sections') echo 'edit-section.php?id=' . htmlspecialchars($row['sec_ID']);
                                                        elseif ($active_tab === 'requirements') echo 'edit-requirement.php?id=' . htmlspecialchars($row['sub_req_ID']);
                                                        else echo 'edit-control.php?id=' . htmlspecialchars($row['sub_con_ID']);
                                                    ?>" class="btn btn-sm btn-link text-primary" title="Edit">
                                                        <i class="bi bi-pencil-square fs-6"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-link text-danger" title="Delete">
                                                        <i class="bi bi-trash fs-6"></i>
                                                    </a>
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

    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="viewModalTitle">Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3 pb-4">
                    <table class="table table-bordered mb-0">
                        <tbody id="modalContent">
                            </tbody>
                    </table>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Pass the active tab to JS so we know how to format the fields
        const activeTab = "<?php echo $active_tab; ?>";

        function openViewModal(rowElement) {
            // Get the data from the attribute
            const data = JSON.parse(rowElement.dataset.row);
            const content = document.getElementById('modalContent');
            const title = document.getElementById('viewModalTitle');
            
            let html = '';

            if (activeTab === 'sections') {
                title.innerText = `Section: ${data.sec_ID}`;
                html += `<tr><th class="bg-light" style="width: 30%">ID</th><td>${data.sec_ID}</td></tr>`;
                html += `<tr><th class="bg-light">Name</th><td>${data.sec_name}</td></tr>`;
                html += `<tr><th class="bg-light">Type</th><td>${data.type}</td></tr>`;
            } 
            else if (activeTab === 'requirements') {
                title.innerText = `Requirement: ${data.sub_req_ID}`;
                html += `<tr><th class="bg-light" style="width: 30%">ID</th><td>${data.sub_req_ID}</td></tr>`;
                html += `<tr><th class="bg-light">Description</th><td>${data.sub_req_name}</td></tr>`;
                html += `<tr><th class="bg-light">Section</th><td>${data.sec_ID} - ${data.sec_name}</td></tr>`;
            } 
            else if (activeTab === 'controls') {
                title.innerText = `Control: ${data.sub_con_ID}`;
                html += `<tr><th class="bg-light" style="width: 30%">ID</th><td>${data.sub_con_ID}</td></tr>`;
                html += `<tr><th class="bg-light">Control Name</th><td>${data.sub_con_name}</td></tr>`;
                html += `<tr><th class="bg-light">Category</th><td>${data.sec_ID} - ${data.sec_name}</td></tr>`;
            }

            content.innerHTML = html;

            // Show Modal
            const modal = new bootstrap.Modal(document.getElementById('viewModal'));
            modal.show();
        }
    </script>
</body>
</html>