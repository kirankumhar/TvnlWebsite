<?php
/**
 * Profile Controller
 * Handles profile update requests
 */

require_once __DIR__ . '/../database/Database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Session expired. Please login again.';
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header("Location: ../../manage-profile.php");
    exit;
}

$userId = $_SESSION['user_id'];
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$mobile = filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

// Validate input
if (empty($username)) {
    $_SESSION['error'] = 'Username is required.';
    header("Location: ../../manage-profile.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email address.';
    header("Location: ../../manage-profile.php");
    exit;
}

try {
    $database = new Database();
    $pdo = $database->getConnection();

    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :userId");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Email is already taken by another user.';
        header("Location: ../../manage-profile.php");
        exit;
    }

    // Update user profile
    $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, mobile = :mobile WHERE id = :userId");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':mobile', $mobile);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Update session variables
        $_SESSION['user_name'] = $username;
        $_SESSION['user_mail'] = $email;
        
        $_SESSION['message'] = 'Profile updated successfully';
    } else {
        $_SESSION['error'] = 'Error updating profile';
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
}

header("Location: ../../manage-profile.php");
exit;
