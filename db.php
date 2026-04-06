<?php
$host = '127.0.0.1';
$dbname = 'member_system';
$username = 'root';
$password = 'root1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    echo "連線失敗：" . $e->getMessage();
}
?>