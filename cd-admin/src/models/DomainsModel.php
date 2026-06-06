<?php
require_once __DIR__ . '../../utils/ActivityLogger.php';

class DomainsModel
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

    public function insertDomains($engName, $hinName, $domainPath, $domainAbout, $createdBy)
    {
        $sql = "INSERT INTO domains 
            (eng_name, hin_name, domain_path, about, created_by)
            VALUES (:engName, :hinName, :domainPath, :domainAbout, :createdBy)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':engName' => $engName,
            ':hinName' => $hinName,
            ':domainPath' => $domainPath,
            ':domainAbout' => $domainAbout,
            ':createdBy' => $createdBy
        ]);

        $id = $this->pdo->lastInsertId();

        $this->logger->log('domains', $id, 'INSERT', null, null, json_encode([
            'eng_name' => $engName,
            'hin_name' => $hinName,
            'domain_path' => $domainPath,
            'about' => $domainAbout
        ]), $createdBy , $this->loggedId);

        return true;
    }

    public function getAllDomains()
    {
        $sql = "SELECT * FROM domains";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateDomains($id, $engName, $hinName, $domainPath, $domainAbout, $updatedBy)
    {
        // Fetch old data
        $oldDataStmt = $this->pdo->prepare("SELECT * FROM domains WHERE id = :id");
        $oldDataStmt->execute([':id' => $id]);
        $oldData = $oldDataStmt->fetch(PDO::FETCH_ASSOC);

        $sql = "UPDATE domains SET
                eng_name = :engName,
                hin_name = :hinName,
                domain_path = :domainPath,
                about = :domainAbout,
                updated_date = CURRENT_TIMESTAMP,
                updated_by = :updated_by
            WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':engName' => $engName,
            ':hinName' => $hinName,
            ':domainPath' => $domainPath,
            ':domainAbout' => $domainAbout,
            ':updated_by' => $updatedBy
        ]);

        $newData = json_encode([
            'eng_name' => $engName,
            'hin_name' => $hinName,
            'domain_path' => $domainPath,
            'about' => $domainAbout
        ]);

        $this->logger->log(
            'domains',
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


    public function softDeleteDomains($id, $updatedBy)
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

            $sql = "UPDATE domains SET
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
                'domains',
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
