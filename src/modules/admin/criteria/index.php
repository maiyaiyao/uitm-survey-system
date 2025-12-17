<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$search = $_GET['search'] ?? '';
$params = [];

// Fetch all criteria with their parent Domain name + Audit Info (Created/Updated By)
$sql = "
    SELECT c.*, d.domain_name, d.domain_ID,
    uc.full_name AS created_by_name,
    uu.full_name AS updated_by_name,
    (SELECT COUNT(*) FROM element e WHERE e.criteria_ID = c.criteria_ID AND e.status = 'Active') as element_count
    FROM criteria c
    JOIN domain d ON c.domain_ID = d.domain_ID
    LEFT JOIN user uc ON c.input_id = uc.user_ID
    LEFT JOIN user uu ON c.updated_id = uu.user_ID
";

if (!empty($search)) {
    $sql .= " WHERE c.criteria_name LIKE :search1 OR d.domain_name LIKE :search2";
    $searchParam = "%" . $search . "%";
    $params[':search1'] = $searchParam;
    $params[':search2'] = $searchParam;
}

// Sort by Status (Active first), then Domain, then Criteria
$sql .= " ORDER BY (CASE WHEN c.status = 'Active' THEN 0 ELSE 1 END) ASC, c.criteria_name ASC";

$all_criteria = $db->fetchAll($sql, $params);
$flash = getFlashMessage();

// Helper for status badge (Local function or use global if available)
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
                            class="btn btn-primary shadow-sm px-4 py-2 rounded-3" 
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
                                        <th class="ps-4" style="width: 30%;">Criteria Name</th>
                                        <th style="width: 20%;">Parent Domain</th>
                                        <th class="text-center">Elements</th>
                                        <th>Created</th>
                                        <th>Last Updated</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_criteria as $row): 
                                        // Row Click Logic
                                        $viewLink = "../element/view-element.php?id=" . $row['criteria_ID'];
                                        $isActive = $row['status'] === 'Active';
                                        
                                        $rowAction = $isActive 
                                            ? "window.location.href='" . $viewLink . "';" 
                                            : "alert('Action Denied: Please activate this criteria before viewing details.');";
                                    ?>
                                    <tr onclick="<?php echo $rowAction; ?>" style="cursor: pointer; transition: background-color 0.2s;">
                                        
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['criteria_name']); ?></span>
                                        </td>
                                        
                                        <td>
                                            <a href="view-criteria.php?id=<?php echo $row['domain_ID']; ?>" class="text-decoration-none" onclick="event.stopPropagation();">
                                                <span class="badge bg-light text-dark border">
                                                    <?php echo htmlspecialchars($row['domain_name']); ?>
                                                </span>
                                            </a>
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
</body>
</html>