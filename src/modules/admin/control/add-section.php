<?php
// Path: src/modules/admin/control/add-section.php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sec_id = sanitize($_POST['sec_id']);
        $type = sanitize($_POST['type']);
        $sec_name = sanitize($_POST['sec_name']);

        // Validation
        if (empty($sec_id) || empty($type) || empty($sec_name)) {
            throw new Exception('All fields are required.');
        }

        // Check for Duplicate ID
        $exists = $db->fetchOne("SELECT sec_ID FROM section WHERE sec_ID = :id", [':id' => $sec_id]);
        if ($exists) {
            throw new Exception("Section ID '$sec_id' already exists.");
        }

        // Insert
        $sql = "INSERT INTO section (sec_ID, type, sec_name) VALUES (:id, :type, :name)";
        $db->query($sql, [
            ':id' => $sec_id,
            ':type' => $type,
            ':name' => $sec_name
        ]);

        setFlashMessage('success', "Section '$sec_id' added successfully.");
        header('Location: manage-sections.php');
        exit();

    } catch (Exception $e) {
        setFlashMessage('danger', $e->getMessage());
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Section - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        body.sb-collapsed .main-content-wrapper { margin-left: 80px; width: calc(100% - 80px); }
        @media (max-width: 991.98px) { .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .form-label { font-weight: 600; color: #344767; font-size: 0.875rem; margin-bottom: 0.5rem; }
        .form-control, .form-select { border-radius: 0.5rem; padding: 0.6rem 1rem; border: 1px solid #d2d6da; }
        .form-control:focus, .form-select:focus { border-color: #e293d3; box-shadow: 0 0 0 2px #e9aede; }
        
        .btn-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none; color: white;
        }
        .btn-gradient-primary:hover { opacity: 0.9; color: white; }
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
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a></li>
                            <li class="breadcrumb-item"><a href="manage-sections.php" class="text-decoration-none text-secondary">Manage Sections</a></li>
                            <li class="breadcrumb-item active text-dark">Add Section</li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">Add New Section</h3>
                            <p class="text-muted mb-0">Define a new high-level ISO Category or Requirement.</p>
                        </div>
                        <div>
                            <a href="manage-sections.php" class="btn btn-outline-secondary shadow-sm px-4 py-2 rounded-3">
                                <i class="bi bi-arrow-left me-2"></i>Back
                            </a>
                        </div>
                    </div>

                    <?php if ($flash): ?><div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div><?php endif; ?>

                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                                    <h5 class="mb-0">Section Details</h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST">
                                        
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label for="sec_id" class="form-label">Section ID <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="sec_id" name="sec_id" 
                                                       placeholder="e.g. A.9 or 11" required>
                                                <div class="form-text">Unique identifier (e.g., A.5, Clause 4).</div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                                <select class="form-select" id="type" name="type" required>
                                                    <option value="Control">Control (Annex A)</option>
                                                    <option value="Requirement">Requirement (Clauses)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="sec_name" class="form-label">Section Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="sec_name" name="sec_name" 
                                                   placeholder="e.g. Access Control" required>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                            <a href="manage-sections.php" class="btn btn-light px-4 rounded-3">Cancel</a>
                                            <button type="submit" class="btn btn-gradient-primary px-4 rounded-3 shadow-sm">
                                                <i class="bi bi-save me-2"></i>Save Section
                                            </button>
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
</body>
</html>