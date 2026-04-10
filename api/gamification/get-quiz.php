<?php
// api/gamification/get-quiz.php - Get random quiz questions
session_start();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../db_connect.php';
    
    $category = isset($_GET['category']) ? $_GET['category'] : 'general';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
    
    // Validate category
    $valid_categories = ['earthquake', 'typhoon', 'flood', 'landslide', 'fire', 'general'];
    if (!in_array($category, $valid_categories)) {
        $category = 'general';
    }
    
    // Get random questions from category
    $sql = "SELECT id, question, option_a, option_b, option_c, option_d, difficulty, points 
            FROM quiz_questions 
            WHERE category = ? AND active = 1 
            ORDER BY RAND() 
            LIMIT ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category, $limit]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'category' => $category,
        'questions' => $questions,
        'total' => count($questions)
    ]);
    
} catch (Exception $e) {
    error_log("Get quiz error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading quiz'
    ]);
}