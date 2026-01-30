<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$pageTitle = "Organization Reports";

// Logic: Fetch Organizations with Survey Metadata 
$sql = "SELECT 
            o.org_ID, 
            o.org_name, 
            o.org_code,
            COUNT(DISTINCT d.dept_ID) as dept_count,
            COUNT(DISTINCT s.survey_ID) as survey_count,
            MAX(s.end_date) as last_survey_date
        FROM organization o
        LEFT JOIN department d ON o.org_ID = d.org_ID
        LEFT JOIN survey s ON o.org_ID = s.org_ID AND s.status != 'Draft' 
        WHERE o.status = 'Active'
        GROUP BY o.org_ID, o.org_name
        ORDER BY o.org_name ASC";

try {
    $orgReports = $db->fetchAll($sql);
} catch (Exception $e) {
    $orgReports = [];
    $error = "Database Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Project Standard Layout */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }

        /* Card Styling */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .org-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        /* Typography & Components */
        .text-purple { color: #667eea; }
        .bg-gradient-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .meta-badge {
            font-size: 0.75rem;
            padding: 0.5em 0.8em;
            border-radius: 8px;
            background: #f8f9fa;
            color: #67748e;
            font-weight: 600;
            border: 1px solid #e9ecef;
        }
        
        .user-meta .name { font-weight: 600; color: #344767; }
        .user-meta .date { font-size: 0.75rem; color: #adb5bd; }
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
                            <h3 class="fw-bold mb-1">Organization Reports</h3>
                            <p class="text-muted mb-0">Select an organization to view specific survey results and performance metrics.</p>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary shadow-sm px-4 py-2 rounded-3">
                            <i class="bi bi-arrow-left me-2"></i>Back To Hub
                        </a>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="row g-4">
                        <?php if (empty($orgReports)): ?>
                            <div class="col-12">
                                <div class="card p-5 text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-building-slash display-4 text-muted"></i>
                                    </div>
                                    <h5>No Organizations Found</h5>
                                    <p class="text-muted">There are no active organizations with survey data available.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($orgReports as $org): ?>
                                <div class="col-md-6 col-xl-4">
                                    <a href="org_details.php?org_id=<?php echo $org['org_ID']; ?>" class="text-decoration-none">
                                        <div class="card org-card h-100">
                                            <div class="card-body p-4 d-flex flex-column">
                                                
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h5 class="fw-bold text-dark mb-1">
                                                            <?php echo htmlspecialchars($org['org_name']); ?>
                                                        </h5>
                                                        <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle">
                                                            <?php echo htmlspecialchars($org['org_code']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="rounded-circle bg-light p-3 shadow-sm">
                                                        <i class="bi bi-building fs-4 bg-gradient-icon"></i>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-auto">
                                                    <div class="d-flex gap-2 mb-3 flex-wrap">
                                                        <div class="meta-badge">
                                                            <i class="bi bi-diagram-3 me-1"></i> <?php echo $org['dept_count']; ?> Depts
                                                        </div>
                                                        <div class="meta-badge">
                                                            <i class="bi bi-clipboard-data me-1"></i> <?php echo $org['survey_count']; ?> Surveys
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="border-top pt-3">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <small class="text-muted">Last Activity</small>
                                                            <small class="fw-bold text-dark">
                                                                <?php echo $org['last_survey_date'] ? date('d M Y', strtotime($org['last_survey_date'])) : 'No data yet'; ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>