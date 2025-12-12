<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$element_id = $_GET['element_id'] ?? null;
if (!$element_id) {
    setFlashMessage('danger', 'Element ID missing.');
    redirect(BASE_URL . '/modules/admin/domain/index.php');
}

$db = new Database();

// Get Element Details (Added status to selection if available, assuming element table has 'status')
$element = $db->fetchOne("
    SELECT e.*, c.criteria_name, c.criteria_ID, d.domain_ID, d.domain_name
    FROM element e 
    JOIN criteria c ON e.criteria_ID = c.criteria_ID
    JOIN domain d ON c.domain_ID = d.domain_ID
    WHERE e.element_ID = :id", [':id' => $element_id]);

if (!$element) { die("Element not found."); }

// Get Existing Scores for this Element
$scores = $db->fetchAll("
    SELECT se.*, 
           s.score_level, 
           s.desc_level, 
           u_create.full_name as input_username,  
           u_update.full_name as updated_username 
    FROM score_element se
    JOIN score s ON se.score_ID = s.score_ID
    LEFT JOIN user u_create ON se.input_id = u_create.user_ID    
    LEFT JOIN user u_update ON se.updated_id = u_update.user_ID  
    WHERE se.element_ID = :eid
    ORDER BY s.score_level ASC
", [':eid' => $element_id]);

$flash = getFlashMessage();

// Calculate Summary Stats
$total_scores_defined = count($scores);
$element_status = $element['status'] ?? 'Active'; // Fallback if status column doesn't exist in your schema
$criteria_name = $element['criteria_name'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Scores - <?php echo APP_NAME; ?></title>
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
                            <li class="breadcrumb-item">
                                <a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a>
                            </li>
                            
                            <li class="breadcrumb-item">
                                <a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="../criteria/view-criteria.php?id=<?php echo $element['domain_ID']; ?>" 
                                class="text-decoration-none text-secondary"
                                title="Domain: <?php echo htmlspecialchars($element['domain_name']); ?>"> Domain <?php echo htmlspecialchars(truncate($element['domain_name'], 20)); ?>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="../element/view-element.php?id=<?php echo $element['criteria_ID']; ?>" 
                                class="text-decoration-none text-secondary"
                                title="Criteria: <?php echo htmlspecialchars($element['criteria_name']); ?>"> Criteria <?php echo htmlspecialchars(truncate($element['criteria_name'], 20)); ?> 
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-dark" aria-current="page"
                                title="Element: <?php echo htmlspecialchars($element['element_name']); ?>"> Element <?php echo htmlspecialchars(truncate($element['element_name'], 25)); ?> 
                            </li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div>
                            <p class="text-muted mb-0 mt-1 ms-1">
                                Criteria: <span class="fw-semibold"><?php echo htmlspecialchars($criteria_name); ?></span>
                            </p>
                            <div class="d-flex align-items-center gap-2">
                                <a href="../element/view-element.php?id=<?php echo htmlspecialchars($element['criteria_ID']); ?>" class="btn btn-outline-secondary btn-sm rounded-circle" title="Back">
                                    <i class="bi bi-arrow-left"></i>
                                </a>
                                <h3 class="fw-bold mb-0">Element - <?php echo htmlspecialchars($element['element_name']); ?></h3>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 ms-auto">
                            <a href="form-score.php?element_id=<?php echo $element_id; ?>" 
                               class="btn btn-primary shadow-sm px-3 rounded-3"
                               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-plus-lg me-2"></i>Add Description
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
                                        <div class="stat-card-title mb-1">Total Definitions</div>
                                        <div class="stat-card-value"><?php echo $total_scores_defined; ?></div>
                                    </div>
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="bi bi-list-ol"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-card-title mb-1">Parent Criteria</div>
                                        <div class="fw-bold-dark text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($criteria_name); ?>">
                                            <?php echo htmlspecialchars($criteria_name); ?>
                                        </div>
                                    </div>
                                    <div class="stat-icon bg-info-subtle text-info">
                                        <i class="bi bi-diagram-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="stat-card-title mb-1">Element Status</div>
                                        <div class="fw-bold-dark">
                                            <?php if ($element_status === 'Active'): ?>
                                                <span class="text-success">Active</span>
                                            <?php else: ?>
                                                <span class="text-danger">Inactive</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="stat-icon <?php echo $element_status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                        <i class="bi bi-activity"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-5">
                        <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                            <h5 class="mb-0">Score Descriptions</h5>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="width: 15%;">Level</th>
                                        <th style="width: 20%;">Global Desc</th>
                                        <th>Detailed Description (For this Element)</th>
                                        <th style="width: 15%;">Created By</th>
                                        <th style="width: 15%;">Updated By</th>
                                        <th style="width: 10%;" class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($scores)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-journal-x display-4 d-block mb-2"></i>
                                                    No specific score descriptions added yet. 
                                                    <a href="form-score.php?element_id=<?php echo $element_id; ?>">Add one now</a>.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($scores as $score): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <span class="badge bg-primary fs-6 shadow-sm">Level <?php echo $score['score_level']; ?></span>
                                                </td>
                                                <td class="fw-semibold text-dark">
                                                    <?php echo htmlspecialchars($score['desc_level']); ?>
                                                </td>
                                                <td>
                                                    <span class="d-block text-secondary" style="line-height: 1.6;">
                                                        <?php echo nl2br(htmlspecialchars($score['details'])); ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold-dark"><?php echo htmlspecialchars($score['input_username'] ?? 'System'); ?></span>
                                                        <span class="text-muted" style="font-size: 0.75rem;">
                                                            <?php 
                                                            $inputDate = $score['input_at'] ?? $score['created_at'] ?? null;
                                                            echo !empty($inputDate) ? date('d/m/Y', strtotime($inputDate)) : '-'; 
                                                            ?>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold-dark"><?php echo htmlspecialchars($score['updated_username'] ?? '-'); ?></span>
                                                        <span class="text-muted" style="font-size: 0.75rem;">
                                                            <?php echo !empty($score['updated_at']) ? date('d/m/Y', strtotime($score['updated_at'])) : '-'; ?>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td class="text-end pe-4">
                                                    <a href="form-score.php?element_id=<?php echo $element_id; ?>&se_id=<?php echo $score['se_ID']; ?>" 
                                                    class="btn btn-sm btn-link text-primary px-2" 
                                                    title="Edit">
                                                        <i class="bi bi-pencil-square fs-6"></i>
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