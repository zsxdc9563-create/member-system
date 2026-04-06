<?php
session_start();


// 清除所有 Session 資料
session_destroy();


// 跳回登入頁
header('Location: login.html');
exit;

?>