<?php
require_once __DIR__ . '/../models/PermissionModule.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '../../../permissionjson.php';

class PermissionController
{
    private $pdo;

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->getConnection();
        session_start();
    }

    public function updatePermission()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            exit;
        }

        $data = [
            'permission' => $_POST['permission'] ?? [],
            'module' => $_POST['module'] ?? [],
            'permission_id' => $_POST['permission_id'] ?? [],
            'userId' => filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT),
            'updatedBy' => $_SESSION['user_id']
        ];

        $userId = $data['userId'];

        // Save user
        $userModel = new PermissionModule($this->pdo);
        $result = $userModel->syncPermissions($data);

        if ($result) {
            $_SESSION['message'] = "Permission Updated Successfully.";
            header("Location: ../../edit-permission.php?id=$userId");
        } else {
            $_SESSION['error'] = "Failed to create user.";
            header("Location: ../../edit-permission.php?id=$userId");
            exit;
        }
    }
}


$controller = new PermissionController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if (!empty($action) && $action == 'permissionUpdate') {
            $controller->updatePermission();
        }
    }
} else {
    // $controller->showUsers();
}
