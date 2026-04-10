<?php
session_start();
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in JSON response
ini_set('log_errors', 1);

// Check authentication
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'responder'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
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

// Validate required fields
$drillId = isset($input['drill_id']) ? (int)$input['drill_id'] : 0;
$type = isset($input['type']) ? $input['type'] : '';
$identifier = isset($input['identifier']) ? $input['identifier'] : '';
$entered = isset($input['entered']) ? (int)$input['entered'] : 0;

if ($drillId <= 0 || empty($type) || empty($identifier)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

// Validate entered value (0 or 1)
if (!in_array($entered, [0, 1])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid entered value'
    ]);
    exit;
}

try {
    // Include database connection
    require_once '../../db_connect.php';
    
    if ($type === 'user') {
        // Update registered user
        $stmt = $pdo->prepare("
            UPDATE drill_participations 
            SET entered = ? 
            WHERE drill_id = ? AND user_id = ?
        ");
        $stmt->execute([$entered, $drillId, $identifier]);
        $affected = $stmt->rowCount();
        
    } elseif ($type === 'email') {
        // Update public participant (walk-in)
        $stmt = $pdo->prepare("
            UPDATE drill_participants 
            SET entered = ? 
            WHERE drill_id = ? AND email = ?
        ");
        $stmt->execute([$entered, $drillId, $identifier]);
        $affected = $stmt->rowCount();
        
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid participant type'
        ]);
        exit;
    }
    
    if ($affected === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No participant found to update'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Participant status updated successfully',
        'entered' => $entered
    ]);
    
} catch (PDOException $e) {
    // Log the error
    error_log("Database error in update-participant-entered.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
} catch (Exception $e) {
    error_log("Error in update-participant-entered.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}

$action = $input['action'] ?? 'entered'; // New parameter
$drillId = (int)$input['drill_id'];
$type = $input['type'];
$identifier = $input['identifier'];

try {
    require_once '../../db_connect.php';
    
    if ($type === 'email') {
        // Update drill_participants table
        
        if ($action === 'entered') {
            $stmt = $pdo->prepare("UPDATE drill_participants SET entered = ? WHERE drill_id = ? AND email = ?");
            $stmt->execute([$input['value'], $drillId, $identifier]);
            
        } elseif ($action === 'start') {
            $stmt = $pdo->prepare("UPDATE drill_participants SET status = 'in_progress', started_at = NOW() WHERE drill_id = ? AND email = ?");
            $stmt->execute([$drillId, $identifier]);
            
        } elseif ($action === 'complete') {
            $stmt = $pdo->prepare("UPDATE drill_participants SET status = 'completed', finished_at = NOW() WHERE drill_id = ? AND email = ?");
            $stmt->execute([$drillId, $identifier]);
        }
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>