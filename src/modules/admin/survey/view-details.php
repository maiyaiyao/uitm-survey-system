<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$survey_id = $_GET['id'] ?? null;

if (!$survey_id) {
    setFlashMessage('danger', 'Invalid Survey ID.');
    redirect('index.php');
}

// 1. Fetch Survey Metadata
$survey = $db->fetchOne("
    SELECT s.*, 
           uc.full_name AS created_by_name,
           uu.full_name AS updated_by_name
    FROM survey s
    LEFT JOIN user uc ON s.created_by = uc.user_ID
    LEFT JOIN user uu ON s.updated_id = uu.user_ID
    WHERE s.survey_ID = :id
", [':id' => $survey_id]);

if (!$survey) {
    setFlashMessage('danger', 'Survey not found.');
    redirect('index.php');
}

// 2. Fetch Linked Domains
$domains = $db->fetchAll("
    SELECT d.domain_ID, d.domain_name, d.status
    FROM survey_domain sd
    JOIN domain d ON sd.domain_id = d.domain_ID
    WHERE sd.survey_id = :id
    ORDER BY d.domain_name ASC
", [':id' => $survey_id]);

// 3. Fetch Linked Participants (Users)
$participants = $db->fetchAll("
    SELECT u.full_name, u.primary_email, u.department, us.status as completion_status
    FROM user_survey us
    JOIN user u ON us.user_ID = u.user_ID
    WHERE us.survey_ID = :id
    ORDER BY u.full_name ASC
", [':id' => $survey_id]);

// Calculate Stats
$total_participants = count($participants);
$completed_count = count(array_filter($participants, fn($p) => $p['completion_status'] === 'Completed'));
$completion_rate = ($total_participants > 0) ? round(($completed_count / $total_participants) * 100) : 0;

// Helper: Status Badge
function getStatusBadge($status) {
    $colors = [
        'Active' => 'success',
        'Draft' => 'warning', 
        'Archived' => 'secondary',
        'Completed' => 'info'
    ];
    $color = $colors[$status] ?? 'secondary';
    return "<span class='badge bg-{$color}-subtle text-{$color}-emphasis border border-{$color}-subtle'>{$status}</span>";
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Details - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Shared Admin Styles */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .sidebar { position: relative; width: 100%; height: auto; } .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .info-label { font-weight: 600; color: #6c757d; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-size: 1rem; color: #343a40; font-weight: 500; }
        
        /* Stats Circle */
        .progress-circle { width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 8px solid #e9ecef; position: relative; margin: 0 auto; }
        .progress-circle.high { border-color: #198754; }
        .progress-circle.med { border-color: #ffc107; }
        .progress-circle.low { border-color: #dc3545; }
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
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">Survey Management</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page">Survey Details</li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h3 class="fw-bold mb-1">Survey Configuration</h3>
                            <p class="text-muted mb-0">Detailed settings and assignment status for <strong><?php echo htmlspecialchars($survey['survey_ID']); ?></strong></p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-outline-secondary px-4 rounded-3">
                                <i class="bi bi-arrow-left me-2"></i>Back
                            </a>
                            <a href="form-survey.php?id=<?php echo htmlspecialchars($survey_id); ?>" 
                               class="btn btn-primary px-4 rounded-3 shadow-sm"
                               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-pencil-square me-2"></i>Edit Survey
                            </a>
                        </div>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show border-0 shadow-sm mb-4">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row g-4">
                        
                        <div class="col-lg-8">
                            <div class="card h-100">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-info-circle me-2"></i>General Information</h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <div class="info-label mb-1">Survey Name</div>
                                                <div class="info-value"><?php echo htmlspecialchars($survey['survey_name']); ?></div>
                                            </div>
                                            <div class="mb-4">
                                                <div class="info-label mb-1">Target Department</div>
                                                <div class="info-value"><?php echo htmlspecialchars($survey['department']); ?></div>
                                            </div>
                                            <div class="mb-4">
                                                <div class="info-label mb-1">Current Status</div>
                                                <div class="info-value"><?php echo getStatusBadge($survey['status']); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <div class="info-label mb-1">Start Date</div>
                                                <div class="info-value"><i class="bi bi-calendar-event me-2 text-muted"></i><?php echo formatDate($survey['start_date'], 'd M Y, h:i A'); ?></div>
                                            </div>
                                            <div class="mb-4">
                                                <div class="info-label mb-1">End Date</div>
                                                <div class="info-value"><i class="bi bi-flag me-2 text-muted"></i><?php echo formatDate($survey['end_date'], 'd M Y, h:i A'); ?></div>
                                            </div>
                                            <div class="mb-4">
                                                <div class="info-label mb-1">Last Updated</div>
                                                <div class="small text-muted">
                                                    By <?php echo htmlspecialchars($survey['updated_by_name'] ?? 'System'); ?><br>
                                                    on <?php echo formatDate($survey['updated_by']); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="p-3 bg-light rounded-3">
                                                <div class="info-label mb-2">Description</div>
                                                <p class="mb-0 text-secondary"><?php echo nl2br(htmlspecialchars($survey['survey_description'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="mb-0 fw-bold text-success"><i class="bi bi-graph-up me-2"></i>Progress Tracker</h6>
                                </div>
                                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                                    
                                    <?php 
                                        $circle_class = 'low';
                                        if($completion_rate > 75) $circle_class = 'high';
                                        elseif($completion_rate > 40) $circle_class = 'med';
                                    ?>
                                    
                                    <div class="progress-circle <?php echo $circle_class; ?> mb-3">
                                        <div>
                                            <div class="h2 fw-bold mb-0"><?php echo $completion_rate; ?>%</div>
                                            <div class="small text-muted" style="font-size: 0.7rem;">COMPLETED</div>
                                        </div>
                                    </div>

                                    <div class="row text-center mt-3">
                                        <div class="col-6 border-end">
                                            <div class="h4 fw-bold mb-0"><?php echo $total_participants; ?></div>
                                            <div class="small text-muted text-uppercase">Assigned</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="h4 fw-bold mb-0 text-success"><?php echo $completed_count; ?></div>
                                            <div class="small text-muted text-uppercase">Finished</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-light border-top text-center py-3">
                                    <a href="preview.php?id=<?php echo $survey_id; ?>" class="btn btn-outline-dark btn-sm w-100">
                                        <i class="bi bi-eye me-2"></i>Preview Questions
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-layers me-2 text-warning"></i>Audit Scope (Domains)</h6>
                                    <span class="badge bg-secondary rounded-pill"><?php echo count($domains); ?></span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <?php if(empty($domains)): ?>
                                            <div class="p-4 text-center text-muted">No domains linked.</div>
                                        <?php else: ?>
                                            <?php foreach($domains as $d): ?>
                                                <div class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($d['domain_name']); ?></strong>
                                                        <div class="small text-muted"><?php echo $d['domain_ID']; ?></div>
                                                    </div>
                                                    <?php if($d['status'] !== 'Active'): ?>
                                                        <span class="badge bg-danger-subtle text-danger-emphasis">Inactive Domain</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-info"></i>Authorized Participants</h6>
                                    <span class="badge bg-secondary rounded-pill"><?php echo count($participants); ?></span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 400px;">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th class="ps-4">Name</th>
                                                    <th>Email</th>
                                                    <th class="text-end pe-4">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(empty($participants)): ?>
                                                    <tr><td colspan="3" class="text-center py-4 text-muted">No participants assigned.</td></tr>
                                                <?php else: ?>
                                                    <?php foreach($participants as $p): ?>
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($p['full_name']); ?></div>
                                                                <div class="small text-muted"><?php echo htmlspecialchars($p['department'] ?? '-'); ?></div>
                                                            </td>
                                                            <td class="small text-muted"><?php echo htmlspecialchars($p['primary_email']); ?></td>
                                                            <td class="text-end pe-4">
                                                                <?php if($p['completion_status'] == 'Completed'): ?>
                                                                    <span class="badge bg-success"><i class="bi bi-check-lg"></i> Done</span>
                                                                <?php elseif($p['completion_status'] == 'In progress'): ?>
                                                                    <span class="badge bg-warning text-dark">In Progress</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">Pending</span>
                                                                <?php endif; ?>
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

                    </div> </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>