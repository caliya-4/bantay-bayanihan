<?php
// api/gamification/update-checklist.php - Toggle checklist item
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    require_once __DIR__ . '/../../db_connect.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    $item_id = $input['item_id'] ?? 0;
    $completed = $input['completed'] ?? false;
    $user_id = $_SESSION['user_id'];
    
    // Get item points
    $stmt = $pdo->prepare("SELECT points FROM checklist_items WHERE id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }
    
    $pdo->beginTransaction();
    
    // Initialize user_stats if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_stats (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    
    // Check if record exists
    $stmt = $pdo->prepare("SELECT id, completed FROM user_checklist WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$user_id, $item_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing
        if ($completed && !$existing['completed']) {
            // Marking as complete - award points
            $stmt = $pdo->prepare("UPDATE user_checklist SET completed = 1, completed_at = NOW() WHERE user_id = ? AND item_id = ?");
            $stmt->execute([$user_id, $item_id]);
            
            $stmt = $pdo->prepare("UPDATE user_stats SET total_points = total_points + ? WHERE user_id = ?");
            $stmt->execute([$item['points'], $user_id]);
            
            $points_earned = $item['points'];
            
        } elseif (!$completed && $existing['completed']) {
            // Unmarking - remove points
            $stmt = $pdo->prepare("UPDATE user_checklist SET completed = 0, completed_at = NULL WHERE user_id = ? AND item_id = ?");
            $stmt->execute([$user_id, $item_id]);
            
            $stmt = $pdo->prepare("UPDATE user_stats SET total_points = total_points - ? WHERE user_id = ?");
            $stmt->execute([$item['points'], $user_id]);
            
            $points_earned = -$item['points'];
        } else {
            $points_earned = 0;
        }
    } else {
        // Insert new
        $completed_val = $completed ? 1 : 0;
        $completed_at = $completed ? 'NOW()' : 'NULL';
        
        $stmt = $pdo->prepare("INSERT INTO user_checklist (user_id, item_id, completed, completed_at) VALUES (?, ?, ?, " . ($completed ? "NOW()" : "NULL") . ")");
        $stmt->execute([$user_id, $item_id, $completed_val]);
        
        if ($completed) {
            $stmt = $pdo->prepare("UPDATE user_stats SET total_points = total_points + ? WHERE user_id = ?");
            $stmt->execute([$item['points'], $user_id]);
            $points_earned = $item['points'];
        } else {
            $points_earned = 0;
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'completed' => $completed,
        'points_earned' => $points_earned
    ]);
    
} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    error_log("Update checklist error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating checklist']);
}