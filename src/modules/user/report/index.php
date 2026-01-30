<?php
/**
 * User Reports & Certificates Hub
 * UPDATED: Added Department column and context-aware linking.
 */

require_once '../../../config/config.php';
requireRole(['user']);

$db = new Database();
$user_ID = getCurrentUserId();

// --- 1. Fetch Completed Surveys ---
// UPDATED SQL: Fetch from 'user_survey' to get specific department context.
$sql = "SELECT s.survey_ID, s.survey_name, s.end_date, 
               d.dept_name, us.dept_ID,
               (SELECT MAX(r.input_at) 
                FROM response r 
                WHERE r.survey_ID = s.survey_ID 
                AND r.user_ID = us.user_ID
               ) as completion_date
        FROM user_survey us
        JOIN survey s ON us.survey_ID = s.survey_ID
        LEFT JOIN department d ON us.dept_ID = d.dept_ID
        WHERE us.user_ID = :uid 
        AND us.status = 'Completed'
        ORDER BY completion_date DESC";

$completed_surveys = $db->fetchAll($sql, [':uid' => $user_ID]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Certificates - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Shared Dashboard Styles */
        html, body { height: 100%; margin: 0; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; width: 270px; z-index: 100; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); color: white; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .sidebar { position: relative; width: 100%; height: auto; } .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .icon-box { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <?php include_once __DIR__ . '/../../includes/user_sidebar.php'; ?>
            
            <div class="col-md-9 col-lg-10 main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-2">
                                    <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-dark" aria-current="page">My Reports</li>
                                </ol>
                            </nav>
                            <h3 class="fw-bold mb-1">Reports</h3>
                            <p class="text-muted mb-0">Manage your survey records and download completion proofs.</p>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-12">
                            <div class="card bg-white p-4">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-success-subtle text-success me-4">
                                        <i class="bi bi-award-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1">Total Completed Assessments</h5>
                                        <h2 class="mb-0 fw-bold"><?php echo count($completed_surveys); ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-white">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold">Completion History</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Survey Details</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Department</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Completed On</th>
                                        <th class="text-end pe-4 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($completed_surveys)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-file-earmark-x display-4 mb-3 d-block opacity-50"></i>
                                                    No completed surveys found.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($completed_surveys as $row): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['survey_name']); ?></span>
                                                        <span class="small text-muted">ID: <?php echo $row['survey_ID']; ?></span>
                                                    </div>
                                                </td>

                                                <td>
                                                    <?php if(!empty($row['dept_name'])): ?>
                                                        <span class="badge bg-light text-primary border border-primary-subtle rounded-pill">
                                                            <i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($row['dept_name']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-secondary border rounded-pill">General</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <div class="d-flex align-items-center text-muted">
                                                        <i class="bi bi-calendar-check me-2"></i>
                                                        <?php 
                                                            // Handle cases where completion_date might be null (though unlikely for 'Completed' status)
                                                            echo $row['completion_date'] ? date('d M Y', strtotime($row['completion_date'])) : 'N/A'; 
                                                        ?>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="btn-group">
                                                        <a href="view.php?id=<?php echo $row['survey_ID']; ?>" 
                                                           class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-eye"></i> Report
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>