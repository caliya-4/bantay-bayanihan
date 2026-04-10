<?php
header('Content-Type: application/json');
require '../../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

try {
    $stmt = $pdo->prepare("\
        INSERT INTO evacuation_centers (name, type, barangay, lat, lng, created_at)\
        VALUES (?, ?, ?, ?, ?, NOW())\
    ");
    $stmt->execute([
        $input['name'] ?? 'Unnamed Center',
        $input['type'] ?? 'safe_zone',
        $input['barangay'] ?? 'Unknown',
        $input['lat'],
        $input['lng']
    ]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>