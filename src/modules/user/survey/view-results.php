<?php
/**
 * View Survey Results
 * Read-only report of the user's submitted assessment.
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

// Fetch Hierarchical Data (Domain -> Criteria -> Element)
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

// Fetch User Responses with Details
// We join response -> score_element -> score to get the text descriptions of what they chose
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
    WHERE r.user_ID = :uid
";
$raw_responses = $db->fetchAll($sql_responses, [':uid' => $current_user_id]);

// Map responses by element_ID for easy lookup
$user_responses = [];
foreach ($raw_responses as $resp) {
    $user_responses[$resp['element_ID']] = $resp;
}

// Group Structure Data
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
    
    // Attach user response if exists
    $response = $user_responses[$e_id] ?? null;
    
    $report_data[$d_id]['criteria'][$c_id]['elements'][$e_id] = [
        'name' => $row['element_name'],
        'response' => $response
    ];
}

// Calculate Stats
$total_elements = count($raw_structure);
$answered_elements = count(array_intersect(array_column($raw_structure, 'element_ID'), array_keys($user_responses)));
$completion_rate = ($total_elements > 0) ? round(($answered_elements / $total_elements) * 100) : 0;

// Helper for Score Badge Color
function getScoreColor($score) {
    if ($score >= 4) return 'success';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results: <?php echo htmlspecialchars($assignment['survey_name']); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Global Layout */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .sidebar { position: relative; width: 100%; height: auto; } .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        /* Result Specifics */
        .response-card {
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }
        .response-card.score-5, .response-card.score-4 { border-left-color: #198754; } /* Green */
        .response-card.score-3 { border-left-color: #0dcaf0; } /* Cyan */
        .response-card.score-2 { border-left-color: #ffc107; } /* Yellow */
        .response-card.score-1 { border-left-color: #dc3545; } /* Red */
        .response-card.no-score { border-left-color: #adb5bd; border-style: dashed; }

        .score-badge-large {
            width: 45px; height: 45px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; font-size: 1.2rem; font-weight: bold;
            color: white;
        }
        
        /* Print Styles */
        @media print {
            .sidebar, .btn-print, .breadcrumb, .no-print { display: none !important; }
            .main-content-wrapper { margin-left: 0 !important; width: 100% !important; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; break-inside: avoid; }
            body { background-color: white !important; }
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
                            <button onclick="window.print()" class="btn btn-outline-primary btn-print">
                                <i class="bi bi-printer me-2"></i> Print Report
                            </button>
                            <a href="index.php" class="btn btn-secondary btn-print">Back</a>
                        </div>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> no-print"><?php echo $flash['message']; ?></div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <span class="badge bg-primary-subtle text-primary-emphasis mb-2"><?php echo htmlspecialchars($assignment['department']); ?></span>
                                    <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($assignment['survey_name']); ?></h2>
                                    <p class="text-muted mb-3"><?php echo htmlspecialchars($assignment['survey_description']); ?></p>
                                    
                                    <div class="d-flex gap-4 text-sm">
                                        <div>
                                            <small class="text-uppercase text-muted fw-bold d-block">Status</small>
                                            <span class="fw-bold text-<?php echo $assignment['status'] === 'Completed' ? 'success' : 'warning'; ?>">
                                                <?php echo strtoupper($assignment['status']); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <small class="text-uppercase text-muted fw-bold d-block">Due Date</small>
                                            <span class="fw-bold"><?php echo date('d M Y', strtotime($assignment['end_date'])); ?></span>
                                        </div>
                                        <div>
                                            <small class="text-uppercase text-muted fw-bold d-block">Completion</small>
                                            <span class="fw-bold"><?php echo $completion_rate; ?>%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center d-none d-md-block border-start">
                                    <div class="text-muted small mb-2">Overall Progress</div>
                                    <div class="position-relative d-inline-block">
                                        <svg width="100" height="100" viewBox="0 0 36 36">
                                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#eee" stroke-width="3" />
                                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="<?php echo ($completion_rate == 100) ? '#198754' : '#0d6efd'; ?>" stroke-width="3" stroke-dasharray="<?php echo $completion_rate; ?>, 100" />
                                        </svg>
                                        <div class="position-absolute top-50 start-50 translate-middle fw-bold fs-4">
                                            <?php echo $completion_rate; ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="results-container">
                        <?php if (empty($report_data)): ?>
                            <div class="alert alert-info text-center p-5">
                                <h4>No data available</h4>
                                <p>This survey does not appear to have any questions configured.</p>
                            </div>
                        <?php else: ?>
                            
                            <?php foreach ($report_data as $d_id => $domain): ?>
                                <div class="card border-0 shadow-sm rounded-4 mb-4 break-inside-avoid">
                                    <div class="card-header bg-white border-bottom py-3">
                                        <h5 class="mb-0 text-primary">
                                            <i class="bi bi-layers-fill me-2"></i> <?php echo htmlspecialchars($domain['name']); ?>
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php foreach ($domain['criteria'] as $c_id => $criteria): ?>
                                            <div class="p-4 border-bottom last-no-border">
                                                <h6 class="fw-bold text-secondary text-uppercase mb-3 small tracking-wide">
                                                    <?php echo htmlspecialchars($criteria['name']); ?>
                                                </h6>

                                                <?php foreach ($criteria['elements'] as $e_id => $element): 
                                                    $resp = $element['response'];
                                                    $score = $resp['score'] ?? 0;
                                                    $color = getScoreColor($score);
                                                    $bg_class = $score > 0 ? "score-{$score}" : "no-score";
                                                ?>
                                                    <div class="card response-card <?php echo $bg_class; ?> bg-light mb-3 border-0">
                                                        <div class="card-body d-flex gap-3 align-items-start">
                                                            <div class="flex-shrink-0">
                                                                <?php if ($score > 0): ?>
                                                                    <div class="score-badge-large bg-<?php echo $color; ?>">
                                                                        <?php echo $score; ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="score-badge-large bg-secondary text-white-50">
                                                                        ?
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($element['name']); ?></h6>
                                                                
                                                                <?php if ($score > 0): ?>
                                                                    <div class="mt-2">
                                                                        <span class="badge bg-white text-<?php echo $color; ?> border border-<?php echo $color; ?> mb-1">
                                                                            <?php echo htmlspecialchars($resp['selected_level_desc'] ?? "Level $score"); ?>
                                                                        </span>
                                                                        <p class="mb-0 text-secondary small">
                                                                            <?php echo nl2br(htmlspecialchars($resp['selected_detail'] ?? 'No detail provided for this level.')); ?>
                                                                        </p>
                                                                    </div>
                                                                    <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                                                                        <i class="bi bi-clock me-1"></i> Answered: <?php echo date('d M Y, h:i A', strtotime($resp['input_at'])); ?>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <p class="text-danger small mb-0 mt-2"><i class="bi bi-exclamation-circle me-1"></i> Not answered</p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        <?php endif; ?>
                    </div>

                    <div class="d-none d-print-block text-center mt-5 pt-5 border-top">
                        <p class="text-muted small">
                            Report generated on <?php echo date('d M Y, h:i A'); ?> by <?php echo htmlspecialchars($current_user['full_name']); ?>.<br>
                            UiTM ISO 27001 Audit System.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>