<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$flash = getFlashMessage();
$current_user = getCurrentUser();

// --- 1. Determine Mode (Add vs Edit) ---
$edit_id = $_GET['id'] ?? null;
$edit_data = null;
$is_edit = false;

if ($edit_id) {
    $edit_data = $db->fetchOne("SELECT * FROM score WHERE score_ID = :id", [':id' => $edit_id]);
    if ($edit_data) {
        $is_edit = true;
    }
}

// --- 2. Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level = sanitize($_POST['score_level']);
    $desc = sanitize($_POST['desc_level']);
    $post_id = $_POST['score_id'] ?? null; // ID from hidden input

    try {
        if (!empty($post_id)) {
            // === UPDATE ===
            $sql = "UPDATE score SET 
                    score_level = :lvl, 
                    desc_level = :desc, 
                    updated_id = :uid, 
                    updated_at = NOW() 
                    WHERE score_ID = :id";
            
            $db->query($sql, [
                ':lvl' => $level,
                ':desc' => $desc,
                ':uid' => $current_user['user_ID'],
                ':id' => $post_id
            ]);
            setFlashMessage('success', "Score Level updated successfully.");
        } else {
            // === INSERT ===
            // Check if level already exists to prevent duplicates
            $exists = $db->fetchOne("SELECT score_ID FROM score WHERE score_level = :lvl AND status = 'Active'", [':lvl' => $level]);
            if ($exists) {
                throw new Exception("Score Level $level already exists.");
            }

            $db->query("INSERT INTO score (score_level, desc_level, input_id, input_at, status) VALUES (?, ?, ?, NOW(), 'Active')", 
                [$level, $desc, $current_user['user_ID']]);
            setFlashMessage('success', 'New Global Level added successfully.');
        }
        
        redirect('levels.php' . (isset($_GET['element_id']) ? '?element_id='.$_GET['element_id'] : ''));

    } catch (Exception $e) {
        setFlashMessage('danger', 'Error: ' . $e->getMessage());
    }
}

// Fetch all levels for the list
$levels = $db->fetchAll("SELECT * FROM score ORDER BY score_level ASC");

// Determine Back Link
$back_link = isset($_GET['element_id']) ? "index.php?element_id=" . $_GET['element_id'] : "../domain/index.php";
$back_text = isset($_GET['element_id']) ? "Back to Element" : "Back to Settings";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Levels - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Page Layout */
        html, body { 
            height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; 
        }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        .table th { background-color: #9d83b7ff; border-bottom: 2px solid #f0f2f5; color: black; font-size: 0.75rem; padding: 1rem; text-transform: uppercase; }
        .table td { padding: 1rem; vertical-align: middle; color: #67748e; font-size: 0.875rem; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; }
        .btn-gradient-success { background: linear-gradient(135deg, #2dce89 0%, #2dcecc 100%); border: none; color: white; }
        .btn-gradient-success:hover { opacity: 0.9; color: white; }
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
                            <li class="breadcrumb-item"><a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page">Global Levels</li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-0">Global Score Levels</h3>
                            <p class="text-muted mb-0">Define the standard scoring scale (1-10, etc.) used across the system.</p>
                        </div>
                        <div>
                            <a href="<?php echo $back_link; ?>" class="btn btn-outline-secondary px-3 shadow-sm rounded-3">
                                <i class="bi bi-arrow-left me-2"></i><?php echo $back_text; ?> 
                            </a>
                        </div>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show border-0 shadow-sm mb-4">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row g-4">
                        
                        <div class="col-md-4">
                            <div class="card h-100 sticky-top" style="top: 20px; z-index: 1;">
                                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="bi <?php echo $is_edit ? 'bi-pencil-square' : 'bi-plus-circle'; ?> text-primary me-2"></i>
                                        <?php echo $is_edit ? 'Edit Level' : 'Add New Level'; ?>
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST" id="levelForm">
                                        <?php if ($is_edit): ?>
                                            <input type="hidden" name="score_id" value="<?php echo htmlspecialchars($edit_data['score_ID']); ?>">
                                        <?php endif; ?>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-secondary text-uppercase">Score Level (Number)</label>
                                            <input type="number" name="score_level" class="form-control" required 
                                                   placeholder="e.g., 6" 
                                                   value="<?php echo $is_edit ? htmlspecialchars($edit_data['score_level']) : ''; ?>">
                                            <div class="form-text">Enter the numeric value for sorting.</div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label small fw-bold text-secondary text-uppercase">Description</label>
                                            <input type="text" name="desc_level" class="form-control" required 
                                                   placeholder="e.g., Advanced" 
                                                   value="<?php echo $is_edit ? htmlspecialchars($edit_data['desc_level']) : ''; ?>">
                                            <div class="form-text">The label displayed to users.</div>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-gradient-success shadow-sm py-2 rounded-3">
                                                <i class="bi bi-save me-2"></i><?php echo $is_edit ? 'Save Changes' : 'Add Level'; ?>
                                            </button>
                                            
                                            <?php if ($is_edit): ?>
                                                <a href="levels.php" class="btn btn-outline-secondary py-2 rounded-3">Cancel</a>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card h-100">
                                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                                    <h6 class="mb-0 fw-bold">Existing Levels</h6>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4">Level</th>
                                                <th>Description</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($levels as $l): ?>
                                                <tr onclick="window.location.href='levels.php?id=<?php echo $l['score_ID']; ?>';" 
                                                    style="cursor: pointer; transition: background-color 0.2s;"
                                                    class="<?php echo ($is_edit && $l['score_ID'] == $edit_id) ? 'table-active' : ''; ?>">
                                                    
                                                    <td class="ps-4">
                                                        <span class="badge rounded-pill bg-primary shadow-sm" style="min-width: 30px;">
                                                            <?php echo $l['score_level']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="fw-bold text-dark">
                                                        <?php echo htmlspecialchars($l['desc_level']); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($l['status'] === 'Active'): ?>
                                                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis"><?php echo $l['status']; ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let isDirty = false;
            
            // 1. Target the specific form ID
            const form = document.getElementById('levelForm'); 

            if (form) {
                // A. Detect changes on inputs
                form.addEventListener('change', () => isDirty = true);
                form.addEventListener('input', () => isDirty = true);
                
                // B. Disable warning on legitimate submit
                form.addEventListener('submit', () => {
                    isDirty = false;
                });

                // C. Trigger warning on navigation
                window.addEventListener('beforeunload', function (e) {
                    if (isDirty) {
                        e.preventDefault();
                        e.returnValue = ''; 
                    }
                });
            }
        });
    </script>
</body>
</html>