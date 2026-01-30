<?php

require_once '../../../config/config.php';
requireRole(['admin']);

// 1. Initialize Database
$db = new Database();

// 2. Handle Delete Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $db->beginTransaction();
    try {
        $survey_id_to_delete = $_POST['survey_ID'];
        
        // 1. Delete links from 'user_survey' bridge table
        $db->query("DELETE FROM user_survey WHERE survey_ID = :sid", [':sid' => $survey_id_to_delete]);

        // 2. Delete links from 'survey_department' bridge table
        $db->query("DELETE FROM survey_department WHERE survey_ID = :sid", [':sid' => $survey_id_to_delete]);

        // 3. Delete from 'surveys' table
        $db->query("DELETE FROM survey WHERE survey_ID = :sid", [':sid' => $survey_id_to_delete]);

        $db->commit();
        setFlashMessage('success', "Survey deleted successfully.");
    
    } catch (Exception $e) {
        $db->rollBack();
        setFlashMessage('error', 'Error deleting survey: ' . $e->getMessage());
    }
    header('Location: index.php');
    exit();
}

// --- 3. Fetch Data for View (GET) ---
try {
    $current_timestamp = date('Y-m-d H:i:s');

    $db->query("UPDATE survey SET status = 'Completed' WHERE status = 'Active' AND end_date < :now", [':now' => $current_timestamp]);
    
    // 1. Base Query
    $sql = "SELECT s.*, 
                u.full_name as created_by_name,
                u2.full_name as updated_by_name,
                GROUP_CONCAT(DISTINCT d.dept_name SEPARATOR ', ') as department_list,
                o.org_name
            FROM survey s
            LEFT JOIN user u ON s.created_by = u.user_ID
            LEFT JOIN user u2 ON s.updated_id = u2.user_ID
            LEFT JOIN organization o ON s.org_ID = o.org_ID
            LEFT JOIN survey_department sd ON s.survey_ID = sd.survey_ID
            LEFT JOIN department d ON sd.dept_ID = d.dept_ID "; 

    $params = [];
    $conditions = [];

    // 2. Apply Filters
    if (!empty($_GET['search'])) {
        $conditions[] = "s.survey_name LIKE :search";
        $params[':search'] = "%" . $_GET['search'] . "%";
    }

    if (!empty($_GET['department'])) {
        $conditions[] = "d.dept_name LIKE :dept";
        $params[':dept'] = "%" . $_GET['department'] . "%";
    }

    if (!empty($_GET['status'])) {
        $conditions[] = "s.status = :status";
        $params[':status'] = $_GET['status'];
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // 3. Apply Grouping
    $sql .= " GROUP BY s.survey_ID";

    // 4. Apply Ordering
    $sql .= " ORDER BY 
                    CASE WHEN s.status = 'Archived' THEN 1 ELSE 0 END ASC, 
                    s.updated_by DESC, 
                    s.created_at DESC";
    
    // 5. Execute Final Query
    $db_surveys = $db->fetchAll($sql, $params); 

} catch (Exception $e) {
    $db_surveys = [];
    setFlashMessage('error', 'Database Error: ' . $e->getMessage());
}

// 5. Helper function for status badges
function getStatusBadge($status, $start_date, $end_date) {
    $current_time = time();
    $start_time = strtotime($start_date);
    $end_time = strtotime($end_date);

    if ($status === 'Draft') {
        return '<span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">Draft</span>';
    }
    if ($status === 'Archived') {
        return '<span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">Archived</span>';
    }

    if ($current_time < $start_time) {
        return '<span class="badge rounded-pill bg-info-subtle text-info-emphasis">Scheduled</span>';
    } elseif ($current_time > $end_time) {
        return '<span class="badge rounded-pill bg-success-subtle text-success-emphasis">Completed</span>';
    } else {
        return '<span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">Active</span>';
    }
}

// 6. Helper for formatting date
function formatShortDate($date) {
    if (!$date) return '-';
    return date('d M Y H:i:A', strtotime($date));
}

// Variables for View
$current_user = getCurrentUser();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Management - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }
        .table th { font-weight: 700; background-color: #9d83b7ff; border-bottom: 2px solid #f0f2f5; color: black; text-transform: uppercase; font-size: 0.75rem; padding: 1rem; }
        .table td { padding: 1rem; vertical-align: top; color: #67748e; font-size: 0.875rem; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08); }
        .col-name { min-width: 230px; }
        .col-desc { max-width: 250px; }
        .col-audit { min-width: 160px; }
        .col-dates { min-width: 140px; }
        .desc-text { cursor: pointer; color: #495057; transition: color 0.2s; }
        .desc-text:hover { color: #667eea; }
        .user-meta { display: flex; flex-direction: column; line-height: 1.3; }
        .user-meta .name { font-weight: 600; color: #344767; }
        .user-meta .date { font-size: 0.75rem; color: #adb5bd; }
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
                            <li class="breadcrumb-item active text-dark" aria-current="page">Survey Management</li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">Survey Management</h3>
                            <p class="text-muted mb-0">Create, manage, and monitor assessment surveys.</p>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="form-survey.php" 
                                class="btn btn-primary shadow-sm px-4 py-2 rounded-3" 
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-plus-lg me-2"></i>Create New Survey
                            </a>
                        </div>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show border-0 shadow-sm mb-4">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm rounded-4 mb-5">
                        <div class="card-header bg-white border-bottom py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 d-inline-block">All Surveys List</h5>
                                <small class="text-muted ms-2">(<?php echo count($db_surveys); ?> records found)</small>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                                <i class="bi bi-funnel me-1"></i>Filters
                            </button>
                        </div>

                        <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
                            <div class="offcanvas-header border-bottom">
                                <h5 class="offcanvas-title" id="filterOffcanvasLabel">Filter Surveys</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                <form method="GET" action="">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Search Name</label>
                                        <input type="text" name="search" class="form-control" placeholder="Survey Name..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Department</label>
                                        <input type="text" name="department" class="form-control" placeholder="e.g. HR, IT..." value="<?php echo htmlspecialchars($_GET['department'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="">All Statuses</option>
                                            <option value="Active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="Archived" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Archived') ? 'selected' : ''; ?>>Archived</option>
                                            <option value="Draft" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Draft') ? 'selected' : ''; ?>>Draft</option>
                                        </select>
                                    </div>
                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">Apply Filters</button>
                                        <a href="index.php" class="btn btn-outline-secondary">Reset All</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4 col-name">Survey Name</th>
                                        <th class="col-desc">Description</th>
                                        <th>Organization</th>
                                        <th class="col-dates">Duration</th>
                                        <th class="col-audit">Audit Info</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($db_surveys)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                    No surveys found. <a href="form-survey.php">Create one now</a>.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($db_surveys as $survey): 
                                            $viewDetailsUrl = "view-details.php?id=" . $survey['survey_ID'];
                                        ?>
                                            <tr onclick="window.location.href='<?php echo $viewDetailsUrl; ?>';" style="cursor: pointer; transition: background-color 0.2s;">
                                                <td class="ps-4">
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($survey['survey_name']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="col-desc">
                                                    <div class="desc-text" 
                                                         onclick="event.stopPropagation();"
                                                         data-bs-toggle="modal" 
                                                         data-bs-target="#descriptionModal" 
                                                         data-survey-title="<?php echo htmlspecialchars($survey['survey_name']); ?>"
                                                         data-survey-desc="<?php echo htmlspecialchars($survey['survey_description']); ?>">
                                                        <?php 
                                                            $desc = $survey['survey_description'];
                                                            echo htmlspecialchars(mb_strimwidth($desc, 0, 50, "...")); 
                                                        ?>
                                                        <?php if(strlen($desc) > 50): ?>
                                                            <small class="text-primary d-block mt-1 fw-bold" style="font-size: 0.75rem;">View Full</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                 <td>
                                                    <span class="fw-bold text-dark small">
                                                        <?php echo htmlspecialchars($survey['org_name'] ?? 'No Org'); ?>
                                                    </span>
                                                    <div class="text-xs text-muted">
                                                        <?php echo htmlspecialchars(mb_strimwidth($survey['department_list'] ?? '', 0, 30, '...')); ?>
                                                    </div>
                                                </td>
                                                <td class="col-dates">
                                                    <div class="d-flex flex-column gap-1">
                                                        <small class="text-success fw-semibold">
                                                            <i class="bi bi-play-circle me-1"></i>
                                                            <?php echo formatShortDate($survey['start_date']); ?>
                                                        </small>
                                                        <small class="text-danger fw-semibold">
                                                            <i class="bi bi-stop-circle me-1"></i>
                                                            <?php echo formatShortDate($survey['end_date']); ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td class="col-audit">
                                                    <div class="user-meta mb-2">
                                                        <span class="text-xs text-dark text-uppercase fw-bold">Created</span>
                                                        <span class="name"><?= htmlspecialchars($survey['created_by_name'] ?? 'System') ?></span>
                                                        <span class="date"><?= date('d M Y', strtotime($survey['created_at'])) ?></span>
                                                    </div>
                                                    <?php if (!empty($survey['updated_by_name']) && $survey['updated_by'] != $survey['created_at']): ?>
                                                        <div class="user-meta">
                                                            <span class="text-xs text-dark text-uppercase fw-bold">Updated</span>
                                                            <span class="name"><?= htmlspecialchars($survey['updated_by_name']) ?></span>
                                                            <span class="date"><?= date('d M Y', strtotime($survey['updated_by'])) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo getStatusBadge($survey['status'], $survey['start_date'], $survey['end_date']); ?>
                                                </td>
                                                <td class="text-end pe-4" onclick="event.stopPropagation();">
                                                    <div class="d-flex justify-content-end gap-1" style="overflow-x: auto;">
                                                        <a href="form-survey.php?id=<?php echo htmlspecialchars($survey['survey_ID']); ?>" 
                                                           class="btn btn-sm btn-link text-primary px-2" 
                                                           title="Edit">
                                                            <i class="bi bi-pencil-square fs-6"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-link text-danger px-2" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteModal"
                                                                data-bs-survey-id="<?php echo htmlspecialchars($survey['survey_ID']); ?>"
                                                                data-bs-survey-name="<?php echo htmlspecialchars($survey['survey_name']); ?>"
                                                                title="Delete">
                                                            <i class="bi bi-trash fs-6"></i>
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

    <div class="modal fade" id="descriptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="modalSurveyTitle">Survey Description</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p id="modalSurveyDesc" class="text-muted mb-0"></p>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="deleteForm" method="POST" action="index.php">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="delete_survey_ID" name="survey_ID" value="">
                    
                    <div class="modal-header border-bottom-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="text-danger mb-3">
                            <i class="bi bi-exclamation-circle display-1"></i>
                        </div>
                        <h4 class="mb-2">Are you sure?</h4>
                        <p class="text-muted px-4">
                            Do you really want to delete the survey "<strong id="deleteSurveyName" class="text-dark"></strong>"? 
                            This process cannot be undone.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center border-top-0 pt-0 pb-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4">Delete Survey</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const descModal = document.getElementById('descriptionModal');
        if (descModal) {
            descModal.addEventListener('show.bs.modal', event => {
                const trigger = event.relatedTarget;
                const title = trigger.getAttribute('data-survey-title');
                const desc = trigger.getAttribute('data-survey-desc');
                document.getElementById('modalSurveyTitle').textContent = title;
                document.getElementById('modalSurveyDesc').textContent = desc || 'No description provided.';
            });
        }
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const surveyId = button.getAttribute('data-bs-survey-id');
                const surveyName = button.getAttribute('data-bs-survey-name');
                document.getElementById('delete_survey_ID').value = surveyId;
                document.getElementById('deleteSurveyName').textContent = surveyName;
            });
        }
    </script>
</body>
</html>