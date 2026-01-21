<?php
// Path: ../../../config/config.php (up three levels from element/edit-element.php)
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$element_id = $_GET['id'] ?? null;
$element = null;

// --- 1. Fetch Existing Element Data ---
if (!$element_id) {
    setFlashMessage('error', 'Error: Element ID is missing.');
    header('Location: ../domain/index.php');
    exit();
}

try {
    // Fetch element and its parent criteria/domain info
    $element = $db->fetchOne("
        SELECT 
            e.element_ID, e.element_name, e.status, e.criteria_ID,
            c.criteria_name,
            d.domain_ID,
            d.domain_name
        FROM element e
        LEFT JOIN criteria c ON e.criteria_ID = c.criteria_ID
        LEFT JOIN domain d ON c.domain_ID = d.domain_ID
        WHERE e.element_ID = :id
    ", [':id' => $element_id]);

    if (!$element) {
        setFlashMessage('error', "Error: Element with ID '{$element_id}' not found.");
        header('Location: ../domain/index.php');
        exit();
    }

    // [NEW] Fetch All Active Criteria (Grouped by Domain for the Dropdown)
    $all_criteria = $db->fetchAll("
        SELECT c.criteria_ID, c.criteria_name, d.domain_name 
        FROM criteria c
        JOIN domain d ON c.domain_ID = d.domain_ID 
        WHERE c.status = 'Active' 
        ORDER BY d.domain_name ASC, c.criteria_name ASC
    ");

} catch (Exception $e) {
    setFlashMessage('error', 'Database Error: Could not retrieve data.');
    header('Location: ../domain/index.php');
    exit();
}

// --- 2. Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $new_element_name = sanitize($_POST['element_name']);
        $new_status = sanitize($_POST['status']);
        $new_criteria_id = $_POST['criteria_id']; // [NEW] Capture new criteria

        if (empty($new_element_name)) {
            throw new Exception('Element name cannot be empty.');
        }
        if (empty($new_criteria_id)) {
            throw new Exception('Criteria selection is required.');
        }

        // [UPDATED] Update element record including criteria_ID
        $sql = "UPDATE element SET 
                    element_name = :element_name,
                    criteria_ID = :criteria_id,
                    status = :status,
                    updated_id = :user_id,
                    updated_at = NOW()
                WHERE element_ID = :element_id";

        $current_user = getCurrentUser();
        $user_id = $current_user ? $current_user['user_ID'] : 'SYSTEM';

        $db->query($sql, [
            ':element_name' => $new_element_name,
            ':criteria_id' => $new_criteria_id,
            ':status' => $new_status,
            ':user_id' => $user_id,
            ':element_id' => $element_id
        ]);

        setFlashMessage('success', "Element updated successfully.");
        
        // Redirect to the (potentially new) parent criteria's list
        header("Location: view-element.php?id={$new_criteria_id}");
        exit();

    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
        header('Location: edit-element.php?id=' . $element_id);
        exit();
    }
}

// Variables for the view
$current_user = getCurrentUser();
$flash = getFlashMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Element - <?php echo htmlspecialchars($element['element_ID']); ?> - <?php echo APP_NAME; ?></title>
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
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">Element</a></li>
                            <li class="breadcrumb-item active text-dark">Edit Element</li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-5 gap-3">
                        <div>
                            <h3 class="fw-bold mb-1">Edit Element: <?php echo htmlspecialchars($element['element_name']); ?></h3>
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
                            <div class="card border-0 shadow-sm rounded-4 mb-5">
                                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                                    <h5 class="mb-0">Element Details</h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST" id="editElementForm">
                                        
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="element_id" class="form-label">Element ID</label>
                                                <input type="text" class="form-control bg-light" id="element_id" value="<?php echo htmlspecialchars($element['element_ID']); ?>" readonly>
                                            </div>
                                            
                                            <div class="col-md-8 mb-3">
                                                <label for="criteria_id" class="form-label">Criteria <span class="text-danger">*</span></label>
                                                <select class="form-select" id="criteria_id" name="criteria_id" required>
                                                    <?php 
                                                    $current_domain = '';
                                                    foreach ($all_criteria as $c): 
                                                        if ($current_domain !== $c['domain_name']) {
                                                            if ($current_domain !== '') echo '</optgroup>';
                                                            $current_domain = $c['domain_name'];
                                                            echo '<optgroup label="' . htmlspecialchars($current_domain) . '">';
                                                        }
                                                    ?>
                                                        <option value="<?php echo $c['criteria_ID']; ?>" 
                                                            <?php echo ($c['criteria_ID'] == $element['criteria_ID']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($c['criteria_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                    <?php if ($current_domain !== '') echo '</optgroup>'; ?>
                                                </select>
                                                <div class="form-text text-muted">You can move this element to a different criteria.</div>
                                                
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="element_name" class="form-label">Element Name <span class="text-danger">*</span></label>
                                            
                                            <textarea class="form-control" id="element_name" name="element_name" rows="4" 
                                                      maxlength="200" required><?php echo htmlspecialchars($element['element_name']); ?></textarea>
                                            
                                            <div class="d-flex justify-content-between mt-1">
                                                <div class="form-text text-muted">Update the description of this assessment item.</div>
                                                <small class="text-muted char-count" data-for="element_name">200 characters remaining</small>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="Active" <?php echo ($element['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="Inactive" <?php echo ($element['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 mt-5 pt-3 border-top">
                                            <a href="index.php" class="btn btn-outline-secondary px-4 rounded-3">
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

        // Dirty Form Logic
        let isDirty = false;
        const form = document.getElementById('editElementForm'); 

        if (form) {
            form.addEventListener('change', () => isDirty = true);
            form.addEventListener('input', () => isDirty = true);
            form.addEventListener('submit', () => { isDirty = false; });

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