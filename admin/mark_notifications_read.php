<?php
session_start();
require '../db_connect.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','responder'])){
    header('Location: ../login.php');
    exit;
}
$upd = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
$upd->execute([$_SESSION['user_id']]);
header('Location: dashboard.php');
exit;
