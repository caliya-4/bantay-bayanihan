<?php
// api/gamification/get-user-stats.php - Get user statistics
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    require_once __DIR__ . '/../../db_connect.php';
    
    $user_id = $_SESSION['user_id'];
    
    // Get user info
    $stmt = $pdo->prepare("SELECT name, barangay FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Initialize stats if not exists
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_stats (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    
    // Get user stats
    $stmt = $pdo->prepare("SELECT * FROM user_stats WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get total questions available
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quiz_questions WHERE active = 1");
    $total_questions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get checklist completion
    $stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM user_checklist WHERE user_id = ? AND completed = 1");
    $stmt->execute([$user_id]);
    $completed_items = $stmt->fetch(PDO::FETCH_ASSOC)['completed'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM checklist_items WHERE active = 1");
    $total_items = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Calculate preparedness score
    $quiz_completion = $total_questions > 0 ? ($stats['quizzes_completed'] / $total_questions) * 100 : 0;
    $checklist_completion = $total_items > 0 ? ($completed_items / $total_items) * 100 : 0;
    $drill_score = min($stats['drills_attended'] * 20, 100); // Max 5 drills = 100%
    
    $preparedness_score = ($quiz_completion * 0.3) + ($checklist_completion * 0.4) + ($drill_score * 0.3);
    
    // Update preparedness score
    $stmt = $pdo->prepare("UPDATE user_stats SET preparedness_score = ?, checklist_completion_rate = ? WHERE user_id = ?");
    $stmt->execute([$preparedness_score, $checklist_completion, $user_id]);
    
    // Get barangay rank
    $stmt = $pdo->prepare("
        SELECT COUNT(*) + 1 as rank
        FROM user_stats us
        JOIN users u ON us.user_id = u.id
        WHERE u.barangay = ? AND us.total_points > ?
    ");
    $stmt->execute([$user['barangay'], $stats['total_points']]);
    $rank_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'stats' => [
            'total_points' => $stats['total_points'],
            'quizzes_completed' => $stats['quizzes_completed'],
            'total_questions' => $total_questions,
            'drills_attended' => $stats['drills_attended'],
            'checklist_completed' => $completed_items,
            'checklist_total' => $total_items,
            'preparedness_score' => round($preparedness_score, 1),
            'barangay_rank' => $rank_data['rank'],
            'last_quiz_date' => $stats['last_quiz_date'],
            'last_drill_date' => $stats['last_drill_date']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get user stats error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading stats']);
}