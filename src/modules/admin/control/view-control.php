<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$id = $_GET['id'] ?? null;
if (!$id) redirect('index.php');

$db = new Database();

// 1. Handle Form Submission (Add Link)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criteria_id'])) {
    try {
        $criteria_id = $_POST['criteria_id'];
        // Check if already linked
        $exists = $db->fetchOne("SELECT link_ID FROM criteria_control WHERE criteria_ID = ? AND sub_con_ID = ?", [$criteria_id, $id]);
        
        if (!$exists) {
            $db->query("INSERT INTO criteria_control (criteria_ID, sub_con_ID) VALUES (?, ?)", [$criteria_id, $id]);
            setFlashMessage('success', 'Criteria linked successfully.');
        } else {
            setFlashMessage('warning', 'This criteria is already linked.');
        }
    } catch (Exception $e) {
        setFlashMessage('danger', $e->getMessage());
    }
    redirect("view-control.php?id=$id");
}

// 2. Handle Delete Link
if (isset($_GET['action']) && $_GET['action'] === 'unlink' && isset($_GET['link_id'])) {
    $db->query("DELETE FROM criteria_control WHERE link_ID = ?", [$_GET['link_id']]);
    setFlashMessage('success', 'Link removed.');
    redirect("view-control.php?id=$id");
}

// 3. Fetch Control Details
$control = $db->fetchOne("SELECT sc.*, s.sec_name FROM sub_con sc JOIN section s ON sc.sec_ID = s.sec_ID WHERE sc.sub_con_ID = ?", [$id]);

// 4. Fetch Linked Criteria
$linked = $db->fetchAll("
    SELECT cic.link_ID, c.criteria_ID, c.criteria_name, d.domain_name 
    FROM criteria_control cic
    JOIN criteria c ON cic.criteria_ID = c.criteria_ID
    JOIN domain d ON c.domain_ID = d.domain_ID
    WHERE cic.sub_con_ID = ?
", [$id]);

// 5. Fetch Available Criteria (for dropdown)
$available = $db->fetchAll("SELECT criteria_ID, criteria_name FROM criteria WHERE status = 'Active' ORDER BY criteria_name");

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Map Control <?php echo htmlspecialchars($id); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        body.sb-collapsed .main-content-wrapper { margin-left: 80px; width: calc(100% - 80px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            <div class="col-auto">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>
            <div class="col main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <a href="index.php" class="text-decoration-none text-secondary small"><i class="bi bi-arrow-left"></i> Back to Controls</a>
                            <h3 class="fw-bold mt-2"><?php echo htmlspecialchars($control['sub_con_ID']); ?>: <?php echo htmlspecialchars($control['sub_con_name']); ?></h3>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($control['sec_name']); ?></span>
                        </div>
                    </div>

                    <?php if ($flash): ?><div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div><?php endif; ?>

                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="card h-100">
                                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Linked Assessment Criteria</h6></div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Criteria Name</th>
                                                <th>Domain</th>
                                                <th class="text-end pe-4">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($linked)): ?>
                                                <tr><td colspan="3" class="text-center py-4 text-muted">No criteria mapped to this control yet.</td></tr>
                                            <?php else: ?>
                                                <?php foreach($linked as $l): ?>
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($l['criteria_name']); ?></div>
                                                        <small class="text-muted"><?php echo $l['criteria_ID']; ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($l['domain_name']); ?></td>
                                                    <td class="text-end pe-4">
                                                        <a href="?id=<?php echo $id; ?>&action=unlink&link_id=<?php echo $l['link_ID']; ?>" 
                                                           class="btn btn-sm btn-outline-danger" onclick="return confirm('Unlink this criteria?');">
                                                            <i class="bi bi-trash"></i> Unlink
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

                        <div class="col-md-4">
                            <div class="card bg-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="card-body p-4">
                                    <h5 class="card-title mb-3"><i class="bi bi-link-45deg me-2"></i>Map New Criteria</h5>
                                    <p class="small opacity-75 mb-4">Select an existing criteria from the system to link it to this ISO control for reporting purposes.</p>
                                    
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label small text-uppercase fw-bold opacity-75">Select Criteria</label>
                                            <select name="criteria_id" id="select_criteria" class="form-select border-0 text-dark" required>
                                                <option value="">-- Choose Criteria --</option>
                                                <?php foreach($available as $a): ?>
                                                    <option value="<?php echo $a['criteria_ID']; ?>">
                                                        <?php echo htmlspecialchars($a['criteria_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-light text-primary fw-bold">Link Criteria</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#select_criteria').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "-- Choose Criteria --",
                dropdownAutoWidth: true
            });
        });
    </script>
</body>
</html>