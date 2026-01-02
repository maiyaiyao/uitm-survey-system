<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$id = $_GET['id'] ?? null;

if (!$id) {
    setFlashMessage('error', 'No ID provided.');
    header('Location: index.php?tab=sections');
    exit;
}

$section = $db->fetchAll("SELECT * FROM section WHERE sec_ID = :id", [':id' => $id]);
if (!$section) {
    setFlashMessage('error', 'Section not found.');
    header('Location: index.php?tab=sections');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sec_name = trim($_POST['sec_name']);
        if (empty($sec_name)) throw new Exception("Section name is required.");

        $sql = "UPDATE section SET sec_name = :name WHERE sec_ID = :id";
        $db->query($sql, [':name' => $sec_name, ':id' => $id]);
        
        setFlashMessage('success', "Section updated successfully.");
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
    <title>Edit Section - <?php echo APP_NAME; ?></title>
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
                            <li class="breadcrumb-item"><a href="index.php?tab=sections" class="text-secondary text-decoration-none">ISO Standards</a></li>
                            <li class="breadcrumb-item active text-dark">Edit Section</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold">Edit Section: <?php echo htmlspecialchars($id ?? ''); ?></h5>
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
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($section['sec_ID'] ?? ''); ?>" disabled>
                                    <div class="form-text">ID cannot be changed.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Section Name</label>
                                    <input type="text" name="sec_name" class="form-control" value="<?php echo htmlspecialchars($section['sec_name'] ?? ''); ?>" required maxlength="50">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Type</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($section['type'] ?? ''); ?>" disabled>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="index.php?tab=sections" class="btn btn-light">Cancel</a>
                                    <button type="submit" class="btn btn-gradient-primary px-4">Update Section</button>
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