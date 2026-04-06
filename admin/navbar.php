<?php
// admin/navbar.php
?>
<nav style="
    background: #2c3e50;
    padding: 12px 32px;
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 24px;
">
    <span style="color:#fff; font-weight:bold; font-size:16px;">⚙️ 管理介面</span>
    
    <a href="/member/admin/user.php" style="color:#ccc; text-decoration:none; font-size:14px;">
        👤 使用者管理
    </a>
    <a href="/member/admin/role.php" style="color:#ccc; text-decoration:none; font-size:14px;">
        🏷️ 角色管理
    </a>
    <a href="/member/admin/permission.php" style="color:#ccc; text-decoration:none; font-size:14px;">
        🔑 權限管理
    </a>

    <span style="flex:1"></span>

    <span style="color:#aaa; font-size:14px;">
        <?php echo $_SESSION['username']; ?>（admin）
    </span>
    <a href="/member/dashboard.php" style="color:#ccc; text-decoration:none; font-size:14px;">
        🏠 回首頁
    </a>
    <a href="/member/logout.php" style="color:#e74c3c; text-decoration:none; font-size:14px;">
        登出
    </a>
</nav>