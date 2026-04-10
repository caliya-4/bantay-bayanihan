<?php
session_start();
header('Content-Type: application/json');
require '../../db_connect.php';

// Authorization: only admin or responder users may delete centers
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'responder'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    if (empty($input['id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing id']);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM evacuation_centers WHERE id = ?");
    $stmt->execute([$input['id']]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>