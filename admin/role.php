<?php
require_once '../middleware.php';
requireAdmin();
require_once '../db.php';

$errors = [];
$success = '';

// 新增角色
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add') {
        $name        = trim($_POST['name']);
        $description = trim($_POST['description']);

        if (empty($name)) {
            $errors[] = '角色名稱不能為空';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (:name, :description)");
                $stmt->execute([':name' => $name, ':description' => $description]);
                $success = '角色新增成功！';
            } catch (PDOException $e) {
                $errors[] = '角色名稱已存在！';
            }
        }
    }

    if ($_POST['action'] === 'delete') {
        $roleId = (int)$_POST['role_id'];
        // 防止刪除 admin
        $role = $pdo->prepare("SELECT name FROM roles WHERE id = :id");
        $role->execute([':id' => $roleId]);
        $roleName = $role->fetchColumn();

        if ($roleName === 'admin') {
            $errors[] = 'admin 角色不能刪除！';
        } else {
            $pdo->prepare("DELETE FROM roles WHERE id = :id")
                ->execute([':id' => $roleId]);
            $success = '角色刪除成功！';
        }
    }
}

// 查詢所有角色 + 該角色有幾個人
$roles = $pdo->query("
    SELECT r.id, r.name, r.description,
           COUNT(ur.user_id) AS user_count
    FROM roles r
    LEFT JOIN user_roles ur ON r.id = ur.role_id
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
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:10px; border:1px solid #ddd; text-align:left; }
        th { background:#f5f5f5; }
        .form-row { display:flex; gap:12px; margin-top:20px; align-items:flex-end; }
        .form-row input { padding:8px; border:1px solid #ddd; border-radius:6px; flex:1; }
        .btn { padding:8px 16px; border:none; border-radius:6px; cursor:pointer; }
        .btn-add { background:#4f6ef7; color:#fff; }
        .btn-del { background:#e74c3c; color:#fff; }
        .success { color:green; margin-top:12px; }
        .error   { color:red;   margin-top:12px; }
    </style>
</head>
<body>
    <?php require_once 'navbar.php'; ?>
    <div class="container">
        <h2>角色管理</h2>
        <a href="index.php">← 回管理首頁</a>

        <?php foreach ($errors as $e): ?>
            <p class="error">❌ <?php echo $e; ?></p>
        <?php endforeach; ?>
        <?php if ($success): ?>
            <p class="success">✅ <?php echo $success; ?></p>
        <?php endif; ?>

        <!-- 新增角色表單 -->
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-row">
                <input type="text" name="name" placeholder="角色名稱（英文）" required>
                <input type="text" name="description" placeholder="說明">
                <button type="submit" class="btn btn-add">新增角色</button>
            </div>
        </form>

        <!-- 角色列表 -->
        <table>
            <tr>
                <th>ID</th>
                <th>角色名稱</th>
                <th>說明</th>
                <th>人數</th>
                <th>操作</th>
            </tr>
            <?php foreach ($roles as $role): ?>
            <tr>
                <td><?php echo $role['id']; ?></td>
                <td><?php echo $role['name']; ?></td>
                <td><?php echo $role['description']; ?></td>
                <td><?php echo $role['user_count']; ?> 人</td>
                <td>
                    <?php if ($role['name'] !== 'admin'): ?>
                    <form method="POST" onsubmit="return confirm('確定刪除？')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                        <button type="submit" class="btn btn-del">刪除</button>
                    </form>
                    <?php else: ?>
                        <span style="color:#aaa">不可刪除</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>