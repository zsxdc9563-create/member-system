<?php
require 'db.php';  //接收表單資料

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];


    //驗證使用者名稱
   
    if (strlen($username) < 2) {
        $errors[] = "使用者名稱至少需要 2個字!";
    }

    //驗證信箱格式
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "信箱格式不正確!";
    }

    //驗證密碼長度
    if (strlen($password) < 6){
        $errors[] = "密碼至少需要6個字元!";
    }

    // 檢查信箱是否已存在
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $errors[] = "這個信箱已經註冊過了！";
        }
    }



    // 沒有錯誤才寫入資料庫
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $sql  = "INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username'      => $username,
            ':email'         => $email,
            ':password_hash' => $password_hash,
        ]);
        header('Location: login.html');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">    
<head>
    <meta charset="UTF-8">
    <title>會員註冊</title>
    <link rel="stylesheet" href="style.css">
   
</head>
<body>
    <div class="container">
        <h2>會員註冊</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p>❌ <?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <label>使用者名稱：</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>

            <label>信箱：</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>

            <label>密碼：</label>
            <input type="password" name="password" required>

            <button type="submit">註冊</button>
        </form>
        <a href="login.html">已有帳號？登入</a>
    </div>
</body>
</html>