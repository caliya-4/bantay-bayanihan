<?php
// api/gamification/get-leaderboard.php - Get barangay leaderboard
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../db_connect.php';
    
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    
    // Calculate preparedness scores for all barangays
    $sql = "SELECT 
                u.barangay,
                COUNT(DISTINCT u.id) as total_users,
                COUNT(DISTINCT CASE WHEN us.last_quiz_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN us.user_id END) as active_users,
                COALESCE(AVG(us.preparedness_score), 0) as avg_preparedness,
                COALESCE(SUM(us.total_points), 0) as total_points,
                COALESCE(AVG(us.drills_attended), 0) as avg_drills
            FROM users u
            LEFT JOIN user_stats us ON u.id = us.user_id
            WHERE u.barangay IS NOT NULL AND u.barangay != ''
            GROUP BY u.barangay
            HAVING total_users > 0
            ORDER BY avg_preparedness DESC, total_points DESC
            LIMIT ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limit]);
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add rank positions
    foreach ($leaderboard as $index => &$entry) {
        $entry['rank'] = $index + 1;
        $entry['avg_preparedness'] = round($entry['avg_preparedness'], 1);
        
        // Add medal emoji
        if ($entry['rank'] == 1) $entry['medal'] = '🥇';
        elseif ($entry['rank'] == 2) $entry['medal'] = '🥈';
        elseif ($entry['rank'] == 3) $entry['medal'] = '🥉';
        else $entry['medal'] = '';
    }
    
    echo json_encode([
        'success' => true,
        'leaderboard' => $leaderboard,
        'total' => count($leaderboard),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    error_log("Leaderboard error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading leaderboard'
    ]);
}