<?php
// Path: ../../../config/config.php
require_once '../../../config/config.php';
requireRole(['admin']);

// Get the parent Criteria ID from the URL
$criteria_id = $_GET['criteria_id'] ?? null;
if (!$criteria_id) {
    // Redirect if no criteria_id is provided
    setFlashMessage('danger', 'No criteria specified.');
    redirect(BASE_URL . '/modules/admin/domain/index.php');
}

$db = new Database();

// Get criteria details (and its domain) for context and breadcrumbs
$sql_criteria = "
    SELECT 
        c.criteria_ID, 
        c.criteria_name, 
        d.domain_ID, 
        d.domain_name
    FROM criteria c
    JOIN domain d ON c.domain_ID = d.domain_ID
    WHERE c.criteria_ID = :id
";
$criteria = $db->fetchOne($sql_criteria, [':id' => $criteria_id]);

if (!$criteria) {
    setFlashMessage('danger', 'Criteria not found');
    redirect(BASE_URL . '/modules/admin/domain/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $element_name = sanitize($_POST['element_name']);
        
        if (empty($element_name)) {
            throw new Exception('Element name is required');
        }
        
        // Insert element. The database trigger (if one exists) or logic
        // should handle the element_ID generation.
        $sql = "INSERT INTO element (criteria_ID, element_name, input_id, input_at, status) 
                VALUES (:criteria_id, :element_name, :user_id, NOW(), 'Active')";
        
        $current_user = getCurrentUser();
        $user_id = $current_user ? $current_user['user_ID'] : 'SYSTEM';

        $db->query($sql, [
            ':criteria_id' => $criteria_id,
            ':element_name' => $element_name,
            ':user_id' => $user_id
        ]);
        
        setFlashMessage('success', "New element added successfully to Criteria {$criteria_id}.");
        
        // Redirect back to the element list for that criteria
        // (which is linked from view-criteria.php)
        header("Location: view-element.php?id={$criteria_id}");
        exit();

    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
        header('Location: add-element.php?criteria_id=' . $criteria_id);
        exit();
    }
}

$flash = getFlashMessage(); 

// Variables needed for the sidebar's active state logic
$currentPage = basename(__FILE__); // 'add-element.php'
$currentDir = basename(__DIR__); // 'element'
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Element - <?php echo APP_NAME; ?></title>
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
        .form-control {
            border: 1px solid #d2d6da;
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
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
                                <a href="../criteria/view-criteria.php?id=<?php echo htmlspecialchars($criteria['domain_ID']); ?>"
                                class="text-decoration-none text-secondary"
                                title="Domain: <?php echo htmlspecialchars($criteria['domain_name']); ?>"> Domain <?php echo htmlspecialchars(truncate($criteria['domain_name'], 20)); ?>
                                </a>
                            </li>

                            <li class="breadcrumb-item">
                                <a href="view-element.php?id=<?php echo htmlspecialchars($criteria['criteria_ID']); ?>"
                                class="text-decoration-none text-secondary"
                                title="Criteria: <?php echo htmlspecialchars($criteria['criteria_name']); ?>"> Criteria <?php echo htmlspecialchars(truncate($criteria['criteria_name'], 20)); ?>
                                </a>
                            </li>

                            <li class="breadcrumb-item active text-dark">Element - Add Element</li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">Add New Element</h3>
                            <p class="text-muted mb-0">Adding to Criteria: <strong><?php echo htmlspecialchars($criteria['criteria_name']); ?></strong></p>
                        </div>
                        <div>
                            <a href="view-element.php?id=<?php echo htmlspecialchars($criteria['criteria_ID']); ?>" class="btn btn-outline-secondary shadow-sm px-4 py-2 rounded-3">
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
                                    <h5 class="mb-0">Element Details</h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST" id="addElementForm">
                                        
                                        <div class="mb-4">
                                            <label for="element_name" class="form-label">Element Name <span class="text-danger">*</span></label>
                                            
                                            <textarea class="form-control" id="element_name" name="element_name" rows="4" 
                                                      maxlength="200" required 
                                                      placeholder="e.g., Terdapat polisi keselamatan maklumat yang diluluskan oleh pengurusan..."></textarea>
                                            
                                            <div class="d-flex justify-content-between mt-1">
                                                <div class="form-text text-muted">Enter the full, descriptive name of the new assessment item.</div>
                                                <small class="text-muted char-count" data-for="element_name">200 characters remaining</small>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 mt-5 pt-3 border-top">
                                            <a href="view-element.php?id=<?php echo htmlspecialchars($criteria['criteria_ID']); ?>" class="btn btn-outline-secondary px-4 rounded-3">
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary px-4 rounded-3" 
                                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                                <i class="bi bi-plus-lg me-2"></i>Add Element
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
            const counters = document.querySelectorAll('.char-count');

            counters.forEach(counter => {
                const inputId = counter.getAttribute('data-for');
                const inputElement = document.getElementById(inputId);

                if (inputElement) {
                    const maxLength = inputElement.getAttribute('maxlength');

                    const updateCount = () => {
                        const currentLength = inputElement.value.length;
                        const remaining = maxLength - currentLength;
                        
                        counter.textContent = `${remaining} characters remaining`;

                        if (remaining === 0) {
                            counter.classList.remove('text-muted');
                            counter.classList.add('text-danger');
                        } else {
                            counter.classList.add('text-muted');
                            counter.classList.remove('text-danger');
                        }
                    };

                    // Run immediately and on input
                    updateCount();
                    inputElement.addEventListener('input', updateCount);
                }
            });
        });
    
        document.addEventListener('DOMContentLoaded', function() {
            let isDirty = false;
            
            // 1. CHANGE THIS ID to match your HTML form ID
            const form = document.getElementById('addElementForm'); 

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