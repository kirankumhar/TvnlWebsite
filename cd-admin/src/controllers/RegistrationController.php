<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../database/Database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../../dashboard_view.php");
    exit;
}

class RegistrationController {
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }
        session_start();
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email address.';
            header('Location: cdrms/registration_form.php');
            exit;
        }
        if (empty($password) || strlen($password) < 8) {
            // echo "Password must be at least 8 characters long.";
            $_SESSION['error'] = 'Password must be at least 8 characters long.';
            header('Location: ../../registration_form.php');
            exit;
        }

        $database = new Database();
        $pdo = $database->getConnection();

        $userModel = new UserModel($pdo);
        if ($userModel->isEmailRegistered($email)) {
            $_SESSION['error'] = 'Email is already registered.';
            header('Location: ../../registration_form.php');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if ($userModel->registerUser($email, $hashedPassword)) {
            $_SESSION['success'] = 'Registration successful!';
            header('Location: ../../registration_form.php');
            exit;
        } else {
            $_SESSION['error'] = 'Registration failed. Please try again.';
            header('Location: ../../registration_form.php');
            exit;
        }
    }
}

$controller = new RegistrationController();
$controller->register();
?>
