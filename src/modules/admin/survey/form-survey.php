<?php
// Path: modules/admin/survey/form-survey.php
require_once '../../../config/config.php';
requireRole(['admin']);

$db = new Database();
$current_user = getCurrentUser();

// --- 1. Determine Mode & Initialize Variables ---
// Check for 'id' OR 'survey_id' to be safe with different link formats
$survey_id = isset($_GET['id']) ? $_GET['id'] : (isset($_GET['survey_id']) ? $_GET['survey_id'] : null);
$is_edit = !empty($survey_id);

// Default empty values for Create mode
$survey_data = [
    'survey_name' => '',
    'department' => '',
    'start_date' => '',
    'end_date' => '',
    'status' => 'Draft',
    'survey_description' => ''
];
$linked_domain_ids = []; // Array of selected domain IDs
$existing_emails_str = ''; // Comma-separated string of emails

// Helper to format datetime for HTML5 input (YYYY-MM-DDTHH:MM)
function formatDateTimeForInput($dateString) {
    if (empty($dateString)) return "";
    $timestamp = strtotime($dateString);
    return ($timestamp !== false) ? date('Y-m-d\TH:i', $timestamp) : "";
}

try {
    // A. Fetch All Available Domains (Needed for both Create and Edit)
    $all_domains = $db->fetchAll("SELECT domain_ID, domain_name, status FROM domain ORDER BY domain_name");

    // B. If Edit Mode, Fetch Existing Data
    if ($is_edit) {
        // 1. Fetch Survey Details
        $survey_data = $db->fetchOne("SELECT * FROM survey WHERE survey_ID = :id", [':id' => $survey_id]);
        
        if (!$survey_data) {
            setFlashMessage('error', "Error: Survey with ID '{$survey_id}' not found.");
            header('Location: index.php');
            exit();
        }

        // 2. Fetch Linked Domain IDs
        $linked_links = $db->fetchAll("SELECT domain_id FROM survey_domain WHERE survey_id = :id", [':id' => $survey_id]);
        $linked_domain_ids = array_column($linked_links, 'domain_id');

        // 3. Fetch Linked Users (Emails)
        $linked_users = $db->fetchAll("
            SELECT u.primary_email 
            FROM user u 
            JOIN user_survey us ON u.user_ID = us.user_ID 
            WHERE us.survey_ID = :id
        ", [':id' => $survey_id]);
        
        $existing_emails_str = implode(",", array_column($linked_users, 'primary_email'));
    }

} catch (Exception $e) {
    setFlashMessage('error', 'Database Error: ' . $e->getMessage());
    header('Location: index.php');
    exit();
}

// --- 2. Handle Form Submission (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = $db->getConnection();
        
        // Sanitize Inputs
        $post_data = [
            'survey_name' => sanitize($_POST['survey_name']),
            'department' => sanitize($_POST['department']),
            'start_date' => sanitize($_POST['start_date']),
            'end_date' => sanitize($_POST['end_date']),
            'status' => sanitize($_POST['status']),
            'survey_description' => sanitize($_POST['survey_description']),
            'domain_ids' => $_POST['domain_ids'] ?? [],
            'allowed_emails' => $_POST['allowed_emails'] ?? ''
        ];

        // Validation
        if (empty($post_data['survey_name']) || empty($post_data['department']) || empty($post_data['start_date']) || empty($post_data['end_date'])) {
            throw new Exception('Please fill out all required fields: Name, Department, Start Date, and End Date.');
        }
        if (empty($post_data['domain_ids'])) {
            throw new Exception('You must select at least one domain for the survey.');
        }

        $user_id_system = $current_user ? $current_user['user_ID'] : 'SYSTEM';

        // --- Database Transaction ---
        $db->beginTransaction();

        if ($is_edit) {
            // === UPDATE LOGIC ===
            $sql_update = "UPDATE survey SET 
                            survey_name = :name, department = :dept, start_date = :start, end_date = :end, 
                            status = :status, survey_description = :desc, updated_id = :uid, updated_by = NOW() 
                           WHERE survey_ID = :sid";
            
            $db->query($sql_update, [
                ':name' => $post_data['survey_name'], ':dept' => $post_data['department'], 
                ':start' => $post_data['start_date'], ':end' => $post_data['end_date'], 
                ':status' => $post_data['status'], ':desc' => $post_data['survey_description'],
                ':uid' => $user_id_system, ':sid' => $survey_id
            ]);

            // Clean up existing domains to rebuild (Domains have no progress state, safe to delete/re-add)
            $db->query("DELETE FROM survey_domain WHERE survey_id = :sid", [':sid' => $survey_id]);
            
            // NOTE: We do NOT delete user_survey here anymore to prevent progress loss.
            
            $target_survey_id = $survey_id;
            $success_msg = "Survey '{$post_data['survey_name']}' updated successfully.";

        } else {
            // === CREATE LOGIC ===
            // Generate Survey ID (SV001, SV002...)
            $last_id_row = $db->fetchOne("SELECT survey_ID FROM survey ORDER BY survey_ID DESC LIMIT 1");
            $new_id_num = 1;
            if ($last_id_row) {
                $last_id_num = (int) substr($last_id_row['survey_ID'], 2);
                $new_id_num = $last_id_num + 1;
            }
            $target_survey_id = 'SV' . str_pad($new_id_num, 3, '0', STR_PAD_LEFT);

            $sql_insert = "INSERT INTO survey (
                                survey_ID, survey_name, department, start_date, end_date, 
                                status, survey_description, created_by, created_at, updated_id, updated_by
                            ) VALUES (
                                :sid, :name, :dept, :start, :end, 
                                :status, :desc, :cid, NOW(), :uid, NOW()
                            )";
            
            $db->query($sql_insert, [
                ':sid' => $target_survey_id, ':name' => $post_data['survey_name'], ':dept' => $post_data['department'], 
                ':start' => $post_data['start_date'], ':end' => $post_data['end_date'], 
                ':status' => $post_data['status'], ':desc' => $post_data['survey_description'],
                ':cid' => $user_id_system, ':uid' => $user_id_system
            ]);
            
            $success_msg = "Survey '{$post_data['survey_name']}' (ID: {$target_survey_id}) created successfully.";
        }

        // === COMMON BRIDGE TABLE LOGIC ===
        
        // 1. Insert Domains (Rebuild)
        $sql_domain = "INSERT INTO survey_domain (survey_domain_id, survey_id, domain_id) VALUES (NULL, :sid, :did)";
        foreach ($post_data['domain_ids'] as $did) {
            $db->query($sql_domain, [':sid' => $target_survey_id, ':did' => sanitize($did)]);
        }

        // 2. Manage Users (Smart Sync to preserve progress)
        $email_list = preg_split('/[\s,]+/', $post_data['allowed_emails'], -1, PREG_SPLIT_NO_EMPTY);
        $email_list = array_unique($email_list);
        
        // A. Resolve submitted emails to User IDs
        $new_user_ids = [];
        $not_found_emails = [];
        $sql_find_user = "SELECT user_ID FROM user WHERE primary_email = :email LIMIT 1";
        
        foreach ($email_list as $email) {
            $email = trim($email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
            
            $u = $db->fetchOne($sql_find_user, [':email' => $email]);
            if ($u) {
                $new_user_ids[] = $u['user_ID'];
            } else {
                $not_found_emails[] = $email;
            }
        }
        
        // B. Fetch Current Participants (Only needed for Edit mode)
        $current_user_ids = [];
        if ($is_edit) {
            $curr_users = $db->fetchAll("SELECT user_ID FROM user_survey WHERE survey_ID = :sid", [':sid' => $target_survey_id]);
            $current_user_ids = array_column($curr_users, 'user_ID');
        }
        
        // C. Calculate Differences
        $users_to_add = array_diff($new_user_ids, $current_user_ids);
        $users_to_remove = array_diff($current_user_ids, $new_user_ids);
        
        // D. Perform Updates
        
        // Add new users
        if (!empty($users_to_add)) {
            $sql_insert_us = "INSERT INTO user_survey (user_survey_ID, survey_ID, user_ID, status) VALUES (NULL, :sid, :uid, 'Pending')";
            foreach ($users_to_add as $uid) {
                $db->query($sql_insert_us, [':sid' => $target_survey_id, ':uid' => $uid]);
            }
        }
        
        // Remove unselected users
        if (!empty($users_to_remove)) {
            $sql_delete_us = "DELETE FROM user_survey WHERE survey_ID = :sid AND user_ID = :uid";
            foreach ($users_to_remove as $uid) {
                $db->query($sql_delete_us, [':sid' => $target_survey_id, ':uid' => $uid]);
            }
        }

        $db->commit();

        if (!empty($not_found_emails)) {
            $success_msg .= " (Warning: Some emails were not found: " . implode(', ', $not_found_emails) . ")";
        }
        
        setFlashMessage('success', $success_msg);
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        $db->rollBack();
        setFlashMessage('error', 'Error: ' . $e->getMessage());
        
        // Restore submitted data to the form
        $survey_data = array_merge($survey_data, $_POST);
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
        /* Shared Styles */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
        
        /* Email Chip & Form Styles */
        .domain-checkbox-group {
            max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6;
            border-radius: 0.375rem; padding: 1rem; background-color: #fff;
        }
        .email-input-container {
            display: flex; flex-wrap: wrap; align-items: center; gap: 6px; padding: 6px;
            border: 1px solid #dee2e6; border-radius: 0.375rem; background-color: #fff; cursor: text; min-height: 45px;
        }
        .email-input-container:focus-within { border-color: #86b7fe; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); }
        .email-chip {
            display: inline-flex; align-items: center; background-color: #e9ecef; border-radius: 16px;
            padding: 4px 10px; font-size: 0.875rem; color: #495057; user-select: none;
        }
        .email-chip .btn-close { font-size: 0.65rem; margin-left: 6px; cursor: pointer; }
        .email-search-input { border: none; outline: none; flex-grow: 1; min-width: 150px; background: transparent; padding: 4px 0; }
        .autocomplete-list {
            position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #dee2e6;
            border-radius: 0.375rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 9999 !important;
            max-height: 200px; overflow-y: auto; display: none;
        }
        .autocomplete-item { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f8f9fa; }
        .autocomplete-item:hover { background-color: #f8f9fa; }
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
                                <?php echo $is_edit ? "Update details for <strong>" . htmlspecialchars($survey_data['survey_name']) . "</strong>" : "Fill in the details below to initialize a new assessment."; ?>
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
                            <h5 class="mb-0">Survey Details</h5>
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
                                            <div class="d-flex justify-content-end">
                                                <small class="text-muted char-count" data-for="survey_name">100 characters remaining</small>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="department" name="department" required
                                                   value="<?php echo htmlspecialchars($survey_data['department']); ?>">
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

                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="Draft" <?php echo ($survey_data['status'] === 'Draft') ? 'selected' : ''; ?>>Draft (Hidden)</option>
                                                <option value="Active" <?php echo ($survey_data['status'] === 'Active') ? 'selected' : ''; ?>>Active (Publish / Scheduled)</option>
                                                <option value="Archived" <?php echo ($survey_data['status'] === 'Archived') ? 'selected' : ''; ?>>Archived (Closed Forever)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="survey_description" class="form-label">Description</label>
                                            <textarea class="form-control" id="survey_description" name="survey_description" 
                                                    maxlength="500" rows="4" 
                                                    placeholder="Enter a brief description..."><?php echo htmlspecialchars($survey_data['survey_description']); ?></textarea>
                                            <div class="d-flex justify-content-end">
                                                <small class="text-muted char-count" data-for="survey_description">500 characters remaining</small>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label mb-0">Authorized Participants</label>
                                                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" id="addAllUsersBtn">
                                                    Add All Active Users
                                                </button>
                                            </div>
                                            
                                            <input type="hidden" name="allowed_emails" id="hidden_emails" 
                                                value="<?php echo htmlspecialchars($existing_emails_str); ?>">

                                            <div class="position-relative">
                                                <div class="email-input-container" id="emailContainer">
                                                    <input type="text" class="email-search-input" id="emailInput" 
                                                        placeholder="Type name or email..." autocomplete="off">
                                                </div>
                                                <div class="autocomplete-list" id="suggestionList"></div>
                                            </div>
                                            <div class="form-text mt-1">Type to search users. Press Enter to add.</div>
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
                                                    <div class="text-muted small p-2">No domains found. Please create domains first.</div>
                                                <?php else: ?>
                                                    <?php foreach ($all_domains as $domain): 
                                                        $is_linked = in_array($domain['domain_ID'], $linked_domain_ids);
                                                        $is_active = $domain['status'] === 'Active';
                                                        $label_class = $is_active ? '' : 'text-muted';
                                                        $label_title = $is_active ? '' : ' (Inactive)';
                                                        // Only disable if inactive AND not already linked (allow unchecking previously linked inactive domains)
                                                        $is_disabled = !$is_active && !$is_linked;
                                                    ?>
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="domain_ids[]" 
                                                                   value="<?php echo htmlspecialchars($domain['domain_ID']); ?>" 
                                                                   id="domain_<?php echo htmlspecialchars($domain['domain_ID']); ?>"
                                                                   <?php echo $is_linked ? 'checked' : ''; ?>
                                                                   <?php echo $is_disabled ? 'disabled' : ''; ?>>
                                                            <label class="form-check-label small <?php echo $label_class; ?>" for="domain_<?php echo htmlspecialchars($domain['domain_ID']); ?>">
                                                                <?php echo htmlspecialchars($domain['domain_name']); ?> 
                                                                <span class="text-secondary opacity-75 ms-1" style="font-size: 0.75rem;">(<?php echo htmlspecialchars($domain['domain_ID']); ?>)</span>
                                                                <?php echo $label_title; ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="form-text">Select at least one domain.</div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- 1. Logic: Email Chips, Domain Toggle, Char Counter ---
        document.addEventListener('DOMContentLoaded', function() {
            
            // A. Domain Toggle
            const toggleBtn = document.getElementById('toggleDomainsBtn');
            if(toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const checkboxes = document.querySelectorAll('input[name="domain_ids[]"]:not(:disabled)');
                    if (checkboxes.length === 0) return;
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(cb => { cb.checked = !allChecked; });
                    this.textContent = !allChecked ? 'Deselect All' : 'Select All';
                });
            }

            // B. Character Counters
            document.querySelectorAll('.char-count').forEach(counter => {
                const input = document.getElementById(counter.getAttribute('data-for'));
                if (input) {
                    const updateCount = () => {
                        const remaining = input.getAttribute('maxlength') - input.value.length;
                        counter.textContent = `${remaining} characters remaining`;
                        counter.classList.toggle('text-danger', remaining === 0);
                        counter.classList.toggle('text-muted', remaining > 0);
                    };
                    updateCount();
                    input.addEventListener('input', updateCount);
                }
            });

            // C. Email Chips
            const container = document.getElementById('emailContainer');
            const input = document.getElementById('emailInput');
            const hiddenInput = document.getElementById('hidden_emails');
            const suggestionList = document.getElementById('suggestionList');
            const addAllBtn = document.getElementById('addAllUsersBtn');
            
            let emails = hiddenInput.value ? hiddenInput.value.split(',').map(e => e.trim()).filter(e => e) : [];
            renderChips();

            function updateHiddenInput() {
                hiddenInput.value = emails.join(',');
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function renderChips() {
                container.querySelectorAll('.email-chip').forEach(c => c.remove());
                emails.forEach((email, index) => {
                    const chip = document.createElement('div');
                    chip.className = 'email-chip';
                    chip.innerHTML = `<span>${email}</span><button type="button" class="btn-close btn-close-white ms-2"></button>`;
                    chip.querySelector('.btn-close').addEventListener('click', (e) => { e.stopPropagation(); emails.splice(index, 1); renderChips(); updateHiddenInput(); });
                    container.insertBefore(chip, input);
                });
            }

            function addEmail(email) {
                email = email.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (email && emailRegex.test(email) && !emails.includes(email)) {
                    emails.push(email);
                    renderChips();
                    updateHiddenInput();
                }
            }

            // Email Input Events
            container.addEventListener('click', () => input.focus());
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); addEmail(input.value); input.value = ''; suggestionList.style.display = 'none'; }
                else if (e.key === 'Backspace' && input.value === '' && emails.length > 0) { emails.pop(); renderChips(); updateHiddenInput(); }
            });
            input.addEventListener('input', () => {
                const query = input.value.trim();
                if (query.length < 1) { suggestionList.style.display = 'none'; return; }
                fetch(`search_users.php?q=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        suggestionList.innerHTML = '';
                        if(data.length === 0) { suggestionList.style.display = 'none'; return; }
                        data.forEach(u => {
                            if(emails.includes(u.primary_email)) return;
                            const div = document.createElement('div');
                            div.className = 'autocomplete-item';
                            div.innerHTML = `<span class="fw-bold">${u.primary_email}</span> <small class="text-muted">(${u.full_name})</small>`;
                            div.onclick = () => { addEmail(u.primary_email); input.value = ''; suggestionList.style.display = 'none'; };
                            suggestionList.appendChild(div);
                        });
                        suggestionList.style.display = 'block';
                    });
            });
            document.addEventListener('click', (e) => { if(!container.contains(e.target)) suggestionList.style.display = 'none'; });

            // Add All Users
            if (addAllBtn) {
                addAllBtn.addEventListener('click', function() {
                    if (!confirm('Add ALL active users?')) return;
                    this.textContent = 'Processing...';
                    fetch('search_users.php?all=1').then(r => r.json()).then(data => {
                        emails = data.map(u => u.primary_email);
                        renderChips(); updateHiddenInput();
                        this.textContent = 'Add All Active Users';
                    });
                });
            }
        });

        // --- 2. Unsaved Changes Protection ---
        document.addEventListener('DOMContentLoaded', function() {
            let isDirty = false;
            const form = document.getElementById('surveyForm'); 
            if (form) {
                form.addEventListener('change', () => isDirty = true);
                form.addEventListener('input', () => isDirty = true);
                form.addEventListener('submit', () => { isDirty = false; });
            }
            window.addEventListener('beforeunload', (e) => { if (isDirty) { e.preventDefault(); e.returnValue = ''; } });
        });
    </script>
</body>
</html>