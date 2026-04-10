<?php
// api/evacuation/search.php
header('Content-Type: application/json');
require_once '../../db_connect.php';

try {
    $barangay = isset($_GET['barangay']) ? trim($_GET['barangay']) : '';
    
    if (empty($barangay)) {
        echo json_encode(['success' => false, 'message' => 'Barangay parameter required']);
        exit;
    }
    
    // Search for evacuation sites in the specified barangay
    $stmt = $pdo->prepare("
        SELECT id, name, type, barangay, latitude, longitude, capacity, facilities, status
        FROM evacuation_sites 
        WHERE LOWER(barangay) LIKE LOWER(?)
        AND status = 'active'
        ORDER BY name ASC
    ");
    
    $stmt->execute(['%' . $barangay . '%']);
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse JSON facilities if stored as JSON
    foreach ($sites as &$site) {
        if (isset($site['facilities']) && is_string($site['facilities'])) {
            $decoded = json_decode($site['facilities'], true);
            if ($decoded !== null) {
                $site['facilities'] = $decoded;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $sites,
        'count' => count($sites)
    ]);
    
} catch (PDOException $e) {
    error_log("Evacuation search error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}