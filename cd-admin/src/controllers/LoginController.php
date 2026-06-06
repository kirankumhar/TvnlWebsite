<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../database/Database.php';

class LoginController
{
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        session_start();

        // Verify CAPTCHA
        if (!isset($_SESSION['captcha']) || empty($_POST['captcha'])) {
            $_SESSION['login_error'] = 'Please complete the CAPTCHA.';
            header('Location: ../../index.php');
            exit;
        }

        if ($_POST['captcha'] !== $_SESSION['captcha']) {
            $_SESSION['login_error'] = 'Invalid CAPTCHA code.';
            header('Location: ../../index.php');
            exit;
        }

        // Clear CAPTCHA session after verification
        unset($_SESSION['captcha']);

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['login_error'] = 'Invalid email address.';
            header('Location: ../../index.php');
            exit;
        }

        if (empty($password)) {
            $_SESSION['login_error'] = 'Password is required.';
            header('Location: ../../index.php');
            exit;
        }

        $database = new Database();
        $pdo = $database->getConnection();
        $userModel = new UserModel($pdo);

        // Check if user is blocked
        $user = $userModel->getUserByEmail($email);

        if ($user) {
            // Check if user is blocked
            if ($userModel->isUserBlocked($user['id'])) {
                $blockedUntil = new DateTime($user['blocked_until']);
                $now = new DateTime();
                $interval = $now->diff($blockedUntil);

                if ($now < $blockedUntil) {
                    $remainingMinutes = $interval->i + ($interval->h * 60);
                    $_SESSION['login_error'] = "Account is blocked. Please try again after {$remainingMinutes} minutes.";
                    header('Location: ../../index.php');
                    exit;
                } else {
                    // Unblock user if block time has passed
                    $userModel->resetFailedAttempts($user['id']);
                }
            }
        } else {
            $_SESSION['login_error'] = "Account is Deactivated.";
            header('Location: ../../index.php');
            exit;
        }

        // Verify password
        if ($user && password_verify($password, $user['password'])) {
            // Reset failed attempts on successful login
            $userModel->resetFailedAttempts($user['id']);

            // Log the successful login activity
            $result = $userModel->logActivity($user['id'], 'User logged in successfully');
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $user['id'];
            date_default_timezone_set('Asia/Kolkata');
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            $_SESSION['exp_session'] = 60 * 60; // Session expiration 15 min
            $_SESSION['login_id'] = $result;

            header("Location: ../../dashboard_view.php");
            exit;
        } else {
            // Handle failed login attempt
            if ($user) {
                // Increment failed attempts
                $attempts = $userModel->incrementFailedAttempt($user['id']);

                // Log failed attempt
                $userModel->logActivity($user['id'], 'Failed login attempt');

                // Check if should block user
                if ($attempts >= 3) {
                    $userModel->blockUser($user['id'], 60); // Block for 60 minutes

                    $_SESSION['login_error'] = 'Too many failed attempts. Account blocked for 60 minutes.';
                    header('Location: ../../index.php');
                    exit;
                } else {
                    $remainingAttempts = 3 - $attempts;
                    $_SESSION['login_error'] = "Invalid email or password. {$remainingAttempts} attempts remaining.";
                }
            } else {
                // User doesn't exist
                $_SESSION['login_error'] = 'Invalid email or password.';
            }

            header('Location: ../../index.php');
            exit;
        }
    }

    public function getDomainNameById($domainId)
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $stmt = $pdo->prepare("SELECT eng_name FROM domains WHERE id = ?");
        $stmt->execute([$domainId]);
        return $stmt->fetchColumn();
    }
}

$controller = new LoginController();
$controller->login();
