<?php
// api/admin/delete-closure.php
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

// Only accept POST or DELETE requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) {
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
    
    // Also check for GET parameter as fallback
    $closure_id = null;
    if (isset($data['id'])) {
        $closure_id = intval($data['id']);
    } elseif (isset($_GET['id'])) {
        $closure_id = intval($_GET['id']);
    }
    
    if (!$closure_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Closure ID is required'
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
    
    // Delete the closure
    $stmt = $pdo->prepare("DELETE FROM road_closures WHERE id = ?");
    $result = $stmt->execute([$closure_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Road closure deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete road closure'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Delete closure error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("General error in delete-closure.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred'
    ]);
}