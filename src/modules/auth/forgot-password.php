<?php
// 1. IMPORT PHPMAILER CLASSES AT THE TOP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 2. LOAD DEPENDENCIES
// If you used Composer:
if (file_exists('../../vendor/autoload.php')) {
    require '../../vendor/autoload.php';
} 

require_once '../../config/config.php';
require_once '../../includes/models/User.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = sanitize($_POST['email']);
        
        if (empty($email)) {
            throw new Exception('Email is required');
        }
        
        if (!isValidEmail($email)) {
            throw new Exception('Invalid email format');
        }
        
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Save token to database
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "INSERT INTO password_reset_tokens (user_ID, token, expires_at) 
                    VALUES (:user_ID, :token, :expires_at)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_ID' => $user['user_ID'],
                ':token' => $token,
                ':expires_at' => $expires_at
            ]);
            
            $reset_link = BASE_URL . "/modules/auth/reset-password.php?token=" . $token;
            
            // --- START PHPMAILER LOGIC ---
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();        
            $mail->SMTPAuth   = true;                       
            $mail->Username   = SMTP_USER;     
            $mail->Password   = SMTP_PASS; 
            $mail->Host     = SMTP_HOST;         
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = 587;                        

            // Recipients
            $mail->setFrom('no-reply@uitm.edu.my', 'UiTM Survey System');
            $mail->addAddress($email);                        // Add a recipient

            // Content
            $mail->isHTML(true);                              // Set email format to HTML
            $mail->Subject = 'Reset Your Password';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                    <h2 style='color: #667eea;'>Password Reset Request</h2>
                    <p>We received a request to reset your password for the UiTM Survey System.</p>
                    <p>Click the button below to reset it:</p>
                    <p>
                        <a href='$reset_link' style='background-color: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                    </p>
                    <p style='font-size: 12px; color: #777;'>If the button doesn't work, copy and paste this link:<br>$reset_link</p>
                    <p>This link will expire in 1 hour.</p>
                </div>
            ";
            $mail->AltBody = "Reset your password here: $reset_link";

            $mail->send();
            // --- END PHPMAILER LOGIC ---
            
            error_log("Password reset link sent to: " . $email);
        }
        
        // Always show success message (security best practice)
        $success = 'If your email is registered, you will receive a password reset link shortly.';
        
    } catch (Exception $e) {
        // Log the actual error for debugging, but show a generic error to user if it's a mailer error
       $error = "<b>DEBUG ERROR:</b> " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .forgot-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .forgot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="forgot-card">
                    <div class="forgot-header">
                        <i class="bi bi-key" style="font-size: 3rem;"></i>
                        <h3 class="mt-3 mb-0">Forgot Password?</h3>
                        <p class="mb-0 mt-2">Reset your password</p>
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
                        
                        <?php if (!$success): ?>
                            <p class="text-muted mb-4">
                                Enter your email address and we'll send you a link to reset your password.
                            </p>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" 
                                           placeholder="your.email@uitm.edu.my" required>
                                </div>
                                
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send me-2"></i>Send Reset Link
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <a href="login.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left me-2"></i>Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>