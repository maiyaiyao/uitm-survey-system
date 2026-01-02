<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$sections = $db->fetchAll("SELECT * FROM section WHERE type = 'Control' ORDER BY sec_ID ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sec_ID = $_POST['sec_ID'];
        $sub_con_name = trim($_POST['sub_con_name']);

        if (empty($sec_ID) || empty($sub_con_name)) {
            throw new Exception('All fields are required.');
        }

        // Insert using dummy ID 'AUTO' because trigger handles it
        $sql = "INSERT INTO sub_con (sec_ID, sub_con_ID, sub_con_name) VALUES (:sec, 'AUTO', :name)";
        $db->query($sql, [':sec' => $sec_ID, ':name' => $sub_con_name]);

        setFlashMessage('success', "Control added successfully (ID auto-generated).");
        header('Location: index.php?tab=controls');
        exit;
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Control - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .btn-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; }
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
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-secondary text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php?tab=controls" class="text-secondary text-decoration-none">ISO Standards</a></li>
                            <li class="breadcrumb-item active text-dark">Add Control</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold">Add New Control</h5>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($msg = getFlashMessage()): ?>
                                <div class="alert alert-<?php echo $msg['type']; ?> alert-dismissible fade show">
                                    <?php echo $msg['message']; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Control Category (Section)</label>
                                    <select name="sec_ID" class="form-select" required>
                                        <option value="">Select Category...</option>
                                        <?php foreach ($sections as $sec): ?>
                                            <option value="<?php echo htmlspecialchars($sec['sec_ID']); ?>">
                                                <?php echo htmlspecialchars($sec['sec_ID'] . ' - ' . $sec['sec_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Control ID (e.g., A.5.x) will be auto-generated.</div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Control Name / Description</label>
                                    <textarea name="sub_con_name" class="form-control" rows="3" required maxlength="100"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="index.php?tab=controls" class="btn btn-light">Cancel</a>
                                    <button type="submit" class="btn btn-gradient-primary px-4">Save Control</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>