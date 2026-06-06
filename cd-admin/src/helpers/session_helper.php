<?php
/**
 * Session Helper Functions
 * Provides session management utilities including 15-minute timeout
 */

/**
 * Start session if not already started
 */
function startSessionIfNotStarted() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    startSessionIfNotStarted();
    return isset($_SESSION['user_id']) && isset($_SESSION['login_time']);
}

/**
 * Check session timeout (15 minutes)
 * @return bool True if session is valid, false if expired
 */
function checkSessionTimeout() {
    startSessionIfNotStarted();
    
    if (!isLoggedIn()) {
        return false;
    }
    
    // Check if session expiration is set
    if (!isset($_SESSION['exp_session'])) {
        $_SESSION['exp_session'] = 15 * 60; // 15 minutes in seconds
    }
    
    // Check if login time is set
    if (!isset($_SESSION['login_time'])) {
        return false;
    }
    
    date_default_timezone_set('Asia/Kolkata');
    $login_time = strtotime($_SESSION['login_time']);
    $current_time = time();
    $session_duration = $_SESSION['exp_session'];
    
    // Check if session has expired
    if (($current_time - $login_time) > $session_duration) {
        // Log the session timeout
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/../models/UserModel.php';
            require_once __DIR__ . '/../database/Database.php';
            
            try {
                $database = new Database();
                $pdo = $database->getConnection();
                $userModel = new UserModel($pdo);
                $userModel->logActivity($_SESSION['user_id'], 'Session Timeout');
            } catch (Exception $e) {
                // Log error but don't break the flow
                error_log('Session timeout logging failed: ' . $e->getMessage());
            }
        }
        
        // Destroy session
        session_unset();
        session_destroy();
        
        return false;
    }
    
    return true;
}

/**
 * Require login - redirects to login page if not logged in or session expired
 * @param string $redirectUrl Optional redirect URL (default: index.php)
 */
function requireLogin($redirectUrl = 'index.php') {
    startSessionIfNotStarted();
    
    if (!checkSessionTimeout()) {
        $_SESSION['login_error'] = 'Session expired. Please login again.';
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    if (!isLoggedIn()) {
        $_SESSION['login_error'] = 'Please login to access this page.';
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Get remaining session time in seconds
 * @return int Remaining seconds, 0 if expired
 */
function getRemainingSessionTime() {
    startSessionIfNotStarted();
    
    if (!isLoggedIn() || !isset($_SESSION['login_time']) || !isset($_SESSION['exp_session'])) {
        return 0;
    }
    
    date_default_timezone_set('Asia/Kolkata');
    $login_time = strtotime($_SESSION['login_time']);
    $current_time = time();
    $session_duration = $_SESSION['exp_session'];
    $elapsed = $current_time - $login_time;
    $remaining = $session_duration - $elapsed;
    
    return max(0, $remaining);
}

/**
 * Refresh session timeout (extend by 15 minutes)
 */
function refreshSession() {
    startSessionIfNotStarted();
    
    if (isLoggedIn()) {
        date_default_timezone_set('Asia/Kolkata');
        $_SESSION['login_time'] = date('Y-m-d H:i:s');
        $_SESSION['exp_session'] = 15 * 60; // 15 minutes
    }
}
