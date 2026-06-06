<?php

class ActivityLogger
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function log(
        string $table,
        int $recordId,
        string $action,
        ?string $column,
        $oldValue,
        $newValue,
        int $userId,
        int $loginId
    ) {
        $sql = "INSERT INTO activity_logs 
                (table_name, record_id, action, column_name, old_value, new_value, login_id, performed_by, ip_address)
                VALUES 
                (:table_name, :record_id, :action, :column_name, :old_value, :new_value, :login_id, :performed_by, :ip)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':table_name'   => $table,
            ':record_id'    => $recordId,
            ':action'       => $action,
            ':column_name'  => $column,
            ':old_value'    => $oldValue,
            ':new_value'    => $newValue,
            ':login_id'     => $loginId,
            ':performed_by' => $userId,
            ':ip'           => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}
