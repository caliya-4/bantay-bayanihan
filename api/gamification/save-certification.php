<?php
// api/gamification/save-certification.php - Save certification results
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// allow guests with email or logged-in users
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// parse input
$input = json_decode(file_get_contents('php://input'), true);
$email = isset($input['email']) ? filter_var($input['email'], FILTER_VALIDATE_EMAIL) : null;
if (!$user_id && !$email) {
    echo json_encode(['success' => false, 'message' => 'Must be logged in or provide a valid email']);
    exit;
}

// after this, $user_id may be null (guest) but $email will be set

try {
    require_once __DIR__ . '/../../db_connect.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    $score = isset($input['score']) ? intval($input['score']) : 0;
    
    if ($score < 75) {
        echo json_encode(['success' => false, 'message' => 'Score below passing threshold']);
        exit;
    }

    if ($user_id) {
        // Logged-in user path: save record to database
        try {
            $pdo->query("SELECT 1 FROM certifications LIMIT 1");
        } catch (Exception $e) {
            // create table with optional guest columns
            $pdo->exec("\
                CREATE TABLE IF NOT EXISTS `certifications` (\
                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,\
                    `user_id` int(11) DEFAULT NULL,\
                    `email` varchar(255) DEFAULT NULL,\
                    `barangay` varchar(255) DEFAULT NULL,\
                    `score` int(11) NOT NULL,\
                    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,\
                    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,\
                    INDEX `idx_user_id` (`user_id`),\
                    INDEX `idx_email` (`email`),\
                    INDEX `idx_barangay` (`barangay`)\
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci\
            ");
        }

        $stmt = $pdo->prepare("INSERT INTO certifications (user_id, score) VALUES (?, ?)");
        $result = $stmt->execute([$user_id, $score]);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Certification saved successfully',
                'certification_id' => $pdo->lastInsertId()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save certification']);
        }
    } else {
        // guest path - store record with email/barangay and send certificate via email
        try {
            // ensure table exists (should from user path but just in case)
            $pdo->query("SELECT 1 FROM certifications LIMIT 1");
        } catch (Exception $e) {
            $pdo->exec("\
                CREATE TABLE IF NOT EXISTS `certifications` (\
                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,\
                    `user_id` int(11) DEFAULT NULL,\
                    `email` varchar(255) DEFAULT NULL,\
                    `barangay` varchar(255) DEFAULT NULL,\
                    `score` int(11) NOT NULL,\
                    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,\
                    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,\
                    INDEX `idx_user_id` (`user_id`),\
                    INDEX `idx_email` (`email`),\
                    INDEX `idx_barangay` (`barangay`)\
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci\
            ");
        }
        // insert guest row
        $stmt = $pdo->prepare("INSERT INTO certifications (email, barangay, score) VALUES (?, ?, ?)");
        $stmt->execute([$email, $input['barangay'] ?? null, $score]);

        $to = $email;
        $subject = 'Your Bantay Bayanihan Certification';
        $body = "Congratulations! You passed the Bantay Bayanihan certification quiz with a score of {$score}%.\n\n" .
                "Barangay: " . ($input['barangay'] ?? 'N/A') . "\n\n" .
                "You can print this email as proof of completion.\n" .
                "Thank you for taking the quiz!";

        try {
            require_once __DIR__ . '/../../vendor/autoload.php';
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isMail();
            $mail->setFrom('noreply@bantaybayanihan.local', 'Bantay Bayanihan');
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();
        } catch (Exception $e) {
            error_log('Certification email error: ' . $e->getMessage());
            @mail($to, $subject, $body);
        }

        echo json_encode(['success' => true, 'message' => 'Certificate sent to email']);
    }
    
} catch (Exception $e) {
    error_log("Save certification error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
