<?php
session_start();
header('Content-Type: application/json');
require '../../db_connect.php';

// Authorization: only admin or responder users may edit centers
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

    // Build dynamic SET clause from provided fields
    $updatable = ['name', 'barangay', 'type', 'lat', 'lng'];
    $fields = [];
    $params = [];
    foreach ($updatable as $f) {
        if (isset($input[$f])) {
            $fields[] = "$f = ?";
            $params[] = $input[$f];
        }
    }

    if (empty($fields)) {
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        exit;
    }

    $params[] = $input['id'];
    $sql = "UPDATE evacuation_centers SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>