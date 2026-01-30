<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();

// 1. Get Pre-selected Criteria 
$preselected_id = $_GET['criteria_id'] ?? null;
$preselected_criteria = null;


if ($preselected_id) {
    $preselected_criteria = $db->fetchOne("
        SELECT c.criteria_ID, c.criteria_name, d.domain_ID, d.domain_name
        FROM criteria c
        JOIN domain d ON c.domain_ID = d.domain_ID
        WHERE c.criteria_ID = :id
    ", [':id' => $preselected_id]);
}

// 2. Fetch All Active Criteria 
$all_criteria = $db->fetchAll("
    SELECT c.criteria_ID, c.criteria_name, d.domain_name 
    FROM criteria c
    JOIN domain d ON c.domain_ID = d.domain_ID 
    WHERE c.status = 'Active' 
    ORDER BY d.domain_name ASC, c.criteria_name ASC
");

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $element_name = sanitize($_POST['element_name']);
        $selected_criteria_id = $_POST['criteria_id'] ?? null;
        
        // Validation
        if (empty($selected_criteria_id)) {
            throw new Exception('Please select a Criteria.');
        }
        if (empty($element_name)) {
            throw new Exception('Element name is required.');
        }
        
        // Insert Query
        $sql = "INSERT INTO element (criteria_ID, element_name, input_id, input_at, status) 
                VALUES (:criteria_id, :element_name, :user_id, NOW(), 'Active')";
        
        $current_user = getCurrentUser();
        $user_id = $current_user ? $current_user['user_ID'] : 'SYSTEM';

        $db->query($sql, [
            ':criteria_id' => $selected_criteria_id,
            ':element_name' => $element_name,
            ':user_id' => $user_id
        ]);
        
        setFlashMessage('success', "New element added successfully.");
        
        if ($preselected_id) {
            header("Location: view-element.php?id={$selected_criteria_id}");
        } else {
            header("Location: index.php"); 
        }
        exit();

    } catch (Exception $e) {
        setFlashMessage('danger', $e->getMessage());
        // Reload page
        header('Location: add-element.php' . ($preselected_id ? '?criteria_id=' . $preselected_id : ''));
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
            
            <div class="col-auto">
                <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
            </div>
            
            <div class="col main-content-wrapper">
                <div class="main-content px-4 py-4">

                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="../parameter-settings.php" class="text-decoration-none text-secondary">Parameter Settings</a></li>
                            
                            <?php if ($preselected_criteria): ?>
                                <li class="breadcrumb-item">
                                    <a href="../criteria/view-criteria.php?id=<?php echo $preselected_criteria['domain_ID']; ?>" class="text-decoration-none text-secondary">
                                        Domain <?php echo htmlspecialchars(truncate($preselected_criteria['domain_name'], 15)); ?>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="view-element.php?id=<?php echo $preselected_criteria['criteria_ID']; ?>" class="text-decoration-none text-secondary">
                                        Criteria <?php echo htmlspecialchars(truncate($preselected_criteria['criteria_name'], 15)); ?>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">Element</a></li>
                            <?php endif; ?>

                            <li class="breadcrumb-item active text-dark">Add Element</li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">Add New Element</h3>
                            <p class="text-muted mb-0">Create a new assessment element linked to a criteria.</p>
                        </div>
                        <div>
                            <a href="<?php echo $preselected_id ? "view-element.php?id=$preselected_id" : "index.php"; ?>" class="btn btn-outline-secondary shadow-sm px-4 py-2 rounded-3">
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
                                            <label for="criteria_id" class="form-label">Select Criteria <span class="text-danger">*</span></label>
                                            
                                            <select class="form-select" id="criteria_id" name="criteria_id" required>
                                                <option value="" disabled <?php echo empty($preselected_id) ? 'selected' : ''; ?>>-- Choose Criteria --</option>
                                                
                                                <?php 
                                                // Grouping Logic for Dropdown
                                                $current_domain = '';
                                                foreach ($all_criteria as $c): 
                                                    // If domain changes, close previous optgroup and start new one
                                                    if ($current_domain !== $c['domain_name']) {
                                                        if ($current_domain !== '') echo '</optgroup>';
                                                        $current_domain = $c['domain_name'];
                                                        echo '<optgroup label="' . htmlspecialchars($current_domain) . '">';
                                                    }
                                                ?>
                                                    <option value="<?php echo $c['criteria_ID']; ?>" 
                                                        <?php echo ($preselected_id == $c['criteria_ID']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($c['criteria_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <?php if ($current_domain !== '') echo '</optgroup>'; ?>
                                            </select>
                                            
                                            <div class="form-text text-muted">Elements must belong to a specific Criteria (grouped by Domain above).</div>
                                            
                                            
                                            
                                        </div>

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
                                            <a href="<?php echo $preselected_id ? "view-element.php?id=$preselected_id" : "index.php"; ?>" class="btn btn-outline-secondary px-4 rounded-3">
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
            // Character Counter Logic
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
                    updateCount();
                    inputElement.addEventListener('input', updateCount);
                }
            });

            // Form Dirty Check Logic
            let isDirty = false;
            const form = document.getElementById('addElementForm'); 

            if (form) {
                form.addEventListener('change', () => isDirty = true);
                form.addEventListener('input', () => isDirty = true);
                
                form.addEventListener('submit', () => {
                    isDirty = false;
                });

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