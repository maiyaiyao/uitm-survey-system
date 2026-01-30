<?php
// Path: src/modules/admin/report/gap_manager.php
require_once '../../../config/config.php';
requireRole(['admin', 'auditor']);
$db = new Database();
$current_auditor_id = getCurrentUserId(); // Logged in user (Admin/Auditor)

// 1. Handle New Finding
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_gap') {
    // Determine Target User (Auditee) & Survey
    $target_user = !empty($_POST['target_user_id']) ? $_POST['target_user_id'] : null;
    $target_survey = !empty($_POST['survey_id']) ? $_POST['survey_id'] : null;

    $db->query("
        INSERT INTO gap_analysis 
        (domain_ID, survey_ID, user_ID, auditor_id, severity, status, comment, created_at)
        VALUES (:did, :sid, :uid, :aid, :sev, 'Open', :com, NOW())
    ", [
        ':did' => $_POST['domain_id'],
        ':sid' => $target_survey,      // Linked Survey
        ':uid' => $target_user,        // The Auditee
        ':aid' => $current_auditor_id, // The Auditor (You)
        ':sev' => $_POST['severity'],
        ':com' => $_POST['comment']
    ]);
    
    setFlashMessage('success', 'Finding logged successfully.');
    redirect('gap_manager.php');
}

// 2. Handle Status Update
if (isset($_GET['resolve'])) {
    $db->query("UPDATE gap_analysis SET status = 'Resolved' WHERE GA_id = :id", [':id' => $_GET['resolve']]);
    setFlashMessage('success', 'Finding marked as resolved.');
    redirect('gap_manager.php');
}

