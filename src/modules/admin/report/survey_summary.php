<?php
// Path: src/modules/admin/report/index.php
require_once '../../../config/config.php';
requireRole(['admin', 'auditor']);

$db = new Database();
$pageTitle = "Organization Reports";

// --- Logic: Fetch Organizations with Survey Metadata (No Scores) ---
$sql = "SELECT 
            o.org_ID, 
            o.org_name, 
            o.org_code,
            COUNT(DISTINCT d.dept_ID) as dept_count,
            COUNT(DISTINCT s.survey_ID) as survey_count,
            MAX(s.end_date) as last_survey_date
        FROM organization o
        LEFT JOIN department d ON o.org_ID = d.org_ID
        LEFT JOIN survey s ON o.org_ID = s.org_ID AND s.status != 'Draft' -- Only count real surveys
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
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; padding: 2rem; }
        .org-card { transition: transform 0.2s, box-shadow 0.2s; border: none; border-radius: 12px; }
        .org-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .meta-badge { font-size: 0.8rem; padding: 0.4em 0.8em; border-radius: 6px; background: #e9ecef; color: #495057; font-weight: 600; }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>

    <div class="main-content-wrapper">
        <div class="mb-4">
            <h3 class="fw-bold mb-1">Organization Reports</h3>
            <p class="text-muted">Select an organization to view specific survey results.</p>
        </div>

        <?php if (isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

        <div class="row g-4">
            <?php foreach ($orgReports as $org): ?>
                <div class="col-md-6 col-xl-4">
                    <a href="org_details.php?org_id=<?php echo $org['org_ID']; ?>" class="text-decoration-none">
                        <div class="card org-card h-100 shadow-sm">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($org['org_name']); ?></h5>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($org['org_code']); ?></span>
                                    </div>
                                    <div class="rounded-circle bg-light p-3">
                                        <i class="bi bi-building fs-4 text-secondary"></i>
                                    </div>
                                </div>
                                
                                <div class="mt-auto">
                                    <div class="d-flex gap-2 mb-3">
                                        <div class="meta-badge"><i class="bi bi-diagram-3 me-1"></i> <?php echo $org['dept_count']; ?> Depts</div>
                                        <div class="meta-badge <?php echo $org['survey_count'] > 0 ? 'bg-success-subtle text-success' : ''; ?>">
                                            <i class="bi bi-clipboard-data me-1"></i> <?php echo $org['survey_count']; ?> Surveys
                                        </div>
                                    </div>
                                    
                                    <small class="text-muted d-block border-top pt-3">
                                        <i class="bi bi-clock-history me-1"></i> 
                                        Last Activity: 
                                        <strong><?php echo $org['last_survey_date'] ? date('d M Y', strtotime($org['last_survey_date'])) : 'Never'; ?></strong>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>