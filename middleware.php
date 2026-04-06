<?php

require_once __DIR__ . '/auth.php';

// 確認有登入
function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: /member/login.html');   // ✅
        exit;
    }
}

// 確認有登入 + 有指定 permission
function requirePermission(string $permission): void {
    requireLogin();
    
    if (!hasPermission($_SESSION['user_id'], $permission)) {
        http_response_code(403);
        echo '你沒有權限存取此頁面';
        exit;
    }
}

// 確認是 admin
function requireAdmin(): void {
    requireLogin();
    
    $roles = getUserRoles($_SESSION['user_id']);
    if (!in_array('admin', $roles)) {
        http_response_code(403);
        echo '此頁面僅限管理員';
        exit;
    }
}
?>