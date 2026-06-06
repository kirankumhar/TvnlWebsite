<?php

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../models/SyFyModel.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../");
    exit;
}

class SyFyController
{
    public function addSyFy()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
        $calenderYear = filter_input(INPUT_POST, 'sessionYear', FILTER_SANITIZE_NUMBER_INT);
        $financialYear = filter_input(INPUT_POST, 'financialYear', FILTER_SANITIZE_STRING);
        $createdBy = $_SESSION['user_id'];
        $updatedBy = null;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        if (empty($type) || ($type !== 'sy' && $type !== 'fy')) {
            $_SESSION['error'] = "Invalid Type or Empty Type";
            header("Location: ../../create-session.php");
            exit;
        }

        $database = new Database();
        $pdo = $database->getConnection();

        $syFyModel = new SyFyModel($pdo);

        if ($syFyModel->existsSyFy($calenderYear, $financialYear)) {
            $_SESSION['error'] = "A record with the provided session or financial year already exists.";
            header("Location: ../../create-session.php");
            exit;
        }

        if ($syFyModel->insertSyFy($type, $calenderYear, $financialYear, $createdBy, $updatedBy, $ipAddress)) {
            $_SESSION['message'] = "Data saved successfully.";
            header("Location: ../../create-session.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to add record.";
            header("Location: ../../create-session.php");
            exit;
        }
    }

    public function fetchData()
    {
        $database = new Database();
        $pdo = $database->getConnection();

        $syFyModel = new SyFyModel($pdo);

        $data = $syFyModel->getSyFyData();

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function displayData()
    {
        $protocol = isset($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $base_url = $protocol . $_SERVER['HTTP_HOST'] . '/r_admin';

        $database = new Database();
        $pdo = $database->getConnection();

        $syFyModel = new SyFyModel($pdo);

        $data = $syFyModel->getSyFyData();

        // print_r($syFyModel);
        // die;

        include $base_url . '/manage-session.php';
    }

    public function DeleteSyFy()
    {
        $id = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_NUMBER_INT);

        $database = new Database();
        $pdo = $database->getConnection();

        $syFyModel = new SyFyModel($pdo);

        $cat = $syFyModel->deleteSyFy($id);

        if ($cat === 'post_available') {
            $response = [
                'status' => 'error',
                'message' => "Post Available can't able to delete Session/Financial."
            ];

            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        if ($cat) {
            $response = [
                'status' => 'success',
                'message' => "Session/Financial Year deleted successfully."
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $response = [
                'status' => 'error',
                'message' => "Failed to delete session."
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    }

    public function updateSyFy()
    {
        // session_start();
        $id = filter_input(INPUT_POST, 'catid', FILTER_SANITIZE_NUMBER_INT);
        $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
        $calenderYear = filter_input(INPUT_POST, 'sessionYear', FILTER_SANITIZE_NUMBER_INT);
        $financialYear = filter_input(INPUT_POST, 'financialYear', FILTER_SANITIZE_STRING);
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        if (empty($type) || ($type !== 'sy' && $type !== 'fy')) {
            $_SESSION['error'] = "Invalid Type or Empty Type";
            header("Location: ../../create-session.php");
            exit;
        }
        $updatedBy = $_SESSION['user_id'];

        $database = new Database();
        $pdo = $database->getConnection();

        $syFyModel = new SyFyModel($pdo);

        if ($syFyModel->existsSyFy($calenderYear, $financialYear)) {
            $_SESSION['error'] = "A record with the provided session and financial year already exists.";
            header("Location: ../../edit-session.php?id=" . $id);
            exit;
        }

        if ($syFyModel->updateSyFy($id, $type, $calenderYear, $financialYear, $updatedBy, $ipAddress)) {
            $_SESSION['message'] = "Category updated successfully.";
            header("Location: ../../edit-session.php?id=" . $id);
            exit;
        } else {
            $_SESSION['error'] = "Failed to updated category.";
            header("Location: ../../edit-category.php?id=" . $id);
            exit;
        }
    }
}

$controller = new SyFyController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if (!empty($action) && $action == 'updateSyFy') {
            $controller->updateSyFy();
        } elseif (!empty($action) && $action == 'deleteSyFy') {
            $controller->DeleteSyFy();
        }
    } else {
        $controller->addSyFy();
    }
} else {
    $controller->displayData();
}

// $controller->addSyFy();
// $controller->fetchData();
// // $controller->displayData();
