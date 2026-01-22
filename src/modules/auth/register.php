<?php
require_once '../../config/config.php';
require_once '../../includes/models/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getPrimaryRole();
    redirect(BASE_URL . "/modules/$role/dashboard.php");
}

$error = '';
$db = new Database();

// --- FETCH DROPDOWN DATA (For the form) ---
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $userModel = new User();
        $conn = $db->getConnection();
        
        // Get form data and sanitize
        $full_name = sanitize($_POST['full_name']);
        $primary_email = sanitize($_POST['primary_email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $user_handphone_no = sanitize($_POST['user_handphone_no'] ?? '');
        $user_phone_company = sanitize($_POST['user_phone_company'] ?? '');
        $user_position = sanitize($_POST['user_position'] ?? '');
        
        // --- NEW: Capture IDs instead of Text ---
        $org_ID = !empty($_POST['org_ID']) ? (int)$_POST['org_ID'] : null;
        $dept_ID = !empty($_POST['dept_ID']) ? (int)$_POST['dept_ID'] : null;
        
        // --- Validation ---
        if (empty($full_name) || empty($primary_email) || empty($password) || empty($user_handphone_no)) {
            throw new Exception('Full Name, Email, Password, and Phone Number are required fields.');
        }
        
        if (empty($org_ID) || empty($dept_ID)) {
            throw new Exception('Please select your Organization and Department.');
        }
        
        if (!isValidEmail($primary_email)) {
            throw new Exception('Invalid email format');
        }
        
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }
        
        if (!isStrongPassword($password)) {
            throw new Exception('Password must be at least 8 characters with uppercase, lowercase, number and special character');
        }
        
        // Check if email already exists
        if ($userModel->emailExists($primary_email)) {
            throw new Exception('Email already registered');
        }
        
        // --- Transaction ---
        $conn->beginTransaction();
        
        try {
            // Create user
            $user_ID = $userModel->create([
                'primary_email' => $primary_email,
                'password' => $userModel->hashPassword($password),
                'full_name' => $full_name,
                'status' => 'Active', 
                'email_verified' => 0, // Not verified initially
                
                // Save the IDs
                'org_ID' => $org_ID,
                'dept_ID' => $dept_ID,
                
                // Legacy text fields (set to NULL or empty since we use IDs now)
                'department' => null,
                'user_organization' => null,
                
                'user_position' => $user_position,
                'user_phone_company' => $user_phone_company,
                'user_handphone_no' => $user_handphone_no
            ]);

            // --- SAFETY CHECK ---
            if (empty($user_ID)) {
                $checkUser = $userModel->findByEmail($primary_email);
                if ($checkUser && !empty($checkUser['user_ID'])) {
                    $user_ID = $checkUser['user_ID'];
                } else {
                    throw new Exception("User creation failed: Could not retrieve new User ID.");
                }
            }
            
            // Assign default 'user' role
            $role_id = 2; // Default User Role ID
            $userModel->assignRole($user_ID, $role_id, 'System');
            
            // Commit transaction
            $conn->commit();
            
            // Set success message
            setFlashMessage('success', 'Registration successful! Please login with your credentials.');
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
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
        }
        .btn-google {
            background: #fff;
            border: 1px solid #ddd;
            color: #444;
            padding: 12px;
        }
        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 5px;
            transition: all 0.3s;
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        .divider span {
            padding: 0 1rem;
            color: #666;
        }
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
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="registerForm">
                            <div class="mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="full_name" 
                                       placeholder="Enter your full name" required 
                                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="primary_email" id="email"
                                       placeholder="your.email@example.com" required 
                                       value="<?php echo htmlspecialchars($_POST['primary_email'] ?? ''); ?>">
                                <small class="text-muted" id="emailHelp">This will be your primary login email.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Handphone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="user_handphone_no" 
                                       placeholder="e.g., +60123456789" required
                                       value="<?php echo htmlspecialchars($_POST['user_handphone_no'] ?? ''); ?>">
                            </div>

                            <hr class="my-4">
                            <p class="text-muted small">Organization Details</p>
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Organization <span class="text-danger">*</span></label>
                                    <select class="form-select" name="org_ID" id="orgSelect" required>
                                        <option value="">-- Select Organization --</option>
                                        <?php foreach ($organizations as $org): ?>
                                            <option value="<?php echo $org['org_ID']; ?>"
                                                <?php echo (isset($_POST['org_ID']) && $_POST['org_ID'] == $org['org_ID']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($org['org_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department/Faculty <span class="text-danger">*</span></label>
                                    <select class="form-select" name="dept_ID" id="deptSelect" required disabled>
                                        <option value="">-- Select Organization First --</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Position/Title</label>
                                    <input type="text" class="form-control" name="user_position" 
                                           placeholder="e.g., Manager, Auditor" 
                                           value="<?php echo htmlspecialchars($_POST['user_position'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Company Phone No.</label>
                                <input type="text" class="form-control" name="user_phone_company" 
                                       placeholder="e.g., +60312345678"
                                       value="<?php echo htmlspecialchars($_POST['user_phone_company'] ?? ''); ?>">
                            </div>

                            <hr class="my-4">

                            <div class="mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" 
                                           id="password" placeholder="Create a strong password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength" id="passwordStrength"></div>
                                <small class="text-muted">
                                    Min. 8 characters with uppercase, lowercase, number & special character
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="confirm_password" 
                                       id="confirm_password" placeholder="Re-enter your password" required>
                                <small class="text-danger" id="passwordMatch" style="display: none;">
                                    Passwords do not match
                                </small>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-person-check me-2"></i>Create Account
                                </button>
                            </div>
                        </form>
                        
                        <div class="divider">
                            <span>OR</span>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <a href="google-login.php?action=register" class="btn btn-google">
                                <img src="https://www.google.com/favicon.ico" width="20" class="me-2">
                                Sign up with Google
                            </a>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">
                                Already have an account? 
                                <a href="login.php" class="text-decoration-none fw-bold">Sign In</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="<?php echo BASE_URL; ?>" class="text-white text-decoration-none">
                        <i class="bi bi-arrow-left me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const allDepartments = <?php echo json_encode($all_departments); ?>;
        // Keep selected dept if page reloads due to error
        const oldDeptID = "<?php echo $_POST['dept_ID'] ?? ''; ?>";
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- 1. CASCADING DROPDOWN LOGIC ---
            const orgSelect = document.getElementById('orgSelect');
            const deptSelect = document.getElementById('deptSelect');

            function updateDepartments() {
                const orgId = orgSelect.value;
                
                // Reset Department Dropdown
                deptSelect.innerHTML = '<option value="">-- Select Department --</option>';
                
                if (!orgId) {
                    deptSelect.disabled = true;
                    return;
                }
                
                deptSelect.disabled = false;

                // Filter departments matching the selected Org ID
                const filteredDepts = allDepartments.filter(dept => dept.org_ID == orgId);

                if (filteredDepts.length === 0) {
                    const option = document.createElement('option');
                    option.text = "-- No Departments Found for this Organization --";
                    option.disabled = true;
                    deptSelect.add(option);
                } else {
                    filteredDepts.forEach(dept => {
                        const option = document.createElement('option');
                        option.value = dept.dept_ID;
                        option.text = dept.dept_name + (dept.dept_code ? ` (${dept.dept_code})` : '');
                        
                        // Reselect if error occurred and page reloaded
                        if (dept.dept_ID == oldDeptID) {
                            option.selected = true;
                        }
                        deptSelect.add(option);
                    });
                }
            }

            // Listen for changes
            orgSelect.addEventListener('change', updateDepartments);

            // Trigger on load (if org is already selected e.g., after validation error)
            if (orgSelect.value) {
                updateDepartments();
            }

            // --- 2. PASSWORD TOGGLE ---
            document.getElementById('togglePassword').addEventListener('click', function() {
                const password = document.getElementById('password');
                const icon = this.querySelector('i');
                
                if (password.type === 'password') {
                    password.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    password.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
            
            // --- 3. PASSWORD STRENGTH ---
            document.getElementById('password').addEventListener('input', function() {
                const password = this.value;
                const strengthBar = document.getElementById('passwordStrength');
                let strength = 0;
                
                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^a-zA-Z0-9]/.test(password)) strength++;
                
                const colors = ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#28a745'];
                const widths = ['20%', '40%', '60%', '80%', '100%'];
                
                strengthBar.style.width = widths[strength - 1] || '0%';
                strengthBar.style.backgroundColor = colors[strength - 1] || '#e9ecef';
            });
            
            // --- 4. PASSWORD MATCH ---
            document.getElementById('confirm_password').addEventListener('input', function() {
                const password = document.getElementById('password').value;
                const confirmPassword = this.value;
                const matchMessage = document.getElementById('passwordMatch');
                
                if (confirmPassword && password !== confirmPassword) {
                    matchMessage.style.display = 'block';
                    this.classList.add('is-invalid');
                } else {
                    matchMessage.style.display = 'none';
                    this.classList.remove('is-invalid');
                }
            });
        });
    </script>
</body>
</html>