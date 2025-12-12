<?php
require_once '../../config/config.php';
require_once '../../includes/models/User.php';

/**
 * Google OAuth Callback Handler
 * Processes the response from Google OAuth
 */

try {
    // Verify state parameter (CSRF protection)
    if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['google_oauth_state']) {
        throw new Exception('Invalid state parameter. Possible CSRF attack.');
    }
    
    // Check for authorization code
    if (!isset($_GET['code'])) {
        throw new Exception('Authorization code not received from Google.');
    }
    
    $auth_code = $_GET['code'];
    
    // Exchange authorization code for access token
    $token_url = 'https://oauth2.googleapis.com/token';
    
    $token_data = [
        'code' => $auth_code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $token_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception('Failed to get access token from Google.');
    }
    
    $token_info = json_decode($token_response, true);
    
    if (!isset($token_info['access_token'])) {
        throw new Exception('Access token not found in response.');
    }
    
    // Get user information from Google
    $userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $userinfo_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token_info['access_token']
    ]);
    
    $userinfo_response = curl_exec($ch);
    curl_close($ch);
    
    $google_user = json_decode($userinfo_response, true);
    
    if (!isset($google_user['email'])) {
        throw new Exception('Email not found in Google user info.');
    }
    
    // Process user data
    $google_email = $google_user['email'];
    $google_name = $google_user['name'] ?? '';
    // FIX: Add fallback for 'sub' ID
    $google_sub_id = $google_user['id'] ?? $google_user['sub']; 
    $google_picture = $google_user['picture'] ?? null;
    
    $userModel = new User();
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if user exists by Google ID
    $existing_user = $userModel->findByGoogleId($google_sub_id);
    
    if ($existing_user) {
        // User exists - login
        if (!$existing_user['status']) {
            throw new Exception('Your account has been deactivated. Please contact administrator.');
        }
        
        loginUser($existing_user);
        
        $role = getPrimaryRole();
        // FIX: Ensure role is not empty
        if (empty($role)) { $role = 'user'; }
        redirect(BASE_URL . "/modules/$role/dashboard.php");
        
    } else {
        // Check if email already exists
        $existing_email = $userModel->findByEmail($google_email);
        
        if ($existing_email) {
            // Email exists - link Google account
            $userModel->linkGoogleAccount($existing_email['user_ID'], $google_sub_id);
            
            // Refetch user data to ensure the new Google ID is linked and roles are correct
            $linked_user = $userModel->findById($existing_email['user_ID']);
            
            loginUser($linked_user);
            
            setFlashMessage('success', 'Google account linked successfully!');
            $role = getPrimaryRole();
            // FIX: Ensure role is not empty
            if (empty($role)) { $role = 'user'; }
            redirect(BASE_URL . "/modules/$role/dashboard.php");
            
        } else {
            // New user - create account and assign default role
            
            // FIX: Set role to 'user' (ID 2) unconditionally
            $default_role_id = 2; 
            
            $conn->beginTransaction();
            
            try {
                // Create new user
                $user_ID = $userModel->create([
                    'primary_email' => $google_email, // UPDATED: Changed 'email' to 'primary_email' to match DB
                    'password' => null, // No password for Google sign-in only
                    'google_sub_id' => $google_sub_id,
                    'full_name' => $google_name,
                    'email_verified' => 1 // Google emails are verified
                ]);

                // --- SAFETY CHECK START ---
                // If create() returned empty (common issue), force fetch the ID by email
                if (empty($user_ID)) {
                    $checkUser = $userModel->findByEmail($google_email);
                    if ($checkUser && !empty($checkUser['user_ID'])) {
                        $user_ID = $checkUser['user_ID'];
                    } else {
                        throw new Exception("User creation failed: Could not retrieve new User ID.");
                    }
                }
                // --- SAFETY CHECK END ---
                
                // FIX: Pass all required arguments to assignRole
                $userModel->assignRole($user_ID, $default_role_id, 'Google');
                
                
                $conn->commit();
                
                // Login the new user
                $new_user = $userModel->findById($user_ID);
                loginUser($new_user);
                
                setFlashMessage('success', 'Account created successfully! Welcome to UiTM Audit System.');
                
                // FIX: Hardcode 'user' role for guaranteed correct redirection
                $role = 'user'; 
                
                redirect(BASE_URL . "/modules/$role/dashboard.php");
                
            } catch (Exception $e) {
                $conn->rollBack();
                // FIX: Capture and display the specific database error message
                error_log('New User Transaction Failed: ' . $e->getMessage());
                setFlashMessage('danger', 'Account creation failed: ' . $e->getMessage());
                redirect(BASE_URL . '/modules/auth/login.php');
            }
        }
    }
    
} catch (Exception $e) {
    error_log('Google OAuth Error: ' . $e->getMessage());
    setFlashMessage('danger', 'Google Sign-In failed: ' . $e->getMessage());
    redirect(BASE_URL . '/modules/auth/login.php');
}
?>