<?php
require_once '../../config/config.php';

/**
 * Google OAuth Login Initiation
 * Redirects user to Google OAuth consent screen
 */

// Check if Google OAuth is configured
if (GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID') {
    setFlashMessage('danger', 'Google Sign-In is not configured. Please contact administrator.');
    redirect(BASE_URL . '/modules/auth/login.php');
}

// Store action in session (login or register)
$action = $_GET['action'] ?? 'login';
$_SESSION['google_action'] = $action;

// Google OAuth 2.0 endpoint
$google_oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth';

// Parameters for Google OAuth
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online',
    'state' => bin2hex(random_bytes(16)), // CSRF protection
    'prompt' => 'select_account' // Force account selection
];

// Store state in session for verification
$_SESSION['google_oauth_state'] = $params['state'];

// Build OAuth URL
$oauth_url = $google_oauth_url . '?' . http_build_query($params);

// Redirect to Google
header('Location: ' . $oauth_url);
exit();
?>