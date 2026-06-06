<?php
// TenderController.php - Fixed paths

// Correct path: From controllers/tender to models (go up 2 levels)
require_once __DIR__ . '/../../models/TenderModel.php';
require_once __DIR__ . '/../../database/Database.php';

class TenderController
{
    private $pdo;
    private $tenderModel;

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->getConnection();
        $this->tenderModel = new TenderModel($this->pdo);
    }

    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Upload file to server
     */
    private function uploadFile($file, $subFolder = '')
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/tvnl-website/cd-admin/uploads/tenders/";
        if (!empty($subFolder)) {
            $uploadDir .= $subFolder . "/";
        }
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $relativePath = "/tvnl-website/cd-admin/uploads/tenders/";
            if (!empty($subFolder)) {
                $relativePath .= $subFolder . "/";
            }
            return $relativePath . $fileName;
        }
        
        return null;
    }

    /**
     * Get formatted file size
     */
    private function getFileSize($file)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        return $this->formatFileSize($file['size']);
    }

    /**
     * Format file size
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    // ==============================================
    // CREATE TENDER NOTICE
    // ==============================================
    
    public function createTenderNotice()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        $this->startSession();
        
        // Convert datetime-local to proper format
        $publishDateTime = !empty($_POST['publish_date']) ? date('Y-m-d H:i:s', strtotime($_POST['publish_date'])) : null;
        $closingDateTime = !empty($_POST['closing_date']) ? date('Y-m-d H:i:s', strtotime($_POST['closing_date'])) : null;
        $openingDateTime = !empty($_POST['opening_date']) ? date('Y-m-d H:i:s', strtotime($_POST['opening_date'])) : null;
        
        // Upload Main PDF
        $mainPdfPath = null;
        $mainPdfTitle = $_POST['main_pdf_title'] ?? 'Main Tender Document';
        
        if (isset($_FILES['main_pdf']) && $_FILES['main_pdf']['error'] === UPLOAD_ERR_OK) {
            $mainPdfPath = $this->uploadFile($_FILES['main_pdf'], 'main_pdfs');
        }
        
        if (!$mainPdfPath) {
            $_SESSION['error'] = "Main PDF attachment is required.";
            header("Location: ../../../create-tender.php");
            exit;
        }
        
        // Prepare tender data
        $tenderData = [
            'tender_number' => trim($_POST['tender_number'] ?? ''),
            'reference_number' => trim($_POST['reference_number'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'department_id' => $_POST['department_id'] ?? null,
            'tender_type_id' => $_POST['tender_type_id'] ?? null,
            'publish_date' => $publishDateTime,
            'closing_date' => $closingDateTime,
            'opening_date' => $openingDateTime,
            'closing_time' => null,
            'opening_time' => null,
            'bid_url' => trim($_POST['bid_url'] ?? ''),
            'status' => $_POST['status'] ?? 'Draft',
            'created_by' => $_SESSION['user_id'] ?? 0
        ];

        // Validation
        $errors = [];
        if (empty($tenderData['tender_number'])) $errors[] = "NIT No. is required.";
        if (empty($tenderData['title'])) $errors[] = "Tender title is required.";
        if (empty($tenderData['department_id'])) $errors[] = "Department is required.";
        if (empty($tenderData['tender_type_id'])) $errors[] = "Tender category is required.";
        if (empty($tenderData['publish_date'])) $errors[] = "Publish date & time is required.";
        if (empty($tenderData['closing_date'])) $errors[] = "Bid submission deadline is required.";
        if (empty($tenderData['opening_date'])) $errors[] = "Opening date & time is required.";
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode("<br>", $errors);
            header("Location: ../../../create-tender.php");
            exit;
        }

        $loginId = $_SESSION['login_id'] ?? 0;
        $tenderId = $this->tenderModel->createTenderNotice($tenderData, $tenderData['created_by'], $loginId);

        if (!$tenderId) {
            $_SESSION['error'] = "Failed to create tender notice.";
            header("Location: ../../../create-tender.php");
            exit;
        }
        
        // Add Main PDF Attachment
        $mainAttachmentData = [
            'tender_id' => $tenderId,
            'attachment_title' => $mainPdfTitle,
            'attachment_title_hindi' => '',
            'attachment_type' => 'main_pdf',
            'file_path' => $mainPdfPath,
            'file_name' => $_FILES['main_pdf']['name'],
            'file_size' => $this->getFileSize($_FILES['main_pdf']),
            'file_extension' => 'pdf',
            'sort_order' => 1
        ];
        $this->tenderModel->addAttachment($tenderId, $mainAttachmentData, $tenderData['created_by'], $loginId);
        
        // Upload Supporting Document (Word/PDF - Optional)
        $attachmentCount = 1;
        if (isset($_FILES['word_doc']) && $_FILES['word_doc']['error'] === UPLOAD_ERR_OK) {
            $wordDocPath = $this->uploadFile($_FILES['word_doc'], 'supporting_docs');
            if ($wordDocPath) {
                $extension = pathinfo($_FILES['word_doc']['name'], PATHINFO_EXTENSION);
                $wordAttachmentData = [
                    'tender_id' => $tenderId,
                    'attachment_title' => trim($_POST['word_doc_title'] ?? 'Supporting Document'),
                    'attachment_title_hindi' => '',
                    'attachment_type' => 'word_doc',
                    'file_path' => $wordDocPath,
                    'file_name' => $_FILES['word_doc']['name'],
                    'file_size' => $this->getFileSize($_FILES['word_doc']),
                    'file_extension' => $extension,
                    'sort_order' => 2
                ];
                $this->tenderModel->addAttachment($tenderId, $wordAttachmentData, $tenderData['created_by'], $loginId);
                $attachmentCount++;
            }
        }
        
        // Upload Additional Attachments
        $additionalTitles = $_POST['additional_title'] ?? [];
        $additionalFiles = $_FILES['additional_file'] ?? [];
        
        $sortOrder = $attachmentCount + 1;
        
        if (isset($additionalFiles['tmp_name']) && is_array($additionalFiles['tmp_name'])) {
            foreach ($additionalFiles['tmp_name'] as $index => $tmpName) {
                if ($attachmentCount >= 10) break;
                
                if ($additionalFiles['error'][$index] === UPLOAD_ERR_OK && !empty($additionalFiles['name'][$index])) {
                    $fileData = [
                        'name' => $additionalFiles['name'][$index],
                        'type' => $additionalFiles['type'][$index],
                        'tmp_name' => $additionalFiles['tmp_name'][$index],
                        'error' => $additionalFiles['error'][$index],
                        'size' => $additionalFiles['size'][$index]
                    ];
                    
                    $filePath = $this->uploadFile($fileData, 'additional');
                    if ($filePath) {
                        $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                        $attachmentData = [
                            'tender_id' => $tenderId,
                            'attachment_title' => trim($additionalTitles[$index] ?? 'Additional Document'),
                            'attachment_title_hindi' => '',
                            'attachment_type' => 'additional',
                            'file_path' => $filePath,
                            'file_name' => $fileData['name'],
                            'file_size' => $this->getFileSize($fileData),
                            'file_extension' => $extension,
                            'sort_order' => $sortOrder++
                        ];
                        $this->tenderModel->addAttachment($tenderId, $attachmentData, $tenderData['created_by'], $loginId);
                        $attachmentCount++;
                    }
                }
            }
        }
        
        $_SESSION['message'] = "Tender created successfully with " . $attachmentCount . " attachment(s).";
        header("Location: ../../../manage-tenders.php");
        exit;
    }

    // ==============================================
    // DISPLAY TENDERS (LIST VIEW)
    // ==============================================
    
    public function showTenders()
    {
        $this->startSession();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? null;
        
        $tenders = $this->tenderModel->getAllTenders($limit, $offset, $search, $status);
        $totalRecords = $this->tenderModel->getTenderCount($search, $status);
        $totalPages = ceil($totalRecords / $limit);
        
        return [
            'tenders' => $tenders,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'search' => $search,
            'status_filter' => $status
        ];
    }

    // ==============================================
    // GET SINGLE TENDER
    // ==============================================
    
    public function getTenderById($id)
    {
        return $this->tenderModel->getTenderById($id);
    }

    // ==============================================
    // GET TENDER ATTACHMENTS
    // ==============================================
    
    public function getTenderAttachments($tenderId)
    {
        return $this->tenderModel->getAttachmentsByTender($tenderId);
    }

    // ==============================================
    // DELETE TENDER NOTICE
    // ==============================================
    
    public function deleteTenderNotice()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        $this->startSession();
        
        $id = $_POST['tender_id'] ?? 0;
        $deletedBy = $_SESSION['user_id'] ?? 0;
        $loginId = $_SESSION['login_id'] ?? 0;
        
        $result = $this->tenderModel->deleteTenderNotice($id, $deletedBy, $loginId);
        
        echo json_encode(['success' => $result, 'message' => $result ? 'Tender deleted successfully' : 'Failed to delete tender']);
        exit;
    }

    // ==============================================
    // UPDATE TENDER STATUS
    // ==============================================
    
    public function updateTenderStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        $this->startSession();
        
        $id = $_POST['tender_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        $updatedBy = $_SESSION['user_id'] ?? 0;
        $loginId = $_SESSION['login_id'] ?? 0;
        
        $result = $this->tenderModel->updateTenderStatus($id, $status, $updatedBy, $loginId);
        
        echo json_encode(['success' => $result, 'message' => $result ? 'Status updated successfully' : 'Failed to update status']);
        exit;
    }

    // ==============================================
    // MASTER DATA METHODS (AJAX)
    // ==============================================
    
    public function getTenderTypes()
    {
        header('Content-Type: application/json');
        echo json_encode($this->tenderModel->getTenderTypes());
        exit;
    }

    public function getDepartments()
    {
        header('Content-Type: application/json');
        echo json_encode($this->tenderModel->getDepartments());
        exit;
    }
}

// ==============================================
// ROUTING
// ==============================================

$controller = new TenderController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_tender':
            $controller->createTenderNotice();
            break;
        case 'delete_tender':
            $controller->deleteTenderNotice();
            break;
        case 'update_status':
            $controller->updateTenderStatus();
            break;
        default:
            $controller->createTenderNotice();
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_types':
            $controller->getTenderTypes();
            break;
        case 'get_departments':
            $controller->getDepartments();
            break;
        default:
            break;
    }
}
?>