<?php
// Load configuration
require_once __DIR__ . '/config.php';

// Get database configuration from environment variables
$dbConfig = getDatabaseConfig();

$host = $dbConfig['host'];
$dbname = $dbConfig['dbname'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];

// Validate database credentials are set
if (empty($host) || empty($dbname) || empty($username)) {
    error_log("Database Configuration Error: Missing required database credentials");
    
    // Show helpful error in development, generic error in production
    $isDebug = env('APP_DEBUG', false);
    if ($isDebug) {
        die("<h1>Database Configuration Error</h1>
             <p>Database credentials are not properly configured.</p>
             <p>Please set these environment variables in Render:</p>
             <ul>
             <li><code>DB_HOST</code></li>
             <li><code>DB_NAME</code></li>
             <li><code>DB_USERNAME</code></li>
             <li><code>DB_PASSWORD</code></li>
             </ul>
             <p>Current values:</p>
             <pre>
             DB_HOST: " . (empty($host) ? '(not set)' : $host) . "
             DB_NAME: " . (empty($dbname) ? '(not set)' : $dbname) . "
             DB_USERNAME: " . (empty($username) ? '(not set)' : $username) . "
             </pre>");
    } else {
        // In production, show generic message
        http_response_code(503);
        die("<h1>Service Unavailable</h1>
             <p>The application is currently being set up or experiencing issues.</p>
             <p>Please try again in a few minutes or contact support.</p>");
    }
}

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
    
    // Show helpful error based on environment
    $isDebug = env('APP_DEBUG', false);
    if ($isDebug) {
        die("<h1>Database Connection Failed</h1>
             <p>Could not connect to the database.</p>
             <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
             <p><strong>Troubleshooting:</strong></p>
             <ul>
             <li>Check that the database service is running in Render</li>
             <li>Verify database credentials in environment variables</li>
             <li>Ensure the database schema has been imported</li>
             </ul>");
    } else {
        // In production, show generic message
        http_response_code(503);
        die("<h1>Service Unavailable</h1>
             <p>The application is currently being set up or experiencing issues.</p>
             <p>Please try again in a few minutes or contact support.</p>");
    }
    
    // Don't die here - let the calling script handle the error
    throw $e;
}
?>