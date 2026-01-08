<?php
// Adjust this path if your folder structure is different (e.g. ../../config/config.php)
require_once '../../../config/config.php';
requireRole(['admin', 'auditor']); // Assuming you might have an auditor role later

$db = new Database();

// ---------------------------------------------------------
// 1. DATA GATHERING: Audit Readiness Stats
// ---------------------------------------------------------

// A. Requirements Coverage (Clauses)
// Count Total Requirements vs. Those with a linked Criteria
$req_stats = $db->fetchOne("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN criteria_ID IS NOT NULL THEN 1 ELSE 0 END) as mapped
    FROM sub_req
");

// B. Controls Coverage (Annex A)
// Count Total Controls vs. Those that exist in the element_control bridge table
$con_stats = $db->fetchOne("
    SELECT 
        (SELECT COUNT(*) FROM sub_con) as total,
        (SELECT COUNT(DISTINCT sub_con_ID) FROM element_control) as mapped
");

// C. Calculate Percentages
$req_percent = ($req_stats['total'] > 0) ? round(($req_stats['mapped'] / $req_stats['total']) * 100) : 0;
$con_percent = ($con_stats['total'] > 0) ? round(($con_stats['mapped'] / $con_stats['total']) * 100) : 0;

// D. Gap Analysis: Get Top 5 Missing Requirements
// These are high priority for the admin to fix
$missing_reqs = $db->fetchAll("
    SELECT sr.sub_req_ID, sr.sub_req_name, s.sec_name 
    FROM sub_req sr
    JOIN section s ON sr.sec_ID = s.sec_ID
    WHERE sr.criteria_ID IS NULL
    ORDER BY sr.sub_req_ID ASC
    LIMIT 5
");

// E. Gap Analysis: Get Top 5 Missing Controls
$missing_cons = $db->fetchAll("
    SELECT sc.sub_con_ID, sc.sub_con_name, s.sec_name
    FROM sub_con sc
    JOIN section s ON sc.sec_ID = s.sec_ID
    WHERE sc.sub_con_ID NOT IN (SELECT DISTINCT sub_con_ID FROM element_control)
    ORDER BY sc.sub_con_ID ASC
    LIMIT 5
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Readiness Report - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        
        .icon-shape { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
        .bg-gradient-info { background: linear-gradient(87deg, #11cdef 0, #1171ef 100%) !important; }
        .bg-gradient-success { background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%) !important; }
        .bg-gradient-warning { background: linear-gradient(87deg, #fb6340 0, #fbb140 100%) !important; }
        
        .progress-bar { transition: width 1s ease-in-out; }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            <div class="col-auto">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>

            <div class="col main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="fw-bold mb-1">Audit Readiness Dashboard</h3>
                            <p class="text-muted mb-0">Overview of ISO 27001 mapping completeness and compliance gaps.</p>
                        </div>
                        <button onclick="window.print()" class="btn btn-white shadow-sm border">
                            <i class="bi bi-printer me-2"></i> Print Report
                        </button>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-xl-6 col-lg-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h6 class="text-uppercase text-muted ls-1 mb-1">Requirements Mapped</h6>
                                            <span class="h2 font-weight-bold mb-0"><?php echo $req_stats['mapped']; ?> <small class="text-muted text-sm">/ <?php echo $req_stats['total']; ?></small></span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon-shape bg-gradient-info text-white shadow">
                                                <i class="bi bi-file-earmark-text"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-3 mb-0 text-muted text-sm">
                                        <span class="<?php echo $req_percent < 100 ? 'text-warning' : 'text-success'; ?> fw-bold me-2">
                                            <i class="bi <?php echo $req_percent < 100 ? 'bi-arrow-down' : 'bi-arrow-up'; ?>"></i> <?php echo $req_percent; ?>%
                                        </span>
                                        <span class="text-nowrap">Completeness</span>
                                    </p>
                                    <div class="progress mt-3" style="height: 5px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $req_percent; ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-lg-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h6 class="text-uppercase text-muted ls-1 mb-1">Annex A Controls Implemented</h6>
                                            <span class="h2 font-weight-bold mb-0"><?php echo $con_stats['mapped']; ?> <small class="text-muted text-sm">/ <?php echo $con_stats['total']; ?></small></span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon-shape bg-gradient-success text-white shadow">
                                                <i class="bi bi-shield-lock"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-3 mb-0 text-muted text-sm">
                                        <span class="<?php echo $con_percent < 100 ? 'text-warning' : 'text-success'; ?> fw-bold me-2">
                                            <i class="bi <?php echo $con_percent < 100 ? 'bi-arrow-down' : 'bi-arrow-up'; ?>"></i> <?php echo $con_percent; ?>%
                                        </span>
                                        <span class="text-nowrap">Completeness</span>
                                    </p>
                                    <div class="progress mt-3" style="height: 5px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $con_percent; ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        
                        <div class="col-lg-5">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-transparent border-0 pb-0">
                                    <h6 class="text-uppercase text-muted ls-1 mb-1">Overview</h6>
                                    <h5 class="h3 mb-0">System Readiness</h5>
                                </div>
                                <div class="card-body d-flex justify-content-center align-items-center position-relative">
                                    <div style="height: 250px; width: 250px;">
                                        <canvas id="readinessChart"></canvas>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0 text-center">
                                    <small class="text-muted">Combined status of Requirements & Controls</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase text-muted ls-1 mb-1">Gap Analysis</h6>
                                        <h5 class="h3 mb-0 text-danger">Action Required</h5>
                                    </div>
                                    <a href="../iso/index.php" class="btn btn-sm btn-outline-primary">Fix Gaps</a>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        
                                        <?php if(!empty($missing_reqs)): ?>
                                            <div class="list-group-item bg-light fw-bold text-muted small text-uppercase">
                                                Unmapped Requirements (Top 5)
                                            </div>
                                            <?php foreach($missing_reqs as $gap): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                                    <div>
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill me-2"><?php echo $gap['sub_req_ID']; ?></span>
                                                        <span class="text-dark fw-semibold"><?php echo $gap['sub_req_name']; ?></span>
                                                        <div class="small text-muted mt-1">Section: <?php echo $gap['sec_name']; ?></div>
                                                    </div>
                                                    <a href="../iso/map_iso.php?type=requirement&id=<?php echo urlencode($gap['sub_req_ID']); ?>" class="btn btn-sm btn-light border text-primary">
                                                        <i class="bi bi-link-45deg"></i> Link
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <?php if(!empty($missing_cons)): ?>
                                            <div class="list-group-item bg-light fw-bold text-muted small text-uppercase mt-2">
                                                Unmapped Controls (Top 5)
                                            </div>
                                            <?php foreach($missing_cons as $gap): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                                    <div>
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill me-2"><?php echo $gap['sub_con_ID']; ?></span>
                                                        <span class="text-dark fw-semibold"><?php echo $gap['sub_con_name']; ?></span>
                                                        <div class="small text-muted mt-1">Category: <?php echo $gap['sec_name']; ?></div>
                                                    </div>
                                                    <a href="../iso/map_iso.php?type=control&id=<?php echo urlencode($gap['sub_con_ID']); ?>" class="btn btn-sm btn-light border text-primary">
                                                        <i class="bi bi-link-45deg"></i> Link
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <?php if(empty($missing_reqs) && empty($missing_cons)): ?>
                                            <div class="text-center py-5">
                                                <i class="bi bi-check-circle-fill text-success display-4"></i>
                                                <h5 class="mt-3 text-success">Great Job!</h5>
                                                <p class="text-muted">All Standards are currently mapped.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Readiness Chart
        const ctx = document.getElementById('readinessChart').getContext('2d');
        const readinessChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Mapped Requirements', 'Missing Requirements', 'Implemented Controls', 'Missing Controls'],
                datasets: [{
                    data: [
                        <?php echo $req_stats['mapped']; ?>, 
                        <?php echo $req_stats['total'] - $req_stats['mapped']; ?>,
                        <?php echo $con_stats['mapped']; ?>, 
                        <?php echo $con_stats['total'] - $con_stats['mapped']; ?>
                    ],
                    backgroundColor: [
                        '#11cdef', // Info (Req Mapped)
                        '#e9ecef', // Gray (Req Missing)
                        '#2dce89', // Success (Con Mapped)
                        '#dee2e6'  // Darker Gray (Con Missing)
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8 }
                    }
                },
                cutout: '70%', // Makes it a donut
            }
        });
    </script>
</body>
</html>