<?php
// api/gamification/submit-answer.php - Submit quiz answer
session_start();
header('Content-Type: application/json');

// allow anonymous users to take mini quizzes; stats will only be recorded for logged-in users
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

try {
    require_once __DIR__ . '/../../db_connect.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    $question_id = $input['question_id'] ?? 0;
    $selected_answer = strtoupper($input['selected_answer'] ?? '');
    // note: $user_id already set earlier from session, don't override (may be null)
    
    // Get question details
    $stmt = $pdo->prepare("SELECT correct_answer, points, explanation, question FROM quiz_questions WHERE id = ?");
    $stmt->execute([$question_id]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$question) {
        echo json_encode(['success' => false, 'message' => 'Question not found']);
        exit;
    }
    
    $is_correct = ($selected_answer === $question['correct_answer']);
    $points_earned = $is_correct ? $question['points'] : 0;
    
    // record attempt and stats only if we have a logged in user
    if ($user_id) {
        // Record attempt
        $stmt = $pdo->prepare("INSERT INTO quiz_attempts (user_id, question_id, selected_answer, is_correct, points_earned) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $question_id, $selected_answer, $is_correct, $points_earned]);
        
        // Update user stats
        $pdo->beginTransaction();
        
        // Initialize user_stats if not exists
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_stats (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        
        // Update stats
        if ($is_correct) {
            $stmt = $pdo->prepare("UPDATE user_stats SET total_points = total_points + ?, quizzes_completed = quizzes_completed + 1, last_quiz_date = CURDATE() WHERE user_id = ?");
            $stmt->execute([$points_earned, $user_id]);
        }
        
        $pdo->commit();
    }
    
    echo json_encode([
        'success' => true,
        'is_correct' => $is_correct,
        'correct_answer' => $question['correct_answer'],
        'explanation' => $question['explanation'],
        'points_earned' => $points_earned,
        'question_text' => $question['question']
    ]);
    
} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    error_log("Submit answer error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error submitting answer']);
}