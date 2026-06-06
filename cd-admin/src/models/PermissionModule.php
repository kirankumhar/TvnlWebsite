<?php
require_once __DIR__ . '../../utils/ActivityLogger.php';

class PermissionModule
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

    public function syncPermissions(array $data): bool
    {
        try {
            $this->pdo->beginTransaction();

            $userId        = (int)$data['userId'];
            $modules       = $data['module'] ?? [];
            $permissions   = $data['permission'] ?? [];
            $permissionIds = $data['permission_id'] ?? [];
            $updatedBy     = $data['updatedBy'] ?? null;

            $oldDataLog = [
                'updated' => [],
                'deleted' => []
            ];

            $newDataLog = [
                'inserted' => [],
                'updated'  => []
            ];

            $fetchStmt  = $this->pdo->prepare("SELECT * FROM permissions WHERE id = :id");

            $insertStmt = $this->pdo->prepare("
                INSERT INTO permissions
                (user_id, module, can_create, can_edit, can_delete)
                VALUES
                (:user_id, :module, :create, :edit, :delete)
            ");

            $updateStmt = $this->pdo->prepare("
                UPDATE permissions SET
                    can_create = :create,
                    can_edit   = :edit,
                    can_delete = :delete
                WHERE id = :id
            ");

            $deleteStmt = $this->pdo->prepare("DELETE FROM permissions WHERE id = :id");

            foreach ($modules as $module) {

                $permId = $permissionIds[$module] ?? null;
                $perms  = $permissions[$module] ?? [];

                // Permission calc
                if (empty($perms)) {
                    $create = $edit = $delete = 1;
                } else {
                    $create = in_array('create', $perms, true) ? 1 : 0;
                    $edit   = in_array('edit',   $perms, true) ? 1 : 0;
                    $delete = in_array('delete', $perms, true) ? 1 : 0;
                }

                /* ---------- INSERT ---------- */
                if (empty($permId)) {

                    $insertStmt->execute([
                        ':user_id' => $userId,
                        ':module'  => $module,
                        ':create'  => $create,
                        ':edit'    => $edit,
                        ':delete'  => $delete
                    ]);

                    $newId = $this->pdo->lastInsertId();

                    $fetchStmt->execute([':id' => $newId]);
                    $newDataLog['inserted'][] = $fetchStmt->fetch(PDO::FETCH_ASSOC);
                }
                /* ---------- UPDATE ---------- */ else {

                    $fetchStmt->execute([':id' => $permId]);
                    $oldData = $fetchStmt->fetch(PDO::FETCH_ASSOC);

                    $updateStmt->execute([
                        ':create' => $create,
                        ':edit'   => $edit,
                        ':delete' => $delete,
                        ':id'     => $permId
                    ]);

                    $fetchStmt->execute([':id' => $permId]);
                    $newData = $fetchStmt->fetch(PDO::FETCH_ASSOC);

                    // Log only if something changed
                    if ($oldData != $newData) {
                        $oldDataLog['updated'][] = $oldData;
                        $newDataLog['updated'][] = $newData;
                    }
                }
            }

            foreach ($permissionIds as $module => $permId) {

                if (!in_array($module, $modules, true) && !empty($permId)) {

                    $fetchStmt->execute([':id' => $permId]);
                    $oldDataLog['deleted'][] = $fetchStmt->fetch(PDO::FETCH_ASSOC);

                    $deleteStmt->execute([':id' => $permId]);
                }
            }

            if (!empty($oldDataLog) || !empty($newDataLog)) {

                $this->logger->log(
                    'permissions',
                    $userId,
                    'PERMISSION_SYNC',
                    null,
                    json_encode($oldDataLog),
                    json_encode($newDataLog),
                    $updatedBy,
                    $this->loggedId
                );
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new Exception($e->getMessage(), (int)$e->getCode());
        }
    }
}
