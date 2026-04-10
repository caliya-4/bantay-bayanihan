<?php
// Disable HTML error output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

session_start();

// Check authentication
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'responder'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input'
    ]);
    exit;
}

$drillId = (int)($input['drill_id'] ?? 0);
$type = $input['type'] ?? '';
$identifier = $input['identifier'] ?? '';
$action = $input['action'] ?? 'entered';
$value = $input['value'] ?? 1;

if ($drillId <= 0 || empty($type) || empty($identifier)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

try {
    require_once '../../db_connect.php';
    
    if ($type === 'email') {
        // Working with drill_participants table (walk-ins)
        
        if ($action === 'entered') {
            $stmt = $pdo->prepare("UPDATE drill_participants SET entered = ? WHERE drill_id = ? AND email = ?");
            $stmt->execute([$value, $drillId, $identifier]);
            
        } elseif ($action === 'start') {
            $stmt = $pdo->prepare("UPDATE drill_participants SET status = 'in_progress', started_at = NOW() WHERE drill_id = ? AND email = ?");
            $stmt->execute([$drillId, $identifier]);
            
        } elseif ($action === 'complete') {
            $stmt = $pdo->prepare("UPDATE drill_participants SET status = 'completed', finished_at = NOW() WHERE drill_id = ? AND email = ?");
            $stmt->execute([$drillId, $identifier]);
        }
        
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid type'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Updated successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}
?>