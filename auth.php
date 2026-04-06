<?php
require_once 'db.php';

// 檢查 user 有沒有某個 permission
function hasPermission(int $userId, string $permission): bool {
    global $pdo;
    
    $sql = "
        SELECT 1
        FROM user_roles ur
        JOIN role_permissions rp ON ur.role_id = rp.role_id
        JOIN permissions p ON rp.permission_id = p.id
        WHERE ur.user_id = :user_id AND p.name = :permission
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id'    => $userId,
        ':permission' => $permission
    ]);
    return $stmt->fetchColumn() !== false;
}

// 取得 user 的所有 roles
function getUserRoles(int $userId): array {
    global $pdo;
    
    $sql = "
        SELECT r.name
        FROM user_roles ur
        JOIN roles r ON ur.role_id = r.id
        WHERE ur.user_id = :user_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>