<?php
// Path: src/modules/admin/survey/form-survey.php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$current_user = getCurrentUser();

$survey_id = isset($_GET['id']) ? $_GET['id'] : (isset($_GET['survey_id']) ? $_GET['survey_id'] : null);
$is_edit = !empty($survey_id);

// Default empty values
$survey_data = [
    'survey_name' => '',
    'org_ID' => '',
    'start_date' => '',
    'end_date' => '',
    'status' => 'Draft',
    'survey_description' => ''
];
$linked_domain_ids = []; 
$linked_dept_ids = [];
$existing_emails_str = ''; 

function formatDateTimeForInput($dateString) {
    if (empty($dateString)) return "";
    $timestamp = strtotime($dateString);
    return ($timestamp !== false) ? date('Y-m-d\TH:i', $timestamp) : "";
}

try {
    $organizations = $db->fetchAll("SELECT * FROM organization WHERE status = 'Active' ORDER BY org_name ASC");
    $all_departments = $db->fetchAll("SELECT * FROM department WHERE status = 'Active' ORDER BY dept_name ASC");
    $all_domains = $db->fetchAll("SELECT domain_ID, domain_name, status FROM domain ORDER BY domain_name");

    if ($is_edit) {
        $survey_data = $db->fetchOne("SELECT * FROM survey WHERE survey_ID = :id", [':id' => $survey_id]);
        if (!$survey_data) {
            setFlashMessage('error', "Error: Survey not found.");
            header('Location: index.php'); exit();
        }

        $linked_domain_ids = array_column($db->fetchAll("SELECT domain_id FROM survey_domain WHERE survey_id = :id", [':id' => $survey_id]), 'domain_id');
        $linked_dept_ids = array_column($db->fetchAll("SELECT dept_ID FROM survey_department WHERE survey_ID = :id", [':id' => $survey_id]), 'dept_ID');
        
        // Fetch users linked to this survey (for display in the input box)
        $linked_users = $db->fetchAll("SELECT DISTINCT u.primary_email FROM user u JOIN user_survey us ON u.user_ID = us.user_ID WHERE us.survey_ID = :id", [':id' => $survey_id]);
        $existing_emails_str = implode(",", array_column($linked_users, 'primary_email'));
    }

} catch (Exception $e) {
    setFlashMessage('error', 'Database Error: ' . $e->getMessage());
    header('Location: index.php'); exit();
}

