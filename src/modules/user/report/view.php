<?php
/**
 * View Survey Results
 * Logic:
 * - Element % = Raw Score * 20
 * - Criteria % = Average of Element %
 * - Domain % = Average of Criteria %
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

// --- 2. Data Fetching & Organization ---

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

// Map responses by element_ID
$user_responses = [];
foreach ($raw_responses as $resp) {
    $user_responses[$resp['element_ID']] = $resp;
}

// --- 3. Calculation Logic ---

$report_data = [];
$domain_scores = []; // To store average for overall calculation

foreach ($raw_structure as $row) {
    $d_id = $row['domain_ID'];
    $c_id = $row['criteria_ID'];
    $e_id = $row['element_ID'];
    
    // Initialize structure if not exists
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
    
    // Process Element Score
    $response = $user_responses[$e_id] ?? null;
    $raw_score = $response['score'] ?? 0; // 1 to 5
    $percentage = $raw_score * 20; // 20, 40, 60, 80, 100
    
    $report_data[$d_id]['criteria'][$c_id]['elements'][$e_id] = [
        'name' => $row['element_name'],
        'response' => $response,
        'percentage' => $percentage
    ];
}

// Calculate Averages (Criteria & Domain)
$total_survey_score = 0;
$domain_count = 0;

foreach ($report_data as $d_id => &$domain) {
    $domain_criteria_sum = 0;
    $criteria_count = 0;

    foreach ($domain['criteria'] as $c_id => &$criteria) {
        $element_sum = 0;
        $element_count = 0;

        foreach ($criteria['elements'] as $element) {
            $element_sum += $element['percentage'];
            $element_count++;
        }

        // Criteria Average = Sum of Elements / Count
        $criteria['avg_score'] = ($element_count > 0) ? round($element_sum / $element_count, 2) : 0;
        
        $domain_criteria_sum += $criteria['avg_score'];
        $criteria_count++;
    }
    unset($criteria);

    // Domain Average = Sum of Criteria Averages / Count
    $domain['avg_score'] = ($criteria_count > 0) ? round($domain_criteria_sum / $criteria_count, 2) : 0;
    
    $total_survey_score += $domain['avg_score'];
    $domain_count++;
}
unset($domain);

// Total Survey Average
$overall_score = ($domain_count > 0) ? round($total_survey_score / $domain_count, 2) : 0;

// Helper to determine color based on Percentage
function getScoreClass($percent) {
    if ($percent >= 80) return ['color' => 'success', 'label' => 'Teroptimum'];
    if ($percent >= 60) return ['color' => 'primary', 'label' => 'Terurus'];
    if ($percent >= 40) return ['color' => 'info', 'label' => 'Tertakrif'];
    if ($percent >= 20) return ['color' => 'warning', 'label' => 'Terlaksana'];
    return ['color' => 'danger', 'label' => 'Permulaan'];
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
        html, body { height: 100%; background-color: #f8f9fa; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.05); }
        .result-detail-text { white-space: pre-wrap; color: #525f7f; line-height: 1.6; font-size: 0.9rem; }
        
        /* Progress Bars */
        .progress-thin { height: 6px; border-radius: 3px; }
        
        /* Score Badges */
        .score-box {
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px; font-weight: 800; font-size: 1.1rem;
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
            
            <div class="col-md-3 col-lg-2 sidebar no-print">
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

                    <div class="card mb-5 border-0 shadow-sm rounded-4 bg-white">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="text-uppercase text-muted fw-bold small mb-2"><?php echo htmlspecialchars($assignment['survey_name']); ?></h5>
                                    <h2 class="fw-bold mb-1">Overall Maturity Score</h2>
                                    <p class="text-muted mb-3">Average across all domains.</p>
                                    
                                    <div class="d-flex align-items-center mt-4">
                                        <?php $ov_style = getScoreClass($overall_score); ?>
                                        <div class="display-3 fw-bold text-<?php echo $ov_style['color']; ?> me-3"><?php echo $overall_score; ?>%</div>
                                        <div>
                                            <span class="badge bg-<?php echo $ov_style['color']; ?>-subtle text-<?php echo $ov_style['color']; ?> px-3 py-2 rounded-pill text-uppercase fw-bold">
                                                <?php echo $ov_style['label']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 border-start">
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Domain Breakdown:</strong></li>
                                        <?php foreach ($report_data as $domain): 
                                            $d_style = getScoreClass($domain['avg_score']);
                                        ?>
                                            <li class="d-flex justify-content-between align-items-center mt-2 text-sm">
                                                <span class="text-muted text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($domain['name']); ?></span>
                                                <span class="fw-bold text-<?php echo $d_style['color']; ?>"><?php echo $domain['avg_score']; ?>%</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php foreach ($report_data as $d_id => $domain): ?>
                        <div class="card mb-5 rounded-4 shadow-sm break-inside-avoid">
                            <div class="card-header bg-white p-4 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($domain['name']); ?></h4>
                                    <div class="text-end">
                                        <div class="h4 fw-bold mb-0 text-primary"><?php echo $domain['avg_score']; ?>%</div>
                                        <small class="text-muted">Domain Score</small>
                                    </div>
                                </div>
                                <div class="progress progress-thin mt-3">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $domain['avg_score']; ?>%"></div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <?php foreach ($domain['criteria'] as $c_id => $criteria): ?>
                                    <div class="p-4 border-bottom last-no-border">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold text-secondary text-uppercase tracking-wide mb-0">
                                                <i class="bi bi-diagram-3 me-2"></i> <?php echo htmlspecialchars($criteria['name']); ?>
                                            </h6>
                                            <span class="badge bg-light text-dark border">
                                                Criteria Avg: <strong><?php echo $criteria['avg_score']; ?>%</strong>
                                            </span>
                                        </div>

                                        <div class="vstack gap-3">
                                            <?php foreach ($criteria['elements'] as $e_id => $element): 
                                                $resp = $element['response'];
                                                $score = $resp['score'] ?? 0;
                                                $percent = $element['percentage'];
                                                $style = getScoreClass($percent);
                                                $bg_color = ($score > 0) ? $style['color'] : 'secondary';
                                            ?>
                                                <div class="d-flex align-items-start gap-3 bg-light p-3 rounded-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="score-box bg-<?php echo $bg_color; ?>">
                                                            <?php echo ($score > 0) ? $score : '?'; ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($element['name']); ?></h6>
                                                            <span class="fw-bold text-<?php echo $bg_color; ?> small"><?php echo $percent; ?>%</span>
                                                        </div>
                                                        
                                                        <?php if ($score > 0): ?>
                                                            <div class="mb-2">
                                                                <span class="text-uppercase fw-bold text-<?php echo $bg_color; ?>" style="font-size: 0.75rem;">
                                                                    <?php echo htmlspecialchars($resp['selected_level_desc'] ?? ''); ?>
                                                                </span>
                                                            </div>
                                                            <div class="result-detail-text"><?php echo htmlspecialchars(trim($resp['selected_detail'] ?? 'No detail provided.')); ?></div>
                                                        <?php else: ?>
                                                            <span class="text-muted fst-italic small">Not answered.</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-none d-print-block text-center mt-5 pt-5 border-top">
                        <p class="text-muted small">
                            Report generated on <?php echo date('d M Y, h:i A'); ?>.<br>
                            <?php echo APP_NAME; ?> - Cybersecurity Maturity Assessment.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>