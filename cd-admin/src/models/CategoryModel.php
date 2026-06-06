<?php
require_once __DIR__ . '../../utils/ActivityLogger.php';

class CategoryModel
{
    private $pdo;
    private $logger;
    private $loggedId;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->logger = new ActivityLogger($pdo);
        $this->loggedId = $_SESSION['login_id'];
    }

    public function insertCategory($engCat, $hinCat, $createdBy)
    {
        $sql = "INSERT INTO category_master (domain_id, category_name, hindi_category_name, created_by) VALUES (0, :engCat, :hinCat, :createdBy)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':engCat', $engCat, PDO::PARAM_STR);
        $stmt->bindParam(':hinCat', $hinCat, PDO::PARAM_STR);
        $stmt->bindParam(':createdBy', $createdBy, PDO::PARAM_STR);
        $stmt->execute();
        $id = $this->pdo->lastInsertId();

        $this->logger->log('category', $id, 'INSERT', null, null, json_encode([
            'domain_id' => 0,
            'category_name' => $engCat,
            'hindi_category_name' => $hinCat,
        ]), $createdBy , $this->loggedId);

        return true;
    }

    public function getAllCategories()
    {
        $sql = "SELECT * FROM category_master";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateCategory($id, $engCat, $hnCat, $updatedBy)
    {   
        // Fetch old data
        $oldDataStmt = $this->pdo->prepare("SELECT * FROM category_master WHERE id = :id");
        $oldDataStmt->execute([':id' => $id]);
        $oldData = $oldDataStmt->fetch(PDO::FETCH_ASSOC);

        $sql = "UPDATE category_master SET
                    domain_id = 0,
                    category_name = :category,
                    hindi_category_name = :hincategory,
                    updated_date = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':category', $engCat, PDO::PARAM_STR);
        $stmt->bindParam(':hincategory', $hnCat, PDO::PARAM_STR);
        $stmt->bindParam(':updated_by', $updatedBy, PDO::PARAM_INT);
        $stmt->execute();

         $newData = json_encode([
            'domain_id' => 0,
            'category_name' => $engCat,
            'hindi_category_name' => $hnCat,
        ]);

        $this->logger->log(
            'category',
            $id,
            'UPDATE',
            NULL,
            json_encode($oldData),
            $newData,
            $updatedBy,
            $this->loggedId
        );
        return true;
    }
    public function softDeleteCategory($id, $updatedBy)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM postings WHERE category = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            return 'post_available';
        }

        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE category_master SET
                    is_deleted = '1',
                    updated_date = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':updated_by', $updatedBy, PDO::PARAM_INT);
            $stmt->execute();

            $sql = "UPDATE sub_category SET
                    is_deleted = '1',
                    updated_date = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                WHERE category_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':updated_by', $updatedBy, PDO::PARAM_INT);
            $stmt->execute();

            $this->pdo->commit();
            $this->logger->log(
                'category',
                $id,
                'DELETE',
                'is_deleted',
                '0',
                '1',
                $updatedBy,
                $this->loggedId
            );
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}

?>