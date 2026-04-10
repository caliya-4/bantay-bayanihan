<?php
// api/admin/add-closure.php
session_start();
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
    $required = ['start_lat', 'start_lng', 'end_lat', 'end_lng', 'description'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            echo json_encode([
                'success' => false,
                'message' => "Missing required field: $field"
            ]);
            exit;
        }
    }
    
    // Validate coordinates
    $start_lat = floatval($data['start_lat']);
    $start_lng = floatval($data['start_lng']);
    $end_lat = floatval($data['end_lat']);
    $end_lng = floatval($data['end_lng']);
    $description = trim($data['description']);
    $severity = isset($data['severity']) ? trim($data['severity']) : 'medium';
    $status = isset($data['status']) ? trim($data['status']) : 'active';
    
    // Validate coordinate ranges
    if ($start_lat < -90 || $start_lat > 90 || $end_lat < -90 || $end_lat > 90) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid latitude values'
        ]);
        exit;
    }
    
    if ($start_lng < -180 || $start_lng > 180 || $end_lng < -180 || $end_lng > 180) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid longitude values'
        ]);
        exit;
    }
    
    // Insert into database
    $sql = "INSERT INTO road_closures 
            (start_lat, start_lng, end_lat, end_lng, description, severity, status, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $start_lat,
        $start_lng,
        $end_lat,
        $end_lng,
        $description,
        $severity,
        $status,
        $_SESSION['user_id']
    ]);
    
    if ($result) {
        $closure_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Road closure added successfully',
            'closure_id' => $closure_id,
            'data' => [
                'id' => $closure_id,
                'start_lat' => $start_lat,
                'start_lng' => $start_lng,
                'end_lat' => $end_lat,
                'end_lng' => $end_lng,
                'description' => $description,
                'severity' => $severity,
                'status' => $status
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add road closure'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Road closure add error: " . $e->getMessage());
    
    // Check if table doesn't exist
    if ($e->getCode() == '42S02') {
        echo json_encode([
            'success' => false,
            'message' => 'Road closures table does not exist. Please create it first.',
            'error_code' => 'TABLE_NOT_FOUND'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred',
            'error_code' => 'DB_ERROR'
        ]);
    }
} catch (Exception $e) {
    error_log("General error in add-closure.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred'
    ]);
}