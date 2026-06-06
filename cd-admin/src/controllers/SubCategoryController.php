<?php

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../models/SubCategoryModel.php';

class SubCategoryController
{
    public function insertSubCategory()
    {
        // Check if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        // Start session and retrieve inputs
        session_start();
        $domainId = filter_input(INPUT_POST, 'domainId', FILTER_SANITIZE_STRING);
        $engsubCatName = filter_input(INPUT_POST, 'engsubCatName', FILTER_SANITIZE_STRING);
        $hinsubCatName = filter_input(INPUT_POST, 'hinsubCatName', FILTER_SANITIZE_STRING);
        $categoryId = filter_input(INPUT_POST, 'categoryId', FILTER_SANITIZE_NUMBER_INT);
        $createdBy = $_SESSION['user_id'];

        // Establish a database connection
        $database = new Database();
        $pdo = $database->getConnection();

        if ((int)$domainId === 0 && (int)$categoryId > 0) {
            $stmt = $pdo->prepare("SELECT domain_id FROM category_master WHERE id = :categoryId LIMIT 1");
            $stmt->execute([':categoryId' => $categoryId]);
            $domainId = (string) $stmt->fetchColumn();
        }

        // Instantiate the SubCategoryModel
        $subCategoryModel = new SubCategoryModel($pdo);

        // Insert the sub-category
        if ($subCategoryModel->insertSubCategory($domainId, $engsubCatName, $hinsubCatName,  $categoryId, $createdBy)) {

            $_SESSION['message'] = "Sub Category added successfully.";
            header("Location: ../../create-sub-category.php");
        } else {
            $_SESSION['error'] = "Failed to create sub category.";
            header("Location: ../../create-sub-category.php");
            exit;
        }
    }

    public function showSubCategoryForm()
    {
        // Establish a database connection
        $database = new Database();
        $pdo = $database->getConnection();

        // Instantiate the CategoryModel
        $categoryModel = new CategoryModel($pdo);

        // Fetch all categories
        $categories = $categoryModel->getAllCategories();

        // Include the sub-category form view page and pass the categories data
        include __DIR__ . '/../views/sub_category_form.php';
    }

    public function updateSubCategory()
    {
        session_start();
        $id = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_NUMBER_INT);
        $domainId = filter_input(INPUT_POST, 'domainId', FILTER_SANITIZE_STRING);
        $categoryId = filter_input(INPUT_POST, 'categoryId', FILTER_SANITIZE_STRING);
        $eng_sub_cat = filter_input(INPUT_POST, 'eng_sub_cat', FILTER_SANITIZE_STRING);
        $hin_sub_cat = filter_input(INPUT_POST, 'hin_sub_cat', FILTER_SANITIZE_STRING);
        $updatedBy = $_SESSION['user_id'];

        $database = new Database();
        $pdo = $database->getConnection();

        $subCategoryModel = new SubCategoryModel($pdo);

        if ($subCategoryModel->updateSubCategory($id, $domainId, $eng_sub_cat, $hin_sub_cat, $categoryId, $updatedBy)) {
            $_SESSION['message'] = "Sub Category updated successfully.";
            header("Location: ../../edit-sub-category.php?id=" . $id);
            exit;
        } else {
            $_SESSION['error'] = "Failed to create sub category.";
            header("Location: ../../edit-sub-category.php?id=" . $id);
            exit;
        }
    }

    public function softDeleteSubCategory()
    {
        session_start();
        $id = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_NUMBER_INT);
        $updatedBy = $_SESSION['user_id'];

        $database = new Database();
        $pdo = $database->getConnection();

        $subCategoryModel = new SubCategoryModel($pdo);

        if ($subCategoryModel->softDeleteSubCategory($id, $updatedBy)) {
            $_SESSION['message'] = "Sub Category added successfully.";
            // header("Location: ../../manage-sub-category.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to create sub category.";
            // header("Location: ../../manage-sub-category.php");
            exit;
        }
    }
}

$controller = new SubCategoryController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if (!empty($action) && $action == 'updateSubCategory') {
            $controller->updateSubCategory();
        } elseif (!empty($action) && $action == 'deleteSubCategory') {
            $controller->softDeleteSubCategory();
        }
    } else {
        $controller->insertSubCategory();
    }
} else {
    $controller->showSubCategoryForm();
}
