<?php
session_start();
require_once 'db.php';
require_once 'middleware.php';
requireLogin();

$errors  = [];
$success = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm      = $_POST['confirm_password'];

    if (strlen($username) < 2) {
        $errors[] = "使用者名稱至少需要 2 個字！";
    }

    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $errors[] = "密碼至少需要 6 個字元！";
        }
        if ($new_password !== $confirm) {
            $errors[] = "兩次密碼不一致！";
        }
    }

    if (empty($errors)) {
        if (!empty($new_password)) {
            $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET username = :username, password_hash = :password_hash WHERE id = :id");
            $stmt->execute([':username' => $username, ':password_hash' => $password_hash, ':id' => $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = :username WHERE id = :id");
            $stmt->execute([':username' => $username, ':id' => $_SESSION['user_id']]);
        }
        $_SESSION['username'] = $username;
        $success = "資料更新成功！";

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>會員資料</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>會員資料編輯</h2>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p>❌ <?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success">✅ <?php echo $success; ?></div>
        <?php endif; ?>

        <form action="profile.php" method="POST">
            <label>使用者名稱：</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label>信箱：（不可修改）</label>
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>

            <label>新密碼：（不修改請留空）</label>
            <input type="password" name="new_password">

            <label>確認新密碼：</label>
            <input type="password" name="confirm_password">

            <button type="submit">儲存變更</button>
        </form>
        <a href="dashboard.php">回到首頁</a>
    </div>
</body>
</html>