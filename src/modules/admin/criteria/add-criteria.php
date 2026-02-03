<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();

// 1. Get Pre-selected Domain (Optional)
$preselected_id = $_GET['domain_id'] ?? null;

// 2. Fetch All Active Domains for the Dropdown
$all_domains = $db->fetchAll("SELECT domain_ID, domain_name FROM domain WHERE status = 'Active' ORDER BY domain_name ASC");

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $criteria_name = sanitize($_POST['criteria_name']);
        $selected_domain_id = $_POST['domain_id'] ?? null; // Get domain from dropdown
        
        // Validation
        if (empty($selected_domain_id)) {
            throw new Exception('Please select a domain.');
        }
        if (empty($criteria_name)) {
            throw new Exception('Criteria name is required.');
        }
        
        // Insert Query
        $sql = "INSERT INTO criteria (domain_ID, criteria_name, input_id, input_at, status) 
                VALUES (:domain_id, :criteria_name, :user_id, NOW(), 'Active')";
        
        $current_user = getCurrentUser();
        $user_id = $current_user ? $current_user['user_ID'] : 'SYSTEM';

        $db->query($sql, [
            ':domain_id' => $selected_domain_id,
            ':criteria_name' => $criteria_name,
            ':user_id' => $user_id
        ]);
        
        setFlashMessage('success', "New criteria added successfully.");
        
        if ($preselected_id) {
            header("Location: ../criteria/view-criteria.php?id={$selected_domain_id}");
        } else {
            header("Location: ../criteria/index.php"); 
        }
        exit();

    } catch (Exception $e) {
        setFlashMessage('danger', $e->getMessage());
        // Reload page (keep query param if it existed)
        header('Location: add-criteria.php' . ($preselected_id ? '?domain_id=' . $preselected_id : ''));
        exit();
    }
}

$flash = getFlashMessage(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Criteria - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Page Layout */
        html, body { 
            height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; 
        }
        
        /* Sidebar Adjustment */
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        
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

        /* Gradient Button Style */
        .btn-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-gradient-primary:hover {
            color: white;
            opacity: 0.9;
        }

        .fw-bold-dark { font-weight: 600; color: #344767; }
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
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">Criteria </a></li>
                            <li class="breadcrumb-item active text-dark">Add Criteria</li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">Add New Criteria</h3>
                            <p class="text-muted mb-0">Define a new criteria and link it to a domain.</p>
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
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                                    <h5 class="mb-0">Criteria Details</h5>
                                </div>
                                <div class="card-body p-4">                                 
                                    <form method="POST" id="addCriteriaForm">
                                        
                                        <div class="mb-4">
                                            <label for="domain_id" class="form-label fw-bold-dark text-sm text-uppercase">Select Domain <span class="text-danger">*</span></label>
                                            <select class="form-select" id="domain_id" name="domain_id" required style="border-radius: 0.5rem; padding: 0.75rem;">
                                                <option value="" disabled <?php echo empty($preselected_id) ? 'selected' : ''; ?>>-- Choose a Domain --</option>
                                                <?php foreach ($all_domains as $d): ?>
                                                    <option value="<?php echo $d['domain_ID']; ?>" 
                                                        <?php echo ($preselected_id == $d['domain_ID']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($d['domain_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text mt-2">Which domain does this criteria belong to?</div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="criteria_name" class="form-label fw-bold-dark text-sm text-uppercase">Criteria Name <span class="text-danger">*</span></label>                                           
                                            <textarea class="form-control" id="criteria_name" name="criteria_name" rows="3" required maxlength="100"
                                                style="border-radius: 0.5rem;"
                                                placeholder="e.g., Audit Scope..."></textarea>                                                  
                                            <div class="d-flex justify-content-end mt-1">
                                                <small class="text-muted char-count" data-for="criteria_name">100 characters remaining</small>
                                            </div>                                             
                                            <div class="form-text mt-2">Enter the full, descriptive name...</div>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 pt-2">
                                            <a href="index.php" class="btn btn-outline-secondary px-4 rounded-3">
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-gradient-primary px-4 rounded-3">
                                                <i class="bi bi-save me-2"></i>Save Criteria
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
        
        const form = document.getElementById('addCriteriaForm'); 

        if (form) {
            // Detect changes
            form.addEventListener('change', () => isDirty = true);
            form.addEventListener('input', () => isDirty = true);
            
            // Allow submit without warning
            form.addEventListener('submit', () => {
                isDirty = false;
            });

            // Warning popup on leave
            window.addEventListener('beforeunload', function (e) {
                if (isDirty) {
                    e.preventDefault();
                    e.returnValue = ''; 
                }
            });
        }

        // Character counter logic
        const counters = document.querySelectorAll('.char-count');
        counters.forEach(counter => {
            const input = document.getElementById(counter.getAttribute('data-for'));
            if (input) {
                const updateCount = () => {
                    const remaining = input.getAttribute('maxlength') - input.value.length;
                    counter.textContent = `${remaining} characters remaining`;
                };
                updateCount(); 
                input.addEventListener('input', updateCount);
            }
        });
    });
</script>
</body>
</html>