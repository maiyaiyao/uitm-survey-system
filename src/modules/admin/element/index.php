<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$search = $_GET['search'] ?? '';
$params = [];

// --- SQL Query ---
// 1. Fetch Element details
// 2. Join Criteria and Domain for context
// 3. Subquery: Count Active scores
// 4. NEW Subquery: Count Usage in Responses (Answered Surveys)
$sql = "
    SELECT e.*, 
           c.criteria_name, c.criteria_ID, 
           d.domain_name, d.domain_ID,
           uc.full_name AS created_by_name,
           uu.full_name AS updated_by_name,
           (SELECT COUNT(*) FROM score_element se WHERE se.element_ID = e.element_ID AND se.status = 'Active') as score_count,
           (SELECT COUNT(*) FROM response r WHERE r.element_ID = e.element_ID) as usage_count
    FROM element e
    JOIN criteria c ON e.criteria_ID = c.criteria_ID
    JOIN domain d ON c.domain_ID = d.domain_ID
    LEFT JOIN user uc ON e.input_id = uc.user_ID
    LEFT JOIN user uu ON e.updated_id = uu.user_ID
";

if (!empty($search)) {
    $sql .= " WHERE e.element_name LIKE :search1 OR c.criteria_name LIKE :search2";
    $searchParam = "%" . $search . "%";
    $params[':search1'] = $searchParam;
    $params[':search2'] = $searchParam;
}

// Sort by Status (Active first), then hierarchy
$sql .= " ORDER BY (CASE WHEN e.status = 'Active' THEN 0 ELSE 1 END) ASC, e.element_name ASC";

$all_elements = $db->fetchAll($sql, $params);
$flash = getFlashMessage();

