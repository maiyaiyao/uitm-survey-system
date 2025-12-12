<?php
// Path: modules/admin/criteria/view_elements.php
require_once '../../../config/config.php';
requireRole(['admin']);

$criteria_id = $_GET['id'] ?? null;
if (!$criteria_id) {
    redirect(BASE_URL . '/modules/admin/domain/index.php');
}

$db = new Database();

// Get criteria details
$criteria = $db->fetchOne("
    SELECT c.*, 
           d.domain_name, 
           d.domain_ID,
           d.status AS domain_status,
           u_input.full_name AS input_username
    FROM criteria c
    LEFT JOIN domain d ON c.domain_ID = d.domain_ID
    LEFT JOIN user u_input ON c.input_id = u_input.user_ID
    WHERE c.criteria_ID = :id
", [':id' => $criteria_id]);

if (!$criteria) {
    setFlashMessage('danger', 'Criteria not found');
    redirect(BASE_URL . '/modules/admin/domain/index.php');
}

// --- SECURITY CHECK: PREVENT ACCESS IF LOCKED ---

// 1. Check if the Parent Domain is Inactive
if ($criteria['domain_status'] !== 'Active') {
    setFlashMessage('danger', 'Access Denied: The criteria is inactive.');
    redirect(BASE_URL . '/modules/admin/domain/index.php');
    exit();
}

// 2. Check if the Criteria itself is Inactive
if ($criteria['status'] !== 'Active') {
    setFlashMessage('danger', 'Access Denied: You cannot view elements of an Inactive criteria.');
    
    // Redirect back to the Criteria List (view-criteria.php) for this Domain
    redirect(BASE_URL . '/modules/admin/criteria/view-criteria.php?id=' . $criteria['domain_ID']);
    exit();
}
// --- END SECURITY CHECK ---

// Get all elements for this criteria (Updated Query)
$elements = $db->fetchAll("
    SELECT e.*, 
           u_create.full_name AS input_username,
           u_update.full_name AS updated_username
    FROM element e
    LEFT JOIN user u_create ON e.input_id = u_create.user_ID
    LEFT JOIN user u_update ON e.updated_id = u_update.user_ID
    WHERE e.criteria_ID = :criteria_id
    ORDER BY e.element_ID
", [':criteria_id' => $criteria_id]);

$flash = getFlashMessage();

// Calculate summary stats
$total_elements = count($elements);
$active_elements = count(array_filter($elements, fn($e) => $e['status'] === 'Active'));
$inactive_elements = $total_elements - $active_elements;

// Helper for status badge
function getStatusBadge($status) {
    if ($status === 'Active') {
        return '<span class="badge rounded-pill bg-success-subtle text-success-emphasis">Active</span>';
    }
    return '<span class="badge rounded-pill bg-danger-subtle text-danger-emphasis">' . htmlspecialchars($status) . '</span>';
}

// Helper to truncate text
if (!function_exists('truncate')) {
    function truncate($string, $limit = 25, $end = '...') {
        if (mb_strlen($string) <= $limit) return $string;
        return mb_substr($string, 0, $limit) . $end;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($criteria['criteria_name']); ?> - Elements - <?php echo APP_NAME; ?></title>
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

        /* Table Styles */
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
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a></li>
                            <li class="breadcrumb-item">
                                <a href="../criteria/view-criteria.php?id=<?php echo htmlspecialchars($criteria['domain_ID']); ?>"
                                   class="text-decoration-none text-secondary"
                                   title="Domain: <?php echo htmlspecialchars($criteria['domain_name']); ?>">
                                   Domain <?php echo htmlspecialchars(truncate($criteria['domain_name'], 20)); ?>
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-dark">
                                <a href="view-element.php?id=<?php echo htmlspecialchars($criteria['criteria_ID']); ?>"
                                   class="text-decoration-none text-dark"
                                   title="Criteria: <?php echo htmlspecialchars($criteria['criteria_name']); ?>">
                                   Criteria <?php echo htmlspecialchars(truncate($criteria['criteria_name'], 20)); ?>
                                </a>
                            </li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div>
                            <p class="text-muted mb-2 mt-1 ms-1">
                                Domain: <span class="fw-semibold"><?php echo htmlspecialchars($criteria['domain_name']); ?></span>
                            </p>

                            <div class="d-flex align-items-center gap-2">
                                <a href="../criteria/view-criteria.php?id=<?php echo htmlspecialchars($criteria['domain_ID']); ?>" 
                                class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm" 
                                title="Back">
                                    <i class="bi bi-arrow-left"></i>
                                </a>
                                <h3 class="fw-bold mb-0">Criteria - <?php echo htmlspecialchars($criteria['criteria_name']); ?></h3>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 ms-auto">
                            <a href="../criteria/edit-criteria.php?id=<?php echo urlencode($criteria_id); ?>" class="btn btn-outline-primary shadow-sm px-3 rounded-3">
                                <i class="bi bi-pencil me-2"></i>Edit Criteria
                            </a>
                            <a href="add-element.php?criteria_id=<?php echo $criteria_id; ?>" 
                               class="btn btn-primary shadow-sm px-3 rounded-3"
                               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-plus-lg me-2"></i>Add Element
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
                                        <div class="stat-card-title mb-1">Total Elements</div>
                                        <div class="stat-card-value"><?php echo $total_elements; ?></div>
                                    </div>
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="bi bi-card-checklist"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-card-title mb-1">Active Elements</div>
                                        <div class="stat-card-value"><?php echo $active_elements; ?></div>
                                    </div>
                                    <div class="stat-icon bg-success-subtle text-success">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-card-title mb-1">Inactive Elements</div>
                                        <div class="stat-card-value"><?php echo $inactive_elements; ?></div>
                                    </div>
                                    <div class="stat-icon bg-danger-subtle text-danger">
                                        <i class="bi bi-dash-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-5">
                        <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                            <h5 class="mb-0">Elements List</h5>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Element Name</th>
                                        <th style="width: 20%;">Created By</th>
                                        <th style="width: 20%;">Last Update</th>
                                        <th style="width: 10%;" class="text-center">Status</th>
                                        <th style="width: 15%;" class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                               <tbody>
                                    <?php if (empty($elements)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                    No elements found for this criteria. <a href="add-element.php?criteria_id=<?php echo $criteria_id; ?>">Add one now</a>.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($elements as $elem): 
                                            // 1. Prepare logic for the row click (Target: Score List)
                                            $viewLink = "../score/index.php?element_id=" . $elem['element_ID'];
                                            $isActive = $elem['status'] === 'Active';
                                            
                                            // Logic: Active -> Redirect. Inactive -> Alert.
                                            $rowAction = $isActive 
                                                ? "window.location.href='" . $viewLink . "';" 
                                                : "alert('Action Denied: Please activate this element before viewing scores.');";
                                        ?>
                                            <tr onclick="<?php echo $rowAction; ?>" style="cursor: pointer; transition: background-color 0.2s;">
                                                <td class="ps-4">
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold-dark"><?php echo htmlspecialchars($elem['element_name']); ?></span>
                                                    </div>
                                                </td>
                                                
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold-dark"><?php echo htmlspecialchars($elem['input_username'] ?? 'System'); ?></span>
                                                        <span class="text-muted" style="font-size: 0.75rem;">
                                                            <?php 
                                                            $inputDate = $elem['input_at'] ?? $elem['created_at'] ?? null;
                                                            echo !empty($inputDate) ? date('d/m/Y', strtotime($inputDate)) : '-'; 
                                                            ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold-dark"><?php echo htmlspecialchars($elem['updated_username'] ?? '-'); ?></span>
                                                        <span class="text-muted" style="font-size: 0.75rem;">
                                                            <?php echo !empty($elem['updated_at']) ? date('d/m/Y', strtotime($elem['updated_at'])) : '-'; ?>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    <?php echo getStatusBadge($elem['status']); ?>
                                                </td>
                                                
                                                <td class="text-end pe-4" onclick="event.stopPropagation();">
                                                    <div class="d-flex justify-content-end gap-1">
                                                        
                                                        <a href="edit-element.php?id=<?php echo $elem['element_ID']; ?>" 
                                                            class="btn btn-sm btn-link <?php echo $elem['status'] === 'Active' ? 'text-dark' : 'text-secondary'; ?> px-2" 
                                                            title="Edit"
                                                            onclick="<?php echo ($elem['status'] !== 'Active') ? "alert('Action Denied: Please activate this element before editing.'); return false;" : ""; ?>">
                                                            <i class="bi bi-pencil-square fs-6"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-link <?php echo $elem['status'] === 'Active' ? 'text-danger' : 'text-success'; ?> px-2" 
                                                                onclick="toggleElementStatus('<?php echo $elem['element_ID']; ?>', '<?php echo $elem['status']; ?>')"
                                                                title="<?php echo $elem['status'] === 'Active' ? 'Deactivate' : 'Activate'; ?>">
                                                            <i class="bi bi-<?php echo $elem['status'] === 'Active' ? 'power' : 'check-circle'; ?> fs-6"></i>
                                                        </button>
                                                    </div>
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
        function toggleElementStatus(elementId, currentStatus) {
            const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
            const actionVerb = newStatus === 'Active' ? 'activate' : 'deactivate';

            if (confirm(`Are you sure you want to ${actionVerb} this element?`)) {
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

                const input3 = document.createElement('input');
                input3.type = 'hidden';
                input3.name = 'criteria_id';
                input3.value = '<?php echo $criteria_id; ?>'; 
                
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