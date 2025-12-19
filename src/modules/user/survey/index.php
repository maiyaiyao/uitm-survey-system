<?php
/**
 * My Surveys (Survey List)
 * Displays all surveys assigned to the current user.
 */

require_once '../../../config/config.php';
requireRole(['user']);

$db = new Database();
$user_ID = getCurrentUserId();
$current_user = getCurrentUser();

// --- 1. Handling Tabs & Search ---
$tab = $_GET['tab'] ?? 'active'; // 'active' or 'history'
$search = $_GET['search'] ?? '';

// --- 2. Build Query ---
// We join 'survey' (Admin side details) with 'user_survey' (User specific status)
$sql = "SELECT s.*, 
               us.status AS user_status,
               us.user_survey_ID
        FROM survey s
        INNER JOIN user_survey us ON s.survey_ID = us.survey_ID
        WHERE us.user_ID = :user_ID";

$params = [':user_ID' => $user_ID];

// Tab Logic
if ($tab === 'history') {
    // History: User completed OR Survey is archived/completed by admin
    $sql .= " AND (us.status = 'Completed' OR s.status IN ('Completed', 'Archived'))";
} else {
    // Active: User NOT completed AND Survey is Active
    $sql .= " AND (us.status != 'Completed' AND s.status = 'Active')";
}
if (isset($_GET['status']) && $_GET['status'] === 'In_progress') {
    $sql .= " AND us.status = 'In progress'";
}

// Search Logic
if (!empty($search)) {
    $sql .= " AND (s.survey_name LIKE :search1 OR s.department LIKE :search2)";
    
    $searchTerm = "%$search%";
    $params[':search1'] = $searchTerm;
    $params[':search2'] = $searchTerm;
}

// Ordering
$sql .= " ORDER BY s.end_date ASC";

// Execute Query
$surveys = $db->fetchAll($sql, $params);

// --- 3. Helper Functions ---
function getBadge($user_status, $survey_status, $end_date) {
    $now = new DateTime();
    $due = new DateTime($end_date);
    
    // 1. Check User Status
    if ($user_status === 'Completed') {
        return '<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Completed</span>';
    }
    
    // 2. Check Survey Status (Admin Level)
    if ($survey_status !== 'Active') {
        return '<span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">Closed</span>';
    }
    
    // 3. Check Due Date
    if ($now > $due) {
        return '<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Overdue</span>';
    }
    
    // 4. Default Progress Status
    if ($user_status === 'In progress') {
        return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">In Progress</span>';
    }
    
    return '<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">Pending</span>';
}

