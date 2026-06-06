<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '../../../system-config.php';

class LogoutController
{
    public function logout()
    {
        // Start the session if it hasn't been started already
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }


        if (isset($_SESSION['user_id'])) {
            $database = new Database();
            $pdo = $database->getConnection();
            $userModel = new UserModel($pdo);

            // Log the logout activity
            $userModel->logActivity($_SESSION['user_id'], 'User logged out');
        }

        session_unset();

        session_destroy();
        header('Location: /cdgps');
        exit;
    }
}

// Instantiate the controller and call the logout method
$controller = new LogoutController();
$controller->logout();
