<?php
session_start();
require '../db_connect.php';
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'],['admin','responder'])) {
    header("Location: ../login.php");
    exit;
}
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage-emergencies.php');
    exit;
}
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';
$allowed = ['handled','spam','responding'];
if(!$id || !in_array($status, $allowed)) {
    header('Location: manage-emergencies.php');
    exit;
}
// Fetch emergency
$stmt = $pdo->prepare('SELECT * FROM emergencies WHERE id = ?');
$stmt->execute([$id]);
$em = $stmt->fetch();
if(!$em) {
    header('Location: manage-emergencies.php');
    exit;
}
// Update status
$up = $pdo->prepare('UPDATE emergencies SET status = ? WHERE id = ?');
$up->execute([$status, $id]);
// Insert notification for the reporting user
if($em['user_id']){
    $message = '';
    if($status === 'handled') {
        $message = "Your emergency report (ID: $id) has been marked as handled by the response team.";
    } elseif ($status === 'spam') {
        $message = "Your emergency report (ID: $id) has been marked as spam by the response team.";
    } elseif ($status === 'responding') {
        $message = "Your emergency report (ID: $id) is now being handled by the response team.";
    }
    $ins = $pdo->prepare('INSERT INTO notifications (user_id, message, url) VALUES (?, ?, ?)');
    $ins->execute([$em['user_id'], $message, 'resident/my-reports.php']);
}
header('Location: manage-emergencies.php?success=1');
exit;
