<?php
require_once __DIR__ . '/../models/UpdatePostModel.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../models/PostingModel.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

class UpdatePostController
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
    public function updatePosting()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        session_start();

        $database = new Database();
        $pdo = $database->getConnection();

        $Model = new UpdatePostModel($pdo);

        $postingId = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

        $stmt = $pdo->prepare("SELECT * FROM notices WHERE uniq_id = :postingId");
        $stmt->bindParam(':postingId', $postingId, PDO::PARAM_INT);
        $stmt->execute();
        $existingData = $stmt->fetch(PDO::FETCH_ASSOC);

        $subCategoryId = $_POST['subCategory'];
        $stmt = $pdo->prepare(
            "SELECT category_id 
            FROM sub_category 
            WHERE id = :subCategoryId
            AND is_deleted = '0'
            LIMIT 1"
        );

        $stmt->execute(['subCategoryId' => $subCategoryId]);
        $catgeoryId = $stmt->fetchColumn();

        $domainId = filter_input(INPUT_POST, 'domainId', FILTER_SANITIZE_NUMBER_INT);
        $categoryid = $catgeoryId;
        $sub_category = filter_input(INPUT_POST, 'subCategory', FILTER_SANITIZE_NUMBER_INT);
        $child_sub_category = filter_input(INPUT_POST, 'childSubCategoryId', FILTER_SANITIZE_NUMBER_INT);
        $document_no = filter_input(INPUT_POST, 'doc_nos', FILTER_SANITIZE_STRING);
        $dated = filter_input(INPUT_POST, 'doc_date', FILTER_SANITIZE_STRING);
        $reference_no = filter_input(INPUT_POST, 'ref_nos', FILTER_SANITIZE_STRING);
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $new_flag = filter_input(INPUT_POST, 'new_tag', FILTER_SANITIZE_STRING);
        $new_no_of_days = filter_input(INPUT_POST, 'new_tag_day', FILTER_SANITIZE_NUMBER_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $updated_by = $_SESSION['user_id'];

        $data = [];

        $data['category'] = 2; // Fixed the category id "General Postings"
        $data['session_year'] = $this->getSessionYear($dated);

        $postingModel = new PostingModel($pdo);

        $data['domainId'] = ($sub_category === $existingData['domain_id']) ? $existingData['domain_id'] : $domainId;
        $data['categoryId'] = ($sub_category === $existingData['notice_category']) ? $existingData['notice_category'] : $categoryid;
        $data['sub_category'] = ($sub_category === $existingData['notice_subcategory']) ? $existingData['notice_subcategory'] : $sub_category;
        $data['child_sub_category'] = ($sub_category === $existingData['notice_childsubcategory']) ? $existingData['notice_childsubcategory'] : $child_sub_category;
        $data['notice_dated'] = ($dated === $existingData['notice_dated']) ? $existingData['notice_dated'] : $dated;
        $data['notice_ref_no'] = ($reference_no === $existingData['notice_ref_no']) ? $existingData['notice_ref_no'] : $reference_no;
        $data['notice_title'] = ($title === $existingData['notice_title']) ? $existingData['notice_title'] : $title;
        $data['notice_new_tag'] = ($new_flag === $existingData['notice_new_tag']) ? $existingData['notice_new_tag'] : $new_flag;
        $data['notice_new_tag_days'] = ($new_no_of_days === $existingData['notice_new_tag_days']) ? $existingData['notice_new_tag_days'] : $new_no_of_days;
        $data['status'] = ($status === $existingData['status']) ? $existingData['status'] : $status;

        $data['uniq_id'] = $postingId;
        $data['ip_address'] = $ip_address;
        $data['updated_by'] = $updated_by;

        $notice_type = filter_input(INPUT_POST, 'notice_type');

        switch ($notice_type) {
            case 'F':
                if (empty($existingData['notice_path'])) {

                    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                        $attachment = $_FILES['attachment'];

                        $allowedTypes = ['image/jpeg', 'image/jpg', 'application/pdf'];
                        $maxFileSize = 1 * 1024 * 1024; // 2 MB limit

                        if (!in_array($attachment['type'], $allowedTypes)) {
                            $_SESSION['error'] = "Invalid file type.";
                            header("Location: ../../edit-notices.php?id=" . $data['uniq_id']);
                            exit;
                        }

                        if ($attachment['size'] > $maxFileSize) {
                            $_SESSION['error'] = "File size exceeds the limit.";
                            header("Location: ../../edit-notices.php?id=" . $data['uniq_id']);
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

                        $originalName = basename($attachment['name']);
                        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

                        // Generate unique name
                        $uniqueName = $data['uniq_id'] . '.' . $extension;
                        $targetFile = $saveDir . $uniqueName;
                        $targetUploadDir = $uploadDir . $uniqueName;

                        if (!move_uploaded_file($attachment['tmp_name'], $targetUploadDir)) {
                            $_SESSION['error'] = "Failed to upload the file.";
                            header("Location: ../../edit-notices.php?id=" . $data['uniq_id']);
                            exit;
                        } else {
                            $data['attachment'] = $targetFile;
                        }


                    } else {
                        $_SESSION['error'] = "No file uploaded or file upload error.";
                        header("Location: ../../edit-notices.php?id=" . $data['uniq_id']);
                        exit;
                    }
                } else {
                    $data['attachment'] = $existingData['notice_path'];
                }
                $data['external_url'] = null;
                break;
            case 'E':
                $data['external_url'] = filter_input(INPUT_POST, 'attachNoticeURL');

                $isNewTab = filter_input(INPUT_POST, 'isNewTab');

                if (!empty($existingData['notice_path'])) {
                    $_SESSION['error'] = "Please delete file first, to save only External URL.";
                    header("Location: ../../edit-notices.php?id=" . $postingId);
                    exit;
                }

                if (strtolower($isNewTab) == 'no') {
                    $data['new_tab_open'] = '_self';
                } else {
                    $data['new_tab_open'] = '_blank';
                }

                $data['attachment'] = null;
                break;
            case 'Both':
                if (empty($existingData['notice_path'])) {

                    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                        $attachment = $_FILES['attachment'];

                        $allowedTypes = ['image/jpeg', 'image/jpg', 'application/pdf'];
                        $maxFileSize = 1 * 1024 * 1024; // 2 MB limit

                        if (!in_array($attachment['type'], $allowedTypes)) {
                            $_SESSION['error'] = "Invalid file type.";
                            header("Location: ../../edit-notices.php?id=" . $data['uniq_id']);
                            exit;
                        }

                        if ($attachment['size'] > $maxFileSize) {
                            $_SESSION['error'] = "File size exceeds the limit.";
                            header("Location: ../../edit-notices.php?id=" . $data['uniq_id']);
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

                        $originalName = basename($attachment['name']);
                        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

                        // Generate unique name
                        $uniqueName = $data['uniq_id'] . '.' . $extension;
                        $targetFile = $saveDir . $uniqueName;
                        $targetUploadDir = $uploadDir . $uniqueName;

                        if (!move_uploaded_file($attachment['tmp_name'], $targetUploadDir)) {
                            $_SESSION['error'] = "Failed to upload the file.";
                            header("Location: ../../edit-notices.php?id=" . $data['uniq_id']);
                            exit;
                        } else {
                            $data['attachment'] = $targetFile;
                        }


                    } else {
                        $_SESSION['error'] = "No file uploaded or file upload error.";
                        header("Location: ../../edit-notices.php?id=" . $data['uniq_id']);
                        exit;
                    }
                } else {
                    $data['attachment'] = $existingData['notice_path'];
                }
                $data['external_url'] = filter_input(INPUT_POST, 'attachNoticeURL');
                break;
            default:
                if (!empty($existingData['notice_path'])) {
                    $_SESSION['error'] = "Please delete file first, to save Neither File nor URL .";
                    header("Location: ../../edit-notices.php?id=" . $postingId);
                    exit;
                }
                $data['external_url'] = null;
                $data['attachment'] = null;
                break;
        }

        if ($Model->updatePosting($data)) {

            $_SESSION['message'] = "Posting updated successfully.";
            header("Location: ../../edit-notices.php?id=" . $postingId);
            exit;
        } else {
            $_SESSION['error'] = "Failed to update posting.";
            header("Location: ../../edit-notices.php?id=" . $postingId);
            exit;
        }
    }
}

$controller = new UpdatePostController();
$controller->updatePosting();

?>