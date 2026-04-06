<?php
require 'db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email        = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm      = $_POST['confirm_password'];

    // 驗證信箱格式
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "信箱格式不正確！";
    }

    // 驗證新密碼長度
    if (strlen($new_password) < 6) {
        $errors[] = "密碼至少需要 6 個字元！";
    }

    // 驗證兩次密碼是否一致
    if ($new_password !== $confirm) {
        $errors[] = "兩次密碼不一致！";
    }

    if (empty($errors)) {
        // 查詢信箱是否存在
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute([':email' => $email]);
        $user = $check->fetch();

        if (!$user) {
            $errors[] = "這個信箱不存在！";
        } else {
            // 更新密碼
            $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $sql  = "UPDATE users SET password_hash = :password_hash WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':password_hash' => $password_hash,
                ':email'         => $email,
            ]);
            $success = "密碼重設成功！請重新登入。";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>忘記密碼</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>忘記密碼</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p>❌ <?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success">
                <p>✅ <?php echo $success; ?></p>
            </div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST">
            <label>信箱：</label>
            <input type="email" name="email" required>

            <label>新密碼：</label>
            <input type="password" name="new_password" required>

            <label>確認新密碼：</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">重設密碼</button>
        </form>
        <a href="login.html">回到登入</a>
    </div>
</body>
</html>