<?php

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../models/PostingModel.php';

class PostingController
{
    public function getSessionYear($event_date)
    {
        // Extract the year and month from the event date
        $year = date('Y', strtotime($event_date));
        $month = date('m', strtotime($event_date));

        // Check if the month is before or after April
        if ($month >= 4) {
            // If the month is April or later, the session year is the current year to the next year
            return $year . '-' . ($year + 1);
        } else {
            // If the month is before April, the session year is the previous year to the current year
            return ($year - 1) . '-' . $year;
        }
    }


    public function saveData()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        session_start();

        $database = new Database();
        $pdo = $database->getConnection();

        $postingModel = new PostingModel($pdo);

        $data = [
            'sub_category' => filter_input(INPUT_POST, 'noticeCategory', FILTER_SANITIZE_NUMBER_INT),
            // 'document_no' => filter_input(INPUT_POST, 'referenceNo', FILTER_SANITIZE_STRING),
            'dated' => filter_input(INPUT_POST, 'dated', FILTER_SANITIZE_STRING),
            'reference_no' => filter_input(INPUT_POST, 'referenceNo', FILTER_SANITIZE_STRING),
            // 'reference_date' => filter_input(INPUT_POST, 'ref_date', FILTER_SANITIZE_STRING),
            'title' => filter_input(INPUT_POST, 'noticeTitle', FILTER_SANITIZE_STRING),
            'new_flag' => filter_input(INPUT_POST, 'new_tag', FILTER_SANITIZE_STRING),
            'new_no_of_days' => filter_input(INPUT_POST, 'newTagDays', FILTER_SANITIZE_NUMBER_INT),
            // 'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING),
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ];

        $data['category'] = 2; // Fixed the category id "General Postings"

        $data['session_year'] = $this->getSessionYear($data['dated']);

        if (isset($_FILES['attachNotice']) && $_FILES['attachNotice']['error'] === UPLOAD_ERR_OK) {
            $attachment = $_FILES['attachNotice'];

            $allowedTypes = ['image/jpeg', 'image/jpg', 'application/pdf'];
            $maxFileSize = 1 * 1024 * 1024; // 1 MB limit

            if (!in_array($attachment['type'], $allowedTypes)) {
                $_SESSION['error'] = "Invalid file type.";
                header("Location: ../../create-notice.php");
                exit;
            }

            if ($attachment['size'] > $maxFileSize) {
                $_SESSION['error'] = "File size exceeds the limit.";
                header("Location: ../../create-notice.php");
                exit;
            }

            $category = $postingModel->getCategoryName($data['category']);
            $catName = $category[0]['category_name'];

            $subcategory = $postingModel->getSubCategoryName($data['sub_category']);
            $subCatName = $subcategory[0]['sub_category_name'];

            if (!empty($data['sub_category'])) {
                $currentYear = date('Y');
                $currentMon = date('m');

                // Create base directories
                $saveDir = "/uploads/$catName/$subCatName/$currentYear/$currentMon/";
                $uploadDir = dirname(dirname(__FILE__)) . '/uploads/' . $catName . '/' . $subCatName . '/' . $currentYear . '/' . $currentMon . '/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
            }

            $data['uniq_id'] = $postingModel->generateUniqueSixDigitID();

            $originalName = basename($attachment['name']);
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);

            // Generate unique name
            $uniqueName = $data['uniq_id'] . '.' . $extension;

            // Final paths
            $targetFile = "$saveDir$uniqueName";
            $targetUploadDir = "$uploadDir$uniqueName";

            if (!move_uploaded_file($attachment['tmp_name'], $targetUploadDir)) {
                $_SESSION['error'] = "Failed to upload the file.";
                header("Location: ../../create-notice.php");
                exit;
            }

