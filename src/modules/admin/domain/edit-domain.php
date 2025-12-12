<?php
// Path: ../../../config/config.php (up three levels from domains/edit-domain.php)
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$domain_id = $_GET['id'] ?? null;
$domain = null;

// --- 1. Fetch Existing Domain Data ---
if (!$domain_id) {
    setFlashMessage('error', 'Error: Domain ID is missing.');
    header('Location: index.php');
    exit();
}

try {
    $domain = $db->fetchOne("SELECT domain_ID, domain_name, status FROM domain WHERE domain_ID = :id", [':id' => $domain_id]);

    if (!$domain) {
        setFlashMessage('error', "Error: Domain with ID '{$domain_id}' not found.");
        header('Location: index.php');
        exit();
    }
} catch (Exception $e) {
    setFlashMessage('error', 'Database Error: Could not retrieve domain data.');
    header('Location: index.php');
    exit();
}

// --- 2. Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $new_domain_name = sanitize($_POST['domain_name']);
        $new_status = sanitize($_POST['status']);
        
        if (empty($new_domain_name)) {
            throw new Exception('Domain name cannot be empty.');
        }
        
        if (!in_array($new_status, ['Active', 'Inactive'])) {
            throw new Exception('Invalid status value provided.');
        }

        $sql = "UPDATE domain SET 
                    domain_name = :domain_name, 
                    status = :status,
                    updated_id = :user_id, 
                    updated_at = NOW() 
                WHERE domain_ID = :domain_id";
        
        $current_user = getCurrentUser();
        $user_id = $current_user ? $current_user['user_ID'] : 'SYSTEM';

        $db->query($sql, [
            ':domain_name' => $new_domain_name,
            ':status' => $new_status,
            ':user_id' => $user_id,
            ':domain_id' => $domain_id
        ]);

        setFlashMessage('success', "Domain updated successfully.");
        header('Location: ../criteria/view-criteria.php?id=' . $domain_id);
        exit();

    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
        header('Location: edit-domain.php?id=' . $domain_id); // Redirect back to form
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
    <title>Edit Domain - <?php echo htmlspecialchars($domain['domain_ID']); ?> - <?php echo APP_NAME; ?></title>
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
            
            <!-- Sidebar -->
            <div class="col-md-2 col-lg-2 sidebar">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 col-lg-10 main-content-wrapper">
                <div class="main-content px-4 py-4">

                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a>
                            </li>
                            
                            <li class="breadcrumb-item active text-dark" aria-current="page" 
                                title="Domain: <?php echo htmlspecialchars($domain['domain_name']); ?>"> Domain <?php echo htmlspecialchars(truncate($domain['domain_name'], 30)); ?>
                            </li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">Edit Domain: <?php echo htmlspecialchars($domain['domain_name']); ?></h3>
                        </div>
                        <div>
                            <a href="../criteria/view-criteria.php?id=<?php echo $domain['domain_ID'];?>" class="btn btn-outline-secondary shadow-sm px-4 py-2 rounded-3">
                                <i class="bi bi-arrow-left me-2"></i>Back
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
                                    <h5 class="mb-0">Domain Details</h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST" id="editDomainForm">
                                        
                                        <!-- Domain ID (Read Only) -->
                                        <div class="mb-3">
                                            <label for="domain_id" class="form-label">Domain ID</label>
                                            <input type="text" class="form-control bg-light" id="domain_id" value="<?php echo htmlspecialchars($domain['domain_ID']); ?>" readonly>
                                            <div class="form-text text-muted ms-1">System generated ID cannot be changed.</div>
                                        </div>
                                        
                                        <!-- Domain Name -->
                                        <div class="mb-4">
                                            <label for="domain_name" class="form-label">Domain Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="domain_name" name="domain_name" 
                                                   value="<?php echo htmlspecialchars($domain['domain_name']); ?>" required>
                                        </div>

                                        <!-- Status -->
                                        <div class="mb-4">
                                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="Active" <?php echo ($domain['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="Inactive" <?php echo ($domain['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>

                                        <!-- Actions -->
                                        <div class="d-flex justify-content-end gap-2 mt-5 pt-3 border-top">
                                            <a href="../criteria/view-criteria.php?id=<?php echo $domain['domain_ID'];?>"  class="btn btn-outline-secondary px-4 rounded-3">
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
        const form = document.getElementById('editDomainForm'); 

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