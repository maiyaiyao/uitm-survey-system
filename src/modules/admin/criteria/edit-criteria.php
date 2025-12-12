<?php
// Path: ../../../config/config.php (up three levels from criteria/edit-criteria.php)
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$criteria_id = $_GET['id'] ?? null;
$criteria = null;

// --- 1. Fetch Existing Criteria Data ---
if (!$criteria_id) {
    setFlashMessage('error', 'Error: Criteria ID is missing.');
    header('Location: ../domain/index.php');
    exit();
}

try {
    $criteria = $db->fetchOne("
        SELECT c.criteria_ID, c.criteria_name, c.status, c.domain_ID, d.domain_name
        FROM criteria c
        LEFT JOIN domain d ON c.domain_ID = d.domain_ID
        WHERE c.criteria_ID = :id
    ", [':id' => $criteria_id]);

    if (!$criteria) {
        setFlashMessage('error', "Error: Criteria with ID '{$criteria_id}' not found.");
        header('Location: ../domain/index.php');
        exit();
    }
} catch (Exception $e) {
    setFlashMessage('error', 'Database Error: Could not retrieve criteria data.');
    header('Location: ../domain/index.php');
    exit();
}

// --- 2. Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $new_criteria_name = sanitize($_POST['criteria_name']);
        $new_status = sanitize($_POST['status']);

        if (empty($new_criteria_name)) {
            throw new Exception('Criteria name cannot be empty.');
        }

        // Update criteria record
        $sql = "UPDATE criteria SET 
                    criteria_name = :criteria_name,
                    status = :status,
                    updated_id = :user_id,
                    updated_at = NOW()
                WHERE criteria_ID = :criteria_id";

        $current_user = getCurrentUser();
        $user_id = $current_user ? $current_user['user_ID'] : 'SYSTEM';

        $db->query($sql, [
            ':criteria_name' => $new_criteria_name,
            ':status' => $new_status,
            ':user_id' => $user_id,
            ':criteria_id' => $criteria_id
        ]);

        setFlashMessage('success', "Criteria {$criteria_id} updated successfully.");
        // Redirect back to the Domain View to see the list of criteria
        header("Location: view-criteria.php?id={$criteria['domain_ID']}");
        exit();

    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
        header('Location: edit-criteria.php?id=' . $criteria_id);
        exit();
    }
}

// Variables for the view
$current_user = getCurrentUser();
$flash = getFlashMessage();
$currentPage = basename(__FILE__);
$currentDir = basename(__DIR__);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Criteria - <?php echo htmlspecialchars($criteria['criteria_ID']); ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Page Layout */
        html, body { 
            height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; 
        }
        
        /* Sidebar Adjustment */
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        /* Form Controls */
        .form-label {
            font-weight: 600;
            color: #344767;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            border: 1px solid #d2d6da;
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #e293d3;
            box-shadow: 0 0 0 2px #e9aede;
        }
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
                            
                            <li class="breadcrumb-item">
                                <a href="view-criteria.php?id=<?php echo htmlspecialchars($criteria['domain_ID']); ?>"
                                class="text-decoration-none text-secondary"
                                title="Domain: <?php echo htmlspecialchars($criteria['domain_name']); ?>"> Domain <?php echo htmlspecialchars(truncate($criteria['domain_name'], 20)); ?>
                                </a>
                            </li>
                           <li class="breadcrumb-item active text-dark" 
                                title="Criteria: <?php echo htmlspecialchars($criteria['criteria_name']); ?>">
                                Criteria <?php echo htmlspecialchars(truncate($criteria['criteria_name'], 20)); ?>
                            </li>
                            
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">Edit Criteria: <?php echo htmlspecialchars($criteria['criteria_name']); ?></h3>
                        </div>
                        <div>
                            <a href="../element/view-element.php?id=<?php echo htmlspecialchars($criteria['criteria_ID']); ?>" class="btn btn-outline-secondary shadow-sm px-4 py-2 rounded-3">
                                <i class="bi bi-arrow-left me-2"></i>Back=
                            </a>
                        </div>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show border-0 shadow-sm mb-4">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 mb-5">
                                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                                    <h5 class="mb-0">Criteria Details</h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST" id="editCriteriaForm">

                                        <div class="mb-3">
                                            <label for="criteria_id" class="form-label">Criteria ID</label>
                                            <input type="text" class="form-control bg-light" id="criteria_id" value="<?php echo htmlspecialchars($criteria['criteria_ID']); ?>" readonly>
                                            <div class="form-text text-muted ms-1">Criteria ID cannot be changed.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="domain_name" class="form-label">Domain</label>
                                            <input type="text" class="form-control bg-light" id="domain_name" value="<?php echo htmlspecialchars($criteria['domain_name'] . ''); ?>" readonly>
                                            <div class="form-text text-muted ms-1">Domain cannot be change here.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="criteria_name" class="form-label">Criteria Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="criteria_name" name="criteria_name" 
                                                   value="<?php echo htmlspecialchars($criteria['criteria_name']); ?>" required 
                                                   placeholder="e.g., Pengurusan Risiko Keselamatan Maklumat">
                                        </div>

                                        <div class="mb-4">
                                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="Active" <?php echo ($criteria['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="Inactive" <?php echo ($criteria['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 mt-5 pt-3 border-top">
                                            <a href="../element/view-element.php?id=<?php echo htmlspecialchars($criteria['criteria_ID']); ?>" class="btn btn-outline-secondary px-4 rounded-3">
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary px-4 rounded-3" 
                                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                                <i class="bi bi-save me-2"></i>Save Changes
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let isDirty = false;
        
        // 1. CHANGE THIS ID to match your HTML form ID
        const form = document.getElementById('editCriteriaForm'); 

        if (form) {
            // A. Detect changes on standard inputs (text, select, checkbox)
            form.addEventListener('change', () => isDirty = true);
            form.addEventListener('input', () => isDirty = true);
            
            // B. If user clicks "Save" or "Submit", disable the warning
            form.addEventListener('submit', () => {
                isDirty = false;
            });

            // C. The Warning Popup
            window.addEventListener('beforeunload', function (e) {
                if (isDirty) {
                    e.preventDefault();
                    e.returnValue = ''; // Required for Chrome/Edge
                }
            });
        }
    });
</script>
</body>
</html>