<?php
require_once '../../config/config.php';
require_once '../../includes/models/User.php';

// Require admin role
requireRole(['admin']);

$current_user = getCurrentUser();

// --- Fetch Dashboard Statistics ---
try {
    $pdo = (new Database())->getConnection();

    // 1. Total Users
    $stmt_users = $pdo->query("SELECT COUNT(*) FROM user");
    $total_users = $stmt_users->fetchColumn();

    // 2. Active Surveys
    $stmt_active = $pdo->query("SELECT COUNT(*) FROM survey WHERE status = 'Active'");
    $total_active = $stmt_active->fetchColumn();

    // 3. Draft Surveys
    $stmt_draft = $pdo->query("SELECT COUNT(*) FROM survey WHERE status = 'draft'");
    $total_draft = $stmt_draft->fetchColumn();

    // 4. Completed Surveys
    $stmt_completed = $pdo->query("SELECT COUNT(*) FROM survey WHERE status = 'completed'");
    $total_completed = $stmt_completed->fetchColumn();

    // 5. Fetch Recent Surveys (Expanded query for the table)
    $stmt_recent = $pdo->query("
        SELECT s.*, u.full_name AS created_by_name
        FROM survey s
        LEFT JOIN user u ON s.created_by = u.user_ID
        ORDER BY s.created_at DESC 
        LIMIT 5
    ");
    $recent_surveys = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Handle DB error gracefully
    $total_users = 0; $total_active = 0; $total_draft = 0; $total_completed = 0;
    $recent_surveys = [];
    $db_error = "Database connection error: " . $e->getMessage();
}

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
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Page Layout */
        html, body { 
            height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; 
        }
        
        /* Sidebar Adjustment for Fixed Layout */
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }

        /* Table Styles (Matching Domain Page) */
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
            color: #67748e; /* Specific Grey Text */
            font-size: 0.875rem;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Stat Cards (Matching Domain Page) */
        .stat-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
            background: white;
            position: relative; /* Added to fix stretched-link issue */
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        
        /* Icon Boxes */
        .icon-shape {
            width: 48px;
            height: 48px;
            background-position: center;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-shape i {
            font-size: 1.25rem;
        }
        
        /* Avatar / User Text */
        .user-meta {
            display: flex;
            flex-direction: column;
            line-height: 1.3;
        }
        .user-meta .name {
            font-weight: 600;
            color: #344767;
        }
        .user-meta .date {
            font-size: 0.75rem;
            color: #adb5bd;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <!-- Sidebar -->
            <div class="col-md-2 col-lg-2 sidebar">
                <?php include_once __DIR__ . '/../includes/admin_sidebar.php'; ?>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 col-lg-10 main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active text-dark" aria-current="page">Dashboard</li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">Overview</h3>
                            <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($current_user['full_name']); ?>!</p>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="survey/form-survey.php" 
                                class="btn btn-primary shadow-sm px-4 py-2 rounded-3" 
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                <i class="bi bi-plus-lg me-2"></i>Create Survey
                            </a>
                        </div>
                    </div>

                    <?php if (isset($db_error)): ?>
                        <div class="alert alert-danger shadow-sm border-0 mb-4">
                            <?php echo htmlspecialchars($db_error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Stats Row -->
                    <div class="row g-4 mb-5">
                        
                        <!-- Total Users -->
                        <div class="col-xl-3 col-sm-6">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">Total Users</p>
                                        <h3 class="font-weight-bolder mb-0 mt-2"><?php echo $total_users; ?></h3>
                                    </div>
                                    <div class="icon-shape bg-primary text-white shadow text-center">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                </div>
                                <a href="user/index.php" class="stretched-link"></a>
                            </div>
                        </div>

                        <!-- Active Surveys -->
                        <div class="col-xl-3 col-sm-6">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">Active Surveys</p>
                                        <h3 class="font-weight-bolder mb-0 mt-2"><?php echo $total_active; ?></h3>
                                    </div>
                                    <div class="icon-shape bg-success text-white shadow text-center">
                                        <i class="bi bi-clipboard-check-fill"></i>
                                    </div>
                                </div>
                                <a href="survey/index.php?status=Active" class="stretched-link"></a>
                            </div>
                        </div>

                        <!-- Draft Surveys -->
                        <div class="col-xl-3 col-sm-6">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">Draft Surveys</p>
                                        <h3 class="font-weight-bolder mb-0 mt-2"><?php echo $total_draft; ?></h3>
                                    </div>
                                    <div class="icon-shape bg-warning text-white shadow text-center">
                                        <i class="bi bi-hourglass-split"></i>
                                    </div>
                                </div>
                                <a href="survey/index.php?status=Draft" class="stretched-link"></a>
                            </div>
                        </div>

                        <!-- Completed Surveys -->
                        <div class="col-xl-3 col-sm-6">
                            <div class="stat-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">Completed</p>
                                        <h3 class="font-weight-bolder mb-0 mt-2"><?php echo $total_completed; ?></h3>
                                    </div>
                                    <div class="icon-shape bg-info text-white shadow text-center">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                </div>
                                <a href="survey/index.php?status=Completed" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-bottom py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Recent Surveys</h5>
                                    <a href="survey/index.php" class="btn btn-sm btn-outline-secondary">View All</a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4">Survey Name</th>
                                                <th>Department</th>
                                                <th>Created By</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-end pe-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recent_surveys)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                            No surveys found.
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($recent_surveys as $survey): ?>
                                                    <tr onclick="window.location.href='survey/view-details.php?id=<?php echo $survey['survey_ID']; ?>';" 
                                                        style="cursor: pointer;">
                                                        <td class="ps-4">
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($survey['survey_name']); ?></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="text-secondary text-sm font-weight-bold"><?php echo htmlspecialchars($survey['department']); ?></span>
                                                        </td>
                                                        <td>
                                                            <div class="user-meta">
                                                                <span class="name"><?php echo htmlspecialchars($survey['created_by_name'] ?? 'System'); ?></span>
                                                                <span class="date"><?php echo date('d M Y', strtotime($survey['created_at'])); ?></span>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php echo getStatusBadge($survey['status']); ?>
                                                        </td>
                                                        <td class="text-end pe-4" onclick="event.stopPropagation();">
                                                            <a href="survey/preview.php?id=<?php echo $survey['survey_ID']; ?>" 
                                                            class="btn btn-sm btn-link text-dark px-2" 
                                                            title="View Details">
                                                                <i class="bi bi-eye fs-6"></i>
                                                            </a>
                                                            <a href="survey/form-survey.php?id=<?php echo $survey['survey_ID']; ?>" 
                                                            class="btn btn-sm btn-link text-primary px-2" 
                                                            title="Edit Survey">
                                                                <i class="bi bi-pencil-square fs-6"></i>
                                                            </a>
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
                                    <div class="d-grid gap-2">
                                        <a href="user/form-user.php" class="btn btn-outline-primary py-2">
                                            <i class="bi bi-person-plus me-2"></i>Add New User
                                        </a>
                                        <a href="survey/form-survey.php" class="btn btn-outline-success py-2">
                                            <i class="bi bi-plus-circle me-2"></i>Create Survey
                                        </a>
                                        <a href="report/index.php" class="btn btn-outline-info py-2">
                                            <i class="bi bi-file-earmark-text me-2"></i>View Reports
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>