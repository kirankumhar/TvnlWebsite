<?php
require_once __DIR__ . '../../utils/ActivityLogger.php';

class SubCategoryModel
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

    public function insertSubCategory($domainId , $engsubCatName, $hinsubCatName, $categoryId, $createdBy)
    {
        $sql = "INSERT INTO sub_category (domain_id, sub_category_name, hindi_sub_category_name, category_id, created_by) VALUES (:domainId, :engsubCatName, :hinsubCatName, :categoryId, :createdBy)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':domainId', $domainId, PDO::PARAM_STR);
        $stmt->bindParam(':engsubCatName', $engsubCatName, PDO::PARAM_STR);
        $stmt->bindParam(':hinsubCatName', $hinsubCatName, PDO::PARAM_STR);
        $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':createdBy', $createdBy, PDO::PARAM_STR);

        $stmt->execute();
        $id = $this->pdo->lastInsertId();

        $this->logger->log('subcategory', $id, 'INSERT', null, null, json_encode([
            'domain_id' => $domainId,
            'category_id' => $categoryId,
            'sub_category_name' => $engsubCatName,
            'hindi_sub_category_name' => $hinsubCatName,
        ]), $createdBy , $this->loggedId);

        return true;
    }

    public function insertChildSubCategory($domainId, $chsubCatName, $hnSubCatName, $description, $subcategoryId, $categoryId, $createdBy)
    {
        $sql = "INSERT INTO child_sub_category (domain_id, child_sub_category_name, hn_child_sub_category_name, description, subcategory_id, category_id, created_by) VALUES (:domainId, :childSubCatName, :hnChildSubCatName, :description, :subCategoryId, :categoryId, :createdBy)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':domainId', $domainId, PDO::PARAM_STR);
        $stmt->bindParam(':childSubCatName', $chsubCatName, PDO::PARAM_STR);
        $stmt->bindParam(':hnChildSubCatName', $hnSubCatName, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':subCategoryId', $subcategoryId, PDO::PARAM_INT);
        $stmt->bindParam(':createdBy', $createdBy, PDO::PARAM_STR);
        $stmt->execute();
        $id = $this->pdo->lastInsertId();

        $newData = json_encode([
            'domain_id' => $domainId,
            'child_sub_category_name' => $chsubCatName,
            'hn_child_sub_category_name' => $hnSubCatName,
            'description' => $description,
            'subcategory_id' => $subcategoryId,
            'category_id' => $categoryId,
        ]);

        $this->logger->log('childsubcategory', $id, 'INSERT', null, null, $newData, $createdBy , $this->loggedId);
        return true;
    }

    public function getAllCategories()
    {
        $sql = "SELECT id, category_name FROM category_master WHERE is_active = 1";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubCategory($cat_id)
    {
        $sql = "SELECT id, sub_category_name FROM sub_category WHERE is_active = 1 AND category_id= '$cat_id'";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateSubCategory($id, $domainId, $eng_sub_cat, $hin_sub_cat, $categoryId, $updatedBy)
    {   
        // Fetch old data
        $oldDataStmt = $this->pdo->prepare("SELECT * FROM sub_category WHERE id = :id");
        $oldDataStmt->execute([':id' => $id]);
        $oldData = $oldDataStmt->fetch(PDO::FETCH_ASSOC);

        $sql = "UPDATE sub_category SET
                    domain_id = :domainId,
                    sub_category_name = :sub_category_name,
                    hindi_sub_category_name = :hindi_sub_category_name,
                    category_id = :categoryId,
                    updated_date = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':domainId', $domainId, PDO::PARAM_STR);
        $stmt->bindParam(':sub_category_name', $eng_sub_cat, PDO::PARAM_STR);
        $stmt->bindParam(':hindi_sub_category_name', $hin_sub_cat, PDO::PARAM_STR);
        $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_STR);
        $stmt->bindParam(':updated_by', $updatedBy, PDO::PARAM_INT);
        $stmt->execute();

         $newData = json_encode([
            'domain_id' => $domainId,
            'sub_category_name' => $eng_sub_cat,
            'hindi_sub_category_name' => $hin_sub_cat,
            'category_id' =>$categoryId
        ]);

        $this->logger->log(
            'subcategory',
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

    public function updateChildSubCategory($id, $domainId, $chsubCatName, $hnSubCatName, $description, $categoryId, $subcategoryId, $updatedBy)
    {   
        // Fetch old data
        $oldDataStmt = $this->pdo->prepare("SELECT * FROM child_sub_category WHERE id = :id");
        $oldDataStmt->execute([':id' => $id]);
        $oldData = $oldDataStmt->fetch(PDO::FETCH_ASSOC);

        $sql = "UPDATE child_sub_category SET
                    domain_id = :domainId,
                    category_id = :category_id,
                    subcategory_id = :subcategory_id,
                    child_sub_category_name = :child_sub_category_name,
                    hn_child_sub_category_name = :hn_child_sub_category_name,
                    description = :description,
                    updated_date = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':domainId', $domainId, PDO::PARAM_STR);
        $stmt->bindParam(':child_sub_category_name', $chsubCatName, PDO::PARAM_STR);
        $stmt->bindParam(':hn_child_sub_category_name', $hnSubCatName, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':subcategory_id', $subcategoryId, PDO::PARAM_INT);
        $stmt->bindParam(':updated_by', $updatedBy, PDO::PARAM_INT);
        $stmt->execute();

         $newData = json_encode([
            'domain_id' => $domainId,
            'child_sub_category_name' => $chsubCatName,
            'hn_child_sub_category_name' => $hnSubCatName,
            'description' => $description,
            'subcategory_id' =>$subcategoryId,
            'category_id' =>$categoryId
        ]);

        $this->logger->log(
            'childsubcategory',
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
    public function softDeleteSubCategory($id, $updatedBy)
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE sub_category SET
                    is_deleted = '1',
                    updated_date = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':updated_by', $updatedBy, PDO::PARAM_INT);
            $stmt->execute();

            $sql1 = "UPDATE postings SET
                    sub_category = NULL,
                    last_updated_on = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                WHERE sub_category = :id";
            $stmt1 = $this->pdo->prepare($sql1);
            $stmt1->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt1->bindParam(':updated_by', $updatedBy, PDO::PARAM_INT);
            $stmt1->execute();
            $this->pdo->commit();
            $this->logger->log(
                'subcategory',
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
            // print_r(throw $e);
            // die('sd');
            return false;
        }
    }

    public function softDeleteChildSubCategory($id, $updatedBy)
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE child_sub_category SET
                    is_deleted = '1',
                    updated_date = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':updated_by', $updatedBy, PDO::PARAM_INT);
            $stmt->execute();

            $this->pdo->commit();
            $this->logger->log(
                'childsubcategory',
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
            
            return false;
        }
    }
}

?>