<?php
require_once '../../config/config.php';
require_once '../../includes/models/User.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

// 1. Verify Token immediately upon page load
if (empty($token)) {
    die("Invalid request.");
}

$db = new Database();
$conn = $db->getConnection();

// Check if token exists, is not used, and is not expired
$sql = "SELECT * FROM password_reset_tokens 
        WHERE token = :token 
        AND used = 0 
        AND expires_at > NOW() 
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([':token' => $token]);
$resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resetRequest) {
    $error = "This password reset link is invalid or has expired.";
}

// 2. Handle Form Submission (New Password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Update User Password
        $userModel = new User();
        // Assuming your User.php has updatePassword($id, $new_pass)
        if ($userModel->updatePassword($resetRequest['user_id'], $password)) {
            
            // Mark token as used
            $updateSql = "UPDATE password_reset_tokens SET used = 1 WHERE token = :token";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->execute([':token' => $token]);

            $success = "Password updated successfully! You can now <a href='login.php'>login</a>.";
        } else {
            $error = "Failed to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
        .card { border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4">
                    <h3 class="text-center mb-4">Set New Password</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php elseif ($resetRequest): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" required minlength="8">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>