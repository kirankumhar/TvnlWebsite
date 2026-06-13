<?php
// CorrigendumController.php - Complete with delete

session_start();
require_once __DIR__ . '/../../database/Database.php';

class CorrigendumController
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

    private function uploadFile($file, $folder)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/corrigendums/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return "/uploads/corrigendums/" . $fileName;
        }
        return null;
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../../manage-corrigendums.php');
            exit;
        }

        $tenderId = $_POST['tender_id'] ?? 0;
        $corrigendumNo = trim($_POST['corrigendum_no'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $corrigendumDate = $_POST['corrigendum_date'] ?? date('Y-m-d');
        
        // Upload PDF
        $pdfPath = $this->uploadFile($_FILES['pdf_file'] ?? null, 'corrigendums');

        try {
            $sql = "INSERT INTO tender_corrigendum (tender_id, corrigendum_no, title, description, corrigendum_date, pdf_path, created_by) 
                    VALUES (:tender_id, :corrigendum_no, :title, :description, :corrigendum_date, :pdf_path, :created_by)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':tender_id' => $tenderId,
                ':corrigendum_no' => $corrigendumNo,
                ':title' => $title,
                ':description' => $description,
                ':corrigendum_date' => $corrigendumDate,
                ':pdf_path' => $pdfPath,
                ':created_by' => $_SESSION['user_id']
            ]);

            $_SESSION['message'] = "Corrigendum created successfully.";
            header('Location: ../../../manage-corrigendums.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ../../../create-corrigendum.php');
            exit;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            exit;
        }

        $id = $_POST['id'] ?? 0;

        try {
            $stmt = $this->pdo->prepare("DELETE FROM tender_corrigendum WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

$controller = new CorrigendumController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $controller->create();
    } elseif ($action === 'delete') {
        $controller->delete();
    }
}
?>