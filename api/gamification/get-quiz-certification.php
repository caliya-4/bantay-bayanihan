<?php
// api/gamification/get-quiz-certification.php - Get comprehensive certification quiz questions
session_start();
header('Content-Type: application/json');


try {
    require_once __DIR__ . '/../../db_connect.php';
    
    // Get 20 random questions covering all categories (3-4 per category)
    $categories = ['earthquake', 'typhoon', 'flood', 'landslide', 'fire', 'general'];
    $all_questions = [];
    
    foreach ($categories as $category) {
        $sql = "SELECT id, question, option_a, option_b, option_c, option_d, difficulty, points, explanation
                FROM quiz_questions 
                WHERE category = ? AND active = 1 
                ORDER BY RAND() 
                LIMIT 3";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $all_questions = array_merge($all_questions, $questions);
    }
    
    // Shuffle all questions
    shuffle($all_questions);
    
    // Limit to 20 questions
    $all_questions = array_slice($all_questions, 0, 20);
    
    echo json_encode([
        'success' => true,
        'questions' => $all_questions,
        'total' => count($all_questions),
        'type' => 'certification'
    ]);
    
} catch (Exception $e) {
    error_log("Get certification quiz error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading quiz'
    ]);
}
