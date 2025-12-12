<?php
// Path: src/modules/admin/control/index.php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();

// --- 1. Get Filter Inputs ---
$search = $_GET['search'] ?? '';
$filter_section = $_GET['section'] ?? '';

// --- 2. Fetch Helper Data (Sections for Dropdown) ---
// We only want sections that are actual controls (type='Control')
$sections_list = $db->fetchAll("SELECT sec_ID, sec_name FROM section WHERE type = 'Control' ORDER BY sec_ID ASC");

// --- 3. Build Main Query ---
$params = [];
$conditions = [];

$sql = "
    SELECT sc.*, s.sec_name, s.sec_ID,
           (SELECT COUNT(*) FROM criteria_control cic WHERE cic.sub_con_ID = sc.sub_con_ID) as linked_count
    FROM sub_con sc
    JOIN section s ON sc.sec_ID = s.sec_ID
";

// Apply Search Filter
if (!empty($search)) {
    $conditions[] = "(sc.sub_con_name LIKE :search1 OR sc.sub_con_ID LIKE :search2)";
    $params[':search1'] = "%$search%";
    $params[':search2'] = "%$search%";
}

// Apply Section Filter
if (!empty($filter_section)) {
    $conditions[] = "sc.sec_ID = :section";
    $params[':section'] = $filter_section;
}

// Combine Conditions
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY sc.sub_con_name ASC";

$controls = $db->fetchAll($sql, $params);
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISO Controls - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        body.sb-collapsed .main-content-wrapper { margin-left: 80px; width: calc(100% - 80px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .table th { background-color: #9d83b7ff; color: black; font-size: 0.75rem; text-transform: uppercase; padding: 1rem; }
        .table td { padding: 1rem; vertical-align: middle; color: #67748e; font-size: 0.875rem; }
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
                            <li class="breadcrumb-item active text-dark">ISO Controls</li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">ISO 27001 Controls</h3>
                            <p class="text-muted mb-0">Map Assessment Criteria to ISO Standards.</p>
                        </div>
                        
                        <div class="d-flex align-items-center gap-2">
                            <form method="GET" class="d-flex gap-2">
                                
                                <select name="section" class="form-select border-0 shadow-sm" style="max-width: 200px;" onchange="this.form.submit()">
                                    <option value="">All Sections</option>
                                    <?php foreach ($sections_list as $sec): ?>
                                        <option value="<?php echo htmlspecialchars($sec['sec_ID']); ?>" 
                                            <?php echo ($filter_section === $sec['sec_ID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sec['sec_ID'] . ' - ' . $sec['sec_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <div class="input-group shadow-sm">
                                    <input type="text" name="search" class="form-control border-0" 
                                           placeholder="Search ID/Name..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-white bg-white border-0" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>

                                <?php if (!empty($search) || !empty($filter_section)): ?>
                                    <a href="index.php" class="btn btn-white bg-white border-0 shadow-sm text-danger" title="Reset Filters">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                <?php endif; ?>
                            </form>

                            <a href="manage-sections.php" 
                               class="btn btn-outline-secondary shadow-sm px-4 py-2 rounded-3 bg-white border-0 text-secondary">
                                <i class="bi bi-folder me-2"></i>Sections
                            </a>

                            <a href="add-control.php" 
                               class="btn btn-primary shadow-sm px-4 py-2 rounded-3 ms-2" 
                               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-plus-lg me-2"></i>Add Control
                            </a>
                        </div>
                    </div>

                    <?php if ($flash): ?><div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div><?php endif; ?>

                    <div class="card">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Controls List</h5>
                            <small class="text-muted"><?php echo count($controls); ?> records found</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Control ID</th>
                                        <th>Control Name</th>
                                        <th>Section (Category)</th>
                                        <th class="text-center">Linked Criteria</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($controls)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                No controls match your filters. <a href="index.php">Clear filters</a> or <a href="add-control.php">add a new control</a>.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($controls as $row): ?>
                                        <tr onclick="window.location.href='view-control.php?id=<?php echo $row['sub_con_ID']; ?>';" style="cursor: pointer;">
                                            <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($row['sub_con_ID']); ?></td>
                                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['sub_con_name']); ?></td>
                                            <td>
                                                <span class="badge bg-light text-secondary border">
                                                    <?php echo htmlspecialchars($row['sec_ID'] . ' - ' . $row['sec_name']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info-subtle text-info-emphasis rounded-pill">
                                                    <?php echo $row['linked_count']; ?> Linked
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="view-control.php?id=<?php echo $row['sub_con_ID']; ?>" class="btn btn-sm btn-link text-primary">
                                                    <i class="bi bi-link-45deg fs-5"></i> Map Criteria
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>