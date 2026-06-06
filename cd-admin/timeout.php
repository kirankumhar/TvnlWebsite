<?php
/**
 * Session Timeout Check
 * Uses the session helper for consistent session management
 */
require_once __DIR__ . '/src/helpers/session_helper.php';

// Check session timeout using helper function
if (!checkSessionTimeout()) {
    // Session expired or user not logged in
    // The checkSessionTimeout function already handles cleanup and session destruction
    // Just redirect if needed
    if (!isLoggedIn()) {
        $_SESSION['login_error'] = 'Session expired. Please login again.';
        header('Location: index.php');
        exit;
    }
}