function getActionBtn($user_status, $survey_status, $survey_id) {
    // If completed or closed, show "View"
    if ($user_status === 'Completed' || $survey_status !== 'Active') {
        return '<a href="view-results.php?id=' . $survey_id . '" class="btn btn-sm btn-outline-secondary px-3"><i class="bi bi-eye me-1"></i> View</a>';
    }
    
    // Otherwise show "Start" or "Continue"
    $label = ($user_status === 'In progress') ? 'Continue' : 'Start';
    $icon = ($user_status === 'In progress') ? 'bi-play-fill' : 'bi-clipboard-check';
    
    return '<a href="assessment.php?survey_id=' . $survey_id . '" class="btn btn-sm btn-primary px-3 shadow-sm btn-action">
                <i class="bi ' . $icon . ' me-1"></i> ' . $label . '
            </a>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Surveys - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Shared Dashboard Styles */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; width: 270px; z-index: 100; padding: 0; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); color: white; display: flex; flex-direction: column; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .sidebar { position: relative; width: 100%; height: auto; } .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        /* Page Specific Styles */
        .nav-tabs .nav-link { color: #6c757d; border: none; border-bottom: 3px solid transparent; padding: 1rem 1.5rem; font-weight: 500; transition: all 0.2s; }
        .nav-tabs .nav-link:hover { color: #5e72e4; background: transparent; }
        .nav-tabs .nav-link.active { color: #5e72e4; border-bottom: 3px solid #5e72e4; background: transparent; }
        
        .table th { font-weight: 600; background-color: #f8f9fa; color: #444; font-size: 0.8rem; text-transform: uppercase; padding: 1rem; border-bottom: 1px solid #e9ecef; }
        .table td { padding: 1rem; vertical-align: middle; color: #525f7f; font-size: 0.9rem; }
        .table-hover tbody tr:hover { background-color: #fcfcfc; }
        
        .btn-action { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; transition: transform 0.2s; }
        .btn-action:hover { opacity: 0.9; transform: translateY(-1px); color: white; }

        /* Description Modal Trigger Styles */
        .desc-text {
            cursor: pointer;
            color: #495057;
            transition: color 0.2s;
        }
        .desc-text:hover {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <?php include_once __DIR__ . '/../../includes/user_sidebar.php'; ?>
            
            <div class="col-md-9 col-lg-10 main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-2">
                                    <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-dark" aria-current="page">My Surveys</li>
                                </ol>
                            </nav>
                            <h3 class="fw-bold mb-0">My Assigned Surveys</h3>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="card-header bg-white border-bottom-0 p-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-center px-4 pt-3 pb-0">
                                <ul class="nav nav-tabs card-header-tabs mx-0 border-bottom-0">
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($tab === 'active') ? 'active' : ''; ?>" 
                                           href="?tab=active">
                                            <i class="bi bi-hourglass-split me-2"></i>Active Tasks
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($tab === 'history') ? 'active' : ''; ?>" 
                                           href="?tab=history">
                                            <i class="bi bi-clock-history me-2"></i>History
                                        </a>
                                    </li>
                                </ul>

                                <form method="GET" class="mb-2 mb-md-0 pb-2 d-flex align-items-center gap-2">
                                    <small class="text-muted d-none d-md-block text-nowrap"><?php echo count($surveys); ?> records</small>
                                    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" 
                                               placeholder="Search surveys..." value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="width: 25%;">Survey Name</th>
                                        <th style="width: 30%;">Description</th>
                                        <th style="width: 15%;">Department</th>
                                        <th style="width: 15%;">Timeline</th>
                                        <th class="text-center" style="width: 10%;">Status</th>
                                        <th class="text-end pe-4" style="width: 5%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($surveys)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <div class="mb-3"><i class="bi bi-clipboard-x display-4 opacity-25"></i></div>
                                                    <h5>No surveys found</h5>
                                                    <p class="small">There are no <?php echo htmlspecialchars($tab); ?> surveys assigned to you at the moment.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($surveys as $row): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['survey_name']); ?></div>
                                                </td>
                                                
                                                <td>
                                                    <div class="desc-text" 
                                                         data-bs-toggle="modal" 
                                                         data-bs-target="#descriptionModal" 
                                                         data-survey-title="<?php echo htmlspecialchars($row['survey_name']); ?>"
                                                         data-survey-desc="<?php echo htmlspecialchars($row['survey_description']); ?>">
                                                        <?php 
                                                            $desc = $row['survey_description'] ?? 'No description provided';
                                                            echo htmlspecialchars(mb_strimwidth($desc, 0, 50, "...")); 
                                                        ?>
                                                        <?php if(strlen($desc) > 50): ?>
                                                            <small class="text-primary d-block mt-1 fw-bold" style="font-size: 0.75rem;">
                                                                View Full
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon-shape bg-light text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                                            <i class="bi bi-building"></i>
                                                        </div>
                                                        <span class="fw-semibold text-secondary"><?php echo htmlspecialchars($row['department']); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column small">
                                                        <span class="text-muted">Due: <span class="text-dark fw-semibold"><?php echo date('d M Y', strtotime($row['end_date'])); ?></span></span>
                                                        <span class="text-muted" style="font-size: 0.75rem;">Started: <?php echo date('d M Y', strtotime($row['start_date'])); ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo getBadge($row['user_status'], $row['status'], $row['end_date']); ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <?php echo getActionBtn($row['user_status'], $row['status'], $row['survey_ID']); ?>
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
                    <p id="modalSurveyDesc" class="text-muted mb-0" style="white-space: pre-wrap;"></p>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Description Modal Logic
        const descModal = document.getElementById('descriptionModal');
        if (descModal) {
            descModal.addEventListener('show.bs.modal', event => {
                // Button that triggered the modal
                const trigger = event.relatedTarget;
                // Extract info from data-* attributes
                const title = trigger.getAttribute('data-survey-title');
                const desc = trigger.getAttribute('data-survey-desc');
                
                // Update the modal's content.
                document.getElementById('modalSurveyTitle').textContent = title;
                document.getElementById('modalSurveyDesc').textContent = desc || 'No description provided.';
            });
        }
    </script>
</body>
</html>