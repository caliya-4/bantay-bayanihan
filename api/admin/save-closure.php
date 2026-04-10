<?php
header('Content-Type: application/json');
require '../../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO road_closures (name, description, start_lat, start_lng, end_lat, end_lng, reported_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $input['name'] ?? 'Road Closure',
        $input['description'] ?? 'No details',
        $input['start_lat'],
        $input['start_lng'],
        $input['end_lat'],
        $input['end_lng'],
        $input['reported_by']
    ]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>