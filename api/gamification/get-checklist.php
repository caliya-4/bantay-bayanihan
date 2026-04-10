<?php
// api/gamification/get-checklist.php - Get preparedness checklist
session_start();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../db_connect.php';
    
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Get all checklist items
    $sql = "SELECT id, category, item_text, description, priority, points, display_order 
            FROM checklist_items 
            WHERE active = 1 
            ORDER BY category, display_order";
    
    $stmt = $pdo->query($sql);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If user logged in, get their completion status
    if ($user_id) {
        foreach ($items as &$item) {
            $stmt = $pdo->prepare("SELECT completed, completed_at FROM user_checklist WHERE user_id = ? AND item_id = ?");
            $stmt->execute([$user_id, $item['id']]);
            $completion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $item['completed'] = $completion ? (bool)$completion['completed'] : false;
            $item['completed_at'] = $completion['completed_at'] ?? null;
        }
    } else {
        foreach ($items as &$item) {
            $item['completed'] = false;
            $item['completed_at'] = null;
        }
    }
    
    // Group by category
    $grouped = [];
    foreach ($items as $item) {
        $category = $item['category'];
        if (!isset($grouped[$category])) {
            $grouped[$category] = [];
        }
        $grouped[$category][] = $item;
    }
    
    echo json_encode([
        'success' => true,
        'items' => $items,
        'grouped' => $grouped,
        'total_items' => count($items)
    ]);
    
} catch (Exception $e) {
    error_log("Get checklist error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading checklist']);
}