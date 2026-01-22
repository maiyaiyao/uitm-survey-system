<?php
// Path: modules/admin/user/form-user.php
require_once '../../../config/config.php';
require_once '../../../includes/models/User.php';

// CRITICAL ISO CONTROL: Only admins should access user provisioning.
requireRole(['admin']);

$db = new Database();
$userModel = new User();
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
    'org_ID' => '',             // Primary Organization ID
    'dept_ID' => '',            // Primary Department ID
    'user_position' => '',
    'role_ID' => '',
    'status' => 'Active'
];

$secondary_dept_ids = [];

// --- FETCH DROPDOWN DATA ---
try {
    $roles = $db->fetchAll("SELECT role_ID, role_name FROM role");
    $organizations = $db->fetchAll("SELECT * FROM organization WHERE status = 'Active' ORDER BY org_name ASC");
    // Fetch departments linked to organizations for the JS filter
    $all_departments = $db->fetchAll("SELECT * FROM department WHERE status = 'Active' ORDER BY dept_name ASC");
} catch (Exception $e) {
    $roles = []; $organizations = []; $all_departments = [];
}

// If Edit Mode: Fetch existing data
if ($is_edit) {
    try {
        $existing_user = $db->fetchOne("
            SELECT u.*, r.role_ID 
            FROM user u
            LEFT JOIN user_role ur ON u.user_ID = ur.user_ID
            LEFT JOIN role r ON ur.role_ID = r.role_ID
            WHERE u.user_ID = :id
        ", [':id' => $user_id]);

        if (!$existing_user) {
            setFlashMessage('danger', 'User not found.');
            redirect('index.php');
        }
        $user_data = array_merge($user_data, $existing_user);
        
        // Fetch Additional Departments (if using bridge table)
        if(method_exists($userModel, 'getSecondaryDepartments')) {
            $secondary_dept_ids = $userModel->getSecondaryDepartments($user_id);
        }

    } catch (Exception $e) {
        setFlashMessage('danger', 'Error loading user: ' . $e->getMessage());
        redirect('index.php');
    }
}

// --- 2. Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = $db->getConnection();
        
        // Sanitize Input
        $post_data = [
            'full_name' => sanitize($_POST['full_name']),
            'primary_email' => sanitize($_POST['primary_email']),
            'user_handphone_no' => sanitize($_POST['user_handphone_no']),
            'role_id' => (int)sanitize($_POST['role_id']),
            'status' => sanitize($_POST['status']),
            'org_ID' => isset($_POST['org_ID']) ? (int)$_POST['org_ID'] : 0,     // Primary Org
            'dept_ID' => isset($_POST['dept_ID']) ? (int)$_POST['dept_ID'] : 0,   // Primary Dept
            'user_position' => sanitize($_POST['user_position'] ?? ''),
            'user_phone_company' => sanitize($_POST['user_phone_company'] ?? ''),
        ];

        // Validation
        if (empty($post_data['full_name']) || empty($post_data['primary_email']) || empty($post_data['role_id'])) {
            throw new Exception('Full Name, Email, and Role are required.');
        }

        // Email Uniqueness
        $email_owner = $userModel->findByEmail($post_data['primary_email']);
        if ($email_owner) {
            if (!$is_edit) throw new Exception('Email already registered.');
            elseif ($email_owner['user_ID'] != $user_id) throw new Exception('Email is already taken.');
        }

        // Password Logic
        $password_hash = null;
        if (!empty($_POST['password'])) {
            if ($_POST['password'] !== $_POST['confirm_password']) throw new Exception('Passwords do not match');
            if (!isStrongPassword($_POST['password'])) throw new Exception('Password must be strong.');
            $password_hash = $userModel->hashPassword($_POST['password']);
        } elseif (!$is_edit) {
            throw new Exception('Password is required for new users.');
        }

        $conn->beginTransaction();

        $fields = [
            'full_name' => $post_data['full_name'],
            'primary_email' => $post_data['primary_email'],
            'status' => $post_data['status'],
            'org_ID' => $post_data['org_ID'],     // Save Primary Org ID
            'dept_ID' => $post_data['dept_ID'],   // Save Primary Dept ID
            'user_position' => $post_data['user_position'],
            'user_phone_company' => $post_data['user_phone_company'],
            'user_handphone_no' => $post_data['user_handphone_no'],
        ];

        if ($is_edit) {
            if ($password_hash) $fields['password'] = $password_hash;
            $userModel->update($user_id, $fields);
            $userModel->updateRole($user_id, $post_data['role_id']);
            
            // Save Additional Departments
            if(method_exists($userModel, 'saveSecondaryDepartments')) {
                $extra_depts = $_POST['secondary_dept_ids'] ?? [];
                $userModel->saveSecondaryDepartments($user_id, $extra_depts);
            }

            $msg = "User updated successfully.";
        } else {
            $fields['password'] = $password_hash;
            $fields['email_verified'] = 'Verified';
            $user_id = $userModel->create($fields);
            $userModel->assignRole($user_id, $post_data['role_id']);
            $msg = "User created successfully.";
        }

        $conn->commit();
        setFlashMessage('success', $msg);
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
        setFlashMessage('error', $e->getMessage());
        $user_data = array_merge($user_data, $_POST);
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $is_edit ? 'Edit User' : 'Create User'; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .main-content-wrapper { margin-left: 16.66667%; width: 83.33333%; }
        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content-wrapper { margin-left: 0; width: 100%; }
        }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11); }
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

                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h3 class="fw-bold mb-1"><?php echo $is_edit ? 'Edit User Profile' : 'Provision New User'; ?></h3>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary rounded-3 px-3">Back</a>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo ($flash['type'] == 'error') ? 'danger' : $flash['type']; ?> fade show border-0 shadow-sm mb-4">
                            <?php echo $flash['message']; ?>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm rounded-4 mb-5">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0">User Account Details</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" id="userForm">
                                <div class="row g-4">
                                    
                                    <div class="col-md-6">
                                        <h6 class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 mb-3">Personal Information</h6>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="full_name" required 
                                                value="<?php echo htmlspecialchars($user_data['full_name']); ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="primary_email" required 
                                                value="<?php echo htmlspecialchars($user_data['primary_email']); ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Organization (Parent) <span class="text-danger">*</span></label>
                                            <select class="form-select" name="org_ID" id="orgSelect" required>
                                                <option value="">-- Select Organization --</option>
                                                <?php foreach ($organizations as $org): ?>
                                                    <option value="<?php echo $org['org_ID']; ?>"
                                                        <?php echo ($user_data['org_ID'] == $org['org_ID']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($org['org_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Primary Department <span class="text-danger">*</span></label>
                                            <select class="form-select" name="dept_ID" id="deptSelect" required disabled>
                                                <option value="">-- Select Organization First --</option>
                                            </select>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Handphone No. <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="user_handphone_no" required
                                                    value="<?php echo htmlspecialchars($user_data['user_handphone_no'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Position</label>
                                                <input type="text" class="form-control" name="user_position" 
                                                    value="<?php echo htmlspecialchars($user_data['user_position'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h6 class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 mb-3">Security & Access Control</h6>

                                        <div class="mb-3">
                                            <label class="form-label">Role Assignment <span class="text-danger">*</span></label>
                                            <select class="form-select" name="role_id" required>
                                                <option value="">-- Select System Role --</option>
                                                <?php foreach ($roles as $role): ?>
                                                    <option value="<?php echo htmlspecialchars($role['role_ID']); ?>"
                                                        <?php echo ($user_data['role_ID'] == $role['role_ID']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars(ucfirst($role['role_name'])); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label">Account Status</label>
                                            <select class="form-select" name="status">
                                                <option value="Active" <?php echo ($user_data['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="Inactive" <?php echo ($user_data['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>

                                        <div class="p-3 bg-light rounded-3 border">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    <?php echo $is_edit ? 'New Password (Leave blank to keep current)' : 'Password <span class="text-danger">*</span>'; ?>
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

                                            <div class="mb-0">
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

                                </div> 
                                
                                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <a href="index.php" class="btn btn-outline-secondary px-4 rounded-3">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4 rounded-3">
                                        <i class="bi bi-save me-2"></i><?php echo $is_edit ? 'Save Changes' : 'Create User'; ?>
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
        const savedPrimaryDept = "<?php echo $user_data['dept_ID']; ?>";
        const savedSecondaryDepts = <?php echo json_encode($secondary_dept_ids); ?>;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. DROPDOWN LOGIC
            const orgSelect = document.getElementById('orgSelect');
            const deptSelect = document.getElementById('deptSelect');

            function updateDepartments() {
                const orgId = orgSelect.value;
                
                // Clear dropdown
                deptSelect.innerHTML = '<option value="">-- Select Department --</option>';

                if (!orgId) {
                    deptSelect.disabled = true;
                    return;
                }
                
                deptSelect.disabled = false;

                // Filter departments for this Organization
                const filtered = allDepts.filter(d => d.org_ID == orgId);

                filtered.forEach(d => {
                    const opt = new Option(d.dept_name + (d.dept_code ? ` (${d.dept_code})` : ''), d.dept_ID);
                    if (d.dept_ID == savedPrimaryDept) opt.selected = true;
                    deptSelect.add(opt);
                });
            }

            // Initialize on page load
            updateDepartments();

            // Listen for org_ID changes
            orgSelect.addEventListener('change', updateDepartments);

            // 2. PASSWORD TOGGLE LOGIC (Main)
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

            // 3. CONFIRM PASSWORD TOGGLE LOGIC (New)
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
        });
    </script>
</body>
</html>