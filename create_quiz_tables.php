<?php
require 'db_connect.php';

try {
    // Add quiz tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS drill_quizzes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            drill_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            passing_score INT DEFAULT 70,
            time_limit_minutes INT DEFAULT 30,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (drill_id) REFERENCES drills(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quiz_questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quiz_id INT NOT NULL,
            question_text TEXT NOT NULL,
            question_type ENUM('multiple_choice') DEFAULT 'multiple_choice',
            options JSON NOT NULL,
            correct_answer VARCHAR(255) NOT NULL,
            points INT DEFAULT 1,
            order_num INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (quiz_id) REFERENCES drill_quizzes(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quiz_results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            drill_id INT NOT NULL,
            user_id INT NULL,
            participant_name VARCHAR(255) NULL,
            participant_email VARCHAR(255) NULL,
            quiz_id INT NOT NULL,
            score INT NOT NULL,
            total_questions INT NOT NULL,
            correct_answers INT NOT NULL,
            passed TINYINT(1) DEFAULT 0,
            time_taken_seconds INT NULL,
            completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (drill_id) REFERENCES drills(id) ON DELETE CASCADE,
            FOREIGN KEY (quiz_id) REFERENCES drill_quizzes(id) ON DELETE CASCADE
        )
    ");

    echo 'Quiz tables created successfully';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
