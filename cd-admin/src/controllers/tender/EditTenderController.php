<?php
// EditTenderController.php - Complete edit controller for tenders

session_start();
require_once __DIR__ . '/../../database/Database.php';

class EditTenderController
{
    private $pdo;

    public function __construct()
    {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            $_SESSION['error_message'] = "Session expired. Please login again.";
            header('Location: ../../../index.php');
            exit;
        }

        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    /**
     * Create directory if not exists
     */
    private function createDirectoryIfNotExists($baseDir)
    {
        $fullPath = __DIR__ . '/../../../' . rtrim($baseDir, '/');
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0777, true);
        }
        return $baseDir;
    }

    /**
     * Upload file handler
     */
    private function uploadFile($fileInputName, $path, $allowedExtensions, $maxSizeMB = 5)
    {
        if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] == UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_message'] = "File upload error for " . $fileInputName;
            return null;
        }

        $fileName = $_FILES[$fileInputName]['name'];
        $fileTmp = $_FILES[$fileInputName]['tmp_name'];
        $fileSize = $_FILES[$fileInputName]['size'] / 1024 / 1024; // Convert to MB
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate extension
        if (!in_array($fileExt, $allowedExtensions)) {
            $_SESSION['error_message'] = "Invalid file type for " . $fileName . ". Allowed: " . implode(', ', $allowedExtensions);
            return null;
        }

        // Validate size
        if ($fileSize > $maxSizeMB) {
            $_SESSION['error_message'] = "File size exceeds {$maxSizeMB}MB limit for " . $fileName;
            return null;
        }

        // Create directory if needed
        $uploadPath = $this->createDirectoryIfNotExists($path);
        $uniqueName = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
        $filePath = $uploadPath . '/' . $uniqueName;
        $fullFilePath = __DIR__ . '/../../../' . $filePath;

        if (move_uploaded_file($fileTmp, $fullFilePath)) {
            return $filePath;
        }

        $_SESSION['error_message'] = "Failed to upload " . $fileName;
        return null;
    }

    /**
     * Update tender
     */
    public function updateTender()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../manage-tenders.php');
            exit;
        }

        if (!isset($_POST['tender_id']) || empty($_POST['tender_id'])) {
            $_SESSION['error'] = "Tender ID is required.";
            header('Location: ../../../manage-tenders.php');
            exit;
        }

        $tenderId = $_POST['tender_id'];

        // Fetch current tender data
        $stmt = $this->pdo->prepare("SELECT * FROM tender_notice WHERE id = ? AND is_deleted = 0");
        $stmt->execute([$tenderId]);
        $currentTender = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentTender) {
            $_SESSION['error'] = "Tender not found.";
            header('Location: ../../../manage-tenders.php');
            exit;
        }

        // Validation
        $errors = [];

        if (empty($_POST['tender_number'])) {
            $errors['tender_number'] = "NIT No. is required";
        }

        if (empty($_POST['title'])) {
            $errors['title'] = "Title is required";
        }

        if (empty($_POST['department_id'])) {
            $errors['department_id'] = "Department is required";
        }

        if (empty($_POST['tender_type_id'])) {
            $errors['tender_type_id'] = "Tender category is required";
        }

        if (empty($_POST['publish_date'])) {
            $errors['publish_date'] = "Publish date is required";
        }

        if (empty($_POST['closing_date'])) {
            $errors['closing_date'] = "Closing date is required";
        }

        if (empty($_POST['opening_date'])) {
            $errors['opening_date'] = "Opening date is required";
        }

        // If errors exist, redirect back
        if (!empty($errors)) {
            $_SESSION['req_error_msg'] = $errors;
            $_SESSION['error'] = "Please fix the errors below.";
            header("Location: ../../../edit-tender.php?id=" . $tenderId);
            exit;
        }

        // Prepare data for update
        $tenderNumber = trim($_POST['tender_number']);
        $referenceNumber = trim($_POST['reference_number'] ?? '');
        $title = trim($_POST['title']);
        $description = trim($_POST['description'] ?? '');
        $departmentId = $_POST['department_id'];
        $tenderTypeId = $_POST['tender_type_id'];
        $publishDate = date('Y-m-d H:i:s', strtotime($_POST['publish_date']));
        $closingDate = date('Y-m-d H:i:s', strtotime($_POST['closing_date']));
        $openingDate = date('Y-m-d H:i:s', strtotime($_POST['opening_date']));
        $bidUrl = trim($_POST['bid_url'] ?? '');
        $status = $_POST['status'] ?? 'Draft';

        // Handle PDF upload
        $pdfPath = $currentTender['pdf_path'];
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $financialYear = date('Y');
            $uploadDir = "uploads/tenders/{$financialYear}/{$tenderId}";
            $newPdfPath = $this->uploadFile('pdf_file', $uploadDir, ['pdf'], 10);
            if ($newPdfPath) {
                $pdfPath = $newPdfPath;
            }
        }

        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE tender_notice SET 
                        tender_number = :tender_number,
                        reference_number = :reference_number,
                        title = :title,
                        description = :description,
                        department_id = :department_id,
                        tender_type_id = :tender_type_id,
                        publish_date = :publish_date,
                        closing_date = :closing_date,
                        opening_date = :opening_date,
                        bid_url = :bid_url,
                        pdf_path = :pdf_path,
                        status = :status,
                        updated_by = :updated_by,
                        updated_at = NOW()
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $tenderId,
                ':tender_number' => $tenderNumber,
                ':reference_number' => $referenceNumber,
                ':title' => $title,
                ':description' => $description,
                ':department_id' => $departmentId,
                ':tender_type_id' => $tenderTypeId,
                ':publish_date' => $publishDate,
                ':closing_date' => $closingDate,
                ':opening_date' => $openingDate,
                ':bid_url' => $bidUrl,
                ':pdf_path' => $pdfPath,
                ':status' => $status,
                ':updated_by' => $_SESSION['user_id']
            ]);

            $this->pdo->commit();

            unset($_SESSION['post']);
            unset($_SESSION['req_error_msg']);

            $_SESSION['message'] = "Tender updated successfully.";
            header('Location: ../../../manage-tenders.php');
            exit;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ../../../edit-tender.php?id=' . $tenderId);
            exit;
        }
    }

    /**
     * Soft delete tender
     */
    public function softDelete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../../manage-tenders.php');
            exit;
        }

        $tenderId = filter_input(INPUT_POST, 'tender_id', FILTER_SANITIZE_NUMBER_INT);

        if (!$tenderId) {
            $_SESSION['error'] = "Invalid tender ID.";
            header('Location: ../../../manage-tenders.php');
            exit;
        }

        // Check if tender exists
        $stmt = $this->pdo->prepare("SELECT * FROM tender_notice WHERE id = :id AND is_deleted = 0");
        $stmt->execute([':id' => $tenderId]);
        $tender = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tender) {
            $_SESSION['error'] = "Tender not found.";
            header('Location: ../../../manage-tenders.php');
            exit;
        }

        try {
            $sql = "UPDATE tender_notice SET is_deleted = 1, updated_by = :updated_by, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $tenderId,
                ':updated_by' => $_SESSION['user_id']
            ]);

            $_SESSION['message'] = "Tender deleted successfully.";
            header('Location: ../../../manage-tenders.php');
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to delete tender: " . $e->getMessage();
            header('Location: ../../../manage-tenders.php');
            exit;
        }
    }

    /**
     * Update tender status only
     */
    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../../manage-tenders.php');
            exit;
        }

        $tenderId = filter_input(INPUT_POST, 'tender_id', FILTER_SANITIZE_NUMBER_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

        if (!$tenderId || !$status) {
            $_SESSION['error'] = "Invalid request.";
            header('Location: ../../../manage-tenders.php');
            exit;
        }

        try {
            $sql = "UPDATE tender_notice SET status = :status, updated_by = :updated_by, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $tenderId,
                ':status' => $status,
                ':updated_by' => $_SESSION['user_id']
            ]);

            $_SESSION['message'] = "Status updated successfully.";
            header('Location: ../../../manage-tenders.php');
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to update status.";
            header('Location: ../../../manage-tenders.php');
            exit;
        }
    }
}

// Route handling
$controller = new EditTenderController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_tender']) && $_POST['delete_tender'] == 1) {
        $controller->softDelete();
    } elseif (isset($_POST['update_status']) && $_POST['update_status'] == 1) {
        $controller->updateStatus();
    } else {
        $controller->updateTender();
    }
} else {
    header('Location: ../../../manage-tenders.php');
    exit;
}
?>