<?php
// api/announcements/get-latest.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once __DIR__ . '/../../db_connect.php';
    
    // Get optional limit parameter
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    
    // Validate limit
    if ($limit < 1 || $limit > 50) {
        $limit = 10;
    }
    
    // Query to get latest announcements
    $sql = "SELECT 
                a.*,
                u.name as author_name
            FROM announcements a
            LEFT JOIN users u ON a.created_by = u.id
            ORDER BY a.created_at DESC
            LIMIT :limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates and truncate long messages for preview
    foreach ($announcements as &$announcement) {
        // Format date
        if (isset($announcement['created_at'])) {
            $announcement['created_at_formatted'] = date('F j, Y g:i A', strtotime($announcement['created_at']));
            $announcement['created_at_short'] = date('M j, Y', strtotime($announcement['created_at']));
        }
        
        // Create preview of message (first 150 characters)
        if (isset($announcement['message']) && strlen($announcement['message']) > 150) {
            $announcement['message_preview'] = substr($announcement['message'], 0, 150) . '...';
        } else {
            $announcement['message_preview'] = $announcement['message'] ?? '';
        }
        
        // Parse priority if exists
        if (!isset($announcement['priority'])) {
            $announcement['priority'] = 'normal';
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $announcements,
        'count' => count($announcements),
        'message' => count($announcements) > 0 ? 'Announcements retrieved successfully' : 'No announcements found'
    ]);
    
} catch (PDOException $e) {
    error_log("Get announcements error: " . $e->getMessage());
    
    // Check if table doesn't exist
    if ($e->getCode() == '42S02') {
        echo json_encode([
            'success' => false,
            'message' => 'Announcements table does not exist',
            'data' => [],
            'error_code' => 'TABLE_NOT_FOUND'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred',
            'data' => [],
            'error_code' => 'DB_ERROR'
        ]);
    }
} catch (Exception $e) {
    error_log("General error in get-latest.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred',
        'data' => []
    ]);
}