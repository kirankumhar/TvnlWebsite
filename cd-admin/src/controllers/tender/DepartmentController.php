<?php

require_once __DIR__ . '/../../models/DepartmentModel.php';
require_once __DIR__ . '/../../database/Database.php';

class DepartmentController
{
    private $pdo;
    private $departmentModel;

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->getConnection();
        $this->departmentModel = new DepartmentModel($this->pdo);
    }

    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function insertDepartment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        $this->startSession();
        
        $engDept = trim(filter_input(INPUT_POST, 'eng_dep', FILTER_SANITIZE_STRING));
        $hinDept = trim(filter_input(INPUT_POST, 'hin_dep', FILTER_SANITIZE_STRING));
        $deptCode = trim(filter_input(INPUT_POST, 'dept_code', FILTER_SANITIZE_STRING));
        $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
        $displayOrder = filter_input(INPUT_POST, 'display_order', FILTER_SANITIZE_NUMBER_INT) ?? 0;
        $createdBy = $_SESSION['user_id'] ?? 0;
        $loginId = $_SESSION['login_id'] ?? 0;

        if (empty($engDept)) {
            $_SESSION['error'] = "Department name (English) is required.";
            header("Location: ../../create-department.php");
            exit;
        }

        if ($this->departmentModel->isDepartmentNameExists($engDept)) {
            $_SESSION['error'] = "Department '" . htmlspecialchars($engDept) . "' already exists.";
            header("Location: ../../create-department.php");
            exit;
        }

        if (!empty($deptCode) && $this->departmentModel->isDepartmentCodeExists($deptCode)) {
            $_SESSION['error'] = "Department code '" . htmlspecialchars($deptCode) . "' already exists.";
            header("Location: ../../create-department.php");
            exit;
        }

        $status = ucfirst(strtolower($status));

        $result = $this->departmentModel->insertDepartment(
            $engDept,
            $hinDept,
            $deptCode,
            $displayOrder,
            $status,
            $createdBy,
            $loginId
        );

        if ($result) {
            $_SESSION['message'] = "Department added successfully.";
            header("Location: ../../manage-departments.php");
        } else {
            $_SESSION['error'] = "Failed to create department. Please try again.";
            header("Location: ../../create-department.php");
        }
        exit;
    }

    public function updateDepartment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        $this->startSession();
        
        $id = filter_input(INPUT_POST, 'dept_id', FILTER_SANITIZE_NUMBER_INT);
        $engDept = trim(filter_input(INPUT_POST, 'eng_dep', FILTER_SANITIZE_STRING));
        $hinDept = trim(filter_input(INPUT_POST, 'hin_dep', FILTER_SANITIZE_STRING));
        $deptCode = trim(filter_input(INPUT_POST, 'dept_code', FILTER_SANITIZE_STRING));
        $status = trim(filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING));
        $displayOrder = filter_input(INPUT_POST, 'display_order', FILTER_SANITIZE_NUMBER_INT) ?? 0;
        $updatedBy = $_SESSION['user_id'] ?? 0;
        $loginId = $_SESSION['login_id'] ?? 0;

        if (empty($id)) {
            $_SESSION['error'] = "Invalid department ID.";
            header("Location: ../../manage-departments.php");
            exit;
        }

        if (empty($engDept)) {
            $_SESSION['error'] = "Department name (English) is required.";
            header("Location: ../../edit-department.php?id=" . $id);
            exit;
        }

        if ($this->departmentModel->isDepartmentNameExists($engDept, $id)) {
            $_SESSION['error'] = "Department '" . htmlspecialchars($engDept) . "' already exists.";
            header("Location: ../../edit-department.php?id=" . $id);
            exit;
        }

        if (!empty($deptCode) && $this->departmentModel->isDepartmentCodeExists($deptCode, $id)) {
            $_SESSION['error'] = "Department code '" . htmlspecialchars($deptCode) . "' already exists.";
            header("Location: ../../edit-department.php?id=" . $id);
            exit;
        }

        $status = ucfirst(strtolower($status));

        $result = $this->departmentModel->updateDepartment(
            $id,
            $engDept,
            $hinDept,
            $deptCode,
            $displayOrder,
            $status,
            $updatedBy,
            $loginId
        );

        if ($result) {
            $_SESSION['message'] = "Department updated successfully.";
            header("Location: ../../manage-departments.php");
        } else {
            $_SESSION['error'] = "Failed to update department.";
            header("Location: ../../edit-department.php?id=" . $id);
        }
        exit;
    }

    public function showDepartments()
    {
        $this->startSession();
        
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        if (!empty($search)) {
            $departments = $this->departmentModel->getDepartmentsPaginated($limit, $offset, $search);
            $allDepts = $this->departmentModel->getAllDepartments(true);
            $totalRecords = count(array_filter($allDepts, function($dept) use ($search) {
                return stripos($dept['department_name'], $search) !== false || 
                       stripos($dept['department_code'] ?? '', $search) !== false ||
                       stripos($dept['department_name_hindi'] ?? '', $search) !== false;
            }));
        } else {
            $departments = $this->departmentModel->getAllDepartments(true);
            $totalRecords = count($departments);
            $departments = array_slice($departments, $offset, $limit);
        }
        
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1;
        
        return [
            'departments' => $departments,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'search' => $search
        ];
    }

    public function getDepartmentById($id)
    {
        return $this->departmentModel->getDepartmentById($id);
    }

    public function softDeleteDepartment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        $this->startSession();
        
        $id = filter_input(INPUT_POST, 'dept_id', FILTER_SANITIZE_NUMBER_INT);
        $updatedBy = $_SESSION['user_id'] ?? 0;
        $loginId = $_SESSION['login_id'] ?? 0;

        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid department ID']);
            exit;
        }

        $result = $this->departmentModel->softDeleteDepartment($id, $updatedBy, $loginId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Department deactivated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to deactivate department']);
        }
        exit;
    }

    public function activateDepartment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        $this->startSession();
        
        $id = filter_input(INPUT_POST, 'dept_id', FILTER_SANITIZE_NUMBER_INT);
        $updatedBy = $_SESSION['user_id'] ?? 0;
        $loginId = $_SESSION['login_id'] ?? 0;

        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Invalid department ID']);
            exit;
        }

        $result = $this->departmentModel->activateDepartment($id, $updatedBy, $loginId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Department activated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to activate department']);
        }
        exit;
    }

    public function updateDisplayOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        $this->startSession();
        
        $orders = json_decode($_POST['orders'] ?? '{}', true);
        $updatedBy = $_SESSION['user_id'] ?? 0;
        $loginId = $_SESSION['login_id'] ?? 0;

        if (empty($orders)) {
            echo json_encode(['success' => false, 'message' => 'No order data provided']);
            exit;
        }

        $result = $this->departmentModel->bulkUpdateDisplayOrders($orders, $updatedBy, $loginId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Display order updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update display order']);
        }
        exit;
    }

    public function checkDepartmentCode()
    {
        header('Content-Type: application/json');
        
        $code = $_GET['code'] ?? '';
        $excludeId = $_GET['exclude_id'] ?? null;
        
        if (empty($code)) {
            echo json_encode(['exists' => false]);
            exit;
        }
        
        $exists = $this->departmentModel->isDepartmentCodeExists($code, $excludeId);
        echo json_encode(['exists' => $exists]);
        exit;
    }

    public function getDepartmentsAjax()
    {
        header('Content-Type: application/json');
        
        $departments = $this->departmentModel->getDepartmentsForDropdown();
        echo json_encode(['success' => true, 'data' => $departments]);
        exit;
    }
}

// Route handling
$controller = new DepartmentController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'insert':
            $controller->insertDepartment();
            break;
        case 'update':
            $controller->updateDepartment();
            break;
        case 'soft_delete':
            $controller->softDeleteDepartment();
            break;
        case 'activate':
            $controller->activateDepartment();
            break;
        case 'update_order':
            $controller->updateDisplayOrder();
            break;
        default:
            $controller->insertDepartment();
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'ajax_departments':
            $controller->getDepartmentsAjax();
            break;
        case 'check_code':
            $controller->checkDepartmentCode();
            break;
    }
}
?>