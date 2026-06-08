<?php
// TenderModel.php - Complete model for tender management

require_once __DIR__ . '/../utils/ActivityLogger.php';

class TenderModel
{
    private $pdo;
    private $logger;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->logger = new ActivityLogger($pdo);
    }

    // ==============================================
    // TENDER NOTICE CRUD OPERATIONS
    // ==============================================
    
    /**
     * Create a new tender notice
     */
    public function createTenderNotice($data, $createdBy, $loginId)
    {
        try {
            $sql = "INSERT INTO tender_notice (
                        tender_number, reference_number, title, description,
                        department_id, tender_type_id, publish_date, closing_date, opening_date,
                        closing_time, opening_time, bid_url, status, created_by
                    ) VALUES (
                        :tender_number, :reference_number, :title, :description,
                        :department_id, :tender_type_id, :publish_date, :closing_date, :opening_date,
                        :closing_time, :opening_time, :bid_url, :status, :created_by
                    )";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':tender_number' => $data['tender_number'],
                ':reference_number' => $data['reference_number'] ?? null,
                ':title' => $data['title'],
                ':description' => $data['description'] ?? null,
                ':department_id' => $data['department_id'],
                ':tender_type_id' => $data['tender_type_id'],
                ':publish_date' => $data['publish_date'],
                ':closing_date' => $data['closing_date'],
                ':opening_date' => $data['opening_date'],
                ':closing_time' => $data['closing_time'] ?? null,
                ':opening_time' => $data['opening_time'] ?? null,
                ':bid_url' => $data['bid_url'] ?? null,
                ':status' => $data['status'],
                ':created_by' => $data['created_by']
            ]);
            
            $id = $this->pdo->lastInsertId();
            
            $this->logger->log('tender_notice', $id, 'INSERT', null, null, json_encode($data), $createdBy, $loginId);
            return $id;
        } catch (PDOException $e) {
            error_log("TenderModel::createTenderNotice Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all tenders with pagination and search
     */
    public function getAllTenders($limit = 10, $offset = 0, $search = '', $status = null)
    {
        $sql = "SELECT t.*, 
                       d.department_name, 
                       tm.type_name 
                FROM tender_notice t
                LEFT JOIN departments d ON t.department_id = d.department_id
                LEFT JOIN tender_type_master tm ON t.tender_type_id = tm.id
                WHERE t.is_deleted = 0";
        
        if ($status) {
            $sql .= " AND t.status = :status";
        }
        if (!empty($search)) {
            $sql .= " AND (t.tender_number LIKE :search OR t.title LIKE :search OR t.reference_number LIKE :search)";
        }
        
        $sql .= " ORDER BY t.created_at DESC, t.id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        if (!empty($search)) {
            $searchTerm = "%{$search}%";
            $stmt->bindParam(':search', $searchTerm);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of tenders
     */
    public function getTenderCount($search = '', $status = null)
    {
        $sql = "SELECT COUNT(*) as total FROM tender_notice WHERE is_deleted = 0";
        
        if ($status) {
            $sql .= " AND status = :status";
        }
        if (!empty($search)) {
            $sql .= " AND (tender_number LIKE :search OR title LIKE :search OR reference_number LIKE :search)";
        }
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        if (!empty($search)) {
            $searchTerm = "%{$search}%";
            $stmt->bindParam(':search', $searchTerm);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Get single tender by ID
     */
    public function getTenderById($id)
    {
        $sql = "SELECT t.*, 
                       d.department_name, 
                       tm.type_name 
                FROM tender_notice t
                LEFT JOIN departments d ON t.department_id = d.department_id
                LEFT JOIN tender_type_master tm ON t.tender_type_id = tm.id
                WHERE t.id = :id AND t.is_deleted = 0";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get tender by number
     */
    public function getTenderByNumber($tenderNumber)
    {
        $sql = "SELECT * FROM tender_notice WHERE tender_number = :tender_number AND is_deleted = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':tender_number', $tenderNumber);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update tender notice
     */
    public function updateTenderNotice($id, $data, $updatedBy, $loginId)
    {
        try {
            $oldData = $this->getTenderById($id);
            
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
                        closing_time = :closing_time,
                        opening_time = :opening_time,
                        bid_url = :bid_url,
                        status = :status,
                        updated_by = :updated_by,
                        updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':tender_number' => $data['tender_number'],
                ':reference_number' => $data['reference_number'] ?? null,
                ':title' => $data['title'],
                ':description' => $data['description'] ?? null,
                ':department_id' => $data['department_id'],
                ':tender_type_id' => $data['tender_type_id'],
                ':publish_date' => $data['publish_date'],
                ':closing_date' => $data['closing_date'],
                ':opening_date' => $data['opening_date'],
                ':closing_time' => $data['closing_time'] ?? null,
                ':opening_time' => $data['opening_time'] ?? null,
                ':bid_url' => $data['bid_url'] ?? null,
                ':status' => $data['status'],
                ':updated_by' => $updatedBy
            ]);
            
            $this->logger->log('tender_notice', $id, 'UPDATE', null, json_encode($oldData), json_encode($data), $updatedBy, $loginId);
            return true;
        } catch (PDOException $e) {
            error_log("TenderModel::updateTenderNotice Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update tender status only
     */
    public function updateTenderStatus($id, $status, $updatedBy, $loginId)
    {
        try {
            $oldData = $this->getTenderById($id);
            
            $sql = "UPDATE tender_notice SET status = :status, updated_by = :updated_by, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':status' => $status,
                ':updated_by' => $updatedBy
            ]);
            
            $this->logger->log('tender_notice', $id, 'UPDATE', 'status', $oldData['status'] ?? null, $status, $updatedBy, $loginId);
            return true;
        } catch (PDOException $e) {
            error_log("TenderModel::updateTenderStatus Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete tender notice
     */
    public function deleteTenderNotice($id, $deletedBy, $loginId)
    {
        try {
            $oldData = $this->getTenderById($id);
            
            $sql = "UPDATE tender_notice SET is_deleted = 1, updated_by = :updated_by, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':updated_by' => $deletedBy
            ]);
            
            $this->logger->log('tender_notice', $id, 'DELETE', null, json_encode($oldData), null, $deletedBy, $loginId);
            return true;
        } catch (PDOException $e) {
            error_log("TenderModel::deleteTenderNotice Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Permanently delete tender notice (hard delete)
     */
    public function hardDeleteTenderNotice($id, $deletedBy, $loginId)
    {
        try {
            $oldData = $this->getTenderById($id);
            
            // First delete all attachments
            $this->deleteAllAttachmentsByTender($id);
            
            // Then delete the tender
            $sql = "DELETE FROM tender_notice WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $this->logger->log('tender_notice', $id, 'HARD_DELETE', null, json_encode($oldData), null, $deletedBy, $loginId);
            return true;
        } catch (PDOException $e) {
            error_log("TenderModel::hardDeleteTenderNotice Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment view count for tender
     */
    public function incrementViewCount($id)
    {
        $sql = "UPDATE tender_notice SET view_count = view_count + 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ==============================================
    // ATTACHMENT CRUD OPERATIONS
    // ==============================================
    
    /**
     * Add attachment to tender
     */
    public function addAttachment($tenderId, $attachmentData, $createdBy, $loginId)
    {
        try {
            $sql = "INSERT INTO tender_attachments (
                        tender_id, attachment_title, attachment_title_hindi, attachment_type,
                        file_path, file_name, file_size, file_extension, sort_order
                    ) VALUES (
                        :tender_id, :attachment_title, :attachment_title_hindi, :attachment_type,
                        :file_path, :file_name, :file_size, :file_extension, :sort_order
                    )";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':tender_id' => $tenderId,
                ':attachment_title' => $attachmentData['attachment_title'],
                ':attachment_title_hindi' => $attachmentData['attachment_title_hindi'] ?? null,
                ':attachment_type' => $attachmentData['attachment_type'],
                ':file_path' => $attachmentData['file_path'],
                ':file_name' => $attachmentData['file_name'],
                ':file_size' => $attachmentData['file_size'],
                ':file_extension' => $attachmentData['file_extension'],
                ':sort_order' => $attachmentData['sort_order']
            ]);
            
            $id = $this->pdo->lastInsertId();
            
            $this->logger->log('tender_attachments', $id, 'INSERT', null, null, json_encode($attachmentData), $createdBy, $loginId);
            return $id;
        } catch (PDOException $e) {
            error_log("TenderModel::addAttachment Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all attachments for a tender
     */
    public function getAttachmentsByTender($tenderId)
    {
        $sql = "SELECT * FROM tender_attachments 
                WHERE tender_id = :tender_id 
                ORDER BY 
                    CASE attachment_type 
                        WHEN 'main_pdf' THEN 1 
                        WHEN 'word_doc' THEN 2 
                        ELSE 3 
                    END, 
                    sort_order ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':tender_id', $tenderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get main PDF attachment
     */
    public function getMainPdfAttachment($tenderId)
    {
        $sql = "SELECT * FROM tender_attachments 
                WHERE tender_id = :tender_id AND attachment_type = 'main_pdf' 
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':tender_id', $tenderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get word document attachments
     */
    public function getWordAttachments($tenderId)
    {
        $sql = "SELECT * FROM tender_attachments 
                WHERE tender_id = :tender_id AND attachment_type = 'word_doc' 
                ORDER BY sort_order";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':tender_id', $tenderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get additional attachments
     */
    public function getAdditionalAttachments($tenderId)
    {
        $sql = "SELECT * FROM tender_attachments 
                WHERE tender_id = :tender_id AND attachment_type = 'additional' 
                ORDER BY sort_order";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':tender_id', $tenderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single attachment by ID
     */
    public function getAttachmentById($id)
    {
        $sql = "SELECT * FROM tender_attachments WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment($attachmentId, $deletedBy, $loginId)
    {
        try {
            $oldData = $this->getAttachmentById($attachmentId);
            
            $sql = "DELETE FROM tender_attachments WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $attachmentId, PDO::PARAM_INT);
            $stmt->execute();
            
            $this->logger->log('tender_attachments', $attachmentId, 'DELETE', null, json_encode($oldData), null, $deletedBy, $loginId);
            
            // Delete physical file if exists
            if ($oldData && isset($oldData['file_path'])) {
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $oldData['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("TenderModel::deleteAttachment Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete all attachments for a tender
     */
    public function deleteAllAttachmentsByTender($tenderId)
    {
        try {
            $attachments = $this->getAttachmentsByTender($tenderId);
            
            // Delete physical files
            foreach ($attachments as $attachment) {
                if (isset($attachment['file_path'])) {
                    $filePath = $_SERVER['DOCUMENT_ROOT'] . $attachment['file_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
            
            // Delete from database
            $sql = "DELETE FROM tender_attachments WHERE tender_id = :tender_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':tender_id', $tenderId, PDO::PARAM_INT);
            $stmt->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("TenderModel::deleteAllAttachmentsByTender Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update attachment sort order
     */
    public function updateAttachmentSortOrder($attachmentId, $sortOrder, $updatedBy, $loginId)
    {
        try {
            $oldData = $this->getAttachmentById($attachmentId);
            
            $sql = "UPDATE tender_attachments SET sort_order = :sort_order WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $attachmentId,
                ':sort_order' => $sortOrder
            ]);
            
            $this->logger->log('tender_attachments', $attachmentId, 'UPDATE', 'sort_order', $oldData['sort_order'] ?? 0, $sortOrder, $updatedBy, $loginId);
            return true;
        } catch (PDOException $e) {
            error_log("TenderModel::updateAttachmentSortOrder Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update attachment title
     */
    public function updateAttachmentTitle($attachmentId, $title, $updatedBy, $loginId)
    {
        try {
            $oldData = $this->getAttachmentById($attachmentId);
            
            $sql = "UPDATE tender_attachments SET attachment_title = :title WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $attachmentId,
                ':title' => $title
            ]);
            
            $this->logger->log('tender_attachments', $attachmentId, 'UPDATE', 'attachment_title', $oldData['attachment_title'] ?? null, $title, $updatedBy, $loginId);
            return true;
        } catch (PDOException $e) {
            error_log("TenderModel::updateAttachmentTitle Error: " . $e->getMessage());
            return false;
        }
    }

    // ==============================================
    // MASTER DATA METHODS
    // ==============================================
    
    /**
     * Get all tender types for dropdown
     */
    public function getTenderTypes()
    {
        $sql = "SELECT id, type_name FROM tender_type_master WHERE status = 'Active' ORDER BY sort_order";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all departments for dropdown
     */
    public function getDepartments()
    {
        $sql = "SELECT department_id, department_name, department_name_hindi 
                FROM departments 
                WHERE status = 'Active' 
                ORDER BY display_order";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get tenders for dropdown (for related tenders in corrigendum/extension)
     */
    public function getTendersForDropdown()
    {
        $sql = "SELECT id, tender_number, title 
                FROM tender_notice 
                WHERE is_deleted = 0 AND status != 'Cancelled' 
                ORDER BY id DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get tender count by status
     */
    public function getTenderCountByStatus()
    {
        $sql = "SELECT status, COUNT(*) as count 
                FROM tender_notice 
                WHERE is_deleted = 0 
                GROUP BY status";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent tenders (for dashboard)
     */
    public function getRecentTenders($limit = 5)
    {
        $sql = "SELECT t.*, d.department_name 
                FROM tender_notice t
                LEFT JOIN departments d ON t.department_id = d.department_id
                WHERE t.is_deleted = 0
                ORDER BY t.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get open tenders (for frontend display)
     */
    public function getOpenTenders($limit = 10, $offset = 0, $financialYear = null, $startDate = null, $endDate = null, $searchTerm = null)
    {
        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT tn.*, 
                       sc.sub_category_name AS tender_category_name
                FROM tenders tn
                LEFT JOIN sub_category sc ON tn.tender_category = sc.id
                WHERE tn.is_deleted = '0'
                AND tn.status = 'A'
                AND tn.tender_closing_date >= :current_date";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':current_date', $currentDate);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search tenders by keyword
     */
    public function searchTenders($keyword, $limit = 20)
    {
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT t.*, d.department_name, tm.type_name 
                FROM tender_notice t
                LEFT JOIN departments d ON t.department_id = d.department_id
                LEFT JOIN tender_type_master tm ON t.tender_type_id = tm.id
                WHERE t.is_deleted = 0
                AND (t.tender_number LIKE :search 
                     OR t.title LIKE :search 
                     OR t.reference_number LIKE :search
                     OR t.description LIKE :search)
                ORDER BY t.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':search', $searchTerm);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get tenders by department
     */
    public function getTendersByDepartment($departmentId, $limit = 20)
    {
        $sql = "SELECT t.*, tm.type_name 
                FROM tender_notice t
                LEFT JOIN tender_type_master tm ON t.tender_type_id = tm.id
                WHERE t.department_id = :department_id
                AND t.is_deleted = 0
                AND t.status = 'Published'
                ORDER BY t.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':department_id', $departmentId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get expired tenders
     */
    public function getExpiredTenders()
    {
        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT t.*, d.department_name 
                FROM tender_notice t
                LEFT JOIN departments d ON t.department_id = d.department_id
                WHERE t.is_deleted = 0
                AND t.status = 'Published'
                AND t.closing_date < :current_date
                ORDER BY t.closing_date DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':current_date', $currentDate);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of open tenders
     * @param string $financialYear
     * @param string $startDate
     * @param string $endDate
     * @param string $searchTerm
     * @return int
     */
    public function getOpenTendersCount($financialYear = null, $startDate = null, $endDate = null, $searchTerm = null)
    {
        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT COUNT(*)
                FROM tenders tn
                WHERE tn.is_deleted = '0'
                AND tn.status = 'A'
                AND tn.tender_closing_date >= :current_date";
        
        $params = [':current_date' => $currentDate];

        if ($financialYear) {
            $sql .= " AND tn.financial_year = :financial_year";
            $params[':financial_year'] = $financialYear;
        }
        if ($startDate) {
            $sql .= " AND tn.tender_posting >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND tn.tender_posting <= :end_date";
            $params[':end_date'] = $endDate;
        }
        if ($searchTerm) {
            $sql .= " AND (tn.tender_title LIKE :search_term OR tn.tender_nit_ref_no LIKE :search_term)";
            $params[':search_term'] = '%' . $searchTerm . '%';
        }
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Update tender closing date (used by extension notices)
     */
    public function updateClosingDate($tenderId, $newClosingDate, $updatedBy, $loginId)
    {
        try {
            $oldData = $this->getTenderById($tenderId);
            
            $sql = "UPDATE tender_notice SET closing_date = :closing_date, updated_by = :updated_by, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $tenderId,
                ':closing_date' => $newClosingDate,
                ':updated_by' => $updatedBy
            ]);
            
            $this->logger->log('tender_notice', $tenderId, 'UPDATE', 'closing_date', $oldData['closing_date'] ?? null, $newClosingDate, $updatedBy, $loginId);
            return true;
        } catch (PDOException $e) {
            error_log("TenderModel::updateClosingDate Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if tender number already exists
     */
    public function isTenderNumberExists($tenderNumber, $excludeId = null)
    {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) FROM tender_notice WHERE tender_number = :tender_number AND id != :exclude_id AND is_deleted = 0";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
        } else {
            $sql = "SELECT COUNT(*) FROM tender_notice WHERE tender_number = :tender_number AND is_deleted = 0";
            $stmt = $this->pdo->prepare($sql);
        }
        $stmt->bindParam(':tender_number', $tenderNumber);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
?>