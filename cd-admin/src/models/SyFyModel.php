<?php

class SyFyModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function insertSyFy($type, $calenderYear, $financialYear, $createdBy, $updatedBy, $ipAddress)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO sy_fy (type, calender_year, financial_year, created_by, updated_by, ip_address)
            VALUES (:type, :calenderYear, :financialYear, :createdBy, :updatedBy, :ipAddress)
        ");

        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':calenderYear', $calenderYear, PDO::PARAM_INT);
        $stmt->bindParam(':financialYear', $financialYear);
        $stmt->bindParam(':createdBy', $createdBy);
        $stmt->bindParam(':updatedBy', $updatedBy);
        $stmt->bindParam(':ipAddress', $ipAddress);

        return $stmt->execute();
    }

    public function existsSyFy($calenderYear, $financialYear)
    {

        if ($calenderYear) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM sy_fy WHERE calender_year = :calenderYear");

            $stmt->bindParam(':calenderYear', $calenderYear, PDO::PARAM_INT);
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM sy_fy WHERE financial_year = :financialYear");

            $stmt->bindParam(':financialYear', $financialYear, PDO::PARAM_STR);
        }

        $stmt->execute();

        // Return true if a record exists, otherwise false
        return $stmt->fetchColumn() > 0;
    }

    public function deleteSyFy($id)
    {

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM postings WHERE type = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            return 'post_available';
        }

        $stmt = $this->pdo->prepare("DELETE FROM sy_fy WHERE id = :session_id");
        $stmt->bindParam(':session_id', $id, PDO::PARAM_STR);
        return $stmt->execute();

    }
    public function getSyFyData()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM sy_fy");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateSyFy($id, $type, $calenderYear, $financialYear, $updatedBy, $ipAddress)
    {
        $sql = "UPDATE sy_fy SET
                `type` = :type1,
                calender_year = :calender_year,
                financial_year = :financial_year,
                updated_by = :updated_by,
                ip_address = :ip_address
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':type1', $type, PDO::PARAM_INT);
        $stmt->bindParam(':calender_year', $calenderYear, PDO::PARAM_STR);
        $stmt->bindParam(':financial_year', $financialYear, PDO::PARAM_STR);
        $stmt->bindParam(':updated_by', $updatedBy, PDO::PARAM_INT);

        $stmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
        return $stmt->execute();
    }
}

?>