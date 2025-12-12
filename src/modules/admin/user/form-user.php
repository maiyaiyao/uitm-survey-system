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
    'user_organization' => '',
    'department' => '',
    'user_position' => '',
    'role_ID' => '',
    'status' => 'Active' // Default status for new users
];

// If Edit Mode: Fetch existing data
if ($is_edit) {
    try {
        // Fetch user details including their assigned role
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
        
        // Populate $user_data with DB values
        $user_data = array_merge($user_data, $existing_user);

    } catch (Exception $e) {
        setFlashMessage('danger', 'Error loading user: ' . $e->getMessage());
        redirect('index.php');
    }
}

// Fetch all available roles for the dropdown
try {
    $roles = $db->fetchAll("SELECT role_ID, role_name FROM role");
} catch (Exception $e) {
    $roles = [];
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
            // Optional fields
            'department' => sanitize($_POST['department'] ?? ''),
            'user_organization' => sanitize($_POST['user_organization'] ?? ''),
            'user_position' => sanitize($_POST['user_position'] ?? ''),
            'user_phone_company' => sanitize($_POST['user_phone_company'] ?? ''),
        ];

        // --- Validation ---
        if (empty($post_data['full_name']) || empty($post_data['primary_email']) || empty($post_data['user_handphone_no']) || empty($post_data['role_id'])) {
            throw new Exception('Full Name, Email, Handphone No., and Role are required.');
        }

        if (!isValidEmail($post_data['primary_email'])) {
            throw new Exception('Invalid email format');
        }

        // Email Uniqueness Check
        // If Create: Check if exists. If Edit: Check if exists AND it's not the current user's email.
        $email_owner = $userModel->findByEmail($post_data['primary_email']);
        if ($email_owner) {
            if (!$is_edit) {
                throw new Exception('Email already registered.');
            } elseif ($email_owner['user_ID'] != $user_id) {
                throw new Exception('Email is already taken by another user.');
            }
        }

        // Password Logic
        $password_hash = null;
        if ($is_edit) {
            // Edit Mode: Only update password if filled
            if (!empty($_POST['password'])) {
                if ($_POST['password'] !== $_POST['confirm_password']) throw new Exception('Passwords do not match');
                if (!isStrongPassword($_POST['password'])) throw new Exception('Password must be strong (Min 8 chars, mixed case & symbols).');
                $password_hash = $userModel->hashPassword($_POST['password']);
            }
        } else {
            // Create Mode: Password is required
            if (empty($_POST['password'])) throw new Exception('Password is required for new users.');
            if ($_POST['password'] !== $_POST['confirm_password']) throw new Exception('Passwords do not match');
            if (!isStrongPassword($_POST['password'])) throw new Exception('Password must be strong (Min 8 chars, mixed case & symbols).');
            $password_hash = $userModel->hashPassword($_POST['password']);
        }

        // --- Database Transaction ---
        $conn->beginTransaction();

        if ($is_edit) {
            // === UPDATE LOGIC ===
            
            // Check self-edit protection
            if ($user_id == $current_user['user_ID'] && $post_data['status'] == 'Inactive') {
                throw new Exception('You cannot deactivate your own account.');
            }

            $update_fields = [
                'full_name' => $post_data['full_name'],
                'primary_email' => $post_data['primary_email'],
                'department' => $post_data['department'],
                'status' => $post_data['status'],
                'user_organization' => $post_data['user_organization'],
                'user_position' => $post_data['user_position'],
                'user_phone_company' => $post_data['user_phone_company'],
                'user_handphone_no' => $post_data['user_handphone_no'],
            ];
            if ($password_hash) {
                $update_fields['password'] = $password_hash;
            }

            $userModel->update($user_id, $update_fields);
            
            // Update Role
            $userModel->updateRole($user_id, $post_data['role_id']);
            
            $msg = "User '{$post_data['full_name']}' updated successfully.";

        } else {
            // === CREATE LOGIC ===
            $new_user_id = $userModel->create([
                'primary_email' => $post_data['primary_email'],
                'password' => $password_hash,
                'full_name' => $post_data['full_name'],
                'department' => $post_data['department'],
                'status' => $post_data['status'],
                'email_verified' => 'Verified', // Admin created
                'user_organization' => $post_data['user_organization'],
                'user_position' => $post_data['user_position'],
                'user_phone_company' => $post_data['user_phone_company'],
                'user_handphone_no' => $post_data['user_handphone_no']
            ]);

            // Assign Role
            $userModel->assignRole($new_user_id, $post_data['role_id']);
            
            $msg = "User '{$post_data['full_name']}' created successfully.";
        }

        $conn->commit();
        setFlashMessage('success', $msg);
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        if (isset($conn)) $conn->rollBack();
        setFlashMessage('error', $e->getMessage());
        // Populate form with submitted data so user doesn't lose input
        $user_data = array_merge($user_data, $_POST);
        // Correct role_id key mapping for the select box
        $user_data['role_ID'] = $_POST['role_id'] ?? ''; 
    }
}

