<?php
// DepartmentModel.php
// Path: C:\xampp\htdocs\tvnl-website\cd-admin\src\models\DepartmentModel.php

require_once __DIR__ . '/../utils/ActivityLogger.php';

class DepartmentModel
{
    private $pdo;
    private $logger;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->logger = new ActivityLogger($pdo);
    }

    /**
     * Insert a new department
     */
    public function insertDepartment($departmentName, $departmentNameHindi, $departmentCode, $displayOrder = 0, $status = 'Active', $createdBy, $loginId)
    {
        try {
            $sql = "INSERT INTO departments (department_name, department_name_hindi, department_code, display_order, status, created_at) 
                    VALUES (:dept_name, :dept_name_hindi, :dept_code, :display_order, :status, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':dept_name', $departmentName, PDO::PARAM_STR);
            $stmt->bindParam(':dept_name_hindi', $departmentNameHindi, PDO::PARAM_STR);
            $stmt->bindParam(':dept_code', $departmentCode, PDO::PARAM_STR);
            $stmt->bindParam(':display_order', $displayOrder, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->execute();
            
            $id = $this->pdo->lastInsertId();

            // Log activity
            $this->logger->log(
                'departments',
                $id,
                'INSERT',
                null,
                null,
                json_encode([
                    'department_name' => $departmentName,
                    'department_name_hindi' => $departmentNameHindi,
                    'department_code' => $departmentCode,
                    'display_order' => $displayOrder,
                    'status' => $status,
                ]),
                $createdBy,
                $loginId
            );

            return $id;
        } catch (PDOException $e) {
            error_log("DepartmentModel::insertDepartment Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all departments
     */
    public function getAllDepartments($includeInactive = false)
    {
        if ($includeInactive) {
            $sql = "SELECT * FROM departments ORDER BY display_order ASC, department_name ASC";
        } else {
            $sql = "SELECT * FROM departments WHERE status = 'Active' ORDER BY display_order ASC, department_name ASC";
        }
        
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get department by ID
     */
    public function getDepartmentById($id)
    {
        $sql = "SELECT * FROM departments WHERE department_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get department by code
     */
    public function getDepartmentByCode($code)
    {
        $sql = "SELECT * FROM departments WHERE department_code = :code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update department details
     */
    public function updateDepartment($id, $departmentName, $departmentNameHindi, $departmentCode, $displayOrder, $status, $updatedBy, $loginId)
    {
        try {
            // Fetch old data for logging
            $oldData = $this->getDepartmentById($id);
            
            $sql = "UPDATE departments SET 
                        department_name = :dept_name,
                        department_name_hindi = :dept_name_hindi,
                        department_code = :dept_code,
                        display_order = :display_order,
                        status = :status,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE department_id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':dept_name', $departmentName, PDO::PARAM_STR);
            $stmt->bindParam(':dept_name_hindi', $departmentNameHindi, PDO::PARAM_STR);
            $stmt->bindParam(':dept_code', $departmentCode, PDO::PARAM_STR);
            $stmt->bindParam(':display_order', $displayOrder, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->execute();

            // Log changes
            if ($oldData['department_name'] != $departmentName) {
                $this->logger->log(
                    'departments', $id, 'UPDATE', 'department_name',
                    $oldData['department_name'], $departmentName, $updatedBy, $loginId
                );
            }
            if (($oldData['department_name_hindi'] ?? '') != $departmentNameHindi) {
                $this->logger->log(
                    'departments', $id, 'UPDATE', 'department_name_hindi',
                    $oldData['department_name_hindi'] ?? '', $departmentNameHindi, $updatedBy, $loginId
                );
            }
            if (($oldData['department_code'] ?? '') != $departmentCode) {
                $this->logger->log(
                    'departments', $id, 'UPDATE', 'department_code',
                    $oldData['department_code'] ?? '', $departmentCode, $updatedBy, $loginId
                );
            }
            if (($oldData['display_order'] ?? 0) != $displayOrder) {
                $this->logger->log(
                    'departments', $id, 'UPDATE', 'display_order',
                    $oldData['display_order'] ?? 0, $displayOrder, $updatedBy, $loginId
                );
            }
            if ($oldData['status'] != $status) {
                $this->logger->log(
                    'departments', $id, 'UPDATE', 'status',
                    $oldData['status'], $status, $updatedBy, $loginId
                );
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("DepartmentModel::updateDepartment Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update department status only
     */
    public function updateDepartmentStatus($id, $status, $updatedBy, $loginId)
    {
        try {
            $oldData = $this->getDepartmentById($id);
            
            $sql = "UPDATE departments SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE department_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->execute();

            $this->logger->log(
                'departments', $id, 'UPDATE', 'status',
                $oldData['status'] ?? null, $status, $updatedBy, $loginId
            );
            
            return true;
        } catch (PDOException $e) {
            error_log("DepartmentModel::updateDepartmentStatus Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete department (hard delete)
     */
    public function deleteDepartment($id, $deletedBy, $loginId)
    {
        try {
            // Check if department has any associated tenders
            $checkTenders = $this->pdo->prepare("SELECT COUNT(*) FROM tenders WHERE department_id = :id");
            $checkTenders->bindParam(':id', $id, PDO::PARAM_INT);
            $checkTenders->execute();
            $tenderCount = $checkTenders->fetchColumn();
            
            if ($tenderCount > 0) {
                return false;
            }
            
            $oldData = $this->getDepartmentById($id);
            
            $sql = "DELETE FROM departments WHERE department_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->logger->log(
                'departments', $id, 'DELETE', null,
                json_encode($oldData), null, $deletedBy, $loginId
            );
            
            return true;
        } catch (PDOException $e) {
            error_log("DepartmentModel::deleteDepartment Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete (set status to Inactive)
     */
    public function softDeleteDepartment($id, $updatedBy, $loginId)
    {
        return $this->updateDepartmentStatus($id, 'Inactive', $updatedBy, $loginId);
    }

    /**
     * Activate department
     */
    public function activateDepartment($id, $updatedBy, $loginId)
    {
        return $this->updateDepartmentStatus($id, 'Active', $updatedBy, $loginId);
    }

    /**
     * Get active departments for dropdown
     */
    public function getDepartmentsForDropdown()
    {
        $sql = "SELECT department_id, department_name, department_name_hindi, department_code 
                FROM departments 
                WHERE status = 'Active' 
                ORDER BY display_order ASC, department_name ASC";
        
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total departments count
     */
    public function getDepartmentCount($status = null)
    {
        if ($status) {
            $sql = "SELECT COUNT(*) FROM departments WHERE status = :status";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $sql = "SELECT COUNT(*) FROM departments";
            $stmt = $this->pdo->query($sql);
        }
        return (int)$stmt->fetchColumn();
    }

    /**
     * Check if department code already exists
     */
    public function isDepartmentCodeExists($code, $excludeId = null)
    {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) FROM departments WHERE department_code = :code AND department_id != :exclude_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
        } else {
            $sql = "SELECT COUNT(*) FROM departments WHERE department_code = :code";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Check if department name already exists
     */
    public function isDepartmentNameExists($name, $excludeId = null)
    {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) FROM departments WHERE department_name = :name AND department_id != :exclude_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
        } else {
            $sql = "SELECT COUNT(*) FROM departments WHERE department_name = :name";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Bulk update display orders
     */
    public function bulkUpdateDisplayOrders($orders, $updatedBy, $loginId)
    {
        try {
            $this->pdo->beginTransaction();
            
            foreach ($orders as $id => $displayOrder) {
                $oldData = $this->getDepartmentById($id);
                
                $sql = "UPDATE departments SET display_order = :display_order, updated_at = CURRENT_TIMESTAMP WHERE department_id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':display_order', $displayOrder, PDO::PARAM_INT);
                $stmt->execute();
                
                if (($oldData['display_order'] ?? 0) != $displayOrder) {
                    $this->logger->log(
                        'departments', $id, 'UPDATE', 'display_order',
                        $oldData['display_order'] ?? 0, $displayOrder, $updatedBy, $loginId
                    );
                }
            }
            
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("DepartmentModel::bulkUpdateDisplayOrders Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get departments with pagination
     */
    public function getDepartmentsPaginated($limit, $offset, $search = '')
    {
        if (!empty($search)) {
            $sql = "SELECT * FROM departments 
                    WHERE department_name LIKE :search OR department_code LIKE :search OR department_name_hindi LIKE :search
                    ORDER BY display_order ASC, department_name ASC 
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($sql);
            $searchTerm = "%{$search}%";
            $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        } else {
            $sql = "SELECT * FROM departments 
                    ORDER BY display_order ASC, department_name ASC 
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($sql);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>