<?php
session_start();
require_once "../../database/Database.php";
class NoticeController
{
    private $pdo;
    private $user_id;

    public function __construct()
    {
        session_start();

        if (!isset($_SESSION['user_id']) || $_SESSION['login'] == 0) {
            header('Location: index.php');
            exit;
        }

        $this->user_id = $_SESSION['user_id'];
        $database = new Database();
        $this->pdo = $database->getConnection();

    }


    private function generateUniqueTenderID($length = 6)
    {
        do {
            $newsID = substr(str_shuffle(str_repeat('0123456789', $length)), 0, $length);
        } while ($this->newsIDExists($newsID));
        return $newsID;
    }

    private function newsIDExists($newsID)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM tenders WHERE uniq_id = ?");
        $stmt->execute([$newsID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    private function createDirectoryIfNotExists($baseDir1)
    {
        $baseDir = '../../' . rtrim($baseDir1, '/') . '/';
        $uploadsDir = "$baseDir";
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }
        return "$baseDir1";
    }

    private function pdfUpload($pdf_attach, $path, $allowedFile)
    {
        if (!isset($_FILES[$pdf_attach]) || $_FILES[$pdf_attach]['error'] == UPLOAD_ERR_NO_FILE) {
            return null; // No file uploaded, return null
        }

        if ($_FILES[$pdf_attach]["name"] != '') {
            $allowed_extensions = $allowedFile;
            $file_ext = pathinfo($_FILES[$pdf_attach]['name'], PATHINFO_EXTENSION);
            $file_size = $_FILES[$pdf_attach]['size'] / 1024 / 1024; // Convert to MB

            if (!in_array(strtolower($file_ext), $allowed_extensions)) {
                $_SESSION['error_message'] = "Invalid file type.";
                header('Location: ../../../create-notice.php');
                exit;
            }

            if ($file_size > 1) {
                $_SESSION['error_message'] = "File size exceeds 1MB limit.";
                header('Location: ../postTenders.php');
                exit;
            }

            $uploads_dir = $this->createDirectoryIfNotExists($path);
            $uniqueName = uniqid() . "_" . $_FILES[$pdf_attach]['name'];
            $file_path = "$uploads_dir/$uniqueName";

            // die($file_path);
            // move_uploaded_file($_FILES[$pdf_attach]["tmp_name"], $file_path);
            // return "$uploads_dir/$uniqueName";

            // Move file to destination
            if (move_uploaded_file($_FILES[$pdf_attach]["tmp_name"], '../../' . $file_path)) {
                return "$uploads_dir/$uniqueName";
            } else {
                return null; // Return null if upload fails
            }

        }
        return null;
    }

    public function insertNotice()
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../create-notice.php');
            exit;
        }

        $errors = [];
        $_SESSION['post'] = $_POST; // Store previous input values

        if (empty($_POST['noticeCategory'])) {
            $errors['noticeCategory'] = "Tender Category is required";
        }

        if (empty($_POST['referenceNo'])) {
            $errors['referenceNo'] = "Ref No is required";
        }

        if (empty($_POST['dated'])) {
            $errors['dated'] = "Dated is required";
        }

        if (empty($_POST['dated'])) {
            $errors['dated'] = "Dated is required";
        }

        if (empty($_POST['noticeTitle'])) {
            $errors['noticeTitle'] = "Notice Title is required";
        }

        if (!isset($_FILES['attachNotice']) || $_FILES['attachNotice']['error'] == UPLOAD_ERR_NO_FILE) {
            $_SESSION['error_message'] = "Notice Attachment is required.";
            header("Location: ../../create-notice.php"); // Redirect to form
            exit();
        }

        // If errors exist, store them in session and redirect back to the form
        if (!empty($errors)) {
            $_SESSION['req_error_msg'] = $errors;
            $_SESSION['error_message'] = "Please fix the errors and try again.";
            header("Location: ../../create-notice.php"); // Redirect to form
            exit();
        }

        $year = date('Y');
        $uniqueTenderID = $this->generateUniqueTenderID();
        $notice_category = $_POST['noticeCategory'] ?? null;
        $notice_ref_no = $_POST['referenceNo'] ?? null;
        $tender_dated = $_POST['dated'] ?? null;
        $notice_title = $_POST['noticeTitle'] ?? null;

        $new_tag = $_POST['new_tag'] ?? null;
        $newTagDays = $_POST['newTagDays'] ?? null;

        $notice_path = $this->pdfUpload('attachNotice', "uploads/Notice/$year/$uniqueTenderID", ['pdf']);

        if ($notice_path) {

            try {
                $this->pdo->beginTransaction();

                $stmt = $this->pdo->prepare(
                    "INSERT INTO notices (uniq_id, notice_category, notice_title, notice_ref_no, notice_dated, notice_path, notice_new_tag, notice_newtag_days,  created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $uniqueTenderID,
                    $notice_category,
                    $notice_title,
                    $notice_ref_no,
                    $tender_dated,
                    $notice_path,
                    $new_tag,
                    $newTagDays,
                    $_SESSION['user_id']
                ]);
                $this->pdo->commit();

                unset($_SESSION['post']);
                unset($_SESSION['req_error_msg']);

                $_SESSION['success_message'] = "Notice has been posted successfully.";
                header('Location: ../../../create-notice.php');
                exit;
            } catch (Exception $e) {
                $this->pdo->rollBack();
                $_SESSION['error_message'] = "Error: " . $e->getMessage();
                header('Location: ../../../create-notice.php');
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Something went wrong, Please try Again or Contact Admin.";
            header("Location: ../../create-notice.php"); // Redirect to form
            exit();
        }

    }

    public function softDelete()
    {
        session_start();

        $post = filter_input(INPUT_POST, 'ed');

        $stmt = $this->pdo->prepare("SELECT * FROM notices WHERE uniq_id = :id AND is_deleted = '0'");
        $stmt->bindParam(':id', $post, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $sql = "UPDATE notices SET is_deleted='1' WHERE uniq_id= :id";

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindParam(':id', $post, PDO::PARAM_INT);

            $data = $stmt->execute();

            if ($data) {
                $_SESSION['message'] = "Notices deleted successfully.";
                header('Location: ../../../manage-notices.php');
                exit;

            } else {
                $_SESSION['error_message'] = "Failed to delete data.";
                header('Location: ../../../manage-notices.php');
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Notices Record not found!.";
            header('Location: ../../../manage-notices.php');
            exit;
        }

    }
}

$controller = new NoticeController();


if (isset($_POST['ed']) && $_POST['ed']) {
    $controller->softDelete();
} else {
    $controller->insertNotice();
}
?>