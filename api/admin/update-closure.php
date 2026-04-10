<?php
// api/admin/update-closure.php
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
    
    if (!$data || !isset($data['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data or missing ID'
        ]);
        exit;
    }
    
    $closure_id = intval($data['id']);
    $status = isset($data['status']) ? trim($data['status']) : null;
    
    if (!$status || !in_array($status, ['active', 'resolved', 'inactive'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid status value'
        ]);
        exit;
    }
    
    // Check if closure exists
    $check = $pdo->prepare("SELECT id FROM road_closures WHERE id = ?");
    $check->execute([$closure_id]);
    
    if (!$check->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Road closure not found'
        ]);
        exit;
    }
    
    // Update the closure
    $stmt = $pdo->prepare("UPDATE road_closures SET status = ?, updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$status, $closure_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Road closure updated successfully',
            'data' => [
                'id' => $closure_id,
                'status' => $status
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update road closure'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Update closure error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("General error in update-closure.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred'
    ]);
}