<?php
require_once '../../config/config.php';
requireRole(['admin']);

$db = new Database();

// --- Fetch Summary Stats (Kept for the badges in the navigation cards) ---
try {
    $stats = [
        'domains' => $db->fetchOne("SELECT COUNT(*) as c FROM domain")['c'],
        'criteria' => $db->fetchOne("SELECT COUNT(*) as c FROM criteria")['c'],
        'elements' => $db->fetchOne("SELECT COUNT(*) as c FROM element")['c'],
        'levels' => $db->fetchOne("SELECT COUNT(*) as c FROM score")['c']
    ];
} catch (Exception $e) {
    $stats = ['domains' => 0, 'criteria' => 0, 'elements' => 0, 'levels' => 0];
}

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parameter Settings - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        /* --- Hub Card Styles (Navigation) --- */
        .hub-card {
            border: none;
            border-radius: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            height: 100%;
            background: white;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .hub-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(50, 50, 93, 0.15);
        }
        .hub-icon-box {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            <div class="col-auto">
                <?php include_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
            </div>
            
            <div class="col main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item active text-dark">Parameter Settings</li>
                        </ol>
                    </nav>

                    <div class="mb-5">
                        <h3 class="fw-bold mb-1">Parameter Settings</h3>
                        <p class="text-muted">Manage the core ISO 27001 assessment structure and scoring configuration.</p>
                    </div>

                    <div class="row g-4">
                        
                        <div class="col-md-6 col-xl-3">
                            <a href="domain/index.php" class="text-decoration-none">
                                <div class="card hub-card p-4">
                                    <div class="hub-icon-box bg-primary-subtle text-primary">
                                        <i class="bi bi-layers-fill"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark">Domains</h5>
                                    <p class="text-muted small mb-3">Manage main audit domains (e.g., Tadbir Urus, Risiko).</p>
                                    <div class="d-flex align-items-center justify-content-between mt-auto">
                                        <span class="badge bg-light text-dark border"><?php echo $stats['domains']; ?> Records</span>
                                        <i class="bi bi-arrow-right text-primary"></i>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <a href="criteria/index.php" class="text-decoration-none">
                                <div class="card hub-card p-4">
                                    <div class="hub-icon-box bg-success-subtle text-success">
                                        <i class="bi bi-list-check"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark">Criteria</h5>
                                    <p class="text-muted small mb-3">Define specific assessment criteria linked to domains.</p>
                                    <div class="d-flex align-items-center justify-content-between mt-auto">
                                        <span class="badge bg-light text-dark border"><?php echo $stats['criteria']; ?> Records</span>
                                        <i class="bi bi-arrow-right text-success"></i>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <a href="element/index.php" class="text-decoration-none">
                                <div class="card hub-card p-4">
                                    <div class="hub-icon-box bg-info-subtle text-info">
                                        <i class="bi bi-file-text-fill"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark">Elements</h5>
                                    <p class="text-muted small mb-3">Manage granular assessment items and detailed questions.</p>
                                    <div class="d-flex align-items-center justify-content-between mt-auto">
                                        <span class="badge bg-light text-dark border"><?php echo $stats['elements']; ?> Records</span>
                                        <i class="bi bi-arrow-right text-info"></i>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <a href="score/levels.php" class="text-decoration-none">
                                <div class="card hub-card p-4">
                                    <div class="hub-icon-box bg-warning-subtle text-warning">
                                        <i class="bi bi-bar-chart-fill"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark">Global Levels</h5>
                                    <p class="text-muted small mb-3">Configure standard scoring levels and definitions.</p>
                                    <div class="d-flex align-items-center justify-content-between mt-auto">
                                        <span class="badge bg-light text-dark border"><?php echo $stats['levels']; ?> Levels</span>
                                        <i class="bi bi-arrow-right text-warning"></i>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <a href="control/index.php" class="text-decoration-none">
                                <div class="card hub-card p-4">
                                    <div class="hub-icon-box bg-secondary-subtle text-secondary">
                                        <i class="bi bi-shield-check"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark">ISO Controls</h5>
                                    <p class="text-muted small mb-3">Map internal criteria to ISO 27001 Annex A controls.</p>
                                    <div class="d-flex align-items-center justify-content-between mt-auto">
                                        <span class="badge bg-light text-dark border">Manage Links</span>
                                        <i class="bi bi-arrow-right text-secondary"></i>
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>