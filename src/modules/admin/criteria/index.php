<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$search = $_GET['search'] ?? '';
$params = [];

// --- 1. SQL Query Update ---
// Fetches Criteria, Parent Domain, User Logs, Element List, and Usage Count for Deletion Safety
$sql = "
    SELECT c.*, d.domain_name, d.domain_ID,
    uc.full_name AS created_by_name,
    uu.full_name AS updated_by_name,
    GROUP_CONCAT(e.element_name ORDER BY e.element_name SEPARATOR '|||') as element_list,
    COUNT(e.element_ID) as element_count,
    
    /* CHECK USAGE FOR DELETE BUTTON - Replace 'response' with your actual answers table */
    (SELECT COUNT(*) 
     FROM response r 
     JOIN element el ON r.element_id = el.element_ID 
     WHERE el.criteria_ID = c.criteria_ID) as usage_count

    FROM criteria c
    JOIN domain d ON c.domain_ID = d.domain_ID
    LEFT JOIN user uc ON c.input_id = uc.user_ID
    LEFT JOIN user uu ON c.updated_id = uu.user_ID
    LEFT JOIN element e ON c.criteria_ID = e.criteria_ID AND e.status = 'Active'
";

if (!empty($search)) {
    $sql .= " WHERE c.criteria_name LIKE :search1 OR d.domain_name LIKE :search2";
    $searchParam = "%" . $search . "%";
    $params[':search1'] = $searchParam;
    $params[':search2'] = $searchParam;
}

// Group by Criteria ID to ensure accurate counts
$sql .= " GROUP BY c.criteria_ID ";

// Sort by Status (Active first), then Criteria Name
$sql .= " ORDER BY (CASE WHEN c.status = 'Active' THEN 0 ELSE 1 END) ASC, c.criteria_name ASC";

$all_criteria = $db->fetchAll($sql, $params);
$flash = getFlashMessage();

