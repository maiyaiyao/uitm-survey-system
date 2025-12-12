<?php
// Path: ../../../config/config.php (up three levels from domains/add-domain.php)
require_once '../../../config/config.php';
requireRole(['admin']);

$error = '';
$success = '';
$db = new Database(); // Initialize database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        
        $domain_name = sanitize($_POST['domain_name']);
        
        if (empty($domain_name)) {
            throw new Exception('Domain name is required');
        }
        
        // Insert domain. The database trigger (trg_domain_ID) will generate the ID.
        $sql = "INSERT INTO domain (domain_name, input_id, input_at, status) 
                VALUES (:domain_name, :user_id, NOW(), 'Active')";
        
        $current_user = getCurrentUser();
        $user_id = $current_user ? $current_user['user_ID'] : 'SYSTEM';

        $db->query($sql, [
            ':domain_name' => $domain_name,
            ':user_id' => $user_id
        ]);
        
        setFlashMessage('success', "Domain '{$domain_name}' added successfully.");
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
        // Reloads the form with the error message
        header('Location: add-domain.php');
        exit(); 
    }
}

$current_user = getCurrentUser();
$flash = getFlashMessage(); 

$currentPage = basename(__FILE__); // 'add-domain.php'
$currentDir = basename(__DIR__); // 'domains'
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Domain - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Page Layout */
        html, body {height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f0f2f5;
            padding: 1.5rem;
            border-top-left-radius: 16px !important;
            border-top-right-radius: 16px !important;
        }
        .card-body {
            padding: 2rem;
            background-color: #fff;
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
        }

        /* Form Controls */
        .form-label {
            font-weight: 600;
            color: #344767;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border: 1px solid #d2d6da;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #e293d3;
            box-shadow: 0 0 0 2px #e9aede;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <div class="col-auto sidebar-container">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>

            <div class="col main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page">Add Domain</li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h3 class="fw-bold mb-1">Add New Domain</h3>
                            <p class="text-muted mb-0">Define a new ISO 27001 Domain (e.g., Tadbir Urus).</p>
                        </div>
                        <div>
                            <a href="index.php" class="btn btn-outline-secondary shadow-sm px-4 py-2 rounded-3">
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
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0 text-dark font-weight-bold">Domain Details</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" id="addDomainForm">
                                        
                                        <div class="mb-4">
                                            <label for="domain_name" class="form-label">Domain Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="domain_name" name="domain_name" required 
                                                   placeholder="e.g., Pengurusan Keselamatan Maklumat">
                                            <div class="form-text text-muted ms-1">Enter the full, descriptive name of the new domain.</div>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 mt-5">
                                            <a href="index.php" class="btn btn-light px-4 py-2 rounded-3">
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 shadow-sm"
                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                                <i class="bi bi-save me-2"></i>Save Domain
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
        const form = document.getElementById('addDomainForm'); 

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