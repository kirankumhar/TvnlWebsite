<?php

class DailyPatientRecordModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function insertRecord($patientCount, $recordDate, $details)
    {
        $sql = "INSERT INTO daily_patient_records (patient_count, record_date, details) 
                VALUES (:patient_count, :record_date, :details)";
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':patient_count', $patientCount, PDO::PARAM_INT);
        $stmt->bindParam(':record_date', $recordDate, PDO::PARAM_STR);
        $stmt->bindParam(':details', $details, PDO::PARAM_STR);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->logError($e->getMessage(), "Inserting record");
            return false;
        }
    }

    public function updateRecord($recordId, $patientCount, $recordDate, $details)
    {
        $sql = "UPDATE daily_patient_records SET
                    patient_count = :patient_count,
                    record_date = :record_date,
                    details = :details
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':id', $recordId, PDO::PARAM_INT);
        $stmt->bindParam(':patient_count', $patientCount, PDO::PARAM_INT);
        $stmt->bindParam(':record_date', $recordDate, PDO::PARAM_STR);
        $stmt->bindParam(':details', $details, PDO::PARAM_STR);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->logError($e->getMessage(), "Updating record");
            return false;
        }
    }

    public function showRecord($recordDate)
    {
        $sql = "SELECT * FROM daily_patient_records WHERE record_date = :record_date";
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':record_date', $recordDate, PDO::PARAM_STR);
        
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->logError($e->getMessage(), "Updating record");
            return false;
        }
    }
    // Function to log errors
    private function logError($message, $context)
    {
        // Implement your error logging here (e.g., write to a file, database, etc.)
        echo "Error in $context: $message\n";
    }
}
