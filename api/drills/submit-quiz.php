<?php
// api/drills/submit-quiz.php
session_start();
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    require_once __DIR__ . '/../../db_connect.php';

    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }

    // Validate required fields
    $drill_id = isset($data['drill_id']) ? intval($data['drill_id']) : 0;
    $quiz_id = isset($data['quiz_id']) ? intval($data['quiz_id']) : 0;
    $participant_name = isset($data['participant_name']) ? trim($data['participant_name']) : '';
    $participant_email = isset($data['participant_email']) ? trim($data['participant_email']) : '';
    $answers = isset($data['answers']) ? $data['answers'] : [];
    $score = isset($data['score']) ? intval($data['score']) : 0;
    $correct_answers = isset($data['correct_answers']) ? intval($data['correct_answers']) : 0;
    $total_questions = isset($data['total_questions']) ? intval($data['total_questions']) : 0;
    $passed = isset($data['passed']) ? boolval($data['passed']) : false;

    // Validation
    if (!$drill_id || !$quiz_id) {
        echo json_encode(['success' => false, 'message' => 'Drill ID and Quiz ID are required']);
        exit;
    }

    if (empty($participant_name) || empty($participant_email)) {
        echo json_encode(['success' => false, 'message' => 'Participant name and email are required']);
        exit;
    }

    // Verify quiz belongs to drill
    $quiz_stmt = $pdo->prepare("SELECT q.* FROM drill_quizzes q WHERE q.id = ? AND q.drill_id = ?");
    $quiz_stmt->execute([$quiz_id, $drill_id]);
    $quiz = $quiz_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        echo json_encode(['success' => false, 'message' => 'Quiz not found or does not belong to this drill']);
        exit;
    }

    // Check if participant already submitted quiz results
    $check_stmt = $pdo->prepare("SELECT id FROM quiz_results WHERE drill_id = ? AND participant_email = ?");
    $check_stmt->execute([$drill_id, $participant_email]);

    if ($check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You have already submitted quiz results for this drill']);
        exit;
    }

    // Insert quiz results
    $insert_stmt = $pdo->prepare("INSERT INTO quiz_results
        (drill_id, participant_name, participant_email, quiz_id, score, total_questions, correct_answers, passed)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $result = $insert_stmt->execute([
        $drill_id,
        $participant_name,
        $participant_email,
        $quiz_id,
        $score,
        $total_questions,
        $correct_answers,
        0, // time_taken_seconds - placeholder for now
        $passed ? 1 : 0
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Quiz results submitted successfully',
            'data' => [
                'score' => $score,
                'passed' => $passed,
                'correct_answers' => $correct_answers,
                'total_questions' => $total_questions
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save quiz results']);
    }

} catch (PDOException $e) {
    error_log("Submit quiz error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in submit-quiz.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
