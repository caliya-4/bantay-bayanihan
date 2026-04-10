<?php
header('Content-Type: application/json');
require '../db_connect.php';
$stmt = $pdo->query("SELECT e.*, u.name FROM emergencies e LEFT JOIN users u ON e.user_id = u.id");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>