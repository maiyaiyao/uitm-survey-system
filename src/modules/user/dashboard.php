<?php
/**
 * User/Auditor Dashboard
 * Aligned with Admin Dashboard Styling
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/config.php';
requireRole(['user']);

$db = new Database();
$user_ID = getCurrentUserId();

// --- 1. Check User Status ---
$status_sql = "SELECT status, full_name, primary_email FROM user WHERE user_ID = :user_ID LIMIT 1";
$user_data = $db->fetchOne($status_sql, [':user_ID' => $user_ID]);

if (!$user_data || $user_data['status'] !== 'Active') {
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php?error=account_inactive");
    exit();
}

$current_user = getCurrentUser();

// --- 2. Fetch Statistics ---
$stats_sql = "SELECT 
    COUNT(DISTINCT CASE WHEN s.status = 'Active' THEN s.survey_ID END) AS active_surveys,
    COUNT(DISTINCT CASE WHEN s.status = 'Completed' THEN s.survey_ID END) AS completed_surveys,
    COUNT(DISTINCT s.survey_ID) AS total_surveys
FROM survey s
INNER JOIN user_survey us ON s.survey_ID = us.survey_ID
WHERE us.user_ID = :user_ID";

$stats = $db->fetchOne($stats_sql, [':user_ID' => $user_ID]);

// --- 3. Fetch Recent Assigned Audits (Limit 5 for Dashboard) ---
$audits_sql = "SELECT s.*, us.status as user_survey_status
        FROM survey s
        INNER JOIN user_survey us ON s.survey_ID = us.survey_ID
        WHERE us.user_ID = :user_ID
        ORDER BY s.start_date DESC LIMIT 5";

$audits = $db->fetchAll($audits_sql, [':user_ID' => $user_ID]);

// Helper for Status Badges
function getStatusBadge($status) {
    if ($status === 'Active') return '<span class="badge rounded-pill bg-success-subtle text-success-emphasis">Active</span>';
    if ($status === 'Draft') return '<span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">Draft</span>';
    if ($status === 'Completed') return '<span class="badge rounded-pill bg-info-subtle text-info-emphasis">Completed</span>';
    return '<span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">' . htmlspecialchars($status) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* --- Global Layout (Matching Admin) --- */
        html, body { 
            height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; 
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed; top: 0; bottom: 0; left: 0;
            width: 270px; /* Fixed width matching Admin */
            z-index: 100; padding: 0;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex; flex-direction: column;
        }
        .main-content-wrapper { 
            margin-left: 270px; /* Matching fixed width */
            width: calc(100% - 270px); 
        }
        
        /* Sidebar Links */
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
            border-left: 3px solid transparent;
            display: flex; align-items: center; gap: 12px;
            transition: all 0.15s ease-in-out;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: white;
        }
        .nav-link-icon {
            width: 28px; height: 28px;
            display: flex; justify-content: center; align-items: center;
            color: rgba(255,255,255,0.9);
            background: rgba(255,255,255,0.15);
            border-radius: 6px;
        }
        .sidebar .nav-link.active .nav-link-icon {
            background: white; color: #764ba2;
        }

        /* Responsive Sidebar */
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }

        /* --- Stat Cards (Matching Admin) --- */
        .stat-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
            background: white;
            overflow: hidden;
            height: 100%;
            position: relative;
        }
        .stat-card:hover { transform: translateY(-3px); }
        
        .icon-shape {
            width: 48px; height: 48px;
            background-position: center;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .icon-shape i { font-size: 1.25rem; }

        /* --- Table Styles (Matching Admin) --- */
        .table th {
            font-weight: 700;
            background-color: #9d83b7ff; /* Purple Header */
            border-bottom: 2px solid #f0f2f5;
            color: black;
            text-transform: uppercase;
            font-size: 0.75rem;
            padding: 1rem;
        }
        .table td {
            padding: 1rem;
            vertical-align: middle;
            color: #67748e;
            font-size: 0.875rem;
        }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }

        /* --- Misc --- */
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .btn-logout {
            background: #fff0f3; color: #d63384; border: none;
            font-weight: 600; font-size: 0.9rem; padding: 0.8rem;
            border-radius: 10px; width: 100%; text-decoration: none;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-logout:hover { background: #ffe0e9; color: #a61e4d; }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <?php include_once __DIR__ . '/../includes/user_sidebar.php'; ?>
            
            <div class="col-md-9 col-lg-10 main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-2">
                                    <li class="breadcrumb-item active text-dark" aria-current="page">Overview</li>
                                </ol>
                            </nav>
                            <h3 class="fw-bold mb-1">Dashboard</h3>
                            <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($current_user['full_name']); ?>!</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-muted small"><?php echo date('l, d F Y'); ?></span>
                            <div class="bg-white p-2 rounded-circle shadow-sm text-primary">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-5">
                        
                        <div class="col-xl-4 col-sm-6">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">Active Audits</p>
                                        <h3 class="font-weight-bolder mb-0 mt-2"><?php echo $stats['active_surveys'] ?? 0; ?></h3>
                                    </div>
                                    <div class="icon-shape bg-primary text-white shadow text-center">
                                        <i class="bi bi-clipboard-data-fill"></i>
                                    </div>
                                </div>
                                <div class="mt-3 mb-0 text-sm">
                                    <span class="text-success font-weight-bold">
                                        <i class="bi bi-arrow-right"></i>
                                    </span>
                                    <a href="my-audits.php" class="text-decoration-none text-muted stretched-link">View Tasks</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-6">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">Completed</p>
                                        <h3 class="font-weight-bolder mb-0 mt-2"><?php echo $stats['completed_surveys'] ?? 0; ?></h3>
                                    </div>
                                    <div class="icon-shape bg-success text-white shadow text-center">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                </div>
                                <div class="mt-3 mb-0 text-sm">
                                    <a href="my-audits.php?status=Completed" class="text-decoration-none text-muted stretched-link">View History</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-sm-6">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">Total Assigned</p>
                                        <h3 class="font-weight-bolder mb-0 mt-2"><?php echo $stats['total_surveys'] ?? 0; ?></h3>
                                    </div>
                                    <div class="icon-shape bg-info text-white shadow text-center">
                                        <i class="bi bi-folder-fill"></i>
                                    </div>
                                </div>
                                <div class="mt-3 mb-0 text-sm">
                                    <span class="text-muted">Lifetime assignments</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-bottom py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">My Assigned Audits</h5>
                                    <a href="my-audits.php" class="btn btn-sm btn-outline-primary rounded-3">View All</a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4">Audit Name</th>
                                                <th>Department</th>
                                                <th>Due Date</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-end pe-4">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($audits)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                            No audits assigned currently.
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($audits as $audit): ?>
                                                    <tr>
                                                        <td class="ps-4">
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($audit['survey_name']); ?></span>
                                                                <small class="text-muted">ID: <?php echo htmlspecialchars($audit['survey_ID']); ?></small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="text-secondary text-sm font-weight-bold">
                                                                <?php echo htmlspecialchars($audit['department']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <i class="bi bi-calendar-event me-2 text-muted"></i>
                                                                <?php echo date('d M Y', strtotime($audit['end_date'])); ?>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php echo getStatusBadge($audit['status']); ?>
                                                        </td>
                                                        <td class="text-end pe-4">
                                                            <?php if($audit['status'] === 'Active'): ?>
                                                                <a href="assessment.php?survey_id=<?php echo $audit['survey_ID']; ?>" 
                                                                   class="btn btn-sm btn-primary px-3 rounded-3"
                                                                   style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                                                    Start
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="view-results.php?survey_id=<?php echo $audit['survey_ID']; ?>" 
                                                                   class="btn btn-sm btn-outline-secondary px-3 rounded-3">
                                                                    View
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                                    <h5 class="mb-0">Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-3">
                                        <a href="my-audits.php" class="btn btn-outline-primary py-3 text-start d-flex align-items-center">
                                            <div class="icon-shape bg-primary-subtle text-primary rounded-circle me-3" style="width: 40px; height: 40px;">
                                                <i class="bi bi-play-fill"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">Continue Assessment</div>
                                                <small class="text-muted">Resume your pending works</small>
                                            </div>
                                        </a>

                                        <a href="reports.php" class="btn btn-outline-info py-3 text-start d-flex align-items-center">
                                            <div class="icon-shape bg-info-subtle text-info rounded-circle me-3" style="width: 40px; height: 40px;">
                                                <i class="bi bi-printer-fill"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">Generate Reports</div>
                                                <small class="text-muted">Download past certificates</small>
                                            </div>
                                        </a>

                                        <div class="p-3 bg-light rounded-3 mt-2">
                                            <h6 class="fw-bold mb-2">Need Help?</h6>
                                            <p class="small text-muted mb-2">Contact the administrator for survey access or technical issues.</p>
                                            <a href="mailto:admin@uitm.edu.my" class="small text-decoration-none fw-bold">
                                                Contact Support <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
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
</body>
</html>