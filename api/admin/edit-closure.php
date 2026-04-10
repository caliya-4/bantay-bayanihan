<?php
header('Content-Type: application/json');
require '../../db_connect.php';
$input = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $pdo->prepare("UPDATE road_closures SET description = ? WHERE id = ?");
    $stmt->execute([$input['description'], $input['id']]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>