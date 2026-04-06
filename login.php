<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $sql  = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];

            // 存 roles 進 session
            $roleStmt = $pdo->prepare("
                SELECT r.name
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = :user_id
            ");
            $roleStmt->execute([':user_id' => $user['id']]);
            $_SESSION['roles'] = $roleStmt->fetchAll(PDO::FETCH_COLUMN);

            header('Location: dashboard.php');
            exit;
        } else {
            echo "信箱或密碼錯誤！";
        }
    } catch (PDOException $e) {
        echo "錯誤：" . $e->getMessage();
    }
}
?>