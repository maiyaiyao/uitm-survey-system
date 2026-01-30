<?php
// Path: src/modules/admin/user/form-user.php
require_once '../../../config/config.php';
require_once '../../../includes/models/User.php';

requireRole(['admin']);

$db = new Database();
$current_user = getCurrentUser();

// --- 1. Determine Mode (Create vs Edit) ---
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$is_edit = ($user_id > 0);

// Initialize default values
$user_data = [
    'full_name' => '',
    'primary_email' => '',
    'user_handphone_no' => '',
    'user_phone_company' => '',
    'org_ID' => '',            
    'user_position' => '',
    'role_ID' => '',
    'status' => 'Active'
];

$linked_dept_ids = []; 

// --- FETCH DROPDOWN DATA ---
try {
    $roles = $db->fetchAll("SELECT role_ID, role_name FROM role");
    $organizations = $db->fetchAll("SELECT * FROM organization WHERE status = 'Active' ORDER BY org_name ASC");
    $all_departments = $db->fetchAll("SELECT * FROM department WHERE status = 'Active' ORDER BY dept_name ASC");

    if ($is_edit) {
        $user_data = $db->fetchOne("SELECT u.*, ur.role_ID FROM user u LEFT JOIN user_role ur ON u.user_ID = ur.user_ID WHERE u.user_ID = :id", [':id' => $user_id]);
        
        if (!$user_data) {
            setFlashMessage('danger', 'User not found.');
            redirect('index.php');
        }

        $linked_depts = $db->fetchAll("SELECT dept_ID FROM user_department WHERE user_ID = :uid ORDER BY is_primary DESC", [':uid' => $user_id]);
        $linked_dept_ids = array_column($linked_depts, 'dept_ID');
    }

} catch (Exception $e) {
    setFlashMessage('danger', 'Database Error: ' . $e->getMessage());
    redirect('index.php');
}

