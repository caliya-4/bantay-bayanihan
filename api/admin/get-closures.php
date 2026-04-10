<?php
// api/admin/get-closures.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once __DIR__ . '/../../db_connect.php';
    
    // Get filter parameters
    $status = isset($_GET['status']) ? trim($_GET['status']) : 'active';
    
    // Build query
    $sql = "SELECT 
                rc.*,
                u.name as created_by_name
            FROM road_closures rc
            LEFT JOIN users u ON rc.created_by = u.id";
    
    // Add status filter if not 'all'
    if ($status !== 'all') {
        $sql .= " WHERE rc.status = :status";
    }
    
    $sql .= " ORDER BY rc.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    
    if ($status !== 'all') {
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $closures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $closures,
        'count' => count($closures)
    ]);
    
} catch (PDOException $e) {
    error_log("Get closures error: " . $e->getMessage());
    
    // Check if table doesn't exist
    if ($e->getCode() == '42S02') {
        echo json_encode([
            'success' => false,
            'message' => 'Road closures table does not exist',
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
    error_log("General error in get-closures.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred',
        'data' => []
    ]);
}