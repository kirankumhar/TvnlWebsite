<?php

require_once __DIR__ . '/../models/DomainsModel.php';
require_once __DIR__ . '/../database/Database.php';

class DomainsController
{

    public function insertDomains()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        session_start();
        $engName = filter_input(INPUT_POST, 'eng_name', FILTER_SANITIZE_STRING);
        $hinName = filter_input(INPUT_POST, 'hin_name', FILTER_SANITIZE_STRING);
        $domainPath = filter_input(INPUT_POST, 'domain_path', FILTER_SANITIZE_STRING);
        $domainAbout = filter_input(INPUT_POST, 'domain_about', FILTER_SANITIZE_STRING);
        $createdBy = $_SESSION['user_id'];

        $database = new Database();
        $pdo = $database->getConnection();

        $domainModel = new DomainsModel($pdo);

        if ($domainModel->insertDomains($engName, $hinName, $domainPath, $domainAbout,  $createdBy)) {
            $_SESSION['message'] = "Domains added successfully.";
            header("Location: ../../add-domain.php");
        } else {
            $_SESSION['error'] = "Failed to create domains.";
            header("Location: ../../add-domain.php");
            exit;
        }
    }

    public function showDomains()
    {
        $protocol = isset($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $base_url = $protocol . $_SERVER['HTTP_HOST'] . '/r_admin';
        $database = new Database();
        $pdo = $database->getConnection();

        $domainModel = new DomainsModel($pdo);

        $categories = $domainModel->getAllDomains();

        include $base_url . '/show_categories.php';
    }

    public function updateDomains()
    {
        session_start();
        $id = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_NUMBER_INT);
        $engName = filter_input(INPUT_POST, 'eng_name', FILTER_SANITIZE_STRING);
        $hinName = filter_input(INPUT_POST, 'hin_name', FILTER_SANITIZE_STRING);
        $domainPath = filter_input(INPUT_POST, 'domain_path', FILTER_SANITIZE_STRING);
        $domainAbout = filter_input(INPUT_POST, 'domain_about', FILTER_SANITIZE_STRING);
        $updatedBy = $_SESSION['user_id'];

        $database = new Database();
        $pdo = $database->getConnection();

        $domainModel = new DomainsModel($pdo);

        if ($domainModel->updateDomains($id, $engName, $hinName, $domainPath, $domainAbout, $updatedBy)) {
            $_SESSION['message'] = "Domain updated successfully.";
            header("Location: ../../edit-domains.php?id=" . $id);
            exit;
        } else {
            $_SESSION['error'] = "Failed to updated domain.";
            header("Location: ../../edit-domains.php?id=" . $id);
            exit;
        }
    }
    public function softDeleteDomains()
    {
        session_start();
        $id = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_NUMBER_INT);
        $updatedBy = $_SESSION['user_id'];

        // Initialize database connection and model
        $database = new Database();
        $pdo = $database->getConnection();
        $domainModel = new DomainsModel($pdo);

        // Attempt to soft delete the category
        $cat = $domainModel->softDeleteDomains($id, $updatedBy);

        if ($cat === 'post_available') {
            $_SESSION['error'] = "Post Available: Cannot delete category.";
            exit;
        } elseif ($cat === true) {
            $_SESSION['message'] = "Category deleted successfully.";
            exit;
        } else {
            $_SESSION['error'] = "Failed to updated category.";
            exit;
        }
    }
}

$controller = new DomainsController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if (!empty($action) && $action == 'updateDomains') {
            $controller->updateDomains();
        } elseif (!empty($action) && $action == 'deleteDomains') {
            $controller->softDeleteDomains();
        }
    } else {
        $controller->insertDomains();
    }
} else {
    $controller->showDomains();
}
