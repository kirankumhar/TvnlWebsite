<?php
// SliderModel.php - Model for slider operations

class SliderModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Upload image file
     */
    private function uploadImage($file)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Create upload directory if not exists
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/tvnl-website/cd-admin/uploads/sliders/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $fileName;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return "/tvnl-website/cd-admin/uploads/sliders/" . $fileName;
        }

        return null;
    }

    /**
     * Get next display order
     */
    public function getNextDisplayOrder()
    {
        $stmt = $this->pdo->query("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM sliders");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['next_order'];
    }

    /**
     * Create new slider
     */
    public function createSlider($data)
    {
        try {
            // Upload image
            $imagePath = $this->uploadImage($data['image']);
            if (!$imagePath) {
                return false;
            }

            // Get next display order
            $displayOrder = $this->getNextDisplayOrder();

            $sql = "INSERT INTO sliders (title, subtitle, image_path, make_slider, status, display_order, created_by, created_at) 
                    VALUES (:title, :subtitle, :image_path, :make_slider, :status, :display_order, :created_by, NOW())";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':title' => $data['title'],
                ':subtitle' => $data['subtitle'],
                ':image_path' => $imagePath,
                ':make_slider' => $data['make_slider'],
                ':status' => $data['status'],
                ':display_order' => $displayOrder,
                ':created_by' => $_SESSION['user_id'] ?? 0
            ]);

        } catch (PDOException $e) {
            error_log("SliderModel::createSlider Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all sliders
     */
    public function getAllSliders($status = null)
    {
        $sql = "SELECT * FROM sliders WHERE 1=1";
        
        if ($status) {
            $sql .= " AND status = :status";
        }
        
        $sql .= " ORDER BY display_order ASC, created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get slider by ID
     */
    public function getSliderById($id)
    {
        $sql = "SELECT * FROM sliders WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update slider
     */
    public function updateSlider($id, $data)
    {
        try {
            // Check if new image is uploaded
            $imagePath = null;
            if (isset($data['image']) && $data['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->uploadImage($data['image']);
            }

            $sql = "UPDATE sliders SET 
                        title = :title,
                        subtitle = :subtitle,
                        make_slider = :make_slider,
                        status = :status,
                        updated_by = :updated_by,
                        updated_at = NOW()";
            
            if ($imagePath) {
                $sql .= ", image_path = :image_path";
            }
            
            $sql .= " WHERE id = :id";
            
            $params = [
                ':id' => $id,
                ':title' => $data['title'],
                ':subtitle' => $data['subtitle'],
                ':make_slider' => $data['make_slider'],
                ':status' => $data['status'],
                ':updated_by' => $_SESSION['user_id'] ?? 0
            ];
            
            if ($imagePath) {
                $params[':image_path'] = $imagePath;
            }
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("SliderModel::updateSlider Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete slider (hard delete)
     */
    public function deleteSlider($id)
    {
        try {
            // Get image path to delete file
            $slider = $this->getSliderById($id);
            if ($slider && !empty($slider['image_path'])) {
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $slider['image_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $sql = "DELETE FROM sliders WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("SliderModel::deleteSlider Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update display order
     */
    public function updateDisplayOrder($orders)
    {
        try {
            $this->pdo->beginTransaction();
            
            foreach ($orders as $id => $displayOrder) {
                $sql = "UPDATE sliders SET display_order = :display_order WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':id' => $id,
                    ':display_order' => $displayOrder
                ]);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("SliderModel::updateDisplayOrder Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update status
     */
    public function updateStatus($id, $status)
    {
        try {
            $sql = "UPDATE sliders SET status = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':status' => $status
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("SliderModel::updateStatus Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active sliders for frontend
     */
    public function getActiveSliders()
    {
        $sql = "SELECT * FROM sliders 
                WHERE status = 'A' 
                ORDER BY display_order ASC, created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get sliders for homepage slider (make_slider = 1)
     */
    public function getHomepageSliders()
    {
        $sql = "SELECT * FROM sliders 
                WHERE status = 'A' AND make_slider = 1 
                ORDER BY display_order ASC, created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>