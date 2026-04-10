<?php
// api/evacuation/nearest.php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    require_once __DIR__ . '/../../db_connect.php';
    
    // Get parameters
    $lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
    $lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
    
    if ($lat === null || $lng === null) {
        echo json_encode(['success' => false, 'message' => 'Latitude and longitude are required']);
        exit;
    }
    
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
        exit;
    }
    
    // Get all centers with basic info (exclude capacity column which was removed)
    $sql = "SELECT 
                id,
                name,
                type,
                barangay,
                lat AS latitude,
                lng AS longitude,
                created_at
            FROM evacuation_centers";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate distances in PHP using Haversine formula
    foreach ($sites as &$site) {
        $lat2 = floatval($site['latitude']);
        $lng2 = floatval($site['longitude']);
        
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat);
        $dLng = deg2rad($lng2 - $lng);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        $site['distance_km'] = round($distance, 2);
    }
    
    // Sort by distance
    usort($sites, function($a, $b) {
        return $a['distance_km'] - $b['distance_km'];
    });
    
    // Limit to requested amount
    $sites = array_slice($sites, 0, $limit);
    
    echo json_encode([
        'success' => true,
        'data' => $sites,
        'count' => count($sites),
        'user_location' => ['lat' => $lat, 'lng' => $lng]
    ]);
    
} catch (PDOException $e) {
    error_log("DB ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error_code' => 'DB_ERROR',
        'details' => $e->getMessage()
    ]);
    
} catch (Exception $e) {
    error_log("GENERAL ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error_code' => 'SERVER_ERROR',
        'details' => $e->getMessage()
    ]);
}
?>