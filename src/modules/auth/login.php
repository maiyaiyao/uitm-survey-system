<?php
require_once '../../config/config.php';
require_once '../../includes/models/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = getPrimaryRole();
    $path = "/modules/$role/dashboard.php";
    redirect(rtrim(BASE_URL, '/') . $path);
    
    //redirect(BASE_URL . "/modules/$role/dashboard.php");
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_type = $_POST['login_type'] ?? 'email';
    
    try {
        $userModel = new User();
        
        if ($login_type === 'email') {
            // Email/Password login
            $primary_email = sanitize($_POST['primary_email']); 
            $password = $_POST['password'];
            
            if (empty($primary_email) || empty($password)) {
                throw new Exception('Email and password are required');
            }
            
            $user = $userModel->findByEmail($primary_email);
            
            if (!$user) {
                throw new Exception('Invalid email or password');
            }
            
            if (!$user['status']) {
                throw new Exception('Your account has been deactivated. Please contact administrator.');
            }
            
            if (!$userModel->verifyPassword($password, $user['password'])) {
                throw new Exception('Invalid email or password');
            }
            
            // Login successful
            loginUser($user);

            /*
            // DEBUG: Add this temporarily
            error_log("SESSION roles: " . (isset($_SESSION['roles']) ? $_SESSION['roles'] : 'NOT SET'));
            $role = getPrimaryRole();
            error_log("Primary role: '$role'");
            var_dump($role); // This will output to the page/browser for immediate feedback
            die(); // Stop execution here to see the output  
            */
        
            // Redirect based on role
            $role = getPrimaryRole();
            $path = "/modules/$role/dashboard.php";
            redirect(rtrim(BASE_URL, '/') . $path);
            
            //redirect(BASE_URL . "/modules/$role/dashboard.php");
            
        } 
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get flash message
$flash = getFlashMessage();
if ($flash) {
    if ($flash['type'] === 'success') {
        $success = $flash['message'];
    } else {
        $error = $flash['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .nav-tabs .nav-link {
            color: #667eea;
            border: none;
            border-bottom: 3px solid transparent;
        }
        .nav-tabs .nav-link.active {
            color: #764ba2;
            border-bottom: 3px solid #764ba2;
            background: transparent;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            opacity: 0.9;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .btn-google {
            background: #fff;
            border: 1px solid #ddd;
            color: #444;
        }
        .btn-google:hover {
            background: #f8f9fa;
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
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <div class="login-header">
                        <i class="bi bi-shield-check" style="font-size: 3rem;"></i>
                        <h3 class="mt-3 mb-0">UiTM Survey System</h3>
                        <p class="mb-0 mt-2">ISO 27001 Assessment</p>
                    </div>
                    
                    <div class="p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <h4 class="text-center mb-4">Sign In</h4>
                        
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="email-login" role="tabpanel">
                            
                                <?php if (isset($_GET['error'])): ?>
    
                                    <div class="error-message" style="color: red; background: #ffdddd; padding: 10px; margin-bottom: 15px; border: 1px solid red;">
                                        <?php 
                                            // Check the value of the error and display a specific message
                                            if ($_GET['error'] == 'account_inactive') {
                                                echo "<strong>Access Denied:</strong> Your account is currently inactive. Please contact the administrator.";
                                            } 
                                            elseif ($_GET['error'] == 'wrong_password') {
                                                echo "Incorrect username or password.";
                                            }
                                            else {
                                                echo "An unknown error occurred.";
                                            }
                                        ?>
                                    </div>

                                <?php endif; ?>

                                <form method="POST" action="">
                                    <input type="hidden" name="login_type" value="email">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" name="primary_email" 
                                            placeholder="your.email@uitm.edu.my" required> </div>
                                    
                                    <div class="mb-3">
                                        <label for="emailPasswordField" class="form-label">Password</label>
                                        <div class="input-group">
                                            <input 
                                            type="password" 
                                            class="form-control" 
                                            name="password" 
                                            id="emailPasswordField" 
                                            placeholder="Enter your password" 
                                            required
                                            >
                                            <button class="btn btn-outline-secondary" type="button" id="emailTogglePassword">
                                            <i id="emailEyeIcon" class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="divider">
                                    <span>OR</span>
                                </div>
                                
                                <div class="d-grid mb-3">
                                    <a href="google-login.php" class="btn btn-google">
                                        <img src="https://www.google.com/favicon.ico" width="20" class="me-2">
                                        Sign in with Google
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="mb-2">
                                <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
                            </p>
                            <p class="mb-0">
                                Don't have an account? 
                                <a href="register.php" class="text-decoration-none fw-bold">Register Now</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="<?php echo BASE_URL; ?>" class="text-white text-decoration-none">
                        <i class="bi bi-arrow-left me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function initializePasswordToggle(fieldId, buttonId, iconId) {
            const passwordField = document.getElementById(fieldId);
            const toggleButton = document.getElementById(buttonId);
            const eyeIcon = document.getElementById(iconId);

            if (passwordField && toggleButton && eyeIcon) {
                toggleButton.addEventListener('click', function () {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    eyeIcon.classList.toggle('bi-eye');
                    eyeIcon.classList.toggle('bi-eye-slash');
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initializePasswordToggle('emailPasswordField', 'emailTogglePassword', 'emailEyeIcon');
        });
    </script>

    <script>
        // Remove the query string (e.g., ?error=account_inactive) from the URL bar
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href.split("?")[0]);
        }
    </script>

</body>
</html>