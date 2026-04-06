<?php
require_once '../middleware.php';
requireAdmin();
require_once '../db.php';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add_permission') {
        $name     = trim($_POST['name']);
        $resource = trim($_POST['resource']);
        $action   = trim($_POST['action_name']);

        if (empty($name) || empty($resource) || empty($action)) {
            $errors[] = '所有欄位都必須填寫！';
        } else {
            try {
                $pdo->prepare("INSERT INTO permissions (name, resource, action) VALUES (:name, :resource, :action)")
                    ->execute([':name' => $name, ':resource' => $resource, ':action' => $action]);
                $success = '權限新增成功！';
            } catch (PDOException $e) {
                $errors[] = '權限名稱已存在！';
            }
        }
    }

    if ($_POST['action'] === 'delete_permission') {
        $permissionId = (int)$_POST['permission_id'];
        $pdo->prepare("DELETE FROM role_permissions WHERE permission_id = :id")
            ->execute([':id' => $permissionId]);
        $pdo->prepare("DELETE FROM permissions WHERE id = :id")
            ->execute([':id' => $permissionId]);
        $success = '權限刪除成功！';
    }

    if ($_POST['action'] === 'add_role_permission') {
        $roleId       = (int)$_POST['role_id'];
        $permissionId = (int)$_POST['permission_id'];
        try {
            $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)")
                ->execute([':role_id' => $roleId, ':permission_id' => $permissionId]);
            $success = '權限指派成功！';
        } catch (PDOException $e) {
            $errors[] = '該角色已有此權限！';
        }
    }

    if ($_POST['action'] === 'remove_role_permission') {
        $roleId       = (int)$_POST['role_id'];
        $permissionId = (int)$_POST['permission_id'];
        $pdo->prepare("DELETE FROM role_permissions WHERE role_id = :role_id AND permission_id = :permission_id")
            ->execute([':role_id' => $roleId, ':permission_id' => $permissionId]);
        $success = '權限移除成功！';
    }
}

$roles       = $pdo->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
$permissions = $pdo->query("SELECT * FROM permissions ORDER BY resource, action")->fetchAll(PDO::FETCH_ASSOC);
$rolePermissions = $pdo->query("SELECT role_id, permission_id FROM role_permissions")->fetchAll(PDO::FETCH_ASSOC);
$rolePermMap = [];
foreach ($rolePermissions as $rp) {
    $rolePermMap[$rp['role_id']][] = $rp['permission_id'];
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>權限管理</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .perm-table { width:100%; border-collapse:collapse; margin-top:16px; }
        .perm-table th, .perm-table td { padding:8px 12px; border:1px solid #ddd; text-align:center; }
        .perm-table th { background:#f5f5f5; }
        .perm-table td:first-child { text-align:left; }
        .resource-header { background:#e8f0fe; font-weight:500; text-align:left; }
    </style>
</head>
<body class="page">
    <?php require_once 'navbar.php'; ?>

    <div class="container">
        <h2>權限管理</h2>

        <?php foreach ($errors as $e): ?>
            <p class="error">❌ <?php echo $e; ?></p>
        <?php endforeach; ?>
        <?php if ($success): ?>
            <p class="success">✅ <?php echo $success; ?></p>
        <?php endif; ?>

        <!-- 新增權限 -->
        <div class="section">
            <h3>新增權限</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_permission">
                <div class="form-row">
                    <input type="text" name="resource" placeholder="resource（如 post）" required>
                    <input type="text" name="action_name" placeholder="action（如 read）" required>
                    <input type="text" name="name" placeholder="名稱（如 post:read）" required>
                    <button type="submit" class="btn btn-add">新增</button>
                </div>
            </form>
        </div>

        <!-- 權限矩陣 -->
        <div class="section">
            <h3>角色權限矩陣</h3>
            <p style="color:#888; font-size:13px;">點擊 ✅ 移除權限，點擊 ⭕ 新增權限</p>
            <table class="perm-table">
                <tr>
                    <th>權限</th>
                    <?php foreach ($roles as $role): ?>
                        <th><?php echo $role['name']; ?></th>
                    <?php endforeach; ?>
                    <th>刪除</th>
                </tr>
                <?php
                $currentResource = '';
                foreach ($permissions as $perm):
                    if ($perm['resource'] !== $currentResource):
                        $currentResource = $perm['resource'];
                ?>
                <tr>
                    <td colspan="<?php echo count($roles) + 2; ?>" class="resource-header">
                        📁 <?php echo $currentResource; ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><?php echo $perm['name']; ?></td>
                    <?php foreach ($roles as $role): ?>
                        <td>
                            <?php $hasPermission = in_array($perm['id'], $rolePermMap[$role['id']] ?? []); ?>
                            <form method="POST">
                                <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                                <input type="hidden" name="permission_id" value="<?php echo $perm['id']; ?>">
                                <?php if ($hasPermission): ?>
                                    <input type="hidden" name="action" value="remove_role_permission">
                                    <button type="submit" style="background:none;border:none;cursor:pointer;font-size:18px;">✅</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="add_role_permission">
                                    <button type="submit" style="background:none;border:none;cursor:pointer;font-size:18px;">⭕</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    <?php endforeach; ?>
                    <td>
                        <form method="POST" onsubmit="return confirm('確定刪除此權限？')">
                            <input type="hidden" name="action" value="delete_permission">
                            <input type="hidden" name="permission_id" value="<?php echo $perm['id']; ?>">
                            <button type="submit" class="btn btn-del">刪除</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>