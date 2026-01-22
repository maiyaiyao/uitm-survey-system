<?php
// Adjust path to config (assuming this file is in src/modules/admin/report/)
require_once '../../../config/config.php';
requireRole(['admin', 'auditor']);

$db = new Database();

// --- 1. Get List of Surveys for Dropdown (UPDATED) ---
// Logic: Show only surveys that are NOT Draft AND have already started (Live or Ended)
$current_time = date('Y-m-d H:i:s');
$surveys = $db->fetchAll("
    SELECT * FROM survey 
    WHERE status != 'Draft' 
    AND start_date <= :now
    ORDER BY created_at DESC
", [':now' => $current_time]);

// 2. Handle Selection
$selected_survey_id = $_GET['survey_id'] ?? ($surveys[0]['survey_ID'] ?? null);
$report_data = null;
$overall_score = 0;
$domains = [];

if ($selected_survey_id) {
    // A. Fetch Structure
    $structure = $db->fetchAll("
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
    ", [':sid' => $selected_survey_id]);

    // B. Fetch Aggregated Scores
    $scores_raw = $db->fetchAll("
        SELECT 
            element_ID, 
            AVG(score) as avg_raw_score,
            COUNT(DISTINCT user_ID) as respondent_count
        FROM response 
        WHERE survey_ID = :sid
        GROUP BY element_ID
    ", [':sid' => $selected_survey_id]);

    // Map scores
    $element_scores = [];
    foreach ($scores_raw as $s) {
        $element_scores[$s['element_ID']] = [
            'avg' => $s['avg_raw_score'],
            'count' => $s['respondent_count']
        ];
    }

    // C. Process Hierarchy
    foreach ($structure as $row) {
        $d_id = $row['domain_ID'];
        $c_id = $row['criteria_ID'];
        $e_id = $row['element_ID'];

        if (!isset($domains[$d_id])) {
            $domains[$d_id] = ['name' => $row['domain_name'], 'criteria' => []];
        }
        if (!isset($domains[$d_id]['criteria'][$c_id])) {
            $domains[$d_id]['criteria'][$c_id] = ['name' => $row['criteria_name'], 'elements' => []];
        }

        $raw = $element_scores[$e_id]['avg'] ?? 0;
        $percent = $raw * 20; // Convert 1-5 scale to 0-100%

        $domains[$d_id]['criteria'][$c_id]['elements'][] = [
            'name' => $row['element_name'],
            'percentage' => $percent,
            'respondents' => $element_scores[$e_id]['count'] ?? 0
        ];
    }

    // D. Calculate Averages
    $total_survey_sum = 0;
    $domain_count = 0;

    foreach ($domains as $d_id => &$d_data) {
        $criteria_sum = 0;
        $criteria_count = 0;

        foreach ($d_data['criteria'] as $c_id => &$c_data) {
            $el_sum = 0;
            $el_count = count($c_data['elements']);
            foreach ($c_data['elements'] as $el) {
                $el_sum += $el['percentage'];
            }
            $c_data['avg'] = ($el_count > 0) ? ($el_sum / $el_count) : 0;
            $criteria_sum += $c_data['avg'];
            $criteria_count++;
        }
        
        $d_data['avg'] = ($criteria_count > 0) ? ($criteria_sum / $criteria_count) : 0;
        $total_survey_sum += $d_data['avg'];
        $domain_count++;
    }
    
    $overall_score = ($domain_count > 0) ? round($total_survey_sum / $domain_count, 2) : 0;
}

// Helper for Color
function getScoreClass($val) {
    if ($val >= 80) return 'success';
    if ($val >= 60) return 'primary';
    if ($val >= 40) return 'info';
    if ($val >= 20) return 'warning';
    return 'danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Summary Report - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Layout & System Styles */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }

        /* Card Styling */
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08); }
        .card-header { background-color: white; border-bottom: 1px solid #f0f2f5; border-radius: 16px 16px 0 0 !important; padding: 1.5rem; }
        
        /* List Group Styling */
        .list-group-item { border-left: none; border-right: none; padding: 1rem 1.5rem; }
        .list-group-item:first-child { border-top: none; }
        .list-group-item:last-child { border-bottom: none; }
        
        /* Score Display */
        .score-display { font-size: 3.5rem; font-weight: 800; line-height: 1; }

        /* --- PRINT STYLES --- */
        @media print {
            /* Hide Sidebar, Navigation, Buttons, Forms */
            .sidebar, 
            .breadcrumb, 
            .btn, 
            .no-print, 
            form, 
            .input-group,
            .alert {
                display: none !important;
            }

            /* Adjust Main Content to Full Width */
            .main-content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }
            .container-fluid, .row, .col-md-10, .col-lg-10 {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Improve Card Appearance for Print */
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                margin-bottom: 20px !important;
                break-inside: avoid; /* Prevent breaking across pages */
            }
            .card-header {
                border-bottom: 2px solid #000 !important;
            }

            /* Typography & Colors */
            body {
                background-color: white !important;
                font-size: 12pt;
                -webkit-print-color-adjust: exact; /* Print background colors */
            }

            /* Ensure Chart is Visible */
            canvas {
                max-width: 100% !important;
                height: auto !important;
            }

            /* Scrollable lists should expand fully */
            .list-group-flush {
                max-height: none !important;
                overflow: visible !important;
            }
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
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">Reports</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page">Survey Summary</li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">Survey Performance Report</h3>
                            <p class="text-muted mb-0">Overview of organizational maturity and domain scores.</p>
                        </div>
                        <div class="d-flex gap-2">
                             <a href="index.php" class="btn btn-outline-secondary shadow-sm px-4 py-2 rounded-3">
                                <i class="bi bi-arrow-left me-2"></i>Back to Hub
                            </a>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <form method="GET" class="row align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-secondary text-uppercase small">Select Survey to Analyze</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-clipboard-data"></i></span>
                                        <select name="survey_id" class="form-select border-start-0 ps-0 bg-light" onchange="this.form.submit()">
                                            <?php foreach ($surveys as $s): ?>
                                                <option value="<?php echo $s['survey_ID']; ?>" <?php echo $selected_survey_id == $s['survey_ID'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($s['survey_name']); ?> 
                                                    (<?php echo date('d M Y', strtotime($s['created_at'])); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if ($selected_survey_id && !empty($domains)): ?>
                        
                        <div class="row g-4 mb-4">
                            <div class="col-lg-4">
                                <div class="card h-100 border-0 shadow-sm text-center">
                                    <div class="card-body d-flex flex-column justify-content-center align-items-center p-5">
                                        <div class="mb-3">
                                            <div class="d-inline-flex align-items-center justify-content-center bg-<?php echo getScoreClass($overall_score); ?> bg-opacity-10 text-<?php echo getScoreClass($overall_score); ?> rounded-circle" style="width: 60px; height: 60px;">
                                                <i class="bi bi-trophy-fill fs-3"></i>
                                            </div>
                                        </div>
                                        <h6 class="text-uppercase text-muted fw-bold ls-1 mb-2">Overall Maturity</h6>
                                        <div class="score-display text-<?php echo getScoreClass($overall_score); ?> mb-2">
                                            <?php echo $overall_score; ?>%
                                        </div>
                                        <p class="text-muted small mb-0">Calculated from <?php echo count($domains); ?> Domains</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-white py-3">
                                        <h5 class="mb-0 fw-bold">Domain Score Summary</h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <ul class="list-group list-group-flush h-100" style="overflow-y: auto; max-height: 300px;">
                                            <?php foreach ($domains as $d): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($d['name']); ?></span>
                                                    </div>
                                                    <span class="badge bg-<?php echo getScoreClass($d['avg']); ?> rounded-pill px-3 py-2">
                                                        <?php echo round($d['avg'], 1); ?>%
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">Performance Visualization</h5>
                                <button onclick="window.print()" class="btn btn-sm btn-light rounded-pill px-3 d-print-none">
                                    <i class="bi bi-printer me-1"></i> Print
                                </button>
                            </div>
                            <div class="card-body p-4">
                                <canvas id="domainChart" style="max-height: 400px; width: 100%;"></canvas>
                            </div>
                        </div>

                        <script>
                            const ctx = document.getElementById('domainChart').getContext('2d');
                            // Create gradient
                            let gradient = ctx.createLinearGradient(0, 0, 0, 400);
                            gradient.addColorStop(0, 'rgba(102, 126, 234, 0.8)'); // Matches system primary
                            gradient.addColorStop(1, 'rgba(118, 75, 162, 0.2)');

                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: <?php echo json_encode(array_column($domains, 'name')); ?>,
                                    datasets: [{
                                        label: 'Maturity Score (%)',
                                        data: <?php echo json_encode(array_column($domains, 'avg')); ?>,
                                        backgroundColor: gradient,
                                        borderColor: '#667eea',
                                        borderWidth: 1,
                                        borderRadius: 6,
                                        barPercentage: 0.6
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            max: 100,
                                            grid: { borderDash: [2, 4], color: '#f0f2f5' }
                                        },
                                        x: {
                                            grid: { display: false }
                                        }
                                    }
                                }
                            });
                        </script>

                    <?php elseif($selected_survey_id): ?>
                         <div class="alert alert-info border-0 shadow-sm d-flex align-items-center" role="alert">
                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                            <div>
                                <strong>No Data Available</strong><br>
                                This survey does not have enough responses or the domain structure is empty.
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>