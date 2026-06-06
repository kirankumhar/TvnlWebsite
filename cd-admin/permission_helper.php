<?php
// permission_helper.php

/**
 * Get module permission for logged-in user
 * @param PDO $pdo
 * @param int $userId
 * @param string $module
 * @return array
 */
function getModulePermission(PDO $pdo, int $userId, string $module): array
{
    static $permissionsCache = [];

    // Cache to avoid multiple DB hits
    if (isset($permissionsCache[$module])) {
        return $permissionsCache[$module];
    }

    $stmt = $pdo->prepare("
        SELECT can_create, can_edit, can_delete
        FROM permissions
        WHERE user_id = :user_id
          AND module = :module
        LIMIT 1
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':module'  => $module
    ]);

    $perm = $stmt->fetch(PDO::FETCH_ASSOC);

    // Default = NO permission
    $permissionsCache[$module] = [
        'create' => (int)($perm['can_create'] ?? 0),
        'edit'   => (int)($perm['can_edit'] ?? 0),
        'delete' => (int)($perm['can_delete'] ?? 0),
    ];

    return $permissionsCache[$module];
}

function hasModuleRow(PDO $pdo, int $userId, string $module): bool
{
    $stmt = $pdo->prepare("
        SELECT id 
        FROM permissions 
        WHERE user_id = :uid 
          AND module = :module
        LIMIT 1
    ");
    $stmt->execute([
        ':uid'    => $userId,
        ':module' => $module
    ]);

    return (bool) $stmt->fetchColumn();
}

function canCreate(PDO $pdo, int $userId, string $module): bool
{
    return getModulePermission($pdo, $userId, $module)['create'] === 1;
}

function canEdit(PDO $pdo, int $userId, string $module): bool
{
    return getModulePermission($pdo, $userId, $module)['edit'] === 1;
}

function canDelete(PDO $pdo, int $userId, string $module): bool
{
    return getModulePermission($pdo, $userId, $module)['delete'] === 1;
}
