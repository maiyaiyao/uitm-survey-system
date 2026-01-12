<?php
require_once '../../../config/config.php';
requireRole(['admin', 'auditor']);
$db = new Database();
$current_auditor_id = getCurrentUserId(); // Logged in user (Admin/Auditor)

// 1. Handle New Finding
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_gap') {
    // Determine the Target User ID (Auditee)
    // If empty, we store NULL (meaning it's a general system gap)
    $target_user = !empty($_POST['target_user_id']) ? $_POST['target_user_id'] : null;

    $db->query("
        INSERT INTO gap_analysis 
        (domain_ID, user_ID, auditor_id, severity, status, comment, created_at)
        VALUES (:did, :uid, :aid, :sev, 'Open', :com, NOW())
    ", [
        ':did' => $_POST['domain_id'],
        ':uid' => $target_user,       // The Auditee
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
    SELECT g.*, d.domain_name, 
           u_target.full_name as auditee_name, 
           u_auditor.full_name as auditor_name
    FROM gap_analysis g
    LEFT JOIN domain d ON g.domain_ID = d.domain_ID
    LEFT JOIN user u_target ON g.user_ID = u_target.user_ID
    LEFT JOIN user u_auditor ON g.auditor_id = u_auditor.user_ID
    ORDER BY g.created_at DESC
");

$domains = $db->fetchAll("SELECT domain_ID, domain_name FROM domain WHERE status='Active'");
$users = $db->fetchAll("SELECT user_ID, full_name, department FROM user WHERE status='Active' ORDER BY full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Gap Manager - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <?php echo getFlashMessage(); ?>
        
        <div class="d-flex justify-content-between mb-4">
            <div>
                <h3>Auditor Findings Log</h3>
                <p class="text-muted small">Log non-compliance and gaps found during audits.</p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-lg"></i> Log Finding
                </button>
                <a href="index.php" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Severity</th>
                                <th>Status</th>
                                <th>Domain</th>
                                <th style="width: 30%;">Finding / Comment</th>
                                <th>Auditee</th>
                                <th>Auditor</th>
                                <th>Date</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($findings)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">No findings logged yet.</td></tr>
                            <?php else: ?>
                                <?php foreach($findings as $f): 
                                    $sev_color = match($f['severity']) { 'Critical'=>'danger', 'High'=>'warning', 'Medium'=>'info', default=>'secondary' };
                                    $stat_color = match($f['status']) { 'Resolved'=>'success', 'Closed'=>'secondary', default=>'primary' };
                                ?>
                                <tr>
                                    <td class="ps-3"><span class="badge bg-<?php echo $sev_color; ?>"><?php echo $f['severity']; ?></span></td>
                                    <td><span class="badge bg-<?php echo $stat_color; ?>-subtle text-<?php echo $stat_color; ?> border border-<?php echo $stat_color; ?>-subtle"><?php echo $f['status']; ?></span></td>
                                    <td><?php echo htmlspecialchars($f['domain_name']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($f['comment'])); ?></td>
                                    <td>
                                        <?php if($f['auditee_name']): ?>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($f['auditee_name']); ?></div>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">System Wide</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($f['auditor_name'] ?? 'Unknown'); ?></small></td>
                                    <td><?php echo date('d M Y', strtotime($f['created_at'])); ?></td>
                                    <td class="text-end pe-3">
                                        <?php if($f['status'] != 'Resolved' && $f['status'] != 'Closed'): ?>
                                            <a href="?resolve=<?php echo $f['GA_id']; ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Mark this finding as Resolved?')">
                                                <i class="bi bi-check-lg"></i>
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
    </div>

    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <input type="hidden" name="action" value="add_gap">
                <div class="modal-header">
                    <h5 class="modal-title">Log New Finding</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Domain Affected</label>
                        <select name="domain_id" class="form-select" required>
                            <option value="">Select Domain...</option>
                            <?php foreach($domains as $d): ?>
                                <option value="<?php echo $d['domain_ID']; ?>"><?php echo htmlspecialchars($d['domain_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Target User / Auditee (Optional)</label>
                        <select name="target_user_id" class="form-select">
                            <option value="">-- General / System Wide Finding --</option>
                            <?php foreach($users as $u): ?>
                                <option value="<?php echo $u['user_ID']; ?>">
                                    <?php echo htmlspecialchars($u['full_name']); ?> 
                                    (<?php echo htmlspecialchars($u['department'] ?? 'No Dept'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Leave blank if this is a general system gap not specific to a user.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Severity</label>
                            <select name="severity" class="form-select">
                                <option>Low</option>
                                <option selected>Medium</option>
                                <option>High</option>
                                <option>Critical</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observation / Comment</label>
                        <textarea name="comment" class="form-control" rows="3" required placeholder="Describe the non-compliance or gap..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Finding</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>