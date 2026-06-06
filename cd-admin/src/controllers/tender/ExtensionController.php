<?php
// ExtensionController.php - Complete with update method

session_start();
require_once __DIR__ . '/../../database/Database.php';

class ExtensionController
{
    private $pdo;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../../../index.php');
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
        
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/tvnl-website/cd-admin/uploads/extensions/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return "/tvnl-website/cd-admin/uploads/extensions/" . $fileName;
        }
        return null;
    }

    /**
     * Create new extension
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../../manage-extensions.php');
            exit;
        }

        $tenderId = $_POST['tender_id'] ?? 0;
        $extensionNo = trim($_POST['extension_no'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $oldClosingDate = date('Y-m-d H:i:s', strtotime($_POST['old_closing_date']));
        $newClosingDate = date('Y-m-d H:i:s', strtotime($_POST['new_closing_date']));
        $extensionDate = $_POST['extension_date'] ?? date('Y-m-d');
        
        // Upload PDF
        $pdfPath = $this->uploadFile($_FILES['pdf_file'] ?? null);

        try {
            $this->pdo->beginTransaction();
            
            // Insert extension record
            $sql = "INSERT INTO tender_extension (tender_id, extension_no, title, description, old_closing_date, new_closing_date, extension_date, pdf_path, created_by) 
                    VALUES (:tender_id, :extension_no, :title, :description, :old_closing_date, :new_closing_date, :extension_date, :pdf_path, :created_by)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':tender_id' => $tenderId,
                ':extension_no' => $extensionNo,
                ':title' => $title,
                ':description' => $description,
                ':old_closing_date' => $oldClosingDate,
                ':new_closing_date' => $newClosingDate,
                ':extension_date' => $extensionDate,
                ':pdf_path' => $pdfPath,
                ':created_by' => $_SESSION['user_id']
            ]);

            // Update tender's closing date
            $updateSql = "UPDATE tender_notice SET closing_date = :new_date WHERE id = :tender_id";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([
                ':new_date' => $newClosingDate,
                ':tender_id' => $tenderId
            ]);

            $this->pdo->commit();
            $_SESSION['message'] = "Extension created and tender closing date updated successfully.";
            header('Location: ../../../manage-extensions.php');
            exit;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ../../../create-extension.php');
            exit;
        }
    }

    /**
     * Update existing extension
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../../manage-extensions.php');
            exit;
        }

        $extensionId = $_POST['extension_id'] ?? 0;
        $tenderId = $_POST['tender_id'] ?? 0;
        $extensionNo = trim($_POST['extension_no'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $oldClosingDate = date('Y-m-d H:i:s', strtotime($_POST['old_closing_date']));
        $newClosingDate = date('Y-m-d H:i:s', strtotime($_POST['new_closing_date']));
        $extensionDate = $_POST['extension_date'] ?? date('Y-m-d');
        
        // Get existing extension to check for old PDF
        $stmt = $this->pdo->prepare("SELECT pdf_path FROM tender_extension WHERE id = :id");
        $stmt->execute([':id' => $extensionId]);
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
            $this->pdo->beginTransaction();
            
            // Update extension record
            $sql = "UPDATE tender_extension SET 
                        tender_id = :tender_id,
                        extension_no = :extension_no,
                        title = :title,
                        description = :description,
                        old_closing_date = :old_closing_date,
                        new_closing_date = :new_closing_date,
                        extension_date = :extension_date,
                        pdf_path = :pdf_path
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $extensionId,
                ':tender_id' => $tenderId,
                ':extension_no' => $extensionNo,
                ':title' => $title,
                ':description' => $description,
                ':old_closing_date' => $oldClosingDate,
                ':new_closing_date' => $newClosingDate,
                ':extension_date' => $extensionDate,
                ':pdf_path' => $pdfPath
            ]);

            // Update tender's closing date
            $updateSql = "UPDATE tender_notice SET closing_date = :new_date WHERE id = :tender_id";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([
                ':new_date' => $newClosingDate,
                ':tender_id' => $tenderId
            ]);

            $this->pdo->commit();
            $_SESSION['message'] = "Extension updated successfully.";
            header('Location: ../../../manage-extensions.php');
            exit;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ../../../edit-extension.php?id=' . $extensionId);
            exit;
        }
    }

    /**
     * Delete extension
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
            
            // Get extension details to restore old closing date
            $stmt = $this->pdo->prepare("SELECT tender_id, old_closing_date, pdf_path FROM tender_extension WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $extension = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($extension) {
                // Restore original closing date to tender
                $updateTender = $this->pdo->prepare("UPDATE tender_notice SET closing_date = :old_date WHERE id = :tender_id");
                $updateTender->execute([
                    ':old_date' => $extension['old_closing_date'],
                    ':tender_id' => $extension['tender_id']
                ]);
                
                // Delete PDF file if exists
                if (!empty($extension['pdf_path'])) {
                    $filePath = $_SERVER['DOCUMENT_ROOT'] . $extension['pdf_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
            
            // Delete the extension record
            $stmt = $this->pdo->prepare("DELETE FROM tender_extension WHERE id = :id");
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
$controller = new ExtensionController();

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
}
?>