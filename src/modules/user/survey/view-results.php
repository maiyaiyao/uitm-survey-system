<?php
/**
 * View Survey Results
 * Redesigned to match system styling.
 */

require_once '../../../config/config.php';
requireRole(['user']);

$db = new Database();
$current_user_id = getCurrentUserId();

// --- 1. Validation ---
$survey_id = $_GET['id'] ?? null;

if (!$survey_id) {
    setFlashMessage('danger', 'Invalid survey ID.');
    redirect('index.php');
}

// Check assignment
$assignment = $db->fetchOne("
    SELECT us.*, s.survey_name, s.department, s.end_date, s.survey_description
    FROM user_survey us
    JOIN survey s ON us.survey_ID = s.survey_ID
    WHERE us.survey_ID = :sid AND us.user_ID = :uid
", [':sid' => $survey_id, ':uid' => $current_user_id]);

if (!$assignment) {
    setFlashMessage('danger', 'Survey assignment not found.');
    redirect('index.php');
}

// --- 2. Data Fetching ---

// Fetch Hierarchical Data
$sql_structure = "
    SELECT 
        d.domain_ID, d.domain_name,
        c.criteria_ID, c.criteria_name,
        e.element_ID, e.element_name
    FROM survey_domain sd
    JOIN domain d ON sd.domain_ID = d.domain_ID
    JOIN criteria c ON d.domain_ID = c.domain_ID
    JOIN element e ON c.criteria_ID = e.criteria_ID
    WHERE sd.survey_ID = :sid 
    AND c.status = 'Active' 
    AND e.status = 'Active'
    ORDER BY d.domain_ID, c.criteria_ID, e.element_ID ASC
";

$raw_structure = $db->fetchAll($sql_structure, [':sid' => $survey_id]);

// Fetch User Responses
$sql_responses = "
    SELECT 
        r.element_ID, 
        r.score, 
        r.input_at,
        r.updated_at,
        se.details AS selected_detail,
        s.desc_level AS selected_level_desc
    FROM response r
    LEFT JOIN score_element se ON r.se_ID = se.se_ID
    LEFT JOIN score s ON se.score_ID = s.score_ID
    WHERE r.user_ID = :uid AND r.survey_ID = :sid
";
$raw_responses = $db->fetchAll($sql_responses, [':uid' => $current_user_id, ':sid' => $survey_id]);

$user_responses = [];
foreach ($raw_responses as $resp) {
    $user_responses[$resp['element_ID']] = $resp;
}

// Group Data
$report_data = [];
foreach ($raw_structure as $row) {
    $d_id = $row['domain_ID'];
    $c_id = $row['criteria_ID'];
    $e_id = $row['element_ID'];
    
    if (!isset($report_data[$d_id])) {
        $report_data[$d_id] = [
            'name' => $row['domain_name'],
            'criteria' => []
        ];
    }
    if (!isset($report_data[$d_id]['criteria'][$c_id])) {
        $report_data[$d_id]['criteria'][$c_id] = [
            'name' => $row['criteria_name'],
            'elements' => []
        ];
    }
    
    $response = $user_responses[$e_id] ?? null;
    $report_data[$d_id]['criteria'][$c_id]['elements'][$e_id] = [
        'name' => $row['element_name'],
        'response' => $response
    ];
}

// Stats
$total_elements = count($raw_structure);
$answered_elements = count($user_responses);
$completion_rate = ($total_elements > 0) ? round(($answered_elements / $total_elements) * 100) : 0;

function getScoreColor($score) {
    if ($score >= 5) return 'success';
    if ($score >= 4) return 'primary';
    if ($score == 3) return 'info';
    if ($score == 2) return 'warning';
    return 'danger';
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Results: <?php echo htmlspecialchars($assignment['survey_name']); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Global System Styling */
        html, body { height: 100%; background-color: #f8f9fa; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        /* Cards */
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.05); }
        .card-header-clean { background: transparent; border-bottom: 1px solid rgba(0,0,0,0.05); padding: 1.5rem; }

        /* Typography */
        .domain-title { font-weight: 800; color: #32325d; letter-spacing: -0.5px; font-size: 1.5rem; }
        .criteria-badge { background-color: #e9ecef; color: #495057; font-size: 0.75rem; font-weight: 700; padding: 5px 10px; border-radius: 8px; text-transform: uppercase; margin-bottom: 10px; display: inline-block; }

        /* Result Specifics */
        .score-circle-lg {
            width: 50px; height: 50px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; font-weight: 800; color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        /* Important: Handles the bullet points from database */
        .result-detail-text {
            white-space: pre-wrap; 
            color: #525f7f;
            line-height: 1.6;
            font-size: 0.9rem;
        }

        .btn-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; }
        .btn-gradient-primary:hover { opacity: 0.9; color: white; }

        /* Print Styles */
        @media print {
            .sidebar, .btn-print, .breadcrumb, .no-print { display: none !important; }
            .main-content-wrapper { margin-left: 0 !important; width: 100% !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; break-inside: avoid; }
            body { background-color: white !important; }
            .container-fluid { padding: 0 !important; }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <div class="col-md-3 col-lg-2 sidebar">
                <?php include_once __DIR__ . '/../../includes/user_sidebar.php'; ?>
            </div>
            
            <div class="col-md-9 col-lg-10 main-content-wrapper">
                <div class="main-content px-4 py-4">

                    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">My Surveys</a></li>
                                <li class="breadcrumb-item active text-dark" aria-current="page">View Results</li>
                            </ol>
                        </nav>
                        <div class="d-flex gap-2">
                            <button onclick="window.print()" class="btn btn-white shadow-sm btn-print text-primary border-0">
                                <i class="bi bi-printer me-2"></i> Print Report
                            </button>
                            <a href="index.php" class="btn btn-secondary btn-print rounded-pill px-4">Back</a>
                        </div>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> no-print rounded-4 border-0 shadow-sm mb-4"><?php echo $flash['message']; ?></div>
                    <?php endif; ?>

                    <div class="card mb-5 border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <span class="badge bg-light text-primary border mb-2"><?php echo htmlspecialchars($assignment['department']); ?></span>
                                    <h2 class="fw-bold mb-2 text-dark"><?php echo htmlspecialchars($assignment['survey_name']); ?></h2>
                                    <p class="text-muted mb-4"><?php echo htmlspecialchars($assignment['survey_description']); ?></p>
                                    
                                    <div class="d-flex flex-wrap gap-4 text-sm">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-light rounded-circle p-2 text-primary"><i class="bi bi-info-circle-fill"></i></div>
                                            <div>
                                                <small class="text-uppercase text-muted fw-bold d-block" style="font-size: 0.7rem;">Status</small>
                                                <span class="fw-bold text-<?php echo $assignment['status'] === 'Completed' ? 'success' : 'warning'; ?>">
                                                    <?php echo strtoupper($assignment['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-light rounded-circle p-2 text-primary"><i class="bi bi-calendar-event-fill"></i></div>
                                            <div>
                                                <small class="text-uppercase text-muted fw-bold d-block" style="font-size: 0.7rem;">Due Date</small>
                                                <span class="fw-bold text-dark"><?php echo date('d M Y', strtotime($assignment['end_date'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-light rounded-circle p-2 text-primary"><i class="bi bi-graph-up-arrow"></i></div>
                                            <div>
                                                <small class="text-uppercase text-muted fw-bold d-block" style="font-size: 0.7rem;">Completion</small>
                                                <span class="fw-bold text-dark"><?php echo $completion_rate; ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-center d-none d-lg-block border-start">
                                    <div class="position-relative d-inline-block">
                                        <svg width="120" height="120" viewBox="0 0 36 36">
                                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#f0f2f5" stroke-width="2" />
                                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                                  fill="none" stroke="<?php echo ($completion_rate == 100) ? '#2dce89' : '#5e72e4'; ?>" 
                                                  stroke-width="2" stroke-dasharray="<?php echo $completion_rate; ?>, 100" />
                                        </svg>
                                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                                            <div class="h3 fw-bold mb-0 text-dark"><?php echo $completion_rate; ?>%</div>
                                            <div class="small text-muted" style="font-size: 0.65rem;">COMPLETE</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($report_data)): ?>
                        <div class="text-center p-5 card shadow-sm rounded-4">
                            <h4 class="text-muted">No questions found.</h4>
                        </div>
                    <?php else: ?>
                        
                        <?php foreach ($report_data as $d_id => $domain): ?>
                            <div class="mb-5 break-inside-avoid">
                                <h3 class="domain-title mb-4 ps-2 border-start border-4 border-primary"><?php echo htmlspecialchars($domain['name']); ?></h3>

                                <?php foreach ($domain['criteria'] as $c_id => $criteria): ?>
                                    <div class="card mb-4 rounded-4 shadow-sm border-0">
                                        <div class="card-header-clean">
                                            <span class="criteria-badge"><i class="bi bi-tag-fill me-1"></i> Criteria</span>
                                            <h5 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($criteria['name']); ?></h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <?php foreach ($criteria['elements'] as $e_id => $element): 
                                                $resp = $element['response'];
                                                $score = $resp['score'] ?? 0;
                                                $color = getScoreColor($score);
                                            ?>
                                                <div class="p-4 border-bottom last-no-border">
                                                    <h6 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($element['name']); ?></h6>
                                                    
                                                    <div class="bg-light p-3 rounded-3 d-flex gap-3 align-items-start">
                                                        <?php if ($score > 0): ?>
                                                            <div class="score-circle-lg bg-<?php echo $color; ?> flex-shrink-0">
                                                                <?php echo $score; ?>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold text-<?php echo $color; ?> text-uppercase small mb-1">
                                                                    <?php echo htmlspecialchars($resp['selected_level_desc'] ?? "Level $score"); ?>
                                                                </div>
                                                                
                                                                <div class="result-detail-text"><?php echo htmlspecialchars(trim($resp['selected_detail'] ?? 'No detail provided.')); ?></div>
                                                                
                                                                <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                                                                    <i class="bi bi-clock me-1"></i> Submitted: <?php echo date('d M Y, h:i A', strtotime($resp['input_at'])); ?>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="score-circle-lg bg-secondary text-white-50 flex-shrink-0">?</div>
                                                            <div class="align-self-center text-muted fst-italic">
                                                                Not answered.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                    <div class="d-none d-print-block text-center mt-5 pt-5 border-top">
                        <p class="text-muted small">
                            Report generated on <?php echo date('d M Y, h:i A'); ?>.<br>
                            <?php echo APP_NAME; ?> - User Result Report.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>