function getStatusBadgeElement($status) {
    if ($status === 'Active') {
        return '<span class="badge rounded-pill bg-success-subtle text-success-emphasis">Active</span>';
    } else {
        return '<span class="badge rounded-pill bg-danger-subtle text-secondary-emphasis">Inactive</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Elements - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }

        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        
        .table th {
            font-weight: 700;
            background-color: #9d83b7ff;
            color: black;
            font-size: 0.75rem;
            text-transform: uppercase;
            padding: 1rem;
            border-bottom: 2px solid #f0f2f5;
        }
        .table td {
            vertical-align: middle;
            padding: 1rem;
            color: #67748e;
            font-size: 0.875rem;
        }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        
        .user-meta { display: flex; flex-direction: column; line-height: 1.3; }
        .user-meta .name { font-weight: 600; color: #344767; }
        .user-meta .date { font-size: 0.75rem; color: #adb5bd; }
        
        .context-meta { display: flex; flex-direction: column; }
        .context-meta .criteria { font-weight: 600; color: #344767; }
        .context-meta .domain { font-size: 0.75rem; color: #8392ab; display: flex; align-items: center; gap: 4px;}
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
                            <li class="breadcrumb-item active text-dark" aria-current="page">Element</li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">All Elements</h3>
                            <p class="text-muted mb-0">Select an element to manage its scoring levels.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <form method="GET" class="d-flex">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control border-0 shadow-sm" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-white bg-white shadow-sm border-0"><i class="bi bi-search"></i></button>
                                </div>
                            </form>
                            
                            <a href="add-element.php" 
                               class="btn btn-primary shadow-sm px-4 py-2 rounded-3 d-flex align-items-center" 
                               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-plus-lg me-2"></i>Add Element
                            </a>
                        </div>
                    </div>

                    <?php if ($flash): ?><div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div><?php endif; ?>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Elements List</h5>
                            <small class="text-muted"><?php echo count($all_elements); ?> records found</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 35%;">Element Name</th>
                                        <th style="width: 25%;">Hierarchy</th>
                                        <th class="text-center">Scores</th>
                                        <th>Last Updated</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_elements as $row): 
                                        $isActive = $row['status'] === 'Active';
                                        
                                        // Drill-down link
                                        $scoreLink = "../score/index.php?element_id=" . $row['element_ID'];
                                        
                                        // Row click action
                                        $rowOnClick = $isActive 
                                            ? "window.location.href='" . $scoreLink . "';" 
                                            : "alert('Please activate this element to manage scores.');";
                                    ?>
                                    
                                    <tr onclick="<?php echo $rowOnClick; ?>" style="cursor: pointer; transition: background-color 0.2s;" title="Click to manage scores">
                                        
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark d-block mb-1">
                                                <?php echo htmlspecialchars($row['element_name']); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="context-meta">
                                                <span class="criteria">
                                                    <i class="bi bi-diagram-2 me-1 text-info"></i>
                                                    <?php echo htmlspecialchars($row['criteria_name']); ?>
                                                </span>
                                                <span class="domain">
                                                    <i class="bi bi-folder me-1"></i>
                                                    <?php echo htmlspecialchars($row['domain_name']); ?>
                                                </span>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($row['score_count'] > 0): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">
                                                    <?php echo $row['score_count']; ?> Levels
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-secondary border rounded-pill px-3">
                                                    Not Configured
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if (!empty($row['updated_by_name'])): ?>
                                                <div class="user-meta">
                                                    <span class="name"><?php echo htmlspecialchars($row['updated_by_name']); ?></span>
                                                    <span class="date"><?php echo formatDate($row['updated_at']); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted text-xs">-</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <?php echo getStatusBadgeElement($row['status']); ?>
                                        </td>
                                        
                                        <td class="text-center pe-4" onclick="event.stopPropagation();">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="edit-element.php?id=<?php echo $row['element_ID']; ?>" 
                                                   class="btn btn-sm btn-link <?php echo $isActive ? 'text-primary' : 'text-secondary'; ?> px-2" 
                                                   title="Edit Element Details"
                                                   onclick="<?php echo (!$isActive) ? "alert('Action Denied: Please activate this element before editing.'); return false;" : ""; ?>">
                                                    <i class="bi bi-pencil-square fs-6"></i>
                                                </a>
                                                
                                                <button class="btn btn-sm btn-link text-<?php echo $isActive ? 'danger' : 'success'; ?> px-2" 
                                                        onclick="toggleStatus('<?php echo $row['element_ID']; ?>', '<?php echo $row['status']; ?>')"
                                                        title="<?php echo $isActive ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="bi bi-<?php echo $isActive ? 'power' : 'check-circle'; ?> fs-6"></i>
                                                </button>

                                                <?php if ($row['usage_count'] == 0): ?>
                                                    <button class="btn btn-sm btn-link text-secondary px-2"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal<?php echo $row['element_ID']; ?>"
                                                            title="Delete Element">
                                                        <i class="bi bi-trash fs-6"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Cannot delete: Included in <?php echo $row['usage_count']; ?> answered surveys">
                                                        <button class="btn btn-sm btn-link text-secondary px-2" disabled style="opacity: 0.5; cursor: not-allowed;">
                                                            <i class="bi bi-trash fs-6"></i>
                                                        </button>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($row['usage_count'] == 0): ?>
                                            <div class="modal fade" id="deleteModal<?php echo $row['element_ID']; ?>" tabindex="-1" aria-hidden="true" onclick="event.stopPropagation();">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow-lg rounded-4">
                                                        <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                                                            <h5 class="modal-title fw-bold">Delete Element?</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body px-4 py-3 text-start">
                                                            <p class="text-muted mb-2">Are you sure you want to delete <strong class="text-dark"><?php echo htmlspecialchars($row['element_name']); ?></strong>?</p>
                                                            <p class="text-danger small mb-0"><i class="bi bi-exclamation-triangle me-1"></i> This action cannot be undone.</p>
                                                        </div>
                                                        <div class="modal-footer border-top-0 pt-0 px-4 pb-4 d-flex justify-content-between align-items-center">
                                                            <form method="POST" action="delete-element.php" class="w-100 d-flex justify-content-between">
                                                                <input type="hidden" name="element_id" value="<?php echo $row['element_ID']; ?>">
                                                                <button type="submit" class="btn btn-outline-danger rounded-3 px-3">
                                                                    <i class="bi bi-trash me-2"></i>Confirm Delete
                                                                </button>
                                                                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Tooltips for disabled buttons
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        function toggleStatus(elementId, currentStatus) {
            const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
            const actionName = newStatus === 'Active' ? 'activate' : 'deactivate';
            
            if (confirm(`Are you sure you want to ${actionName} this element?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'toggle-status.php';
                
                const input1 = document.createElement('input');
                input1.type = 'hidden';
                input1.name = 'element_id';
                input1.value = elementId;
                
                const input2 = document.createElement('input');
                input2.type = 'hidden';
                input2.name = 'new_status';
                input2.value = newStatus;
                
                form.appendChild(input1);
                form.appendChild(input2);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>