// Helper for status badge
function getStatusBadgeCriteria($status) {
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
    <title>All Criteria - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Page Layout */
        html, body { 
            height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; 
        }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }

        /* Card & Table Styles */
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        
        .table th {
            font-weight: 700;
            background-color: #9d83b7ff; /* Purple Header */
            border-bottom: 2px solid #f0f2f5;
            color: black;
            text-transform: uppercase;
            font-size: 0.75rem;
            padding: 1rem;
        }
        .table td {
            padding: 1rem;
            vertical-align: middle;
            color: #67748e;
            font-size: 0.875rem;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Avatar / User Text (Audit Info) */
        .user-meta {
            display: flex;
            flex-direction: column;
            line-height: 1.3;
        }
        .user-meta .name {
            font-weight: 600;
            color: #344767;
        }
        .user-meta .date {
            font-size: 0.75rem;
            color: #adb5bd;
        }
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
                            <li class="breadcrumb-item"><a href="../domain/index.php" class="text-decoration-none text-secondary">Domain</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page">Criteria</li>
                        </ol>
                    </nav>
                    
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">All Criteria</h3>
                            <p class="text-muted mb-0">Master list of all criteria across domains.</p>
                        </div>

                        <div class="d-flex gap-2">
                            <form method="GET" class="d-flex">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control border-0 shadow-sm" 
                                           placeholder="Search Criteria..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-white bg-white shadow-sm border-0">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </form>
                            
                            <a href="add-criteria.php" 
                               class="btn btn-primary shadow-sm px-4 py-2 rounded-3 d-flex align-items-center"
                               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-plus-lg me-2"></i>Add Criteria
                            </a>
                        </div>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show border-0 shadow-sm mb-4">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm rounded-4">
                       <div class="card-header bg-white border-bottom py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Criteria List</h5>
                            <small class="text-muted"><?php echo count($all_criteria); ?> records found</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="width: 40%;">Criteria Name</th>
                                        <th class="text-center">Elements</th>
                                        <th>Created</th>
                                        <th>Last Updated</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_criteria as $row): 
                                        $isActive = $row['status'] === 'Active';
                                        
                                        // Parse the elements list
                                        $elementNames = !empty($row['element_list']) ? explode('|||', $row['element_list']) : [];
                                    ?>
                                    
                                    <tr data-bs-toggle="modal" 
                                        data-bs-target="#viewModal-<?php echo $row['criteria_ID']; ?>"
                                        style="cursor: pointer; transition: background-color 0.2s;">
                                        
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['criteria_name']); ?></span>
                                            <div class="text-muted text-xs"><i class="bi bi-folder me-1"></i><?php echo htmlspecialchars($row['domain_name']); ?></div>
                                        </td>
                                        
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                <?php echo $row['element_count']; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="user-meta">
                                                <span class="name"><?php echo htmlspecialchars($row['created_by_name'] ?? 'System'); ?></span>
                                                <span class="date"><?php echo formatDate($row['input_at']); ?></span>
                                            </div>
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
                                            <?php echo getStatusBadgeCriteria($row['status']); ?>
                                        </td>
                                        
                                        <td class="text-center pe-4" onclick="event.stopPropagation();">
                                            
                                            <a href="../element/view-element.php?id=<?php echo $row['criteria_ID']; ?>" 
                                               class="btn btn-sm btn-link <?php echo $isActive ? 'text-info' : 'text-secondary'; ?> px-2" 
                                               title="View Elements"
                                               onclick="<?php echo (!$isActive) ? "alert('Action Denied: Please activate this criteria before viewing details.'); return false;" : ""; ?>">
                                                <i class="bi bi-eye fs-6"></i>
                                            </a>

                                            <a href="edit-criteria.php?id=<?php echo $row['criteria_ID']; ?>" 
                                               class="btn btn-sm btn-link <?php echo $isActive ? 'text-primary' : 'text-secondary'; ?> px-2" 
                                               title="Edit Criteria"
                                               onclick="<?php echo (!$isActive) ? "alert('Action Denied: Please activate this criteria before editing.'); return false;" : ""; ?>">
                                                <i class="bi bi-pencil-square fs-6"></i>
                                            </a>

                                            <button class="btn btn-sm btn-link text-<?php echo $isActive ? 'danger' : 'success'; ?> px-2" 
                                                    onclick="toggleStatus('<?php echo $row['criteria_ID']; ?>', '<?php echo $row['status']; ?>')"
                                                    title="<?php echo $isActive ? 'Deactivate' : 'Activate'; ?>">
                                                <i class="bi bi-<?php echo $isActive ? 'power' : 'check-circle'; ?> fs-6"></i>
                                            </button>

                                            <?php if ($row['usage_count'] == 0): ?>
                                                <button class="btn btn-sm btn-link text-secondary px-2" 
                                                        onclick="deleteCriteria('<?php echo $row['criteria_ID']; ?>')"
                                                        title="Delete Criteria (Safe)">
                                                    <i class="bi bi-trash fs-6"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-link text-muted px-2 opacity-25" 
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Cannot Delete: Used in <?php echo $row['usage_count']; ?> surveys" disabled>
                                                    <i class="bi bi-trash fs-6"></i>
                                                </button>
                                            <?php endif; ?>

                                        </td>
                                    </tr>

                                    <div class="modal fade" id="viewModal-<?php echo $row['criteria_ID']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold">Criteria Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body pt-3 pb-4 px-4">
                                                    
                                                    <div class="d-flex align-items-start gap-3 mb-4">
                                                        <div class="bg-primary-subtle rounded-3 p-3 text-primary">
                                                            <i class="bi bi-file-earmark-text fs-3"></i>
                                                        </div>
                                                        <div>
                                                            <div class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Domain</div>
                                                            <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($row['domain_name']); ?></div>
                                                            <h4 class="fw-bold text-primary mb-2"><?php echo htmlspecialchars($row['criteria_name']); ?></h4>
                                                            <?php echo getStatusBadgeCriteria($row['status']); ?>
                                                        </div>
                                                    </div>

                                                    <div class="bg-light rounded-3 p-3 mb-3">
                                                        <h6 class="fw-bold mb-2 text-secondary" style="font-size: 0.8rem; text-transform: uppercase;">
                                                            Associated Elements (<?php echo count($elementNames); ?>)
                                                        </h6>
                                                        <?php if (!empty($elementNames)): ?>
                                                            <ul class="mb-0 ps-3">
                                                                <?php foreach ($elementNames as $elName): ?>
                                                                    <li class="mb-1 text-dark small"><?php echo htmlspecialchars($elName); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php else: ?>
                                                            <p class="mb-0 text-muted small fst-italic">No elements found under this criteria.</p>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="row g-2 mt-3 pt-3 border-top">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">CREATED BY</small>
                                                            <span class="fw-medium text-dark small">
                                                                <?php echo htmlspecialchars($row['created_by_name'] ?? 'System'); ?>
                                                            </span>
                                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                                <?php echo formatDate($row['input_at']); ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block" style="font-size: 0.7rem;">LAST UPDATED</small>
                                                            <?php if(!empty($row['updated_by_name'])): ?>
                                                                <span class="fw-medium text-dark small">
                                                                    <?php echo htmlspecialchars($row['updated_by_name']); ?>
                                                                </span>
                                                                <div class="text-muted" style="font-size: 0.7rem;">
                                                                    <?php echo formatDate($row['updated_at']); ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="text-muted small">-</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="modal-footer border-top-0 pt-0 px-4 pb-4 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <?php if ($row['usage_count'] == 0): ?>
                                                            <button type="button" class="btn btn-outline-danger rounded-3 px-3" 
                                                                    onclick="deleteCriteria('<?php echo $row['criteria_ID']; ?>')">
                                                                <i class="bi bi-trash me-2"></i>Delete
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" title="Cannot delete: This criteria has existing elements.">
                                                                <button class="btn btn-outline-danger rounded-3 px-3" disabled>
                                                                    <i class="bi bi-trash me-2"></i>Delete
                                                                </button>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Close</button>
        
                                                        <a href="edit-criteria.php?id=<?php echo $row['criteria_ID']; ?>" 
                                                           class="btn btn-primary shadow-sm px-4 py-2 rounded-3 d-flex align-items-center" 
                                                           style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;"
                                                           onclick="<?php echo (!$isActive) ? "alert('Action Denied: Please activate this criteria before editing.'); return false;" : ""; ?>">
                                                            Edit
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
        // Initialize Bootstrap Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        function toggleStatus(criteriaId, currentStatus) {
            const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
            const actionName = newStatus === 'Active' ? 'activate' : 'deactivate';
            
            if (confirm(`Are you sure you want to ${actionName} this criteria?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'toggle-status.php'; 
                
                const input1 = document.createElement('input');
                input1.type = 'hidden';
                input1.name = 'criteria_id';
                input1.value = criteriaId;
                
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

        function deleteCriteria(criteriaId) {
            if (confirm('Are you sure you want to PERMANENTLY DELETE this Criteria? This will also delete all associated Elements. This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete-criteria.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'criteria_id';
                input.value = criteriaId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>