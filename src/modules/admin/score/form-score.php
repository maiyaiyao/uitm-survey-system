<?php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$current_user = getCurrentUser();

$element_id = $_GET['element_id'] ?? null;
$se_id = $_GET['se_id'] ?? null; // If editing

if (!$element_id) {
    setFlashMessage('danger', 'Element ID missing.');
    redirect('index.php');
}

// Get Element Info (Joined to get Criteria ID for breadcrumbs)
$element = $db->fetchOne("
    SELECT e.*, c.criteria_name, c.criteria_ID, d.domain_ID , d.domain_name
    FROM element e 
    JOIN criteria c ON e.criteria_ID = c.criteria_ID
    JOIN domain d ON c.domain_ID = d.domain_ID
    WHERE e.element_ID = :id", [':id' => $element_id]);

if (!$element) { die("Element not found."); } 

// Get available global levels
$levels = $db->fetchAll("SELECT * FROM score WHERE status = 'Active' ORDER BY score_level ASC");

// If editing, get existing data
$existing_data = null;
if ($se_id) {
    $existing_data = $db->fetchOne("SELECT * FROM score_element WHERE se_ID = :id", [':id' => $se_id]);
}

// Handle Post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score_id = $_POST['score_id'];
    $details = trim($_POST['details']);
    
    try {
        if ($se_id) {
            // Update
            $db->query("UPDATE score_element SET details = ?, updated_id = ?, updated_at = NOW() WHERE se_ID = ?", 
                [$details, $current_user['user_ID'], $se_id]);
            setFlashMessage('success', 'Score description updated successfully.');
        } else {
            // Insert
            // Check for duplicate
            $check = $db->fetchOne("SELECT se_ID FROM score_element WHERE element_ID = ? AND score_ID = ?", [$element_id, $score_id]);
            if ($check) {
                setFlashMessage('warning', 'A description for this level already exists. Please edit it instead.');
            } else {
                // Trigger 'trg_sa_ID' handles ID generation
                $db->query("INSERT INTO score_element (element_ID, score_ID, details, input_id, input_at, status) VALUES (?, ?, ?, ?, NOW(), 'Active')", 
                    [$element_id, $score_id, $details, $current_user['user_ID']]);
                setFlashMessage('success', 'Score description added successfully.');
            }
        }
        redirect("index.php?element_id=$element_id");
    } catch (Exception $e) {
        setFlashMessage('danger', 'Error: ' . $e->getMessage());
    }
}

$flash = getFlashMessage();

// Helper to truncate text (if not already defined globally)
if (!function_exists('truncate')) {
    function truncate($string, $limit = 25, $end = '...') {
        if (mb_strlen($string) <= $limit) return $string;
        return mb_substr($string, 0, $limit) . $end;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $se_id ? 'Edit' : 'Add'; ?> Score - <?php echo APP_NAME; ?></title>
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
                                <a href="../criteria/view-criteria.php?id=<?php echo htmlspecialchars($element['domain_ID']); ?>"
                                   class="text-decoration-none text-secondary"
                                   title="Domain: <?php echo htmlspecialchars($element['domain_name']); ?>">
                                   Domain <?php echo htmlspecialchars(truncate($element['domain_name'], 15)); ?>
                                </a>
                            </li>

                            <li class="breadcrumb-item">
                                <a href="../element/view-element.php?id=<?php echo htmlspecialchars($element['criteria_ID']); ?>"
                                   class="text-decoration-none text-secondary"
                                   title="Criteria: <?php echo htmlspecialchars($element['criteria_name']); ?>">
                                   Criteria <?php echo htmlspecialchars(truncate($element['criteria_name'], 15)); ?>
                                </a>
                            </li>

                            <li class="breadcrumb-item">
                                <a href="index.php?element_id=<?php echo $element_id; ?>" 
                                   class="text-decoration-none text-secondary"
                                   title="Element: <?php echo htmlspecialchars($element['element_name']); ?>">
                                   Element <?php echo htmlspecialchars(truncate($element['element_name'], 15)); ?>
                                </a>
                            </li>

                            <li class="breadcrumb-item active text-dark"><?php echo $se_id ? 'Edit Description' : 'Add Score'; ?></li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="index.php?element_id=<?php echo $element_id; ?>" class="btn btn-outline-secondary btn-sm rounded-circle" title="Back">
                                    <i class="bi bi-arrow-left"></i>
                                </a>
                                <h3 class="fw-bold mb-0"><?php echo $se_id ? 'Edit Score' : 'Add Score'; ?></h3>
                            </div>
                            <p class="text-muted mb-0 mt-1 ms-5">
                                Element: <span class="fw-semibold"><?php echo htmlspecialchars($element['element_name']); ?></span>
                            </p>
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
                                    <h5 class="mb-0">Score Definition Details</h5>
                                </div>
                                
                                <div class="card-body p-4">
                                    <form method="POST" id="scoreForm">
                                        
                                        <div class="mb-4">
                                            <label class="form-label fw-bold-dark text-sm text-uppercase">Score Level <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select name="score_id" class="form-select" <?php echo $se_id ? 'disabled' : 'required'; ?> style="border-radius: 0.5rem;">
                                                    <option value="">-- Choose Maturity Level --</option>
                                                    <?php foreach ($levels as $lvl): ?>
                                                        <option value="<?php echo $lvl['score_ID']; ?>" 
                                                            <?php echo ($existing_data && $existing_data['score_ID'] == $lvl['score_ID']) ? 'selected' : ''; ?>>
                                                            Level <?php echo $lvl['score_level']; ?> - <?php echo htmlspecialchars($lvl['desc_level']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <?php if($se_id): ?><input type="hidden" name="score_id" value="<?php echo $existing_data['score_ID']; ?>"><?php endif; ?>
                                            <div class="form-text mt-2">Select the global maturity level this specific description corresponds to.</div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-bold-dark text-sm text-uppercase">Detailed Description <span class="text-danger">*</span></label>
                                            <textarea name="details" class="form-control" rows="8" required 
                                                style="border-radius: 0.5rem;"
                                                placeholder="Enter the specific requirements or criteria for this element at this level..."><?php echo htmlspecialchars($existing_data['details'] ?? ''); ?></textarea>
                                            <div class="form-text mt-2">Provide a clear, detailed explanation of what is required to meet this score level.</div>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 pt-3">
                                            <a href="index.php?element_id=<?php echo $element_id; ?>" class="btn btn-outline-secondary px-4 rounded-3">
                                                Cancel
                                            </a>
                                            <button type="submit" class="btn btn-gradient-primary px-4 rounded-3">
                                                <i class="bi bi-save me-2"></i> Save Score
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
            const form = document.getElementById('scoreForm'); 

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