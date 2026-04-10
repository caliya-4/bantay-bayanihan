<?php
// api/gamification/save-certification.php
header('Content-Type: application/json');
session_start();
require_once dirname(__FILE__) . '/../../db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$score    = isset($input['score'])    ? (int)$input['score']    : 0;
$user_id  = isset($input['user_id'])  ? (int)$input['user_id']  : null;
$email    = isset($input['email'])    ? trim($input['email'])    : null;
$barangay = isset($input['barangay']) ? trim($input['barangay']) : null;

if ($score < 75) {
    echo json_encode(['success' => false, 'message' => 'Score below passing threshold']);
    exit;
}

// Generate a unique certificate code
$cert_code = 'BB-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8)) . '-' . date('Y');

try {
    $stmt = $pdo->prepare("
        INSERT INTO certifications 
            (user_id, guest_email, guest_barangay, certificate_code, score, total_questions, percentage, passed, issued_at, created_at)
        VALUES 
            (?, ?, ?, ?, ?, 20, ?, 1, NOW(), NOW())
    ");
    $stmt->execute([$user_id, $email, $barangay, $cert_code, $score, $score]);

    echo json_encode([
        'success'          => true,
        'certificate_code' => $cert_code,
        'message'          => 'Certification saved successfully!'
    ]);
} catch (PDOException $e) {
    // Still return success to user even if DB save fails
    echo json_encode([
        'success'          => true,
        'certificate_code' => $cert_code,
        'message'          => 'Certification issued (DB note: ' . $e->getMessage() . ')'
    ]);
}
