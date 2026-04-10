<?php
// get_evac_centers.php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require '../db_connect.php';

try {
    $stmt = $pdo->query("\
        SELECT 
            name, 
            lat, 
            lng, 
            DATE_FORMAT(created_at, '%M %d, %Y %I:%i %p') as created_at 
        FROM evacuation_centers 
        ORDER BY id DESC
    ");
    $centers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // This line is very important — see what it returns
    error_log("get_evac_centers.php loaded " . count($centers) . " centers");
    
    echo json_encode($centers);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>