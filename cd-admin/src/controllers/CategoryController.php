<?php

require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../database/Database.php';

class CategoryController
{
    public function insertCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        session_start();
        $engCat = filter_input(INPUT_POST, 'eng_cat', FILTER_SANITIZE_STRING);
        $hinCat = filter_input(INPUT_POST, 'hin_cat', FILTER_SANITIZE_STRING);
        $createdBy = $_SESSION['user_id'];

        $database = new Database();
        $pdo = $database->getConnection();

        $checkStmt = $pdo->prepare("
            SELECT id 
            FROM category_master 
            WHERE category_name = :eng_cat
            OR category_name LIKE :eng_like
            LIMIT 1
        ");

        $checkStmt->execute([
            ':eng_cat'  => $engCat,
            ':eng_like' => '%' . $engCat . '%',
        ]);

        if ($checkStmt->rowCount() > 0) {
            $_SESSION['error'] = $engCat ." Category already exists.";
            header("Location: ../../create-category.php");
            exit;
        }

        $categoryModel = new CategoryModel($pdo);

        if ($categoryModel->insertCategory($engCat, $hinCat, $createdBy)) {
            $_SESSION['message'] = "Category added successfully.";
            header("Location: ../../create-category.php");
        } else {
            $_SESSION['error'] = "Failed to create category.";
            header("Location: ../../create-category.php");
            exit;
        }
    }

    public function showCategories()
    {
        $protocol = isset($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $base_url = $protocol . $_SERVER['HTTP_HOST'] . '/r_admin';
        $database = new Database();
        $pdo = $database->getConnection();

        $categoryModel = new CategoryModel($pdo);

        $categories = $categoryModel->getAllCategories();

        include $base_url . '/show_categories.php';
    }

    public function updateCategory()
    {
        session_start();
        $id = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_NUMBER_INT);
        $engCat = filter_input(INPUT_POST, 'eng_cat', FILTER_SANITIZE_STRING);
        $hnCat = filter_input(INPUT_POST, 'hin_cat', FILTER_SANITIZE_STRING);
        $updatedBy = $_SESSION['user_id'];

        $database = new Database();
        $pdo = $database->getConnection();

        $categoryModel = new CategoryModel($pdo);

        if ($categoryModel->updateCategory($id, $engCat, $hnCat, $updatedBy)) {
            $_SESSION['message'] = "Category updated successfully.";
            header("Location: ../../edit-category.php?id=" . $id);
            exit;
        } else {
            $_SESSION['error'] = "Failed to updated category.";
            header("Location: ../../edit-category.php?id=" . $id);
            exit;
        }
    }
    public function softDeleteCategory()
    {
        session_start();
        $id = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_NUMBER_INT);
        $updatedBy = $_SESSION['user_id'];

        // Initialize database connection and model
        $database = new Database();
        $pdo = $database->getConnection();
        $categoryModel = new CategoryModel($pdo);

        // Attempt to soft delete the category
        $cat = $categoryModel->softDeleteCategory($id, $updatedBy);

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

$controller = new CategoryController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if (!empty($action) && $action == 'updateCategory') {
            $controller->updateCategory();
        } elseif (!empty($action) && $action == 'deleteCategory') {
            $controller->softDeleteCategory();
        }
    } else {
        $controller->insertCategory();
    }
} else {
    $controller->showCategories();
}
