<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '../../../permissionjson.php';

class UserController
{
    private $pdo;

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->getConnection();
        session_start();
    }

    public function createUser()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            exit;
        }
        $errors = [];
        global $permissionRoleWise;
        $role = trim($_POST['role'] ?? '');
        $domainStmt = $this->pdo->prepare("SELECT domain_id FROM users WHERE id = :userId LIMIT 1");
        $domainStmt->bindValue(':userId', (int)$_SESSION['user_id'], PDO::PARAM_INT);
        $domainStmt->execute();
        $domainId = (int)$domainStmt->fetchColumn();

        // Sanitize inputs
        $data = [
            'domain_id' => $domainId,
            'name' => trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS)),
            'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
            'phone' => trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS)),
            'role' => trim(filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS)),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'password_expire_in_days' => filter_input(INPUT_POST, 'password_expire_in_days', FILTER_VALIDATE_INT),
        ];

        if (!isset($permissionRoleWise[$role])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid role']);
            return;
        }

        $data['module'] = $_POST['module']
            ?? $permissionRoleWise[$role]['module'];

        $data['permission'] = $_POST['permission']
            ?? $permissionRoleWise[$role]['permission'];

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if (!$data['email']) {
            $errors['email'] = 'Valid email is required';
        }

        if (empty($data['phone'])) {
            $errors['phone'] = 'Phone is required';
        }

        if (empty($data['role'])) {
            $errors['role'] = 'Role is required';
        }

        // Password validation
        if (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Password and Confirm Password do not match';
        }

        // Permission validation
        if (!is_array($data['permission']) || empty($data['permission'])) {
            $errors['permission'] = 'At least one permission is required';
        }

        // If validation fails
        if (!empty($errors)) {
            $_SESSION['error_message'] = implode(" ", $errors);
            header("Location: ../../add-user.php");
            exit();
        }

        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // Created by
        $data['created_by'] = $_SESSION['user_id'] ?? null;

        // Save user
        $userModel = new UserModel($this->pdo);
        $result = $userModel->create($data);

        if ($result) {
            $_SESSION['message'] = "User Create successfully.";
            header("Location: ../../add-user.php");
        } else {
            $_SESSION['error'] = "Failed to create user.";
            header("Location: ../../add-user.php");
            exit;
        }
    }

    public function updateUsersDetails()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            exit;
        }
        $userId = filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT);
        // Sanitize inputs
        $data = [
            'user_id' => filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT),
            'domain_id' => filter_input(INPUT_POST, 'domain_id', FILTER_VALIDATE_INT),
            'username' => trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS)),
            'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
            'phone' => trim(filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_SPECIAL_CHARS)),
            'role' => trim(filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS)),
            'password' => $_POST['updatepassword'] ?? '',
            'password_expire_in_days' => filter_input(INPUT_POST, 'password_expire_in_days', FILTER_VALIDATE_INT),
        ];

        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        }

        if (!$data['email']) {
            $errors['email'] = 'Valid email is required';
        }

        if (empty($data['phone'])) {
            $errors['phone'] = 'Phone is required';
        }

        if (empty($data['role'])) {
            $errors['role'] = 'Role is required';
        }

        // Password validation
        if (isset($data['password']) && strlen($data['password']) < 8) {
            $errors['updatepassword'] = 'Password must be at least 8 characters';
        }

        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        // Updated by
        $data['update_by'] = $_SESSION['user_id'] ?? null;

        // print_r($data); die();
        $userModel = new UserModel($this->pdo);
        $result = $userModel->updateUser($data);
        // print_r($result); die();

        if ($result) {
            $_SESSION['message'] = "User Update successfully.";
            header("Location: ../../edit-user.php?id=$userId");
        } else {
            $_SESSION['error'] = "Failed to update user.";
            header("Location: ../../edit-user.php?id=$userId");
            exit;
        }
    }
}


$controller = new UserController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if (!empty($action) && $action == 'actionUpdateUser') {
            $controller->updateUsersDetails();
        } elseif (!empty($action) && $action == 'deleteDomains') {
            // $controller->softDeleteDomains();
        } elseif (!empty($action) && $action == 'actionCreateUser') {
            $controller->createUser();
        }
    }
} else {
    // $controller->showUsers();
}
