<?php

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../models/SubCategoryModel.php';

class ChildSubCategoryController
{
    public function insertChildSubCategory()
    {
        // Check if request method is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        // Start session and retrieve inputs
        session_start();
        $chsubCatName = filter_input(INPUT_POST, 'chsubCatName', FILTER_SANITIZE_STRING);
        $chhnSubCatName = filter_input(INPUT_POST, 'chhnSubCatName', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $domainId = filter_input(INPUT_POST, 'domainId', FILTER_SANITIZE_NUMBER_INT);
        $categoryId = filter_input(INPUT_POST, 'categoryId', FILTER_SANITIZE_NUMBER_INT);
        $subcategoryId = filter_input(INPUT_POST, 'subCategoryId', FILTER_SANITIZE_NUMBER_INT);
        $createdBy = $_SESSION['user_id'];

        // Establish a database connection
        $database = new Database();
        $pdo = $database->getConnection();

        if ((int)$domainId === 0 && (int)$categoryId > 0) {
            $stmt = $pdo->prepare("SELECT domain_id FROM category_master WHERE id = :categoryId LIMIT 1");
            $stmt->execute([':categoryId' => $categoryId]);
            $domainId = (int) $stmt->fetchColumn();
        }

        // Instantiate the SubCategoryModel
        $subCategoryModel = new SubCategoryModel($pdo);

        // Insert the sub-category
        if ($subCategoryModel->insertChildSubCategory($domainId, $chsubCatName, $chhnSubCatName, $description, $subcategoryId, $categoryId, $createdBy)) {

            $_SESSION['message'] = "Child Sub Category added successfully.";
            header("Location: ../../create-child-sub-category.php");
        } else {
            $_SESSION['error'] = "Failed to create child sub category.";
            header("Location: ../../create-child-sub-category.php");
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

    public function updateChildSubCategory()
    {
        session_start();
        $id = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_NUMBER_INT);
        $chsubCatName = filter_input(INPUT_POST, 'chsubCatName', FILTER_SANITIZE_STRING);
        $chhnSubCatName = filter_input(INPUT_POST, 'chhnSubCatName', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $domainId = filter_input(INPUT_POST, 'domainId', FILTER_SANITIZE_NUMBER_INT);
        $categoryId = filter_input(INPUT_POST, 'categoryId', FILTER_SANITIZE_NUMBER_INT);
        $subcategoryId = filter_input(INPUT_POST, 'subCategoryId', FILTER_SANITIZE_NUMBER_INT);
        $updatedBy = $_SESSION['user_id'];


        $database = new Database();
        $pdo = $database->getConnection();

        if ((int)$domainId === 0 && (int)$categoryId > 0) {
            $stmt = $pdo->prepare("SELECT domain_id FROM category_master WHERE id = :categoryId LIMIT 1");
            $stmt->execute([':categoryId' => $categoryId]);
            $domainId = (int) $stmt->fetchColumn();
        }

        $subCategoryModel = new SubCategoryModel($pdo);

        if ($subCategoryModel->updateChildSubCategory($id, $domainId, $chsubCatName, $chhnSubCatName, $description, $categoryId, $subcategoryId, $updatedBy)) {
            $_SESSION['message'] = "Child Sub Category updated successfully.";
            header("Location: ../../edit-child-sub-category.php?id=" . $id);
            exit;
        } else {
            $_SESSION['error'] = "Failed to create child sub category.";
            header("Location: ../../edit-child-sub-category.php?id=" . $id);
            exit;
        }
    }

    public function softDeleteChildSubCategory()
    {
        session_start();
        $id = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_NUMBER_INT);
        $updatedBy = $_SESSION['user_id'];

        $database = new Database();
        $pdo = $database->getConnection();

        $subCategoryModel = new SubCategoryModel($pdo);

        if ($subCategoryModel->softDeleteChildSubCategory($id, $updatedBy)) {
            $_SESSION['message'] = "Child Sub Category added successfully.";
            // header("Location: ../../manage-sub-category.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to create child sub category.";
            // header("Location: ../../manage-sub-category.php");
            exit;
        }
    }

}

$controller = new ChildSubCategoryController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if (!empty($action) && $action == 'updateChildSubCategory') {
            $controller->updateChildSubCategory();
        } elseif (!empty($action) && $action == 'deleteChildSubCategory') {
            $controller->softDeleteChildSubCategory();
        }
    } else {
        $controller->insertChildSubCategory();
    }

} else {
    $controller->showSubCategoryForm();
}

?>