// --- 2. Handle POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = $db->getConnection();
        
        $post_data = [
            'survey_name' => sanitize($_POST['survey_name']),
            'org_ID' => !empty($_POST['org_ID']) ? (int)$_POST['org_ID'] : null,
            'start_date' => sanitize($_POST['start_date']),
            'end_date' => sanitize($_POST['end_date']),
            'status' => sanitize($_POST['status']),
            'survey_description' => sanitize($_POST['survey_description']),
            'domain_ids' => $_POST['domain_ids'] ?? [],
            'dept_ids' => $_POST['dept_ids'] ?? [], 
            'allowed_emails' => $_POST['allowed_emails'] ?? ''
        ];

        // Validations
        if (empty($post_data['survey_name'])) throw new Exception('Survey Name is required.');
        if (empty($post_data['org_ID'])) throw new Exception('Organization is required.');
        if (empty($post_data['dept_ids'])) throw new Exception('At least one Department is required.');
        if (empty($post_data['domain_ids'])) throw new Exception('At least one Domain is required.');

        $user_id_system = $current_user ? $current_user['user_ID'] : 'SYSTEM';
        $db->beginTransaction();

        // A. Insert/Update Survey
        if ($is_edit) {
            $db->query("UPDATE survey SET survey_name=:name, org_ID=:org, start_date=:start, end_date=:end, status=:status, survey_description=:desc, updated_id=:uid, updated_by=NOW() WHERE survey_ID=:sid", [
                ':name' => $post_data['survey_name'], ':org' => $post_data['org_ID'], ':start' => $post_data['start_date'], ':end' => $post_data['end_date'], ':status' => $post_data['status'], ':desc' => $post_data['survey_description'], ':uid' => $user_id_system, ':sid' => $survey_id
            ]);
            $db->query("DELETE FROM survey_domain WHERE survey_id = :sid", [':sid' => $survey_id]);
            $db->query("DELETE FROM survey_department WHERE survey_id = :sid", [':sid' => $survey_id]);
            $target_survey_id = $survey_id;
            $success_msg = "Survey updated successfully.";
        } else {
            // FIX: Removed manual 'SV' ID generation.
            // Using AUTO_INCREMENT from database.
            $db->query("INSERT INTO survey (survey_name, org_ID, start_date, end_date, status, survey_description, created_by, created_at, updated_id, updated_by) VALUES (:name, :org, :start, :end, :status, :desc, :cid, NOW(), :uid, NOW())", [
                ':name' => $post_data['survey_name'], 
                ':org' => $post_data['org_ID'], 
                ':start' => $post_data['start_date'], 
                ':end' => $post_data['end_date'], 
                ':status' => $post_data['status'], 
                ':desc' => $post_data['survey_description'], 
                ':cid' => $user_id_system, 
                ':uid' => $user_id_system
            ]);
            
            // Get the ID automatically created by MySQL
            $target_survey_id = $db->getConnection()->lastInsertId();
            $success_msg = "Survey created successfully.";
        }

        // B. Save Domains & Depts
        foreach ($post_data['domain_ids'] as $did) $db->query("INSERT INTO survey_domain (survey_id, domain_id) VALUES (:sid, :did)", [':sid' => $target_survey_id, ':did' => sanitize($did)]);
        foreach ($post_data['dept_ids'] as $did) $db->query("INSERT INTO survey_department (survey_ID, dept_ID) VALUES (:sid, :did)", [':sid' => $target_survey_id, ':did' => sanitize($did)]);

        // C. Process Users
        
        $email_list = preg_split('/[\s,]+/', $post_data['allowed_emails'], -1, PREG_SPLIT_NO_EMPTY);
        $email_list = array_unique($email_list);
        $use_email_filter = !empty($email_list);

        // Get existing assignments
        $current_assignments = $db->fetchAll("SELECT CONCAT(user_ID, '-', dept_ID) as key_id FROM user_survey WHERE survey_ID = :sid", [':sid' => $target_survey_id]);
        $existing_keys = array_column($current_assignments, 'key_id');
        $users_added_count = 0;

        foreach ($post_data['dept_ids'] as $target_dept_id) {
            
            // Query strictly relies on user_department table
            $sql_users = "SELECT DISTINCT u.user_ID 
                          FROM user u
                          JOIN user_department ud ON u.user_ID = ud.user_ID
                          WHERE u.org_ID = ? 
                          AND u.status = 'Active'
                          AND ud.dept_ID = ?";
            
            $params = [$post_data['org_ID'], $target_dept_id];
            
            if ($use_email_filter) {
                $placeholders = implode(',', array_fill(0, count($email_list), '?'));
                $sql_users .= " AND u.primary_email IN ($placeholders)";
                $params = array_merge($params, $email_list);
            }

            $target_users = $db->fetchAll($sql_users, $params);

            foreach ($target_users as $user) {
                $uid = $user['user_ID'];
                $key = $uid . '-' . $target_dept_id;

                if (!in_array($key, $existing_keys)) {
                    $db->query("INSERT INTO user_survey (survey_ID, user_ID, dept_ID, status) VALUES (:sid, :uid, :did, 'Pending')", [
                        ':sid' => $target_survey_id,
                        ':uid' => $uid,
                        ':did' => $target_dept_id
                    ]);
                    $users_added_count++;
                }
            }
        }

        $db->commit();
        $success_msg .= " (Assigned " . $users_added_count . " new tasks).";
        setFlashMessage('success', $success_msg);
        header('Location: index.php'); exit();

    } catch (Exception $e) {
        if ($conn->inTransaction()) $db->rollBack();
        setFlashMessage('error', 'Error: ' . $e->getMessage());
        
        $survey_data = array_merge($survey_data, $_POST);
        $linked_dept_ids = $_POST['dept_ids'] ?? [];
        $linked_domain_ids = $_POST['domain_ids'] ?? [];
        $existing_emails_str = $_POST['allowed_emails'] ?? '';
    }
}
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit Survey' : 'Create Survey'; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Styles */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        
        .domain-checkbox-group {
            max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6;
            border-radius: 0.375rem; padding: 1rem; background-color: #fff;
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
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">Survey Management</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page"><?php echo $is_edit ? 'Edit Survey' : 'Create New Survey'; ?></li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h3 class="fw-bold mb-1"><?php echo $is_edit ? 'Edit Survey' : 'Create New Survey'; ?></h3>
                            <p class="text-muted mb-0">
                                <?php echo $is_edit ? "Update details for <strong>" . htmlspecialchars($survey_data['survey_name']) . "</strong>" : "Define target audience and parameters for new assessment."; ?>
                            </p>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary rounded-3 px-3">
                            <i class="bi bi-arrow-left me-2"></i>Back
                        </a>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo ($flash['type'] == 'error') ? 'danger' : $flash['type']; ?> alert-dismissible fade show border-0 shadow-sm mb-4">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm rounded-4 mb-5">
                        <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                            <h5 class="mb-0">Survey Details & Audience</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" id="surveyForm">
                                <div class="row g-4">
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="survey_name" class="form-label">Survey Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="survey_name" name="survey_name" 
                                                maxlength="100" required 
                                                value="<?php echo htmlspecialchars($survey_data['survey_name']); ?>">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="orgSelect" class="form-label">Organization <span class="text-danger">*</span></label>
                                            <select class="form-select" name="org_ID" id="orgSelect" required>
                                                <option value="">-- Select Organization --</option>
                                                <?php foreach ($organizations as $org): ?>
                                                    <option value="<?php echo $org['org_ID']; ?>" <?php echo ($survey_data['org_ID'] == $org['org_ID']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($org['org_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label mb-0">Departments <span class="text-danger">*</span></label>
                                                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" id="toggleDeptsBtn">
                                                    Select All
                                                </button>
                                            </div>
                                            <div class="domain-checkbox-group bg-light" id="deptContainer">
                                                <div class="text-muted small p-2">Please select an Organization first.</div>
                                            </div>
                                            <div class="form-text">Survey will be assigned to all active users in selected departments.</div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" required
                                                       value="<?php echo formatDateTimeForInput($survey_data['start_date']); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                                <input type="datetime-local" class="form-control" id="end_date" name="end_date" required
                                                       value="<?php echo formatDateTimeForInput($survey_data['end_date']); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="survey_description" class="form-label">Description</label>
                                            <textarea class="form-control" id="survey_description" name="survey_description" 
                                                    maxlength="500" rows="4" 
                                                    placeholder="Enter a brief description..."><?php echo htmlspecialchars($survey_data['survey_description']); ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="Draft" <?php echo ($survey_data['status'] === 'Draft') ? 'selected' : ''; ?>>Draft (Hidden)</option>
                                                <option value="Active" <?php echo ($survey_data['status'] === 'Active') ? 'selected' : ''; ?>>Active (Publish / Scheduled)</option>
                                                <option value="Archived" <?php echo ($survey_data['status'] === 'Archived') ? 'selected' : ''; ?>>Archived (Closed)</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label mb-0">Select Domains <span class="text-danger">*</span></label>
                                                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" id="toggleDomainsBtn">
                                                    Select All
                                                </button>
                                            </div>

                                            <div class="domain-checkbox-group bg-light">
                                                <?php if (empty($all_domains)): ?>
                                                    <div class="text-muted small p-2">No domains found.</div>
                                                <?php else: ?>
                                                    <?php foreach ($all_domains as $domain): 
                                                        $is_linked = in_array($domain['domain_ID'], $linked_domain_ids);
                                                    ?>
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="domain_ids[]" 
                                                                   value="<?php echo htmlspecialchars($domain['domain_ID']); ?>" 
                                                                   id="domain_<?php echo htmlspecialchars($domain['domain_ID']); ?>"
                                                                   <?php echo $is_linked ? 'checked' : ''; ?>>
                                                            <label class="form-check-label small" for="domain_<?php echo htmlspecialchars($domain['domain_ID']); ?>">
                                                                <?php echo htmlspecialchars($domain['domain_name']); ?> 
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div> 
                                </div> 
                                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <a href="index.php" class="btn btn-outline-secondary px-4 rounded-3">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4 rounded-3" 
                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                        <i class="bi bi-save me-2"></i><?php echo $is_edit ? 'Save Changes' : 'Create Survey'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        const allDepts = <?php echo json_encode($all_departments); ?>;
        const savedDeptIds = <?php echo json_encode($linked_dept_ids); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const orgSelect = document.getElementById('orgSelect');
            const deptContainer = document.getElementById('deptContainer');
            const toggleDeptsBtn = document.getElementById('toggleDeptsBtn');
            const toggleDomBtn = document.getElementById('toggleDomainsBtn');

            // 1. Dynamic Department Logic 
            function updateDepts() {
                const orgId = orgSelect.value;
                deptContainer.innerHTML = ''; 

                if (!orgId) {
                    deptContainer.innerHTML = '<div class="text-muted small p-2">Select an Organization first.</div>';
                    return;
                }

                // Filter Departments by Org ID
                const filtered = allDepts.filter(d => d.org_ID == orgId);

                if (filtered.length === 0) {
                    deptContainer.innerHTML = '<div class="text-muted small p-2">No departments found for this Organization.</div>';
                } else {
                    filtered.forEach(d => {
                        // Check if this dept was previously selected (Edit mode or validation error reload)
                        const isChecked = savedDeptIds.some(id => id == d.dept_ID) ? 'checked' : '';
                        
                        const div = document.createElement('div');
                        div.className = 'form-check mb-1';
                        div.innerHTML = `
                            <input class="form-check-input" type="checkbox" name="dept_ids[]" 
                                   value="${d.dept_ID}" id="dept_${d.dept_ID}" ${isChecked}>
                            <label class="form-check-label small" for="dept_${d.dept_ID}">
                                ${d.dept_name} <span class="text-muted" style="font-size:0.7em">(${d.dept_code})</span>
                            </label>
                        `;
                        deptContainer.appendChild(div);
                    });
                }
            }
            orgSelect.addEventListener('change', updateDepts);

            // Initialize (if Edit mode or error reload)
            if (orgSelect.value) {
                updateDepts();
            }

            // --- 2. Select All Toggle Logic ---
            // Department Toggle
            if(toggleDeptsBtn) {
                toggleDeptsBtn.addEventListener('click', function() {
                    const checkboxes = deptContainer.querySelectorAll('input[name="dept_ids[]"]');
                    if (checkboxes.length === 0) return;
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(cb => { cb.checked = !allChecked; });
                    this.textContent = !allChecked ? 'Deselect All' : 'Select All';
                });
            }

            // Domain Toggle
            if(toggleDomBtn) {
                toggleDomBtn.addEventListener('click', function() {
                    const checkboxes = document.querySelectorAll('input[name="domain_ids[]"]');
                    if (checkboxes.length === 0) return;
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(cb => { cb.checked = !allChecked; });
                    this.textContent = !allChecked ? 'Deselect All' : 'Select All';
                });
            }

            // --- 3. Unsaved Changes Warning ---
            let isDirty = false;
            const form = document.getElementById('surveyForm'); 
            if (form) {
                form.addEventListener('change', () => isDirty = true);
                form.addEventListener('input', () => isDirty = true);
                form.addEventListener('submit', () => { isDirty = false; });
            }
            window.addEventListener('beforeunload', (e) => { 
                if (isDirty) { e.preventDefault(); e.returnValue = ''; } 
            });
        });
    </script>
</body>
</html>