// --- 2. Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = $db->getConnection();
        
        $post_data = [
            'full_name' => sanitize($_POST['full_name']),
            'primary_email' => sanitize($_POST['primary_email']),
            'user_handphone_no' => sanitize($_POST['user_handphone_no']),
            'user_phone_company' => sanitize($_POST['user_phone_company']),
            'org_ID' => !empty($_POST['org_ID']) ? (int)$_POST['org_ID'] : null,
            'user_position' => sanitize($_POST['user_position']),
            'role_ID' => !empty($_POST['role_ID']) ? (int)$_POST['role_ID'] : null,
            'status' => $_POST['status'] ?? 'Active'
        ];

        $dept_ids = $_POST['dept_ID'] ?? [];

        // Validation
        if (empty($post_data['full_name']) || empty($post_data['primary_email'])) throw new Exception("Name and Email are required.");
        if (empty($post_data['org_ID'])) throw new Exception("Organization is required.");
        if (empty($dept_ids) || empty($dept_ids[0])) throw new Exception("At least one Department is required.");
        
        if (!$is_edit && empty($_POST['password'])) throw new Exception("Password is required for new users.");
        
        $password_hash = null;
        if (!empty($_POST['password'])) {
            if ($_POST['password'] !== $_POST['confirm_password']) throw new Exception("Passwords do not match.");
            // Assuming isStrongPassword exists in config.php
            if (function_exists('isStrongPassword') && !isStrongPassword($_POST['password'])) throw new Exception('Password must be strong (Min 8 chars, mixed case & symbols).');
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        // START TRANSACTION
        $conn->beginTransaction();

        // A. Insert / Update User
        if ($is_edit) {
            // FIX: Manual UPDATE query to ensure 'department' column is excluded
            $sql = "UPDATE user SET 
                        full_name = :name,
                        primary_email = :email,
                        user_handphone_no = :hp,
                        user_phone_company = :office,
                        org_ID = :org,
                        user_position = :pos,
                        status = :status,
                        updated_at = NOW()
                    WHERE user_ID = :uid";
            
            $params = [
                ':name' => $post_data['full_name'],
                ':email' => $post_data['primary_email'],
                ':hp' => $post_data['user_handphone_no'],
                ':office' => $post_data['user_phone_company'],
                ':org' => $post_data['org_ID'],
                ':pos' => $post_data['user_position'],
                ':status' => $post_data['status'],
                ':uid' => $user_id
            ];

            $db->query($sql, $params);

            // Update password if provided
            if ($password_hash) {
                $db->query("UPDATE user SET password = :pwd WHERE user_ID = :uid", [':pwd' => $password_hash, ':uid' => $user_id]);
            }
            
            $target_uid = $user_id;
            $msg = "User updated successfully.";

        } else {
            // FIX: Manual INSERT query to ensure 'department' column is excluded
            $sql = "INSERT INTO user 
                    (full_name, primary_email, user_handphone_no, user_phone_company, org_ID, user_position, status, password, email_verified, created_at)
                    VALUES 
                    (:name, :email, :hp, :office, :org, :pos, :status, :pwd, 1, NOW())";
            
            $db->query($sql, [
                ':name' => $post_data['full_name'],
                ':email' => $post_data['primary_email'],
                ':hp' => $post_data['user_handphone_no'],
                ':office' => $post_data['user_phone_company'],
                ':org' => $post_data['org_ID'],
                ':pos' => $post_data['user_position'],
                ':status' => $post_data['status'],
                ':pwd' => $password_hash
            ]);
            
            $target_uid = $conn->lastInsertId();
            if (!$target_uid) throw new Exception("Failed to create user record.");
            
            $msg = "User created successfully.";
        }

        // B. Handle Roles (Using user_role table)
        // Remove existing role
        $db->query("DELETE FROM user_role WHERE user_ID = :uid", [':uid' => $target_uid]);
        
        // Assign new role
        $role_to_assign = $post_data['role_ID'] ?: 2; // Default to 2 if empty
        $db->query("INSERT INTO user_role (user_ID, role_ID, assigned_at, assigned_by) VALUES (:uid, :rid, NOW(), :by)", [
            ':uid' => $target_uid,
            ':rid' => $role_to_assign,
            ':by' => $current_user['user_ID']
        ]);

        // C. Handle Departments (Using user_department table)
        $db->query("DELETE FROM user_department WHERE user_ID = :uid", [':uid' => $target_uid]);

        $stmtDept = $conn->prepare("INSERT INTO user_department (user_ID, dept_ID, is_primary) VALUES (:uid, :did, :prim)");
        $unique_depts = array_unique($dept_ids);
        
        foreach ($unique_depts as $index => $did) {
            if(empty($did)) continue;
            $is_primary = ($index === 0) ? 1 : 0; 
            $stmtDept->execute([':uid' => $target_uid, ':did' => $did, ':prim' => $is_primary]);
        }

        $conn->commit();
        setFlashMessage('success', $msg);
        redirect('index.php');

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        setFlashMessage('danger', 'Error: ' . $e->getMessage());
        $user_data = array_merge($user_data, $_POST);
        $linked_dept_ids = $_POST['dept_ID'] ?? []; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit User' : 'Create User'; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Page Layout */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        
        /* Sidebar Adjustment for Fixed Layout */
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }

        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08); }
        .btn-add-dept { color: #667eea; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; margin-top: 5px; text-decoration: none; }
        .btn-add-dept:hover { color: #764ba2; text-decoration: underline; }
        .password-strength { height: 4px; border-radius: 2px; margin-top: 6px; transition: all 0.3s; }
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
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">User Management</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page"><?php echo $is_edit ? 'Edit User' : 'Create User'; ?></li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h3 class="fw-bold mb-1"><?php echo $is_edit ? 'Edit User' : 'New User Provisioning'; ?></h3>
                            <p class="text-muted mb-0">Manage user identity, access roles, and organizational assignment.</p>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary rounded-3 px-3">
                            <i class="bi bi-arrow-left me-2"></i>Back
                        </a>
                    </div>

                    <?php if ($msg = getFlashMessage()): ?>
                        <div class="alert alert-<?php echo $msg['type']; ?> alert-dismissible fade show border-0 shadow-sm mb-4">
                            <?php echo $msg['message']; ?> 
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm rounded-4 mb-5">
                        <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                            <h5 class="mb-0">User Details Form</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" id="userForm">
                                <h5 class="text-primary mb-3 fw-bold small text-uppercase">Identity & Access</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($user_data['full_name']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="primary_email" class="form-control" required value="<?php echo htmlspecialchars($user_data['primary_email']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">System Role <span class="text-danger">*</span></label>
                                        <select name="role_ID" class="form-select" required>
                                            <option value="">-- Select Role --</option>
                                            <?php foreach ($roles as $r): ?>
                                                <option value="<?php echo $r['role_ID']; ?>" <?php echo ($user_data['role_ID'] == $r['role_ID']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars(ucfirst($r['role_name'])); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Account Status</label>
                                        <select name="status" class="form-select">
                                            <option value="Active" <?php echo ($user_data['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="Inactive" <?php echo ($user_data['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <hr class="my-4 text-secondary opacity-25">

                                <h5 class="text-primary mb-3 fw-bold small text-uppercase">Organization Structure</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Organization <span class="text-danger">*</span></label>
                                        <select name="org_ID" id="orgSelect" class="form-select" required>
                                            <option value="">-- Select Organization --</option>
                                            <?php foreach ($organizations as $org): ?>
                                                <option value="<?php echo $org['org_ID']; ?>" <?php echo ($user_data['org_ID'] == $org['org_ID']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($org['org_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Department(s) <span class="text-danger">*</span></label>
                                        
                                        <div id="departmentContainer"></div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="btn-add-dept" id="addDeptBtn">
                                                <i class="bi bi-plus-circle-fill me-1"></i> Add another department
                                            </div>
                                        </div>
                                        <div class="form-text small">First selected is Primary.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Position / Job Title</label>
                                        <input type="text" name="user_position" class="form-control" value="<?php echo htmlspecialchars($user_data['user_position']); ?>">
                                    </div>
                                </div>

                                <hr class="my-4 text-secondary opacity-25">

                                <h5 class="text-primary mb-3 fw-bold small text-uppercase">Contact Information</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Mobile Number</label>
                                        <input type="text" name="user_handphone_no" class="form-control" value="<?php echo htmlspecialchars($user_data['user_handphone_no']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Office Phone</label>
                                        <input type="text" name="user_phone_company" class="form-control" value="<?php echo htmlspecialchars($user_data['user_phone_company']); ?>">
                                    </div>
                                </div>

                                <hr class="my-4 text-secondary opacity-25">

                                <h5 class="text-primary mb-3 fw-bold small text-uppercase">Security Credential</h5>
                                <div class="p-4 bg-light rounded-3 border">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <?php echo $is_edit ? 'New Password (Leave blank to keep)' : 'Password <span class="text-danger">*</span>'; ?>
                                            </label>
                                            <div class="input-group">
                                                <input type="password" class="form-control border-end-0" name="password" id="password" 
                                                    <?php echo $is_edit ? '' : 'required'; ?> placeholder="Min 8 chars, mixed case & symbols">
                                                <button class="btn btn-outline-secondary bg-white border-start-0" type="button" id="togglePassword">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div class="password-strength" id="passwordStrength"></div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">
                                                <?php echo $is_edit ? 'Confirm New Password' : 'Confirm Password <span class="text-danger">*</span>'; ?>
                                            </label>
                                            <div class="input-group">
                                                <input type="password" class="form-control border-end-0" name="confirm_password" id="confirm_password" 
                                                    <?php echo $is_edit ? '' : 'required'; ?> placeholder="Re-type password">
                                                <button class="btn btn-outline-secondary bg-white border-start-0" type="button" id="toggleConfirmPassword">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div id="passwordMatch" class="invalid-feedback">Passwords do not match.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 d-flex justify-content-end gap-2">
                                    <a href="index.php" class="btn btn-outline-secondary px-4 rounded-3">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4 rounded-3"
                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                        <i class="bi bi-save me-2"></i> Save User
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
        document.addEventListener('DOMContentLoaded', function() {
            const allDepartments = <?php echo json_encode($all_departments); ?>;
            const savedDeptIds = <?php echo json_encode($linked_dept_ids); ?>; 
            
            const orgSelect = document.getElementById('orgSelect');
            const deptContainer = document.getElementById('departmentContainer');
            const addDeptBtn = document.getElementById('addDeptBtn');

            function getDeptOptions(orgId, selectedId = null) {
                if (!orgId) return '<option value="">-- Select Organization First --</option>';
                const filtered = allDepartments.filter(d => d.org_ID == orgId);
                if (filtered.length === 0) return '<option value="" disabled>No Departments Found</option>';
                let html = '<option value="">-- Select Department --</option>';
                filtered.forEach(d => {
                    const isSelected = (selectedId && d.dept_ID == selectedId) ? 'selected' : '';
                    html += `<option value="${d.dept_ID}" ${isSelected}>${d.dept_name}</option>`;
                });
                return html;
            }

            function addDeptRow(selectedId = null) {
                const orgId = orgSelect.value;
                const newRow = document.createElement('div');
                newRow.className = 'input-group mb-2 department-row';
                const isDisabled = !orgId ? 'disabled' : '';
                const optionsHtml = getDeptOptions(orgId, selectedId);

                newRow.innerHTML = `
                    <select class="form-select dept-select" name="dept_ID[]" required ${isDisabled}>
                        ${optionsHtml}
                    </select>
                    <button class="btn btn-outline-danger" type="button" onclick="removeDeptRow(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
                deptContainer.appendChild(newRow);
            }

            window.removeDeptRow = function(btn) {
                if (deptContainer.querySelectorAll('.department-row').length > 1) {
                    btn.parentElement.remove();
                } else {
                    const select = btn.parentElement.querySelector('select');
                    select.value = "";
                }
            };

            function updateAllDropdowns() {
                const orgId = orgSelect.value;
                const selects = deptContainer.querySelectorAll('.dept-select');
                if (selects.length === 0) { addDeptRow(); return; }
                selects.forEach(select => {
                    const currentVal = select.value;
                    select.innerHTML = getDeptOptions(orgId, currentVal);
                    select.disabled = !orgId;
                    if (!select.querySelector(`option[value="${currentVal}"]`)) select.value = "";
                });
            }

            orgSelect.addEventListener('change', () => { updateAllDropdowns(); });

            addDeptBtn.addEventListener('click', () => {
                if (!orgSelect.value) { alert("Please select an Organization first."); return; }
                addDeptRow();
            });

            if (savedDeptIds.length > 0) {
                savedDeptIds.forEach(did => { addDeptRow(did); });
            } else {
                addDeptRow();
            }
            
            if (orgSelect.value && deptContainer.children.length === 0) { addDeptRow(); }

            // --- PASSWORD LOGIC START ---
            
            // 2. PASSWORD TOGGLE LOGIC
            const togglePassBtn = document.getElementById('togglePassword');
            if(togglePassBtn) {
                togglePassBtn.addEventListener('click', function() {
                    const passwordInput = document.getElementById('password');
                    const icon = this.querySelector('i');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.replace('bi-eye', 'bi-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.replace('bi-eye-slash', 'bi-eye');
                    }
                });
            }

            // 3. CONFIRM PASSWORD TOGGLE LOGIC
            const toggleConfirmBtn = document.getElementById('toggleConfirmPassword');
            if(toggleConfirmBtn) {
                toggleConfirmBtn.addEventListener('click', function() {
                    const confirmInput = document.getElementById('confirm_password');
                    const icon = this.querySelector('i');
                    
                    if (confirmInput.type === 'password') {
                        confirmInput.type = 'text';
                        icon.classList.replace('bi-eye', 'bi-eye-slash');
                    } else {
                        confirmInput.type = 'password';
                        icon.classList.replace('bi-eye-slash', 'bi-eye');
                    }
                });
            }

            // 4. PASSWORD STRENGTH
            const passwordInput = document.getElementById('password');
            if(passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    const strengthBar = document.getElementById('passwordStrength');
                    let strength = 0;
                    
                    if (password.length >= 8) strength++;
                    if (/[a-z]/.test(password)) strength++;
                    if (/[A-Z]/.test(password)) strength++;
                    if (/[0-9]/.test(password)) strength++;
                    if (/[^a-zA-Z0-9]/.test(password)) strength++;
                    
                    const colors = ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#28a745'];
                    strengthBar.style.width = (strength * 20) + '%';
                    strengthBar.style.backgroundColor = colors[strength - 1] || '#e9ecef';
                });
            }

            // 5. PASSWORD MATCH
            const confirmInput = document.getElementById('confirm_password');
            if(confirmInput) {
                confirmInput.addEventListener('input', function() {
                    const password = document.getElementById('password').value;
                    const confirm = this.value;
                    
                    if (confirm && password !== confirm) {
                        this.classList.add('is-invalid');
                        document.getElementById('passwordMatch').style.display = 'block';
                    } else {
                        this.classList.remove('is-invalid');
                        document.getElementById('passwordMatch').style.display = 'none';
                    }
                });
            }
            // --- PASSWORD LOGIC END ---
        });
    </script>
</body>
</html>