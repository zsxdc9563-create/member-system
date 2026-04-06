<?php
require_once '../middleware.php';
requireAdmin();
require_once '../db.php';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // 新增角色
    if ($_POST['action'] === 'add') {
        $name        = trim($_POST['name']);
        $description = trim($_POST['description']);

        if (empty($name)) {
            $errors[] = '角色名稱不能為空';
        } else {
            try {
                $pdo->prepare("INSERT INTO roles (name, description) VALUES (:name, :description)")
                    ->execute([':name' => $name, ':description' => $description]);
                $success = '角色新增成功！';
            } catch (PDOException $e) {
                $errors[] = '角色名稱已存在！';
            }
        }
    }

    // 編輯角色
    if ($_POST['action'] === 'edit') {
        $roleId      = (int)$_POST['role_id'];
        $name        = trim($_POST['name']);
        $description = trim($_POST['description']);

        if (empty($name)) {
            $errors[] = '角色名稱不能為空';
        } else {
            try {
                $pdo->prepare("UPDATE roles SET name = :name, description = :description WHERE id = :id")
                    ->execute([':name' => $name, ':description' => $description, ':id' => $roleId]);
                $success = '角色更新成功！';
            } catch (PDOException $e) {
                $errors[] = '角色名稱已存在！';
            }
        }
    }

    // 刪除角色
    if ($_POST['action'] === 'delete') {
        $roleId = (int)$_POST['role_id'];
        $role   = $pdo->prepare("SELECT name FROM roles WHERE id = :id");
        $role->execute([':id' => $roleId]);
        $roleName = $role->fetchColumn();

        if ($roleName === 'admin') {
            $errors[] = 'admin 角色不能刪除！';
        } else {
            $pdo->prepare("DELETE FROM roles WHERE id = :id")->execute([':id' => $roleId]);
            $success = '角色刪除成功！';
        }
    }
}

// 查詢所有角色 + 人數 + 擁有的權限
$roles = $pdo->query("
    SELECT r.id, r.name, r.description,
           COUNT(DISTINCT ur.user_id) AS user_count,
           GROUP_CONCAT(p.name ORDER BY p.name SEPARATOR ', ') AS permissions
    FROM roles r
    LEFT JOIN user_roles ur ON r.id = ur.role_id
    LEFT JOIN role_permissions rp ON r.id = rp.role_id
    LEFT JOIN permissions p ON rp.permission_id = p.id
    GROUP BY r.id, r.name, r.description
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>角色管理</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .perm-list { font-size:12px; color:#666; margin-top:4px; }
        .edit-form { display:none; margin-top:8px; }
        .edit-form input { width:auto; margin:0; padding:6px; }
    </style>
</head>
<body class="page">
    <?php require_once 'navbar.php'; ?>

    <div class="container">
        <h2>角色管理</h2>

        <?php foreach ($errors as $e): ?>
            <p class="error">❌ <?php echo $e; ?></p>
        <?php endforeach; ?>
        <?php if ($success): ?>
            <p class="success">✅ <?php echo $success; ?></p>
        <?php endif; ?>

        <!-- 新增角色 -->
        <div class="section">
            <h3>新增角色</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <input type="text" name="name" placeholder="角色名稱（英文）" required>
                    <input type="text" name="description" placeholder="說明">
                    <button type="submit" class="btn btn-add">新增</button>
                </div>
            </form>
        </div>

        <!-- 角色列表 -->
        <div class="section">
            <h3>角色列表</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>角色名稱</th>
                    <th>說明</th>
                    <th>人數</th>
                    <th>擁有權限</th>
                    <th>操作</th>
                </tr>
                <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?php echo $role['id']; ?></td>
                    <td><?php echo $role['name']; ?></td>
                    <td><?php echo $role['description']; ?></td>
                    <td><?php echo $role['user_count']; ?> 人</td>
                    <td>
                        <div class="perm-list">
                            <?php echo $role['permissions'] ?? '（無）'; ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($role['name'] !== 'admin'): ?>
                            <!-- 編輯按鈕 -->
                            <button class="btn btn-add"
                                onclick="toggleEdit(<?php echo $role['id']; ?>)">
                                編輯
                            </button>

                            <!-- 刪除按鈕 -->
                            <form method="POST" style="display:inline"
                                onsubmit="return confirm('確定刪除？')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                                <button type="submit" class="btn btn-del">刪除</button>
                            </form>

                            <!-- 編輯表單（預設隱藏）-->
                            <div class="edit-form" id="edit-<?php echo $role['id']; ?>">
                                <form method="POST">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                                    <div class="form-row">
                                        <input type="text" name="name"
                                            value="<?php echo htmlspecialchars($role['name']); ?>" required>
                                        <input type="text" name="description"
                                            value="<?php echo htmlspecialchars($role['description']); ?>">
                                        <button type="submit" class="btn btn-add">儲存</button>
                                        <button type="button" class="btn"
                                            style="background:#888;color:#fff"
                                            onclick="toggleEdit(<?php echo $role['id']; ?>)">取消</button>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <span style="color:#aaa">不可修改</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <script>
    function toggleEdit(id) {
        const form = document.getElementById('edit-' + id);
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
    </script>
</body>
</html>