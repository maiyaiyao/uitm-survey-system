<?php
/**
 * Authentication Helper Functions
 */

/**
 * Login user and create session
 */

function loginUser($user) {
    $_SESSION['user_ID'] = $user['user_ID'];
    $_SESSION['email'] = $user['primary_email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['login_time'] = time();

    // Get roles from database
    $userModel = new User();
    $_SESSION['roles'] = !empty($user['roles']) ? $user['roles'] : 'user';
    //$_SESSION['roles'] = $userModel->getUserRoles($user['user_ID']); 
    

    // Update last login
    $userModel->updateLastLogin($user['user_ID']);

    // Regenerate session ID for security
    session_regenerate_id(true);
}


/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Check if session is expired
 */
function isSessionExpired() {
    if (isset($_SESSION['login_time'])) {
        $elapsed = time() - $_SESSION['login_time'];
        if ($elapsed > SESSION_TIMEOUT) {
            return true;
        }
        // Update login time
        $_SESSION['login_time'] = time();
    }
    return false;
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn() || isSessionExpired()) {
        setFlashMessage('warning', 'Please login to access this page');
        redirect(BASE_URL . '/modules/auth/login.php');
    }
}

/**
 * Require specific role
 */
function requireRole($requiredRoles) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/modules/auth/login.php');
        exit;
    }

    // Get user roles and ensure it's an array
    $userRoles = $_SESSION['roles'] ?? '';
    if (!is_array($userRoles)) {
        $userRoles = explode(',', $userRoles);
    }

    // Filter out empty roles and trim whitespace
    $userRoles = array_map('trim', array_filter($userRoles, function($role) {
        return !empty($role);
    }));

    // Check if user has at least one required role
    foreach ($requiredRoles as $role) {
        if (in_array($role, $userRoles)) {
            return; // Access granted
        }
    }

    // Access denied - redirect to login (with loop prevention)
    $currentUri = $_SERVER['REQUEST_URI'];
    if (strpos($currentUri, '/modules/auth/login.php') === false) {
        setFlashMessage('danger', 'You do not have permission to access this page. Please log in.');
        redirect(BASE_URL . '/modules/auth/login.php');
    } else {
        // Already on login page, prevent loop
        http_response_code(403);
        die('Access denied: Insufficient permissions.');
    }
}

/**
 * Check if user has any of the specified roles
 */
function userHasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRoles = $_SESSION['roles'] ?? [];
    
    foreach ($roles as $role) {
        if (in_array($role, $userRoles)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get primary role (for routing)
 */
function getPrimaryRole() {
    if (isset($_SESSION['roles']) && !empty($_SESSION['roles'])) {
        $roles = $_SESSION['roles'];
   
           // If it's a string, explode into an array; if already an array, use as-is
        if (!is_array($roles)) {
            $roles = explode(',', $roles);
        }
   
           // Filter out empty roles and trim whitespace
        $roles = array_map('trim', array_filter($roles, function($role) {
            return !empty($role);
        }));
   
        if (in_array('admin', $roles)) {
            return 'admin';
        }
           
           // Use the first non-empty role as the primary role, or fallback to 'user'
        return !empty($roles) ? $roles[0] : 'user';
    }
        // Fallback to the default role if fetching failed
    return 'user'; 
}
   

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if email is UiTM email
 */
function isUitmEmail($email) {
    return strpos($email, UITM_EMAIL_DOMAIN) !== false;
}

/**
 * Validate password strength
 */
function isStrongPassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special char
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);
    
    return strlen($password) >= PASSWORD_MIN_LENGTH && 
           $uppercase && $lowercase && $number && $specialChars;
}


/**
 * Generate random password
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
}

/**
 * Send verification email (placeholder)
 */
function sendVerificationEmail($email, $token) {
    // TODO: Implement email sending using PHPMailer or similar
    // For now, just log it
    error_log("Verification email would be sent to: $email with token: $token");
    return true;
}

/**
 * Get user's full information
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'user_ID' => $_SESSION['user_ID'],
        'email' => $_SESSION['email'],
        'full_name' => $_SESSION['full_name'],
        'roles' => $_SESSION['roles'],
    ];
}
?>