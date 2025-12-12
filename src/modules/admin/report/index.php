<?php
require_once '../../../config/config.php';
require_once '../../../includes/models/User.php';

// CRITICAL ISO CONTROL: Restrict access to Admin role
requireRole(['admin']);

$current_user = getCurrentUser();
$db = new Database();
$pdo = $db->getConnection();
$error = '';

// --- 1. Fetch Core Compliance Metrics ---
$metrics = [
    'total_surveys' => 0,
    'total_responses' => 0,
    'unique_users_responded' => 0,
    'avg_score_level' => 'N/A',
    'domains_total' => 0,
];

try {
    // A. Total Surveys (Active, Draft, Completed)
    $metrics['total_surveys'] = $db->fetchOne("SELECT COUNT(survey_ID) FROM survey")['COUNT(survey_ID)'];
    
    // B. Total Responses Recorded (Number of filled elements)
    $metrics['total_responses'] = $db->fetchOne("SELECT COUNT(response_ID) FROM response")['COUNT(response_ID)'];
    
    // C. Unique Users who submitted at least one response
    $metrics['unique_users_responded'] = $db->fetchOne("SELECT COUNT(DISTINCT user_ID) FROM response")['COUNT(DISTINCT user_ID)'];
    
    // D. Total Domains
    $metrics['domains_total'] = $db->fetchOne("SELECT COUNT(domain_ID) FROM domain WHERE status = 'Active'")['COUNT(domain_ID)'];

    // E. Average Score Level (Conceptual, based on result_domain table)
    // NOTE: This assumes you calculate domain scores externally and store them in result_domain
    $avg_score_query = $db->fetchOne("SELECT AVG(domain_score_level) as avg_score FROM result_domain");
    if ($avg_score_query && $avg_score_query['avg_score'] !== null) {
        $metrics['avg_score_level'] = number_format($avg_score_query['avg_score'], 2);
    }

    // F. Domain Compliance Summary (List domains and their last score)
    $domain_summary = $db->fetchAll("
        SELECT 
            rd.domain_ID, d.domain_name, rd.domain_score_level, rd.last_updated_at, rd.num_of_response
        FROM 
            result_domain rd
        JOIN 
            domain d ON rd.domain_ID = d.domain_ID
        ORDER BY 
            rd.domain_score_level DESC
    ");
    
    // G. User Activity Summary (Users who haven't logged in recently)
    $inactive_users = $db->fetchAll("
        SELECT 
            full_name, primary_email, last_login, created_at
        FROM 
            user
        WHERE 
            status = 'Active' AND 
            (last_login IS NULL OR last_login < DATE_SUB(NOW(), INTERVAL 90 DAY))
        ORDER BY 
            last_login ASC
        LIMIT 10
    ");

} catch (Exception $e) {
    $error = "Database Error fetching report data: " . $e->getMessage();
    $domain_summary = [];
    $inactive_users = [];
}

// --- Helper Functions ---
function getScoreLevelBadge($level) {
    if (!is_numeric($level)) return '<span class="badge bg-secondary">N/A</span>';
    
    $level = (float)$level;
    if ($level >= 4.0) $class = 'bg-success';
    elseif ($level >= 3.0) $class = 'bg-info';
    elseif ($level >= 2.0) $class = 'bg-warning text-dark';
    else $class = 'bg-danger';

    return '<span class="badge rounded-pill ' . $class . '">Level ' . number_format($level, 1) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Sidebar and layout styles unified from survey/index.php */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; }
        .main-content-wrapper {
            margin-left: 16.6667%; height: 100vh; 
            overflow-y: auto; padding: 0;
        }
        .main-content { padding: 1.5rem; }
        .card-header.bg-primary, .btn-primary {
            background-color: #667eea !important; border-color: #667eea !important;
        }
        .btn-primary:hover {
            background-color: #764ba2 !important; border-color: #764ba2 !important;
        }
        .report-stat-card {
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .report-stat-card:hover {
             transform: translateY(-2px);
        }
        .icon-large {
            font-size: 3rem;
            opacity: 0.3;
        }
        @media (max-width: 991.98px) {
            html, body { overflow: auto; }
            .sidebar { position: relative; width: 100%; height: auto; overflow-y: visible; z-index: 1; }
            .main-content-wrapper { margin-left: 0; height: auto; overflow-y: visible; }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <div class="col-md-2 col-lg-2 sidebar">
                <?php 
                // Path correction: Sidebar is one level up from /modules/admin/
                include_once __DIR__ . '../../../includes/admin_sidebar.php'; 
                ?>
            </div>
            
            <div class="col-md-10 col-lg-10 main-content-wrapper">
                <div class="main-content">

                    <!-- Breadcrumbs -->
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php"  class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page">Reports & Analytics</li>
                        </ol>
                    </nav>

                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2><i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>ISMS Performance Reports</h2>
                            <p class="text-muted">High-level insights for Management Review (ISO Clause 9.3) and continual improvement.</p>
                        </div>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Report Metrics Cards (ISO Clause 9.1: Monitoring and Measurement) -->
                    <div class="row g-4 mb-5">
                        
                        <div class="col-md-3">
                            <div class="card report-stat-card bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-muted">Total Surveys</h6>
                                        <h3 class="mb-0 text-dark"><?php echo htmlspecialchars($metrics['total_surveys']); ?></h3>
                                    </div>
                                    <i class="bi bi-clipboard-check icon-large text-primary"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card report-stat-card bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-muted">Total Responses</h6>
                                        <h3 class="mb-0 text-dark"><?php echo htmlspecialchars($metrics['total_responses']); ?></h3>
                                    </div>
                                    <i class="bi bi-database icon-large text-success"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card report-stat-card bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-muted">Avg. Compliance Level (out of 5)</h6>
                                        <h3 class="mb-0 text-dark"><?php echo getScoreLevelBadge($metrics['avg_score_level']); ?></h3>
                                    </div>
                                    <i class="bi bi-flag icon-large text-info"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card report-stat-card bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-muted">Unique Participants</h6>
                                        <h3 class="mb-0 text-dark"><?php echo htmlspecialchars($metrics['unique_users_responded']); ?></h3>
                                    </div>
                                    <i class="bi bi-person-check icon-large text-warning"></i>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="row g-4">
                        <!-- Compliance Score by Domain -->
                        <div class="col-lg-8">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Domain Compliance Status (ISO Annex A)</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Domain ID</th>
                                                    <th>Domain Name</th>
                                                    <th class="text-center">Score Level</th>
                                                    <th class="text-center">Responses</th>
                                                    <th>Last Calculated</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($domain_summary)): ?>
                                                    <tr><td colspan="5" class="text-center py-4 text-muted">No domain results found in the system.</td></tr>
                                                <?php else: ?>
                                                    <?php foreach ($domain_summary as $domain): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($domain['domain_ID']); ?></td>
                                                            <td><?php echo htmlspecialchars($domain['domain_name']); ?></td>
                                                            <td class="text-center">
                                                                <?php echo getScoreLevelBadge($domain['domain_score_level']); ?>
                                                            </td>
                                                            <td class="text-center"><?php echo htmlspecialchars($domain['num_of_response']); ?></td>
                                                            <td><?php echo formatDate($domain['last_updated_at'], DATETIME_FORMAT); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Activity / IAM Report -->
                        <div class="col-lg-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-dark text-white">
                                    <h5 class="mb-0">User Access & Inactivity</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>User</th>
                                                    <th>Last Login</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($inactive_users)): ?>
                                                    <tr><td colspan="2" class="text-center py-4 text-muted">All active users have logged in recently.</td></tr>
                                                <?php else: ?>
                                                    <?php foreach ($inactive_users as $user): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                                <small class="text-muted"><?php echo htmlspecialchars($user['primary_email']); ?></small>
                                                            </td>
                                                            <td>
                                                                <?php if ($user['last_login']): ?>
                                                                    <span class="badge bg-danger">
                                                                        <?php echo formatDate($user['last_login'], DATE_FORMAT); ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">Never</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer text-muted">
                                    <small>Showing last 10 users inactive for > 90 days. Review access (ISO A.6.5).</small>
                                </div>
                            </div>
                        </div>

                    </div> <!-- end row -->

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>