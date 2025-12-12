<?php
// Path: modules/admin/domain/view.php (Adjust based on your actual structure)
require_once '../../../config/config.php';
requireRole(['admin']);

$domain_id = $_GET['id'] ?? null;
if (!$domain_id) {
    redirect(BASE_URL . '/modules/admin/domain/index.php');
}

$db = new Database();

// Get domain details
$domain = $db->fetchOne("SELECT * FROM domain WHERE domain_ID = :id", [':id' => $domain_id]);
if (!$domain) {
    setFlashMessage('danger', 'Domain not found');
    redirect(BASE_URL . '/modules/admin/domain/index.php');
}
// --- SECURITY CHECK: PREVENT ACCESS IF INACTIVE ---
if ($domain && $domain['status'] !== 'Active') {
    // 1. Set an error message
    setFlashMessage('error', 'Access Denied: You cannot view details of an Inactive domain.');
    
    // 2. Redirect them away (e.g., back to the Domain List or Dashboard)
    // Adjust this path to point to your main Domain list
    header('Location: ../domain/index.php'); 
    exit();
}

// Get all criteria for this domain
$criteria = $db->fetchAll("
    SELECT c.*, 
    u.full_name AS input_username,
    u_update.full_name AS updated_username,
            COUNT(DISTINCT e.element_ID) as element_count
    FROM criteria c
    LEFT JOIN 
        element e ON c.criteria_ID = e.criteria_ID AND e.status = 'Active'
    LEFT JOIN 
        user u ON c.input_id = u.user_ID
    LEFT JOIN
        user u_update ON c.updated_id = u_update.user_ID
    WHERE c.domain_ID = :domain_id
    GROUP BY c.criteria_ID
    ORDER BY c.criteria_ID
", [':domain_id' => $domain_id]);

$flash = getFlashMessage();

// Helper for status badge styling (matching survey module)
function getStatusBadge($status) {
    if ($status === 'Active') {
        return '<span class="badge rounded-pill bg-success-subtle text-success-emphasis">Active</span>';
    }
    return '<span class="badge rounded-pill bg-danger-subtle text-danger-emphasis">' . htmlspecialchars($status) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($domain['domain_name']); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Page Layout */
        html, body { 
            height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; 
        }
        
        /* Sidebar Adjustment */
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        /* Table Styles - Matching Survey Module */
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

        /* Stat Cards */
        .stat-card-title { font-size: 0.75rem; text-transform: uppercase; color: #8898aa; font-weight: 600; }
        .stat-card-value { font-size: 1.5rem; font-weight: 700; color: #32325d; }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }

        /* Description/Name Links */
        .fw-bold-dark { font-weight: 600; color: #344767; }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <div class="col-md-2 col-lg-2 sidebar">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>
            
            <div class="col-md-10 col-lg-10 main-content-wrapper">
                <div class="main-content px-4 py-4">

                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a>
                            </li>

                            <li class="breadcrumb-item active text-dark" aria-current="page" 
                                title="Domain: <?php echo htmlspecialchars($domain['domain_name']); ?>"> Domain <?php echo htmlspecialchars(truncate($domain['domain_name'], 30)); ?>
                            </li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="../domain/index.php" class="btn btn-outline-secondary btn-sm rounded-circle" title="Back">
                                    <i class="bi bi-arrow-left"></i>
                                </a>
                                <h3 class="fw-bold mb-0">Domain - <?php echo htmlspecialchars($domain['domain_name']); ?></h3>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="../domain/edit-domain.php?id=<?php echo urlencode($domain_id); ?>" class="btn btn-outline-primary shadow-sm px-3 rounded-3">
                                <i class="bi bi-pencil me-2"></i>Edit Domain
                            </a>
                            <a href="../criteria/add-criteria.php?domain_id=<?php echo $domain_id; ?>" 
                               class="btn btn-primary shadow-sm px-3 rounded-3"
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

                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="card h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-card-title mb-1">Total Criteria</div>
                                        <div class="stat-card-value"><?php echo count($criteria); ?></div>
                                    </div>
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="bi bi-list-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-card-title mb-1">Total Elements</div>
                                        <div class="stat-card-value"><?php echo array_sum(array_column($criteria, 'element_count')); ?></div>
                                    </div>
                                    <div class="stat-icon bg-info-subtle text-info">
                                        <i class="bi bi-layers"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-card-title mb-1">Domain Status</div>
                                        <div class="mt-1"><?php echo getStatusBadge($domain['status']); ?></div>
                                    </div>
                                    <div class="stat-icon bg-warning-subtle text-warning">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-5">
                        <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                            <h5 class="mb-0">Criteria List</h5>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Criteria Name</th>
                                        <th style="width: 10%;">Elements</th> <th style="width: 15%;">Created By</th>
                                        <th style="width: 15%;">Last Update</th> <th class="text-center" style="width: 10%;">Status</th>
                                        <th class="text-end pe-4" style="width: 15%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($criteria)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                    No criteria found for this domain. <a href="../criteria/add-criteria.php?domain_id=<?php echo $domain_id; ?>">Add one now</a>.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($criteria as $crit): 
                                            // 1. Prepare logic for the row click
                                            $viewLink = "../element/view-element.php?id=" . $crit['criteria_ID'];
                                            $isActive = $crit['status'] === 'Active';
                                            
                                            // Logic: Active -> Redirect. Inactive -> Alert.
                                            $rowAction = $isActive 
                                                ? "window.location.href='" . $viewLink . "';" 
                                                : "alert('Action Denied: Please activate this criteria before viewing elements.');";
                                        ?>
                                            <tr onclick="<?php echo $rowAction; ?>" style="cursor: pointer; transition: background-color 0.2s;">
                                                <td class="ps-4">
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold-dark"><?php echo htmlspecialchars($crit['criteria_name']); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="bi bi-layers me-1"></i><?php echo $crit['element_count']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-dark text-sm"><?php echo htmlspecialchars($crit['input_username'] ?? '-'); ?></span>
                                                        <span class="text-muted" style="font-size: 0.75rem;">
                                                            <?php echo !empty($crit['input_at']) ? date('d M Y', strtotime($crit['input_at'])) : '-'; ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-dark text-sm">
                                                            <?php echo htmlspecialchars($crit['updated_username'] ?? '-'); ?>
                                                        </span>
                                                        <span class="text-muted" style="font-size: 0.75rem;">
                                                            <?php echo !empty($crit['updated_at']) ? date('d M Y', strtotime($crit['updated_at'])) : '-'; ?>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    <?php echo getStatusBadge($crit['status']); ?>
                                                </td>
                                                
                                                <td class="text-end pe-4" onclick="event.stopPropagation();">
                                                    
                                                    <a href="../criteria/edit-criteria.php?id=<?php echo $crit['criteria_ID']; ?>" 
                                                        class="btn btn-sm btn-link <?php echo $crit['status'] === 'Active' ? 'text-primary' : 'text-secondary'; ?> px-2" 
                                                        title="Edit"
                                                        onclick="<?php echo ($crit['status'] !== 'Active') ? "alert('Action Denied: Please activate this criteria before editing.'); return false;" : ""; ?>">
                                                        <i class="bi bi-pencil-square fs-6"></i>
                                                    </a>
                                                    
                                                    <button class="btn btn-sm btn-link <?php echo $crit['status'] === 'Active' ? 'text-danger' : 'text-success'; ?> px-2" 
                                                            onclick="toggleCriteriaStatus('<?php echo $crit['criteria_ID']; ?>', '<?php echo $crit['status']; ?>')"
                                                            title="<?php echo $crit['status'] === 'Active' ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="bi bi-<?php echo $crit['status'] === 'Active' ? 'power' : 'check-circle'; ?> fs-6"></i>
                                                    </button>
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
    <script>
        function toggleCriteriaStatus(criteriaId, currentStatus) {
            const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
            const actionVerb = newStatus === 'Active' ? 'activate' : 'deactivate';
            
            if (confirm(`Are you sure you want to ${actionVerb} this criteria?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../criteria/toggle-status.php';
                
                const input1 = document.createElement('input');
                input1.type = 'hidden';
                input1.name = 'criteria_id';
                input1.value = criteriaId;
                
                const input2 = document.createElement('input');
                input2.type = 'hidden';
                input2.name = 'new_status';
                input2.value = newStatus;

                const input3 = document.createElement('input');
                input3.type = 'hidden';
                input3.name = 'domain_id';
                input3.value = '<?php echo $domain_id; ?>'; 
                form.appendChild(input3);
                
                form.appendChild(input1);
                form.appendChild(input2);
                form.appendChild(input3);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>