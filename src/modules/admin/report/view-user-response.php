<?php
// Path: src/modules/admin/report/view-user-response.php
require_once '../../../config/config.php';
requireRole(['admin', 'auditor']); // Allow Admins and Auditors

$db = new Database();

// --- 1. Validation & Input ---
$survey_id = $_GET['survey_id'] ?? null;
$target_user_id = $_GET['user_id'] ?? null;

if (!$survey_id || !$target_user_id) {
    setFlashMessage('danger', 'Invalid parameters.');
    // Redirect back to survey list or dashboard
    header("Location: ../survey/index.php");
    exit();
}

// Fetch Target User Details (for display)
$target_user = $db->fetchOne("SELECT full_name, primary_email FROM user WHERE user_ID = :uid", [':uid' => $target_user_id]);

// Check assignment
$assignment = $db->fetchOne("
    SELECT us.*, s.survey_name, s.department, s.end_date, s.survey_description
    FROM user_survey us
    JOIN survey s ON us.survey_ID = s.survey_ID
    WHERE us.survey_ID = :sid AND us.user_ID = :uid
", [':sid' => $survey_id, ':uid' => $target_user_id]);

if (!$assignment) {
    echo "Survey assignment not found for this user.";
    exit();
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
    AND c.status = 'Active' AND e.status = 'Active'
    ORDER BY d.domain_ID, c.criteria_ID, e.element_ID
";
$structure_rows = $db->fetchAll($sql_structure, [':sid' => $survey_id]);

// Fetch User Responses with Details
$sql_responses = "
    SELECT 
        r.element_ID, 
        r.score,
        se.details as selected_detail,
        s.score_level,
        s.desc_level
    FROM response r
    LEFT JOIN score s ON r.score = s.score_level
    LEFT JOIN score_element se ON r.element_ID = se.element_ID 
        AND s.score_ID = se.score_ID
        AND se.status = 'Active'
    WHERE r.survey_ID = :sid AND r.user_ID = :uid
";
$response_rows = $db->fetchAll($sql_responses, [':sid' => $survey_id, ':uid' => $target_user_id]);

// Map Responses for easy lookup
$responses = [];
foreach ($response_rows as $row) {
    $responses[$row['element_ID']] = [
        'score' => $row['score'],
        'score_level' => $row['score_level'] ?? '',
        'desc_level' => $row['desc_level'] ?? '',
        'details' => $row['selected_detail'] ?? ''
    ];
}

// Build Tree Structure & Calculate Scores
$domains = [];
$total_domains_score = 0;
$domain_count = 0;

foreach ($structure_rows as $row) {
    $d_id = $row['domain_ID'];
    $c_id = $row['criteria_ID'];
    $e_id = $row['element_ID'];

    // Initialize Domain
    if (!isset($domains[$d_id])) {
        $domains[$d_id] = [
            'name' => $row['domain_name'],
            'criteria' => []
        ];
    }

    // Initialize Criteria
    if (!isset($domains[$d_id]['criteria'][$c_id])) {
        $domains[$d_id]['criteria'][$c_id] = [
            'name' => $row['criteria_name'],
            'elements' => []
        ];
    }

    // Add Element & Score
    $resp = $responses[$e_id] ?? null;
    $raw_score = $resp ? (float)$resp['score'] : 0;
    $percentage = $raw_score * 20; // 1-5 Scale to %

    $domains[$d_id]['criteria'][$c_id]['elements'][] = [
        'id' => $e_id,
        'name' => $row['element_name'],
        'score' => $raw_score,
        'percentage' => $percentage,
        'has_response' => !empty($resp),
        'detail' => $resp['details'] ?? ''
    ];
}

// Aggregation Logic (Bottom-Up)
foreach ($domains as &$domain) {
    $domain_sum = 0;
    $criteria_count = 0;

    foreach ($domain['criteria'] as &$criteria) {
        $criteria_sum = 0;
        $element_count = count($criteria['elements']);

        foreach ($criteria['elements'] as $el) {
            $criteria_sum += $el['percentage'];
        }

        // Criteria Average
        $criteria['avg'] = ($element_count > 0) ? ($criteria_sum / $element_count) : 0;
        
        $domain_sum += $criteria['avg'];
        $criteria_count++;
    }

    // Domain Average
    $domain['avg'] = ($criteria_count > 0) ? ($domain_sum / $criteria_count) : 0;
    
    $total_domains_score += $domain['avg'];
    $domain_count++;
}

// Overall Score
$overall_maturity = ($domain_count > 0) ? round($total_domains_score / $domain_count, 2) : 0;

// Helper: Color Coding
function getScoreColor($score) {
    if ($score >= 80) return 'success';
    if ($score >= 60) return 'primary';
    if ($score >= 40) return 'info';
    if ($score >= 20) return 'warning';
    return 'danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Response - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Shared Styles */
        html, body { height: 100%; margin: 0; padding: 0; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }
        
        /* Report Specific */
        .score-card { background: white; border-radius: 16px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .maturity-circle { 
            width: 160px; 
            height: 160px; 
            border-radius: 50%; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            margin: 0 auto; 
            border: 10px solid;
            position: relative;
        }
        .maturity-circle .display-6 {
            font-size: 2.5rem;
            line-height: 1;
        }
        .domain-header { background-color: #f8f9fa; border-radius: 8px; padding: 10px 15px; margin-bottom: 15px; border-left: 4px solid #667eea; }
        .criteria-title { font-size: 0.95rem; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 10px; margin-bottom: 8px; }
        .element-row { border-bottom: 1px solid #f0f0f0; padding: 12px 0; }
        .element-row:last-child { border-bottom: none; }
        .result-detail-text { background: #fffde7; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; color: #665c00; margin-top: 6px; border: 1px solid #fff9c4; }
        @media print {
            .sidebar, .btn-back, .no-print { display: none !important; }
            .main-content-wrapper { margin: 0; width: 100%; }
        }
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
                            <li class="breadcrumb-item"><a href="../survey/view-details.php?id=<?php echo $survey_id; ?>" class="text-decoration-none text-secondary">Survey Details</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page">User Response</li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                        <div>
                            <h4 class="fw-bold mb-1">Individual Response View</h4>
                            <p class="text-muted mb-2">
                                <strong>Survey:</strong> <?php echo htmlspecialchars($assignment['survey_name']); ?>
                            </p>
                            <p class="text-muted mb-0">
                                Viewing answers for: <strong class="text-dark"><?php echo htmlspecialchars($target_user['full_name']); ?></strong> 
                                (<?php echo htmlspecialchars($target_user['primary_email']); ?>)
                            </p>
                        </div>
                        <a href="../survey/view-details.php?id=<?php echo $survey_id; ?>" class="btn btn-outline-secondary btn-back">
                            <i class="bi bi-arrow-left me-2"></i>Back to Survey
                        </a>
                    </div>

                    <div class="score-card p-4 mb-4 text-center">
                        <h5 class="text-muted text-uppercase small fw-bold mb-3">Overall Maturity Score</h5>
                        <div class="maturity-circle border-<?php echo getScoreColor($overall_maturity); ?> text-<?php echo getScoreColor($overall_maturity); ?> bg-light">
                            <span class="display-6 fw-bold"><?php echo $overall_maturity; ?>%</span>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-<?php echo getScoreColor($overall_maturity); ?> px-3 py-2 rounded-pill">
                                Status: <?php echo $assignment['status']; ?>
                            </span>
                        </div>
                    </div>

                    <?php foreach ($domains as $domain): ?>
                        <div class="score-card p-0 mb-4 overflow-hidden">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white">
                                <h5 class="mb-0 fw-bold text-primary"><?php echo htmlspecialchars($domain['name']); ?></h5>
                                <span class="badge bg-<?php echo getScoreColor($domain['avg']); ?> fs-6">
                                    <?php echo round($domain['avg'], 1); ?>%
                                </span>
                            </div>
                            <div class="p-4">
                                <?php foreach ($domain['criteria'] as $crit): ?>
                                    <div class="mb-4">
                                        <div class="criteria-title">
                                            <i class="bi bi-layers me-2 text-secondary"></i><?php echo htmlspecialchars($crit['name']); ?>
                                            <span class="float-end text-muted small fw-normal"><?php echo round($crit['avg'], 1); ?>%</span>
                                        </div>
                                        <div class="ps-3 border-start">
                                            <?php foreach ($crit['elements'] as $el): ?>
                                                <div class="element-row">
                                                    <div class="d-flex justify-content-between">
                                                        <span class="text-dark small fw-semibold" style="width: 70%;">
                                                            <?php echo htmlspecialchars($el['name']); ?>
                                                        </span>
                                                        <span class="text-end">
                                                            <?php if ($el['has_response']): ?>
                                                                <span class="badge bg-<?php echo getScoreColor($el['percentage']); ?>">
                                                                    <?php echo $el['score']; ?> / 5
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-secondary border">N/A</span>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <?php if (!empty($el['detail'])): ?>
                                                        <div class="result-detail-text">
                                                            <i class="bi bi-chat-quote-fill me-1 opacity-50"></i>
                                                            <strong>Selected:</strong> <?php echo htmlspecialchars($el['detail']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>