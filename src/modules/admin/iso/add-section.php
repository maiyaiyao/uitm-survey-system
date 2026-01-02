<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sec_ID = trim($_POST['sec_ID']);
        $sec_name = trim($_POST['sec_name']);
        $type = $_POST['type'];

        if (empty($sec_ID) || empty($sec_name) || empty($type)) {
            throw new Exception('All fields are required.');
        }

        // Check duplicate
        $check = $db->fetchAll("SELECT sec_ID FROM section WHERE sec_ID = :id", [':id' => $sec_ID]);
        if ($check) {
            throw new Exception("Section ID '$sec_ID' already exists.");
        }

        $sql = "INSERT INTO section (sec_ID, sec_name, type) VALUES (:id, :name, :type)";
        $db->query($sql, [
            ':id' => $sec_ID,
            ':name' => $sec_name,
            ':type' => $type
        ]);

        setFlashMessage('success', "Section '$sec_ID' added successfully.");
        header('Location: index.php?tab=sections');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Section - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .btn-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; }
        .btn-gradient-primary:hover { color: white; opacity: 0.9; }
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
                            <li class="breadcrumb-item"><a href="index.php?tab=sections" class="text-secondary text-decoration-none">ISO Standards</a></li>
                            <li class="breadcrumb-item active text-dark">Add Section</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold">Add New Section</h5>
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
                                    <label class="form-label fw-bold">Section ID</label>
                                    <input type="text" name="sec_ID" class="form-control" placeholder="e.g., 4, 10, A5" required maxlength="10">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Section Name</label>
                                    <input type="text" name="sec_name" class="form-control" placeholder="e.g., Context of the Organisation" required maxlength="50">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Type</label>
                                    <select name="type" class="form-select" required>
                                        <option value="Requirement">Requirement (Clause)</option>
                                        <option value="Control">Control (Annex A)</option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="index.php?tab=sections" class="btn btn-light">Cancel</a>
                                    <button type="submit" class="btn btn-gradient-primary px-4">Save Section</button>
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