            $data['attachment'] = $targetFile;
        } else {
            $_SESSION['error'] = "No file uploaded or file upload error.";
            header("Location: ../../create-notice.php");
            exit;
        }



        if ($postingModel->saveData($data)) {
            $_SESSION['message'] = "Data saved successfully.";
            header("Location: ../../create-notice.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to save data.";
            header("Location: ../../create-notice.php");
            exit;
        }
    }

    public function fetchSubCategories()
    {
        $categoryId = filter_input(INPUT_GET, 'categoryId', FILTER_SANITIZE_NUMBER_INT);

        $database = new Database();
        $pdo = $database->getConnection();

        $postingModel = new PostingModel($pdo);

        $subCategories = $postingModel->fetchSubCategoriesByCategoryId($categoryId);

        header('Content-Type: application/json');
        echo json_encode($subCategories);
        exit;
    }

    public function fetchType()
    {
        $categoryId = filter_input(INPUT_GET, 'categoryId', FILTER_SANITIZE_NUMBER_INT);

        $database = new Database();
        $pdo = $database->getConnection();

        $postingModel = new PostingModel($pdo);

        $subCategories = $postingModel->fetchTypeByCategoryId($categoryId);

        header('Content-Type: application/json');
        echo json_encode($subCategories);
        exit;
    }

    public function softDelete()
    {
        session_start();

        $post = filter_input(INPUT_POST, 'post', FILTER_SANITIZE_STRING);

        $database = new Database();
        $pdo = $database->getConnection();
        $postingModel = new PostingModel($pdo);

        $data = $postingModel->deletePost($post);

        if ($data) {
            $_SESSION['message'] = "Data deleted successfully.";
            header("Location: ../../manage-postings.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to delete data.";
            header("Location: ../../manage-postings.php");
            exit;
        }
    }

    public function DeleteAttach()
    {
        session_start();
        $post = filter_input(INPUT_POST, 'post', FILTER_SANITIZE_STRING);

        // Create database connection
        $database = new Database();
        $pdo = $database->getConnection();
        $postingModel = new PostingModel($pdo);

        // Try to delete the attachment
        $data = $postingModel->deleteAttach($post);

        // Return JSON response instead of redirecting
        header('Content-Type: application/json');

        if ($data) {
            echo json_encode([
                'success' => true,
                'message' => 'Data deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to delete data.'
            ]);
        }
        exit;
    }

    public function restorePost()
    {
        session_start();

        $post = filter_input(INPUT_POST, 'uid', FILTER_SANITIZE_STRING);

        $database = new Database();
        $pdo = $database->getConnection();

        $postingModel = new PostingModel($pdo);

        $data = $postingModel->restorePost($post);

        // print_r($data);
        // var_dump($data);
        // die('sdf');

        if ($data == 'no_cat') {
            $_SESSION['error'] = "Category not found. Can't be restore this post.";
            header("Location: ../../trash-postings.php");
            exit;
        }

        if ($data) {
            $_SESSION['message'] = "Post restore successfully.";
            header("Location: ../../trash-postings.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to restore post.";
            header("Location: ../../trash-postings.php");
            exit;
        }
    }

    public function searchCatType()
    {
        $categoryId = filter_input(INPUT_GET, 'categoryId', FILTER_SANITIZE_NUMBER_INT);
        $type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_NUMBER_INT);

        $database = new Database();
        $pdo = $database->getConnection();

        $postingModel = new PostingModel($pdo);

        $subCategories = $postingModel->searchTypeCategoryId($categoryId, $type);

        header('Content-Type: application/json');
        echo json_encode($subCategories);
        exit;
    }

    public function hideNotice()
    {
        session_start();

        $post = filter_input(INPUT_POST, 'post');
        $database = new Database();
        $pdo = $database->getConnection();

        $stmt = $pdo->prepare("SELECT * FROM notices WHERE uniq_id = :id AND is_deleted = '0'");
        $stmt->bindParam(':id', $post, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $hideValue = $result['status'] == 'I' ? 'A' : 'I';

            $sql = "UPDATE notices SET status='$hideValue' WHERE uniq_id= :id";

            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':id', $post, PDO::PARAM_INT);

            $data = $stmt->execute();

            header('Content-Type: application/json');

            if ($data) {
                $_SESSION['message'] = "Notice " . ($result['status'] == 'I' ? 'Hide' : 'Unhide') . " successfully.";
                echo json_encode([
                    'success' => true,
                    'message' => 'Data hide successfully.'
                ]);
            } else {
                $_SESSION['error'] = "Failed to hide data.";
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to hide data.'
                ]);
            }
            exit;
        } else {
            $_SESSION['error'] = "News Record not found!.";
            echo json_encode([
                'success' => false,
                'message' => 'Failed data.'
            ]);
        }

    }
}

$controller = new PostingController();

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);

$postAction = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);


if ($action === 'fetchSubCategories') {
    $controller->fetchSubCategories();
}

if ($action === 'fetchType') {
    $controller->fetchType();
}

if ($action === 'searchCatType') {
    $controller->searchCatType();
}


switch ($postAction) {
    case "deletePost":
        $controller->softDelete();
        break;
    case "deleteAttach":
        $controller->DeleteAttach();
        break;
    case "restore":
        $controller->restorePost();
        break;
    case "hideNotice":
        $controller->hideNotice();
        break;
    default:
        $controller->saveData();
        break;
}
