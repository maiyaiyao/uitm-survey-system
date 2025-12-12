<?php
/**
 * User Profile Module
 * Allows users to view and edit their profile information and change password.
 */

require_once '../../config/config.php';
require_once '../../includes/models/User.php';

// 1. Authorization
requireRole(['user']);

$db = new Database();
$userModel = new User();
$current_user_id = getCurrentUserId();

// 2. Fetch User Data
try {
    $user = $db->fetchOne("SELECT * FROM user WHERE user_ID = :id", [':id' => $current_user_id]);
    
    if (!$user) {
        // Should not happen for a logged-in user, but safety first
        session_unset();
        session_destroy();
        redirect(BASE_URL . '/modules/auth/login.php');
    }
} catch (Exception $e) {
    die("Error fetching profile: " . $e->getMessage());
}

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';

        // --- SCENARIO A: Update Profile Details ---
        if ($action === 'update_profile') {
            $updateData = [
                'full_name'          => sanitize($_POST['full_name']),
                'user_handphone_no'  => sanitize($_POST['user_handphone_no']),
                'user_phone_company' => sanitize($_POST['user_phone_company']),
                'user_organization'  => sanitize($_POST['user_organization']),
                'department'         => sanitize($_POST['department']),
                'user_position'      => sanitize($_POST['user_position'])
            ];

            // Validation
            if (empty($updateData['full_name']) || empty($updateData['user_handphone_no'])) {
                throw new Exception("Full Name and Handphone Number are required.");
            }

            // Perform Update using User Model
            $userModel->update($current_user_id, $updateData);
            
            // Update Session Name if changed
            $_SESSION['full_name'] = $updateData['full_name'];
            
            setFlashMessage('success', 'Profile details updated successfully.');
        }

        // --- SCENARIO B: Change Password ---
        elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'];
            $new_password     = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            // 1. If user has a password set (not Google-only), verify current
            if (!empty($user['password'])) {
                if (!$userModel->verifyPassword($current_password, $user['password'])) {
                    throw new Exception("Current password is incorrect.");
                }
            }

            // 2. Validate New Password
            if (strlen($new_password) < 8) {
                throw new Exception("New password must be at least 8 characters.");
            }
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match.");
            }

            // 3. Update Password
            $userModel->updatePassword($current_user_id, $new_password);
            setFlashMessage('success', 'Password changed successfully.');
        }

        // Refresh page to show changes
        redirect('profile.php');

    } catch (Exception $e) {
        setFlashMessage('danger', $e->getMessage());
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Shared Dashboard Styles */
        html, body { height: 100%; margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; width: 270px; z-index: 100; padding: 0; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); color: white; display: flex; flex-direction: column; }
        .main-content-wrapper { margin-left: 270px; width: calc(100% - 270px); }
        @media (max-width: 991.98px) { .sidebar { position: relative; width: 100%; height: auto; } .main-content-wrapper { margin-left: 0; width: 100%; } }
        
        /* Profile Specific Styles */
        .profile-header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            color: white;
        }
        .avatar-circle {
            width: 80px;
            height: 80px;
            background-color: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
            border: 3px solid rgba(255,255,255,0.3);
        }
        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
        }
        /* Fix input group corners inside cards */
        .input-group .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 h-100">
        <div class="row g-0 h-100">
            
            <?php include_once __DIR__ . '/../includes/user_sidebar.php'; ?>
            
            <div class="col-md-9 col-lg-10 main-content-wrapper">
                <div class="main-content px-4 py-4">
                    
                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <?php if($flash['type'] === 'success'): ?><i class="bi bi-check-circle-fill me-2"></i><?php endif; ?>
                            <?php if($flash['type'] === 'danger'): ?><i class="bi bi-exclamation-triangle-fill me-2"></i><?php endif; ?>
                            <?php echo $flash['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card profile-header-card shadow-sm mb-4">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="avatar-circle me-4">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h2 class="h4 mb-1 fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                                <div class="d-flex align-items-center opacity-75">
                                    <i class="bi bi-envelope me-2"></i>
                                    <span><?php echo htmlspecialchars($user['primary_email']); ?></span>
                                    <span class="mx-2">•</span>
                                    <span class="badge bg-white text-primary bg-opacity-25 border border-white border-opacity-25">
                                        <?php echo htmlspecialchars(ucfirst($user['status'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-person-gear me-2 text-primary"></i>Edit Profile</h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="update_profile">
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted text-uppercase fw-bold">Full Name</label>
                                                <input type="text" name="full_name" class="form-control" required
                                                       value="<?php echo htmlspecialchars($user['full_name']); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted text-uppercase fw-bold">Email (Read Only)</label>
                                                <input type="email" class="form-control bg-light" readonly
                                                       value="<?php echo htmlspecialchars($user['primary_email']); ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted text-uppercase fw-bold">Handphone No.</label>
                                                <input type="text" name="user_handphone_no" class="form-control" required
                                                       value="<?php echo htmlspecialchars($user['user_handphone_no']); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted text-uppercase fw-bold">Office Phone</label>
                                                <input type="text" name="user_phone_company" class="form-control"
                                                       value="<?php echo htmlspecialchars($user['user_phone_company']); ?>">
                                            </div>
                                        </div>

                                        <hr class="my-4 border-light">

                                        <div class="mb-3">
                                            <label class="form-label small text-muted text-uppercase fw-bold">Organization</label>
                                            <input type="text" name="user_organization" class="form-control"
                                                   value="<?php echo htmlspecialchars($user['user_organization']); ?>">
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted text-uppercase fw-bold">Department</label>
                                                <input type="text" name="department" class="form-control"
                                                       value="<?php echo htmlspecialchars($user['department']); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted text-uppercase fw-bold">Position</label>
                                                <input type="text" name="user_position" class="form-control"
                                                       value="<?php echo htmlspecialchars($user['user_position']); ?>">
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="submit" class="btn btn-primary px-4 rounded-3" 
                                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                                Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-lock me-2 text-primary"></i>Security</h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="change_password">
                                        
                                        <?php if (!empty($user['password'])): ?>
                                            <div class="mb-3">
                                                <label class="form-label small text-muted text-uppercase fw-bold">Current Password</label>
                                                <div class="input-group">
                                                    <input type="password" name="current_password" id="current_password" class="form-control border-end-0" required>
                                                    <button class="btn bg-white border border-start-0 text-muted" type="button" onclick="togglePassword('current_password', 'icon_current')">
                                                        <i class="bi bi-eye" id="icon_current"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info small mb-3">
                                                <i class="bi bi-google me-1"></i> You logged in via Google. Setting a password here will allow you to login with email as well.
                                            </div>
                                            <input type="hidden" name="current_password" value="">
                                        <?php endif; ?>

                                        <div class="mb-3">
                                            <label class="form-label small text-muted text-uppercase fw-bold">New Password</label>
                                            <div class="input-group">
                                                <input type="password" name="new_password" id="new_password" class="form-control border-end-0" required minlength="8">
                                                <button class="btn bg-white border border-start-0 text-muted" type="button" onclick="togglePassword('new_password', 'icon_new')">
                                                    <i class="bi bi-eye" id="icon_new"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small text-muted text-uppercase fw-bold">Confirm Password</label>
                                            <div class="input-group">
                                                <input type="password" name="confirm_password" id="confirm_password" class="form-control border-end-0" required minlength="8">
                                                <button class="btn bg-white border border-start-0 text-muted" type="button" onclick="togglePassword('confirm_password', 'icon_confirm')">
                                                    <i class="bi bi-eye" id="icon_confirm"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="d-grid mt-4">
                                            <button type="submit" class="btn btn-outline-secondary rounded-3">
                                                Update Password
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
        /**
         * Toggle Password Visibility
         * @param {string} inputId The ID of the password input field
         * @param {string} iconId The ID of the icon element (to switch classes)
         */
        function togglePassword(inputId, iconId) {
            const passwordField = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>