// 3. Fetch Data
// Joins: user_target (Auditee), user_auditor (Auditor)
$findings = $db->fetchAll("
    SELECT g.*, d.domain_name, s.survey_name,
           u_target.full_name as auditee_name, 
           u_auditor.full_name as auditor_name
    FROM gap_analysis g
    LEFT JOIN domain d ON g.domain_ID = d.domain_ID
    LEFT JOIN survey s ON g.survey_ID = s.survey_ID
    LEFT JOIN user u_target ON g.user_ID = u_target.user_ID
    LEFT JOIN user u_auditor ON g.auditor_id = u_auditor.user_ID
    ORDER BY g.created_at DESC
");

// Dropdowns
$domains = $db->fetchAll("SELECT domain_ID, domain_name FROM domain WHERE status='Active'");
$surveys = $db->fetchAll("SELECT survey_ID, survey_name FROM survey ORDER BY created_at DESC");
$users = $db->fetchAll("SELECT user_ID, full_name FROM user WHERE status='Active' ORDER BY full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditor Gap Manager - <?php echo APP_NAME; ?></title>
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

        /* Card Styling */
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08); }
        .card-header { background-color: white; border-bottom: 1px solid #f0f2f5; padding: 1.5rem; border-radius: 16px 16px 0 0 !important; }

        /* Table Styling */
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

        /* Badges & Avatars */
        .badge { font-weight: 600; padding: 0.5em 0.8em; }
        .user-meta .name { font-weight: 600; color: #344767; display: block; }
        .user-meta .role { font-size: 0.75rem; color: #adb5bd; }
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
                            <li class="breadcrumb-item active text-dark" aria-current="page">Auditor Findings</li>
                        </ol>
                    </nav>

                    <?php 
                    $flash = getFlashMessage();
                    if ($flash): 
                    ?>
                        <div class="alert alert-<?php echo ($flash['type'] == 'error') ? 'danger' : $flash['type']; ?> alert-dismissible fade show border-0 shadow-sm mb-4">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">Auditor Findings Log</h3>
                            <p class="text-muted mb-0">Track compliance gaps, non-conformities, and observations.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-outline-secondary shadow-sm px-4 py-2 rounded-3">
                                <i class="bi bi-arrow-left me-2"></i>Back To Hub
                            </a>
                            <button class="btn btn-primary shadow-sm px-4 py-2 rounded-3" 
                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;"
                                    data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-plus-lg me-2"></i>Log Finding
                            </button>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header">
                            <h5 class="mb-0 fw-bold">Recent Findings</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Severity</th>
                                        <th>Status</th>
                                        <th>Context (Domain / Survey)</th>
                                        <th style="width: 30%;">Observation</th>
                                        <th>Involved Parties</th>
                                        <th>Date</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($findings)): ?>
                                        <tr><td colspan="7" class="text-center py-5 text-muted">No findings logged yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($findings as $f): 
                                            // Badge Logic
                                            $sev_class = match($f['severity']) { 
                                                'Critical'=>'danger', 'High'=>'warning', 'Medium'=>'info', default=>'secondary' 
                                            };
                                            $stat_class = match($f['status']) { 
                                                'Resolved'=>'success', 'Closed'=>'secondary', default=>'primary' 
                                            };
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <span class="badge bg-<?php echo $sev_class; ?> rounded-pill">
                                                    <?php echo $f['severity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $stat_class; ?>-subtle text-<?php echo $stat_class; ?> border border-<?php echo $stat_class; ?>-subtle rounded-pill">
                                                    <?php echo $f['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($f['domain_name']); ?></div>
                                                <?php if($f['survey_name']): ?>
                                                    <div class="small text-muted">
                                                        <i class="bi bi-clipboard-data me-1"></i><?php echo htmlspecialchars($f['survey_name']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-dark d-block text-wrap" style="max-width: 400px; line-height: 1.4;">
                                                    <?php echo nl2br(htmlspecialchars($f['comment'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="user-meta mb-1">
                                                    <span class="role">Auditee:</span>
                                                    <span class="name">
                                                        <?php echo htmlspecialchars($f['auditee_name'] ?? 'System Wide'); ?>
                                                    </span>
                                                </div>
                                                <div class="user-meta">
                                                    <span class="role">Auditor:</span>
                                                    <span class="name">
                                                        <?php echo htmlspecialchars($f['auditor_name'] ?? 'Unknown'); ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-secondary small font-weight-bold">
                                                    <?php echo date('d M Y', strtotime($f['created_at'])); ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <?php if($f['status'] != 'Resolved' && $f['status'] != 'Closed'): ?>
                                                    <a href="?resolve=<?php echo $f['GA_id']; ?>" 
                                                       class="btn btn-sm btn-outline-success rounded-pill px-3" 
                                                       onclick="return confirm('Mark this finding as Resolved?')">
                                                        <i class="bi bi-check2-circle me-1"></i> Resolve
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-success small"><i class="bi bi-check-all"></i> Done</span>
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
            </div>
        </div>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content border-0 shadow">
                <input type="hidden" name="action" value="add_gap">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Log New Finding</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Domain Affected</label>
                        <select name="domain_id" class="form-select" required>
                            <option value="">Select Domain...</option>
                            <?php foreach($domains as $d): ?>
                                <option value="<?php echo $d['domain_ID']; ?>"><?php echo htmlspecialchars($d['domain_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Related Survey (Optional)</label>
                        <select name="survey_id" class="form-select">
                            <option value="">-- General / System Wide --</option>
                            <?php foreach($surveys as $s): ?>
                                <option value="<?php echo $s['survey_ID']; ?>"><?php echo htmlspecialchars($s['survey_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Severity Level</label>
                        <select name="severity" class="form-select">
                            <option value="Low">Low: Ada kawalan; perlu penambahbaikan kecil</option>
                            <option value="Medium" selected>Medium: Ada kawalan; pelaksanaan tidak konsisten.</option>
                            <option value="High">High: Kawalan tidak memadai / tidak memenuhi keperluan.</option>
                            <option value="Critical">Critical: Kawalan kritikal tiada / gagal; tindakan segera diperlukan.</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Observation / Comment</label>
                        <textarea name="comment" class="form-control" rows="4" required placeholder="Describe the non-compliance, gap, or observation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 pe-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" 
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        Save Finding
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>