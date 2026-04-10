<?php
// api/evacuation/nearest-debug.php - Debug version with full error details
header('Content-Type: application/json');

// SHOW ALL ERRORS (REMOVE IN PRODUCTION!)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Include database connection
    require_once __DIR__ . '/../../db_connect.php';
    
    // Get parameters
    $lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
    $lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
    
    echo json_encode([
        'debug' => 'Step 1: Parameters received',
        'lat' => $lat,
        'lng' => $lng,
        'limit' => $limit
    ], JSON_PRETTY_PRINT);
    echo "\n\n";
    
    // Validate parameters
    if ($lat === null || $lng === null) {
        throw new Exception('Latitude and longitude are required');
    }
    
    echo json_encode(['debug' => 'Step 2: Parameters validated']) . "\n\n";
    
    // FIRST: Let's just get ALL sites without distance calculation
    echo json_encode(['debug' => 'Step 3: Fetching all sites (simple query)']) . "\n\n";
    
    $simple_sql = "SELECT id, name, type, barangay, latitude, longitude 
                   FROM evacuation_centers 
                     WHERE status = 'active'
                   LIMIT 5";
    
    try {
        $stmt = $pdo->query($simple_sql);
        $all_sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'debug' => 'Step 4: Simple query SUCCESS',
            'sites_found' => count($all_sites),
            'sample' => $all_sites
        ], JSON_PRETTY_PRINT);
        echo "\n\n";
        
    } catch (PDOException $e) {
        echo json_encode([
            'error' => 'Simple query FAILED',
            'message' => $e->getMessage(),
            'sql' => $simple_sql
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    // NOW: Try the distance calculation
    echo json_encode(['debug' => 'Step 5: Trying distance calculation query']) . "\n\n";
    
    $distance_sql = "SELECT 
                id,
                name,
                type,
                barangay,
                latitude,
                longitude,
                (6371 * acos(
                    cos(radians($lat)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians($lng)) + 
                    sin(radians($lat)) * 
                    sin(radians(latitude))
                )) AS distance_km
            FROM evacuation_sites 
            ORDER BY distance_km ASC
            LIMIT $limit";
    
    echo json_encode(['debug' => 'SQL Query', 'sql' => $distance_sql], JSON_PRETTY_PRINT) . "\n\n";
    
    try {
        $stmt = $pdo->query($distance_sql);
        $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format distance
        foreach ($sites as &$site) {
            $site['distance_km'] = number_format($site['distance_km'], 2);
        }
        
        echo json_encode([
            'debug' => 'Step 6: Distance query SUCCESS!',
            'success' => true,
            'data' => $sites,
            'count' => count($sites)
        ], JSON_PRETTY_PRINT);
        
    } catch (PDOException $e) {
        echo json_encode([
            'error' => 'Distance query FAILED',
            'message' => $e->getMessage(),
            'sql' => $distance_sql,
            'error_code' => $e->getCode()
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'PDO Exception',
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'General Exception',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}