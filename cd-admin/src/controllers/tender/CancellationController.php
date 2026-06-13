<?php
// CancellationController.php - Complete cancellation controller (Reason optional)

session_start();
require_once __DIR__ . '/../../database/Database.php';

class CancellationController
{
    private $pdo;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: 500?.?./index.php');
            exit;
        }

        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    /**
     * Upload PDF file
     */
    private function uploadFile($file)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/cancellations/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return "/uploads/cancellations/" . $fileName;
        }
        return null;
    }

    /**
     * Get tender details for AJAX preview
     */
    public function getTenderDetails()
    {
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? 0;
        
        $sql = "SELECT t.*, d.department_name 
                FROM tender_notice t
                LEFT JOIN departments d ON t.department_id = d.department_id
                WHERE t.id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $tender = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tender) {
            echo json_encode(['success' => true, 'tender' => $tender]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    /**
     * Create cancellation notice
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../../manage-cancellations.php');
            exit;
        }

        $tenderId = $_POST['tender_id'] ?? 0;
        $cancellationNo = trim($_POST['cancellation_no'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $reason = trim($_POST['reason'] ?? ''); // Optional - can be empty
        $cancellationDate = $_POST['cancellation_date'] ?? date('Y-m-d');
        
        // Upload PDF
        $pdfPath = $this->uploadFile($_FILES['pdf_file'] ?? null);

        try {
            $this->pdo->beginTransaction();
            
            // Insert cancellation record (reason is optional)
            $sql = "INSERT INTO tender_cancellation (tender_id, cancellation_no, title, reason, cancellation_date, pdf_path, created_by) 
                    VALUES (:tender_id, :cancellation_no, :title, :reason, :cancellation_date, :pdf_path, :created_by)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':tender_id' => $tenderId,
                ':cancellation_no' => $cancellationNo,
                ':title' => $title,
                ':reason' => $reason ?: null, // Save as NULL if empty
                ':cancellation_date' => $cancellationDate,
                ':pdf_path' => $pdfPath,
                ':created_by' => $_SESSION['user_id']
            ]);

            // Update tender status to Cancelled
            $updateSql = "UPDATE tender_notice SET status = 'Cancelled', updated_at = NOW() WHERE id = :tender_id";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([':tender_id' => $tenderId]);

            $this->pdo->commit();
            $_SESSION['message'] = "Cancellation notice created and tender status updated to 'Cancelled'.";
            header('Location: ../../../manage-cancellations.php');
            exit;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ../../../create-cancellation.php');
            exit;
        }
    }

    /**
     * Update cancellation notice
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../../manage-cancellations.php');
            exit;
        }

        $cancellationId = $_POST['cancellation_id'] ?? 0;
        $tenderId = $_POST['tender_id'] ?? 0;
        $cancellationNo = trim($_POST['cancellation_no'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $reason = trim($_POST['reason'] ?? ''); // Optional
        $cancellationDate = $_POST['cancellation_date'] ?? date('Y-m-d');
        
        // Get existing cancellation to check for old PDF
        $stmt = $this->pdo->prepare("SELECT pdf_path FROM tender_cancellation WHERE id = :id");
        $stmt->execute([':id' => $cancellationId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Handle PDF upload if new file provided
        $pdfPath = $existing['pdf_path'] ?? null;
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
            $newPdfPath = $this->uploadFile($_FILES['pdf_file']);
            if ($newPdfPath) {
                // Delete old PDF file
                if ($pdfPath && file_exists($_SERVER['DOCUMENT_ROOT'] . $pdfPath)) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . $pdfPath);
                }
                $pdfPath = $newPdfPath;
            }
        }

        try {
            $sql = "UPDATE tender_cancellation SET 
                        tender_id = :tender_id,
                        cancellation_no = :cancellation_no,
                        title = :title,
                        reason = :reason,
                        cancellation_date = :cancellation_date,
                        pdf_path = :pdf_path
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $cancellationId,
                ':tender_id' => $tenderId,
                ':cancellation_no' => $cancellationNo,
                ':title' => $title,
                ':reason' => $reason ?: null,
                ':cancellation_date' => $cancellationDate,
                ':pdf_path' => $pdfPath
            ]);

            $_SESSION['message'] = "Cancellation notice updated successfully.";
            header('Location: ../../../manage-cancellations.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ../../../edit-cancellation.php?id=' . $cancellationId);
            exit;
        }
    }

    /**
     * Delete cancellation notice
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            exit;
        }

        $id = $_POST['id'] ?? 0;

        try {
            $this->pdo->beginTransaction();
            
            // Get cancellation details
            $stmt = $this->pdo->prepare("SELECT tender_id, pdf_path FROM tender_cancellation WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $cancellation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($cancellation) {
                // Restore tender status to 'Published'
                $updateTender = $this->pdo->prepare("UPDATE tender_notice SET status = 'Published' WHERE id = :tender_id");
                $updateTender->execute([':tender_id' => $cancellation['tender_id']]);
                
                // Delete PDF file if exists
                if (!empty($cancellation['pdf_path'])) {
                    $filePath = $_SERVER['DOCUMENT_ROOT'] . $cancellation['pdf_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
            
            // Delete the cancellation record
            $stmt = $this->pdo->prepare("DELETE FROM tender_cancellation WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $this->pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

// Route handling
$controller = new CancellationController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $controller->create();
            break;
        case 'update':
            $controller->update();
            break;
        case 'delete':
            $controller->delete();
            break;
        default:
            $controller->create();
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_tender') {
    $controller->getTenderDetails();
}
?>