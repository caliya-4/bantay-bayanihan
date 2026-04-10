<?php
// api/evacuation/add-center.php
session_start();
header('Content-Type: application/json');

// Check if user is admin or responder
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'responder'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    require_once __DIR__ . '/../../db_connect.php';
    
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }
    
    // Validate required fields
    $name = isset($data['name']) ? trim($data['name']) : '';
    $type = isset($data['type']) ? trim($data['type']) : '';
    $barangay = isset($data['barangay']) ? trim($data['barangay']) : '';
    $latitude = isset($data['lat']) ? floatval($data['lat']) : 0;
    $longitude = isset($data['lng']) ? floatval($data['lng']) : 0;
    
    // Validation
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Name is required']);
        exit;
    }
    
    if (empty($type)) {
        echo json_encode(['success' => false, 'message' => 'Type is required']);
        exit;
    }
    
    if (empty($barangay)) {
        echo json_encode(['success' => false, 'message' => 'Barangay is required']);
        exit;
    }
    
    if ($latitude == 0 || $longitude == 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
        exit;
    }
    
    if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
        echo json_encode(['success' => false, 'message' => 'Coordinates out of range']);
        exit;
    }
    
    // Insert into evacuation_centers table (no `status` column in schema)
    $sql = "INSERT INTO evacuation_centers 
            (name, type, barangay, lat, lng, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$name, $type, $barangay, $latitude, $longitude]);

    if ($result) {
        $site_id = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Evacuation site added successfully',
            'site_id' => $site_id,
            'data' => [
                'id' => $site_id,
                'name' => $name,
                'type' => $type,
                'barangay' => $barangay,
                'lat' => $latitude,
                'lng' => $longitude
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert into database']);
    }
    
} catch (PDOException $e) {
    error_log("Add evacuation site error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in add-center.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}