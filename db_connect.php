<?php
// Load configuration
require_once __DIR__ . '/config.php';

// Get database configuration from environment variables
$dbConfig = getDatabaseConfig();

$host = $dbConfig['host'];
$dbname = $dbConfig['dbname'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];

try {
    // Create PDO instance with proper error handling
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset={$dbConfig['charset']}",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    // Don't die here - let the calling script handle the error
    throw $e;
}
?>