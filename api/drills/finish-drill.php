<?php
header('Content-Type: application/json');
session_start();
require '../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Method not allowed']);
	exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['drill_id']) || !isset($input['success'])) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Missing required fields']);
	exit;
}

if (!isset($_SESSION['user_id'])) {
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'Not authenticated']);
	exit;
}

$drill_id = (int)$input['drill_id'];
$success = ((int)$input['success'] === 1);
$user_id = (int)$_SESSION['user_id'];

// Ensure participation exists and is in_progress
$stmt = $pdo->prepare("SELECT id, status FROM drill_participations WHERE drill_id = ? AND user_id = ?");
$stmt->execute([$drill_id, $user_id]);
$part = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$part) {
	http_response_code(404);
	echo json_encode(['success' => false, 'message' => 'Participation not found']);
	exit;
}

if ($part['status'] !== 'in_progress') {
	echo json_encode(['success' => false, 'message' => 'Participation not in progress', 'status' => $part['status']]);
	exit;
}

$status = $success ? 'completed' : 'failed';

try {
	if ($success) {
		$stmt = $pdo->prepare("UPDATE drill_participations SET status = ?, finished_at = NOW(), score = 100 WHERE drill_id = ? AND user_id = ? AND status = 'in_progress'");
		$stmt->execute([$status, $drill_id, $user_id]);
	} else {
		$stmt = $pdo->prepare("UPDATE drill_participations SET status = ?, finished_at = NOW() WHERE drill_id = ? AND user_id = ? AND status = 'in_progress'");
		$stmt->execute([$status, $drill_id, $user_id]);
	}

	echo json_encode(['success' => true, 'message' => $success ? 'Mission marked completed' : 'Mission abandoned', 'status' => $status]);
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
