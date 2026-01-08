<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php?tab=controls');
    exit;
}

// Fetch the Control data
$con = $db->fetchOne("SELECT * FROM sub_con WHERE sub_con_ID = :id", [':id' => $id]);
if (!$con) {
    setFlashMessage('error', 'Control not found.');
    header('Location: index.php?tab=controls');
    exit;
}

// Fetch Sections for the dropdown (Filtered by Control type)
$sections = $db->fetchAll("SELECT * FROM section WHERE type = 'Control' ORDER BY sec_ID ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sub_con_name = trim($_POST['sub_con_name']);
        $sec_ID = $_POST['sec_ID'];

        if (empty($sub_con_name) || empty($sec_ID)) throw new Exception("All fields are required.");

        $sql = "UPDATE sub_con SET sub_con_name = :name, sec_ID = :sec WHERE sub_con_ID = :id";
        $db->query($sql, [':name' => $sub_con_name, ':sec' => $sec_ID, ':id' => $id]);
        
        setFlashMessage('success', "Control updated successfully.");
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
    <title>Edit Control - <?php echo APP_NAME; ?></title>
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
                            <li class="breadcrumb-item active text-dark">Edit Control</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold">Edit Control: <?php echo htmlspecialchars($id ?? ''); ?></h5>
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
                                    <label class="form-label fw-bold">Control ID</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($con['sub_con_ID'] ?? ''); ?>" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Control Category (Section)</label>
                                    <select name="sec_ID" class="form-select" required>
                                        <?php foreach ($sections as $sec): ?>
                                            <option value="<?php echo htmlspecialchars($sec['sec_ID']); ?>" 
                                                <?php echo ($sec['sec_ID'] == ($con['sec_ID'] ?? '')) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sec['sec_ID'] . ' - ' . $sec['sec_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Name / Description</label>
                                    <textarea name="sub_con_name" class="form-control" rows="3" required maxlength="100"><?php echo htmlspecialchars($con['sub_con_name'] ?? ''); ?></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="index.php?tab=controls" class="btn btn-light">Cancel</a>
                                    <button type="submit" class="btn btn-gradient-primary px-4">Update Control</button>
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