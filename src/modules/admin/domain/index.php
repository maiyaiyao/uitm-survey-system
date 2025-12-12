<?php
require_once '../../../config/config.php';
require_once '../../../includes/models/User.php'; 

requireRole(['admin']);

$db = new Database();

$search = $_GET['search'] ?? '';
$params = [];

// Base SQL query
$sql = "
    SELECT d.*, 
           uc.full_name AS created_by_name,
           uu.full_name AS updated_by_name,
           COUNT(DISTINCT c.criteria_ID) as criteria_count,
           COUNT(DISTINCT e.element_ID) as element_count
    FROM domain d
    LEFT JOIN user uc ON d.input_id = uc.user_ID
    LEFT JOIN user uu ON d.updated_id = uu.user_ID
    LEFT JOIN criteria c ON d.domain_ID = c.domain_ID AND c.status = 'Active'
    LEFT JOIN element e ON c.criteria_ID = e.criteria_ID AND e.status = 'Active'
";

// Add WHERE clause if search is active
if (!empty($search)) {
    $sql .= " WHERE (d.domain_name LIKE :search_name)";
    $searchTerm = "%" . trim($search) . "%";
    $params[':search_name'] = $searchTerm;
}

// Complete the query
$sql .= " GROUP BY d.domain_ID ORDER BY (CASE WHEN d.status = 'Active' THEN 0 ELSE 1 END) ASC, d.domain_name ASC";

// Fetch results
$domains = $db->fetchAll($sql, $params);

$current_user = getCurrentUser();
$flash = getFlashMessage();

// --- Helper Functions for Badges ---
function getStatusBadgeDomain($status) {
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
    <title>Domain Management - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Page Layout */
        html, body { 
            height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; 
        }
        /* Table Styles */
        .table th {
            font-weight: 700;
            background-color: #9d83b7ff;
            border-bottom: 2px solid #f0f2f5;
            color: black;
            text-transform: uppercase;
            font-size: 0.75rem;
            padding: 1rem;
        }
        .table td {
            padding: 1rem;
            vertical-align: middle;
            color: #000000ff;
            font-size: 0.875rem;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Stat Cards */
        .stat-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
            background: white;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        
        /* Icon Boxes */
        .icon-shape {
            width: 48px;
            height: 48px;
            background-position: center;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-shape i {
            font-size: 1.25rem;
        }
        
        /* Avatar / User Text */
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

        .alert-error {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }

        /* Optional: Add a subtle slide-down animation */
        .alert {
            animation: slideDown 0.5s ease-out;
        }
        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <div class="col-auto sidebar-container">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>
            
            <div class="col main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page">Domain</li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">All Domains</h3>
                            <p class="text-muted mb-0">All Domain in this system</p>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <form method="GET" class="d-flex">
                                <div class="input-group shadow-sm">
                                    <input type="text" name="search" class="form-control border-0" 
                                        placeholder="Search domains..." 
                                        value="<?php echo htmlspecialchars($search); ?>" 
                                        aria-label="Search domains">
                                    <button class="btn btn-white border-0 bg-white" type="submit">
                                        <i class="bi bi-search text-primary"></i>
                                    </button>
                                    <?php if(!empty($search)): ?>
                                        <a href="index.php" class="btn btn-white border-0 bg-white" title="Clear Search">
                                            <i class="bi bi-x-circle text-secondary"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>

                            <a href="add-domain.php" 
                                class="btn btn-primary shadow-sm px-4 py-2 rounded-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-plus-lg me-2"></i>Add Domain
                            </a>
                        </div>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                            <h5 class="mb-0">All Domains List</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="width: 25%;">Domain Name</th>
                                        <th class="text-center">Structure</th>
                                        <th>Created</th>
                                        <th>Last Updated</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($domains as $domain): 
                                        // Row click logic from previous request
                                        $viewLink = "../criteria/view-criteria.php?id=" . $domain['domain_ID'];
                                        $isActive = $domain['status'] === 'Active';
                                        
                                        $rowAction = $isActive 
                                            ? "window.location.href='" . $viewLink . "';" 
                                            : "alert('Action Denied: Please activate this domain before viewing details.');";
                                    ?>
                                        <tr onclick="<?php echo $rowAction; ?>" style="cursor: pointer; transition: background-color 0.2s;">
                                            <td class="ps-4">
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($domain['domain_name']); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <span class="badge bg-light text-dark border" title="Criteria Count">
                                                        <i class="bi bi-list-check me-1"></i><?php echo $domain['criteria_count']; ?>
                                                    </span>
                                                    <span class="badge bg-light text-dark border" title="Element Count">
                                                        <i class="bi bi-file-text me-1"></i><?php echo $domain['element_count']; ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="user-meta">
                                                    <span class="name"><?php echo htmlspecialchars($domain['created_by_name'] ?? 'System'); ?></span>
                                                    <span class="date"><?php echo formatDate($domain['input_at']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($domain['updated_by_name'])): ?>
                                                    <div class="user-meta">
                                                        <span class="name"><?php echo htmlspecialchars($domain['updated_by_name']); ?></span>
                                                        <span class="date"><?php echo formatDate($domain['updated_at']); ?></span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted text-xs">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php echo getStatusBadgeDomain($domain['status']); ?>
                                            </td>
                                            <td class="text-center pe-4" onclick="event.stopPropagation();">
                                                
                                               <a href="edit-domain.php?id=<?php echo $domain['domain_ID']; ?>" 
                                                    class="btn btn-sm btn-link <?php echo $domain['status'] === 'Active' ? 'text-primary' : 'text-secondary'; ?> px-2" 
                                                    title="Edit Domain"
                                                    onclick="<?php echo ($domain['status'] !== 'Active') ? "alert('Action Denied: Please activate this domain before editing.'); return false;" : ""; ?>">
                                                        <i class="bi bi-pencil-square fs-6"></i>
                                                    </a>
                                                <button class="btn btn-sm btn-link text-<?php echo $domain['status'] === 'Active' ? 'danger' : 'success'; ?> px-2" 
                                                        onclick="toggleStatus('<?php echo $domain['domain_ID']; ?>', '<?php echo $domain['status']; ?>')"
                                                        title="<?php echo $domain['status'] === 'Active' ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="bi bi-<?php echo $domain['status'] === 'Active' ? 'power' : 'check-circle'; ?> fs-6"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($domains)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                    No domains found. Add one to get started.
                                                </div>
                                            </td>
                                        </tr>
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
    <script>
        function toggleStatus(domainId, currentStatus) {
            const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
            const actionName = newStatus === 'Active' ? 'activate' : 'deactivate';
            
            if (confirm(`Are you sure you want to ${actionName} this domain?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'toggle-status.php';
                
                const input1 = document.createElement('input');
                input1.type = 'hidden';
                input1.name = 'domain_id';
                input1.value = domainId;
                
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