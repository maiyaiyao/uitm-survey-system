<?php
require_once '../../config/config.php';
require_once '../../includes/models/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getPrimaryRole();
    redirect(BASE_URL . "/modules/$role/dashboard.php");
}

$error = '';
$success = '';
$db = new Database();

// --- FETCH DROPDOWN DATA ---
try {
    // Fetch Active Organizations
    $organizations = $db->fetchAll("SELECT * FROM organization WHERE status = 'Active' ORDER BY org_name ASC");
    // Fetch Active Departments
    $all_departments = $db->fetchAll("SELECT * FROM department WHERE status = 'Active' ORDER BY dept_name ASC");
} catch (Exception $e) {
    $organizations = [];
    $all_departments = [];
}

// --- FORM SUBMISSION LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $userModel = new User($db);
        $conn = $db->getConnection();
        
        // Sanitize Inputs
        $full_name = sanitize($_POST['full_name']);
        $primary_email = sanitize($_POST['primary_email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $user_handphone_no = sanitize($_POST['user_handphone_no'] ?? '');
        $user_phone_company = sanitize($_POST['user_phone_company'] ?? '');
        $user_position = sanitize($_POST['user_position'] ?? '');
        
        // Capture IDs
        $org_ID = !empty($_POST['org_ID']) ? (int)$_POST['org_ID'] : null;
        $dept_ids = $_POST['dept_ID'] ?? []; // Array of IDs
        
        // Validation
        if (empty($full_name) || empty($primary_email) || empty($password) || empty($user_handphone_no)) {
            throw new Exception('Full Name, Email, Password, and Phone Number are required.');
        }
        if (empty($org_ID)) {
            throw new Exception('Please select your Organization.');
        }
        if (empty($dept_ids) || empty($dept_ids[0])) {
            throw new Exception('Please select at least one Department.');
        }
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }
        if ($userModel->emailExists($primary_email)) {
            throw new Exception('Email already registered');
        }
        
        // --- Transaction Start ---
        $conn->beginTransaction();
        
        try {
            // 1. Create user
            // FIX: Removed 'dept_ID' from this array to solve the SQL Error
            $userData = [
                'primary_email' => $primary_email,
                'password' => $userModel->hashPassword($password),
                'full_name' => $full_name,
                'status' => 'Active', 
                'email_verified' => 0, 
                'org_ID' => $org_ID,
                'user_position' => $user_position,
                'user_phone_company' => $user_phone_company,
                'user_handphone_no' => $user_handphone_no
            ];

            $user_ID = $userModel->create($userData);

            // Safety Check
            if (empty($user_ID)) {
                // Try to find the user if create didn't return ID (fallback)
                $checkUser = $userModel->findByEmail($primary_email);
                if ($checkUser && !empty($checkUser['user_ID'])) {
                    $user_ID = $checkUser['user_ID'];
                } else {
                    throw new Exception("User creation failed.");
                }
            }
            
            // 2. Assign Departments (Many-to-Many)
            $stmtDept = $conn->prepare("INSERT INTO user_department (user_ID, dept_ID, is_primary) VALUES (:uid, :did, :is_prim)");
            
            $unique_depts = array_unique($dept_ids);
            
            foreach ($unique_depts as $index => $did) {
                if(empty($did)) continue;
                
                // First selected is primary
                $is_primary = ($index === 0) ? 1 : 0;
                
                $stmtDept->execute([
                    ':uid' => $user_ID,
                    ':did' => $did,
                    ':is_prim' => $is_primary
                ]);
            }

            // 3. Assign Role
            $userModel->assignRole($user_ID, 2, 'System'); // 2 = User
            
            $conn->commit();
            
            setFlashMessage('success', 'Registration successful! Please login.');
            redirect(BASE_URL . '/modules/auth/login.php');
            
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 2rem 0; }
        .register-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden; margin-bottom: 2rem; }
        .register-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; text-align: center; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px; }
        .btn-google { background: #fff; border: 1px solid #ddd; color: #444; padding: 12px; }
        .password-strength { height: 5px; border-radius: 3px; margin-top: 5px; transition: all 0.3s; }
        .divider { display: flex; align-items: center; text-align: center; margin: 1.5rem 0; }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #ddd; }
        .divider span { padding: 0 1rem; color: #666; }
        .btn-add-dept { color: #667eea; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; margin-top: 5px; }
        .btn-add-dept:hover { color: #764ba2; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="register-card">
                    <div class="register-header">
                        <i class="bi bi-person-plus" style="font-size: 3rem;"></i>
                        <h3 class="mt-3 mb-0">Create Account</h3>
                        <p class="mb-0 mt-2">Join UiTM Survey System</p>
                    </div>
                    
                    <div class="p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="registerForm">
                            <div class="mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="full_name" placeholder="Enter your full name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="primary_email" placeholder="your.email@example.com" required value="<?php echo htmlspecialchars($_POST['primary_email'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Handphone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="user_handphone_no" placeholder="e.g., +60123456789" required value="<?php echo htmlspecialchars($_POST['user_handphone_no'] ?? ''); ?>">
                            </div>

                            <hr class="my-4">
                            <p class="text-muted small">Organization Details</p>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Organization <span class="text-danger">*</span></label>
                                    <select class="form-select" name="org_ID" id="orgSelect" required>
                                        <option value="">-- Select Organization --</option>
                                        <?php foreach ($organizations as $org): ?>
                                            <option value="<?php echo $org['org_ID']; ?>" <?php echo (isset($_POST['org_ID']) && $_POST['org_ID'] == $org['org_ID']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($org['org_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department/Faculty <span class="text-danger">*</span></label>
                                    <div id="departmentContainer">
                                        <div class="input-group mb-2 department-row">
                                            <select class="form-select dept-select" name="dept_ID[]" required disabled>
                                                <option value="">-- Select Organization First --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="btn-add-dept" id="addDeptBtn">
                                        <i class="bi bi-plus-circle-fill me-1"></i> Add another department
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Position/Title</label>
                                    <input type="text" class="form-control" name="user_position" placeholder="e.g., Manager" value="<?php echo htmlspecialchars($_POST['user_position'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Company Phone No.</label>
                                <input type="text" class="form-control" name="user_phone_company" placeholder="e.g., +60312345678" value="<?php echo htmlspecialchars($_POST['user_phone_company'] ?? ''); ?>">
                            </div>

                            <hr class="my-4">

                            <div class="mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="bi bi-eye"></i></button>
                                </div>
                                <div class="password-strength" id="passwordStrength"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                                <small class="text-danger" id="passwordMatch" style="display: none;">Passwords do not match</small>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">Create Account</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-bold">Sign In</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- 1. DYNAMIC DEPARTMENT LOGIC ---
            const allDepartments = <?php echo json_encode($all_departments); ?>;
            const orgSelect = document.getElementById('orgSelect');
            const deptContainer = document.getElementById('departmentContainer');
            const addDeptBtn = document.getElementById('addDeptBtn');

            // FIX: Added 'selectedId' parameter with default value
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

            function updateAllDeptDropdowns() {
                const orgId = orgSelect.value;
                const selects = deptContainer.querySelectorAll('.dept-select');
                selects.forEach(select => {
                    const currentValue = select.value;
                    select.innerHTML = getDeptOptions(orgId, currentValue);
                    select.disabled = !orgId;
                });
            }

            if (orgSelect) {
                orgSelect.addEventListener('change', updateAllDeptDropdowns);
            }

            if (addDeptBtn) {
                addDeptBtn.addEventListener('click', function() {
                    const orgId = orgSelect.value;
                    if (!orgId) { alert("Please select an Organization first."); return; }

                    const newRow = document.createElement('div');
                    newRow.className = 'input-group mb-2 department-row';
                    newRow.innerHTML = `
                        <select class="form-select dept-select" name="dept_ID[]" required>
                            ${getDeptOptions(orgId)}
                        </select>
                        <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()">
                            <i class="bi bi-trash"></i>
                        </button>
                    `;
                    deptContainer.appendChild(newRow);
                });
            }

            if (orgSelect && orgSelect.value) {
                updateAllDeptDropdowns();
            }

            // --- 2. PASSWORD UTILITIES ---
            const toggleBtn = document.getElementById('togglePassword');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const password = document.getElementById('password');
                    const icon = this.querySelector('i');
                    if (password.type === 'password') {
                        password.type = 'text';
                        icon.classList.replace('bi-eye', 'bi-eye-slash');
                    } else {
                        password.type = 'password';
                        icon.classList.replace('bi-eye-slash', 'bi-eye');
                    }
                });
            }

            const passInput = document.getElementById('password');
            if (passInput) {
                passInput.addEventListener('input', function() {
                    const password = this.value;
                    const strengthBar = document.getElementById('passwordStrength');
                    let strength = 0;
                    if (password.length >= 8) strength++;
                    if (/[a-z]/.test(password)) strength++;
                    if (/[A-Z]/.test(password)) strength++;
                    if (/[0-9]/.test(password)) strength++;
                    if (/[^a-zA-Z0-9]/.test(password)) strength++;
                    
                    const widths = ['20%', '40%', '60%', '80%', '100%'];
                    const colors = ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#28a745'];
                    
                    if(strengthBar) {
                        strengthBar.style.width = widths[strength - 1] || '0%';
                        strengthBar.style.backgroundColor = colors[strength - 1] || '#e9ecef';
                    }
                });
            }

            const confirmInput = document.getElementById('confirm_password');
            if (confirmInput) {
                confirmInput.addEventListener('input', function() {
                    const password = document.getElementById('password').value;
                    const matchMessage = document.getElementById('passwordMatch');
                    if (this.value && password !== this.value) {
                        matchMessage.style.display = 'block';
                        this.classList.add('is-invalid');
                    } else {
                        matchMessage.style.display = 'none';
                        this.classList.remove('is-invalid');
                    }
                });
            }
        });
    </script>
</body>
</html>