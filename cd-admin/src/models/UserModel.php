<?php
require_once __DIR__ . '../../utils/ActivityLogger.php';

class UserModel
{
    private $pdo;
    private $logger;
    private $loggedId;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->logger = new ActivityLogger($pdo);
        $this->loggedId = $_SESSION['login_id'];
        date_default_timezone_set('Asia/Kolkata');
    }

    public function isUserBlocked($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT blocked_until 
            FROM users 
            WHERE id = :user_id 
            AND blocked_until > NOW()
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function incrementFailedAttempt($userId)
    {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET failed_attempts = failed_attempts + 1,
                last_failed_attempt = NOW()
            WHERE id = :user_id
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Return current attempt count
        $stmt = $this->pdo->prepare("SELECT failed_attempts FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function blockUser($userId, $minutes = 60)
    {
        $blockUntil = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));

        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET blocked_until = :block_until
            WHERE id = :user_id
        ");
        $stmt->bindParam(':block_until', $blockUntil);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function resetFailedAttempts($userId)
    {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET failed_attempts = 0,
                blocked_until = NULL,
                last_failed_attempt = NULL
            WHERE id = :user_id
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getBlockedTimeRemaining($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT TIMESTAMPDIFF(MINUTE, NOW(), blocked_until) as minutes_remaining
            FROM users 
            WHERE id = :user_id 
            AND blocked_until > NOW()
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    // Add this method to check both email and mobile
    public function isEmailOrMobileExists($email, $mobile = null)
    {
        if ($mobile) {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM users 
                WHERE email = :email OR mobile = :mobile
            ");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':mobile', $mobile);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM users 
                WHERE email = :email
            ");
            $stmt->bindParam(':email', $email);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function getUserByEmail($email)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE email = :email 
            AND is_deleted = '0'
        ");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
        try {
            if ($this->isEmailOrMobileExists($data['email'], $data['phone'])) {
                throw new Exception("Email or mobile number already registered.");
            }

            $this->pdo->beginTransaction();

            $now = date('Y-m-d H:i:s');
            $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

            $userSql = "
                INSERT INTO users (
                    domain_id, username, email, mobile, role,
                    password, password_expire_in_days,
                    password_set_date, created_by, created_at, user_ip
                ) VALUES (
                    :domain_id, :name, :email, :phone, :role,
                    :password, :password_expire_in_days,
                    :password_set_date, :created_by, :created_at, :user_ip
                )
            ";

            $userStmt = $this->pdo->prepare($userSql);
            $userStmt->execute([
                ':domain_id' => $data['domain_id'],
                ':name'      => $data['name'],
                ':email'     => $data['email'],
                ':phone'     => $data['phone'],
                ':role'      => $data['role'],
                ':password'  => $data['password'],
                ':password_expire_in_days' => $data['password_expire_in_days'],
                ':password_set_date' => $now,
                ':created_by' => $data['created_by'],
                ':created_at' => $now,
                ':user_ip'    => $ip
            ]);

            $userId = $this->pdo->lastInsertId();

            $modules     = $data['module'] ?? [];
            $permissions = $data['permission'] ?? [];
            $createdBy = $data['created_by'];

            $permSql = "
            INSERT INTO permissions
            (user_id, module, can_create, can_edit, can_delete)
            VALUES
            (:user_id, :module, :create, :edit, :delete)";

            $permStmt = $this->pdo->prepare($permSql);

            foreach ($modules as $module) {

                // Default: all false
                $create = $edit = $delete = 0;

                if (!isset($permissions[$module])) {
                    $create = $edit = $delete = 1;
                } else {
                    $create = in_array('create', $permissions[$module], true) ? 1 : 0;
                    $edit   = in_array('edit',   $permissions[$module], true) ? 1 : 0;
                    $delete = in_array('delete', $permissions[$module], true) ? 1 : 0;
                }

                $permStmt->execute([
                    ':user_id' => $userId,
                    ':module'  => $module,
                    ':create'  => $create,
                    ':edit'    => $edit,
                    ':delete'  => $delete
                ]);
            }

            $data = [
                'domain_id' => $data['domain_id'],
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'],
                'role'      => $data['role'],
                'password'  => $data['password'],
                'password_expire_in_days' => $data['password_expire_in_days'],
                'password_set_date' => $now,
                'created_by' => $data['created_by'],
                'created_at' => $now,
                'user_ip'    => $ip ,
                'module'     => $module,
                'permission' => $permissions
            ];

            $this->logger->log('users', $userId, 'INSERT', null, null, json_encode($data), $createdBy, $this->loggedId);
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw new Exception($e->getMessage(), (int)$e->getCode());
        }
    }


    public function updateUser(array $data): bool
    {
        try {
            $this->pdo->beginTransaction();
            $now = date('Y-m-d H:i:s');
            $ip  = $_SERVER['REMOTE_ADDR'] ?? null;

            // Fetch old data
            $oldDataStmt = $this->pdo->prepare("SELECT domain_id , username, mobile, email, password, role, password_expire_in_days, updated_by, updated_at, user_ip FROM users WHERE id = :id");
            $oldDataStmt->execute([':id' => $data['user_id']]);
            $oldData = $oldDataStmt->fetch(PDO::FETCH_ASSOC);

            // Base SQL
            $sql = "UPDATE users SET
                    domain_id = :domain_id,
                    email = :email,
                    role = :role,
                    password_expire_in_days = :password_expire_in_days,
                    updated_by = :updated_by,
                    updated_at = :updated_at,
                    user_ip = :user_ip";

            // Optional fields
            if (!empty($data['username'])) {
                $sql .= ", username = :username";
            }

            if (!empty($data['phone'])) {
                $sql .= ", mobile = :mobile";
            }

            // Password update only if provided
            if (!empty($data['password'])) {
                $sql .= ", password = :password,
                      password_set_date = :password_set_date";
            }

            $sql .= " WHERE id = :user_id AND is_deleted = '0'";

            $stmt = $this->pdo->prepare($sql);

            // Required bindings
            $params = [
                ':domain_id' => $data['domain_id'],
                ':email' => $data['email'],
                ':username' => $data['username'],
                ':role' => $data['role'],
                ':password_expire_in_days' => $data['password_expire_in_days'],
                ':updated_by' => $data['update_by'],
                ':updated_at' => $now,
                ':user_ip' => $ip,
                ':user_id' => $data['user_id'],
            ];

            // Optional bindings
            if (!empty($data['username'])) {
                $params[':username'] = $data['username'];
            }

            if (!empty($data['phone'])) {
                $params[':mobile'] = $data['phone'];
            }

            if (!empty($data['password'])) {
                $params[':password'] = $data['password']; // already hashed
                $params[':password_set_date'] = $now;
            }

            $stmt->execute($params);

            $newData = json_encode([
                'domain_id' => $data['domain_id'],
                'email' => $data['email'],
                'username' => $data['username'],
                'mobile' => $data['phone'],
                'role' => $data['role'],
                'password_expire_in_days' => $data['password_expire_in_days'],
                'updated_by' => $data['update_by'],
                'updated_at' => $now,
                'user_ip' => $ip,
                'password' => $data['password']
            ]);

            $updatedBy = $_SESSION['user_id'];

            $this->logger->log(
                'users',
                $data['user_id'],
                'UPDATE',
                NULL,
                json_encode($oldData),
                $newData,
                $updatedBy,
                $this->loggedId
            );

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            // Log this in real projects instead of echo
            echo "<pre>";
            echo "Message: {$e->getMessage()}\n";
            echo "Code: {$e->getCode()}\n";
            print_r($e->errorInfo);
            echo "</pre>";

            return false;
        }
    }

    public function logActivity(int $userId, string $action, ?int $minutes = null)
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';

        $sql = "INSERT INTO users_logs (user_id, ip, action, duration)
            VALUES (:user_id, :ip, :action, :duration)";

        if ($action == 'User logged out' || $action == 'Session Timeout') {
            $loginTime = $_SESSION['login_time'];
            $logoutTime = date('Y-m-d H:i:s');
            $diffInSeconds = strtotime($logoutTime) - strtotime($loginTime);
            $minutes = floor($diffInSeconds / 60);
        } else {
            $minutes = null;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id'  => $userId,
            ':ip'       => $ip,
            ':action'   => $action,
            ':duration' => $minutes
        ]);

        // No insert ID needed for logout or timeout
        if (in_array($action, ['User logged out', 'Session Timeout'], true)) {
            return true;
        }

        return (int) $this->pdo->lastInsertId();
    }
}
