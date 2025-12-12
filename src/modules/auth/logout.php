<?php
require_once '../../config/config.php';

/**
 * Logout Handler
 * Destroys session and redirects to login
 */

// Logout user
logoutUser();

// Set success message
setFlashMessage('success', 'You have been logged out successfully.');

// Redirect to login page
redirect(BASE_URL );
?>