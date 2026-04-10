<?php
// api/drills/get-published.php
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
    
    // Query to get published drills
    $sql = "SELECT 
                d.*,
                u.name as created_by_name,
                (SELECT COUNT(*) FROM drill_participations WHERE drill_id = d.id) as participant_count
            FROM drills d
            LEFT JOIN users u ON d.created_by = u.id
            WHERE d.status = 'published'
            ORDER BY d.created_at DESC
            LIMIT :limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $drills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates for better display
    foreach ($drills as &$drill) {
        if (isset($drill['created_at'])) {
            $drill['created_at_formatted'] = date('F j, Y g:i A', strtotime($drill['created_at']));
        }
        
        // Parse steps if JSON
        if (isset($drill['steps']) && is_string($drill['steps'])) {
            $decoded = json_decode($drill['steps'], true);
            if ($decoded !== null) {
                $drill['steps_parsed'] = $decoded;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $drills,
        'count' => count($drills),
        'message' => count($drills) > 0 ? 'Drills retrieved successfully' : 'No published drills found'
    ]);
    
} catch (PDOException $e) {
    error_log("Get published drills error: " . $e->getMessage());
    
    // Check if table doesn't exist
    if ($e->getCode() == '42S02') {
        echo json_encode([
            'success' => false,
            'message' => 'Drills table does not exist',
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
    error_log("General error in get-published.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred',
        'data' => []
    ]);
}