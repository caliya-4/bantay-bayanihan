<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use relative path to db_connect
require __DIR__ . '/../../db_connect.php';

try {
    // Return all centers (ensure lat/lng present)
    // Note: some databases may not have a `capacity` column; select only universally present fields
    $stmt = $pdo->query("SELECT id, name, barangay, type, lat AS latitude, lng AS longitude FROM evacuation_centers WHERE lat IS NOT NULL AND lng IS NOT NULL ORDER BY name ASC");

    $sites = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['facilities'] = ['Basic Shelter'];
        $sites[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $sites]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>