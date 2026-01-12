<?php
// Path: src/modules/admin/report/domain_detail.php
require_once '../../../config/config.php';
requireRole(['admin', 'auditor']);
$db = new Database();

// --- Data Fetching Logic (Preserved) ---
// Fetch all active structure and calculate global averages
$sql = "
    SELECT 
        d.domain_ID, d.domain_name,
        c.criteria_ID, c.criteria_name,
        e.element_ID, e.element_name,
        (SELECT AVG(score) FROM response WHERE element_ID = e.element_ID) as avg_raw
    FROM element e
    JOIN criteria c ON e.criteria_ID = c.criteria_ID
    JOIN domain d ON c.domain_ID = d.domain_ID
    WHERE e.status = 'Active'
    ORDER BY d.domain_ID, c.criteria_ID, e.element_ID
";
$rows = $db->fetchAll($sql);

// Structure Data Hierarchy
$report = [];
foreach ($rows as $r) {
    $d_id = $r['domain_ID'];
    $c_id = $r['criteria_ID'];
    
    if (!isset($report[$d_id])) {
        $report[$d_id] = ['name' => $r['domain_name'], 'criteria' => []];
    }
    if (!isset($report[$d_id]['criteria'][$c_id])) {
        $report[$d_id]['criteria'][$c_id] = ['name' => $r['criteria_name'], 'elements' => []];
    }
    
    $percent = ($r['avg_raw'] ?? 0) * 20; // Convert 1-5 to 0-100%
    $report[$d_id]['criteria'][$c_id]['elements'][] = [
        'name' => $r['element_name'],
        'percent' => $percent
    ];
}

// Helper for Progress Bar Color
function getProgClass($p) {
    if ($p >= 80) return 'bg-success';
    if ($p >= 50) return 'bg-primary';
    return 'bg-danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Analytics - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* System Layout Styles */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }

        /* Card & Accordion Styling */
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08); }
        .accordion-item { border: none; border-radius: 16px !important; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 1rem; overflow: hidden; }
        .accordion-button { border-radius: 16px !important; background-color: white; font-weight: 600; color: #344767; box-shadow: none !important; }
        .accordion-button:not(.collapsed) { background-color: #03050700; color: #667eea; }
        .accordion-button::after { background-size: 1rem; }
        .accordion-body { background-color: #f8f9fa; padding: 1.5rem; }

        /* Progress Bar Sizing */
        .progress { height: 8px; border-radius: 4px; background-color: #e9ecef; }
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
                            <li class="breadcrumb-item active text-dark" aria-current="page">Domain Analytics</li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">Detailed Domain Analytics</h3>
                            <p class="text-muted mb-0">Deep dive into Domains, Criteria, and Elements performance.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-outline-secondary shadow-sm px-4 py-2 rounded-3">
                                <i class="bi bi-arrow-left me-2"></i>Back to Hub
                            </a>
                        </div>
                    </div>

                    <div class="accordion" id="domainAccordion">
                        <?php foreach ($report as $d_id => $domain): 
                            // Calculate domain avg for header
                            $d_sum = 0; $d_cnt = 0;
                            foreach($domain['criteria'] as $c) {
                                foreach($c['elements'] as $e) { $d_sum += $e['percent']; $d_cnt++; }
                            }
                            $d_avg = $d_cnt > 0 ? round($d_sum/$d_cnt, 1) : 0;
                        ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $d_id; ?>">
                                        <div class="d-flex justify-content-between w-100 me-3 align-items-center">
                                            <span class="text-uppercase small fw-bold ls-1"><?php echo htmlspecialchars($domain['name']); ?></span>
                                            <span class="badge <?php echo getProgClass($d_avg); ?> rounded-pill px-3"><?php echo $d_avg; ?>%</span>
                                        </div>
                                    </button>
                                </h2>
                               <div id="collapse<?php echo $d_id; ?>" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <?php foreach ($domain['criteria'] as $criteria): ?>
                                            <div class="card mb-3 border-0 shadow-sm bg-white">
                                                <div class="card-body p-4">
                                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                                        <i class="bi bi-layers me-2 text-secondary"></i>
                                                        <?php echo htmlspecialchars($criteria['name']); ?>
                                                    </h6>
                                                    
                                                    <?php foreach ($criteria['elements'] as $el): ?>
                                                        <div class="mb-4">
                                                            <div class="d-flex justify-content-between align-items-end mb-1">
                                                                <span class="text-muted small" style="max-width: 85%;"><?php echo htmlspecialchars($el['name']); ?></span>
                                                                <span class="fw-bold text-dark small"><?php echo round($el['percent']); ?>%</span>
                                                            </div>
                                                            <div class="progress">
                                                                <div class="progress-bar <?php echo getProgClass($el['percent']); ?>" 
                                                                     role="progressbar" 
                                                                     style="width: <?php echo $el['percent']; ?>%"
                                                                     aria-valuenow="<?php echo $el['percent']; ?>" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if(empty($report)): ?>
                        <div class="alert alert-info border-0 shadow-sm text-center py-5 rounded-4">
                            <i class="bi bi-bar-chart-line display-4 d-block mb-3 text-info"></i>
                            <h5 class="fw-bold">No Data Available</h5>
                            <p class="text-muted">There are currently no active elements or responses to analyze.</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>