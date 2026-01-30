<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();

// Fetch Quick Stats
$total_surveys = $db->fetchOne("SELECT COUNT(*) as count FROM survey WHERE status = 'Active'")['count'];
$total_gaps = $db->fetchOne("SELECT COUNT(*) as count FROM gap_analysis WHERE status = 'Open'")['count'];
$latest_survey = $db->fetchOne("SELECT * FROM survey ORDER BY created_at DESC LIMIT 1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card:hover { transform: translateY(-3px); }
        .icon-box { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            <div class="col-auto">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>
            <div class="col main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page">Reports</li>
                        </ol>
                    </nav>

                    <h3 class="fw-bold mb-4">Analytics & Reporting Hub</h3>

                    <div class="row g-4 mb-5">
                        <div class="col-md-6 col-lg-4">
                            <a href="survey_summary.php" class="text-decoration-none">
                                <div class="card h-100 p-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                                                <i class="bi bi-bar-chart-fill fs-4"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-0">Survey Reports</h5>
                                        </div>
                                        <p class="text-muted small">View overall scores, participation rates, and maturity levels aggregated by specific surveys.</p>
                                        <div class="mt-3">
                                            <span class="badge bg-primary rounded-pill"><?php echo $total_surveys; ?> Active Surveys</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <a href="domain_detail.php" class="text-decoration-none">
                                <div class="card h-100 p-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                                                <i class="bi bi-diagram-3-fill fs-4"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-0">Domain Analytics</h5>
                                        </div>
                                        <p class="text-muted small">Detailed breakdown of Domains, Criteria, and Elements performance across the organization.</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <a href="gap_manager.php" class="text-decoration-none">
                                <div class="card h-100 p-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-danger bg-opacity-10 text-danger me-3">
                                                <i class="bi bi-clipboard-check-fill fs-4"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-0">Auditor Findings</h5>
                                        </div>
                                        <p class="text-muted small">Log and track compliance gaps, observations, and non-conformities found during audits.</p>
                                        <div class="mt-3">
                                            <span class="badge bg-danger rounded-pill"><?php echo $total_gaps; ?> Open Issues</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                         <div class="col-md-6 col-lg-4">
                            <a href="system_readiness.php" class="text-decoration-none">
                                <div class="card h-100 p-3">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                                                <i class="bi bi-gear-wide-connected fs-4"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-0">Audit Readiness</h5>
                                        </div>
                                        <p class="text-muted small">Check ISO 27001 mapping completeness for Requirements and Controls.</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3">Recent Context</h5>
                    <div class="card">
                        <div class="card-body">
                            <?php if($latest_survey): ?>
                                <p class="mb-0">Latest Survey Created: <strong><?php echo htmlspecialchars($latest_survey['survey_name']); ?></strong> on <?php echo date('d M Y', strtotime($latest_survey['created_at'])); ?></p>
                            <?php else: ?>
                                <p class="mb-0 text-muted">No surveys found.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>