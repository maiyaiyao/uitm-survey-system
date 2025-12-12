<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$search = $_GET['search'] ?? '';
$params = [];

// Fetch all elements with Criteria and Domain info + Audit Logs (Created/Updated By)
$sql = "
    SELECT e.*, 
           c.criteria_name, c.criteria_ID, 
           d.domain_name, d.domain_ID,
           uc.full_name AS created_by_name,
           uu.full_name AS updated_by_name
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
        /* Page Layout */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }

        /* Card & Table Styles */
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        
        .table th {
            font-weight: 700;
            background-color: #9d83b7ff; /* Purple Header */
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
                            <li class="breadcrumb-item"><a href="../criteria/index.php" class="text-decoration-none text-secondary">Criteria</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page">Element</li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="fw-bold mb-1">All Elements</h3>
                            <p class="text-muted mb-0">Master list of assessment elements.</p>
                        </div>
                        <form method="GET" class="d-flex">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control border-0 shadow-sm" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-white bg-white shadow-sm border-0"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                    </div>

                    <?php if ($flash): ?><div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div><?php endif; ?>

                    <div class="card">
                        <div class="card-header bg-white py-3"><h5 class="mb-0">Elements List</h5></div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 35%;">Element Name</th>
                                        <th>Context (Domain > Criteria)</th>
                                        <th>Created</th>
                                        <th>Last Updated</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_elements as $row): 
                                        // Row click redirects to the Criteria View where this element belongs
                                        $redirectUrl = "view-element.php?id=" . $row['criteria_ID'];
                                    ?>
                                    <tr onclick="window.location.href='<?php echo $redirectUrl; ?>';" style="cursor: pointer; transition: background-color 0.2s;">
                                        
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark">
                                                <?php echo htmlspecialchars(mb_strimwidth($row['element_name'], 0, 60, "...")); ?>
                                            </span>
                                        </td>
                                        
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small class="fw-bold text-dark"><?php echo htmlspecialchars($row['domain_name']); ?></small>
                                                <small class="text-muted"><i class="bi bi-arrow-return-right me-1"></i><?php echo htmlspecialchars(mb_strimwidth($row['criteria_name'], 0, 40, "...")); ?></small>
                                            </div>
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
                                            <span class="badge rounded-pill <?php echo $row['status'] === 'Active' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis'; ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
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