$flash = getFlashMessage();
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

                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-decoration-none text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">User Management</a></li>
                            <li class="breadcrumb-item active text-dark" aria-current="page"><?php echo $is_edit ? 'Edit User' : 'Create New'; ?></li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h3 class="fw-bold mb-1"><?php echo $is_edit ? 'Edit User Profile' : 'Provision New User'; ?></h3>
                            <p class="text-muted mb-0">
                                <?php echo $is_edit ? 'Update access control and personal details.' : 'Fill in the details below to create a new user account.'; ?>
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
                            <h5 class="mb-0">User Account Details</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" id="userForm">
                                <div class="row g-4">
                                    
                                    <div class="col-md-6">
                                        <h6 class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 mb-3">Personal Information</h6>
                                        
                                        <div class="mb-3">
                                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="full_name" name="full_name" required 
                                                value="<?php echo htmlspecialchars($user_data['full_name']); ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="primary_email" class="form-label">Email Address (Login ID) <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="primary_email" name="primary_email" 
                                                placeholder="user@example.com" required 
                                                value="<?php echo htmlspecialchars($user_data['primary_email']); ?>">
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Handphone No. <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="user_handphone_no" required
                                                    value="<?php echo htmlspecialchars($user_data['user_handphone_no']); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Company Phone</label>
                                                <input type="text" class="form-control" name="user_phone_company" 
                                                    placeholder="Optional"
                                                    value="<?php echo htmlspecialchars($user_data['user_phone_company']); ?>">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Organization / Company</label>
                                            <input type="text" class="form-control" name="user_organization" 
                                                placeholder="e.g. UiTM" 
                                                value="<?php echo htmlspecialchars($user_data['user_organization']); ?>">
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Department</label>
                                                <input type="text" class="form-control" name="department" 
                                                    placeholder="e.g. FSKM" 
                                                    value="<?php echo htmlspecialchars($user_data['department']); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Position</label>
                                                <input type="text" class="form-control" name="user_position" 
                                                    placeholder="e.g. Lecturer" 
                                                    value="<?php echo htmlspecialchars($user_data['user_position']); ?>">
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
                                                <option value="Active" <?php echo ($user_data['status'] == 'Active') ? 'selected' : ''; ?>>Active (Can Login)</option>
                                                <option value="Inactive" <?php echo ($user_data['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive (Access Revoked)</option>
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
                                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" 
                                                    <?php echo $is_edit ? '' : 'required'; ?> placeholder="Re-type password">
                                                <div id="passwordMatch" class="invalid-feedback">Passwords do not match.</div>
                                            </div>
                                        </div>
                                    </div>

                                </div> 
                                
                                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <a href="index.php" class="btn btn-outline-secondary px-4 rounded-3">
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4 rounded-3" 
                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Unsaved Changes Protection
        document.addEventListener('DOMContentLoaded', function() {
            let isDirty = false;
            const form = document.getElementById('userForm');
            
            if (form) {
                form.addEventListener('change', () => isDirty = true);
                form.addEventListener('input', () => isDirty = true);
                form.addEventListener('submit', () => { isDirty = false; });
            }

            window.addEventListener('beforeunload', function (e) {
                if (isDirty) {
                    e.preventDefault();
                    e.returnValue = ''; 
                }
            });
        });

        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
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
            strengthBar.style.width = (strength * 20) + '%';
            strengthBar.style.backgroundColor = colors[strength - 1] || '#e9ecef';
        });
        
        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>