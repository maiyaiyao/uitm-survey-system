<?php
require_once '../../config/config.php';
require_once '../../includes/models/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getPrimaryRole();
    redirect(BASE_URL . "/modules/$role/dashboard.php");
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $userModel = new User();
        $db = new Database();
        $conn = $db->getConnection();
        
        // Get form data and sanitize
        $full_name = sanitize($_POST['full_name']);
        $primary_email = sanitize($_POST['primary_email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // New fields for the 'user' table (collecting them, but not all are required for basic registration)
        $department = sanitize($_POST['department'] ?? '');
        $user_organization = sanitize($_POST['user_organization'] ?? '');
        $user_position = sanitize($_POST['user_position'] ?? '');
        $user_phone_company = sanitize($_POST['user_phone_company'] ?? '');
        $user_handphone_no = sanitize($_POST['user_handphone_no'] ?? '');
        
        // --- Validation ---
        if (empty($full_name) || empty($primary_email) || empty($password) || empty($user_handphone_no)) {
            throw new Exception('Full Name, Email, Password, and Phone Number are required fields.');
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
            // Create user - using 'primary_email' for the email field
            $user_ID = $userModel->create([
                'primary_email' => $primary_email,
                'password' => $userModel->hashPassword($password),
                'full_name' => $full_name,
                // New fields for 'user' table
                'department' => $department,
                'status' => 'active', // Assuming a default status
                'email_verified' => 0, // Not verified initially
                'user_organization' => $user_organization,
                'user_position' => $user_position,
                'user_phone_company' => $user_phone_company,
                'user_handphone_no' => $user_handphone_no
            ]);

            // --- SAFETY CHECK START ---
            // If create() returned empty, force fetch the ID by email
            if (empty($user_ID)) {
                $checkUser = $userModel->findByEmail($primary_email);
                if ($checkUser && !empty($checkUser['user_ID'])) {
                    $user_ID = $checkUser['user_ID'];
                } else {
                    throw new Exception("User creation failed: Could not retrieve new User ID.");
                }
            }
            // --- SAFETY CHECK END ---
            
            // Assign default 'user' role (ID 2 is specified)
            $role_name = 'user'; 
            $role_id = $userModel->getRoleIdByName($role_name);
            
            if (!$role_id) {
                 // Fallback if role 'user' isn't found, try to use ID 2 directly for safety
                 $role_id = 2; 
            }
            
            // Assign role
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
                            <p class="text-muted small">Optional Additional Information (can be added later)</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Organization/Company</label>
                                    <input type="text" class="form-control" name="user_organization" 
                                           placeholder="e.g., UiTM, XYZ Corp" 
                                           value="<?php echo htmlspecialchars($_POST['user_organization'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Position/Title</label>
                                    <input type="text" class="form-control" name="user_position" 
                                           placeholder="e.g., Manager, Auditor" 
                                           value="<?php echo htmlspecialchars($_POST['user_position'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department/Faculty</label>
                                    <input type="text" class="form-control" name="department" 
                                           placeholder="e.g., FSKM, Audit Dept" 
                                           value="<?php echo htmlspecialchars($_POST['department'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Company Phone No.</label>
                                    <input type="text" class="form-control" name="user_phone_company" 
                                           placeholder="e.g., +60312345678"
                                           value="<?php echo htmlspecialchars($_POST['user_phone_company'] ?? ''); ?>">
                                </div>
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
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a>
                                </label>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // No user type selection logic needed anymore.
        
        // Toggle password visibility
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
        
        // Password strength indicator
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
        
        // Password match validation
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
    </script>
</body>
</html>