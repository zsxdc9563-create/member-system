<?php
session_start();
require_once 'middleware.php';
requireLogin();

// 檢查是否已登入，沒登入就跳回登入頁
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>會員首頁</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>歡迎回來！</h2>
        <p><?php echo $_SESSION['username']; ?></p>
        <p>你已成功登入會員系統。</p>

        <?php if (in_array('admin', $_SESSION['roles'])): ?>
            <div style="margin-top:20px; padding:16px; background:#f0f4ff; border-radius:8px;">
                <h3>管理員選單</h3>
                <a href="admin/user.php">使用者管理</a> ｜
                <a href="admin/role.php">角色管理</a> ｜
                <a href="admin/permission.php">權限管理</a>
            </div>
        <?php endif; ?>

        <div style="margin-top:20px;">
            <a href="profile.php">個人資料</a> ｜
            <a href="logout.php">登出</a>
        </div>
    </div>
</body>
</html>
