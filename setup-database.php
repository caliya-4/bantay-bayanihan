<?php
/**
 * Database Setup Script for Render Deployment
 * 
 * This script helps you import the database schema after deployment.
 * DELETE THIS FILE after successful setup for security.
 * 
 * Usage: Access via browser at https://your-app.onrender.com/setup-database.php
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Security: Only allow in development or when explicitly enabled
$appEnv = env('APP_ENV', 'production');
$setupEnabled = isset($_GET['setup']) && $_GET['setup'] === 'true';

if ($appEnv === 'production' && !$setupEnabled) {
    http_response_code(403);
    die('Access denied. Add ?setup=true to the URL to enable database setup.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Bantay Bayanihan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 14px;
        }
        
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .status.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px 5px 10px 0;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #5568d3;
        }
        
        button.danger {
            background: #dc3545;
        }
        
        button.danger:hover {
            background: #c82333;
        }
        
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 13px;
            margin: 10px 0;
        }
        
        .code {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛡️ Database Setup</h1>
        <p>This script will import the database schema for Bantay Bayanihan.</p>
        
        <?php
        if (isset($_POST['import'])) {
            echo '<h2 style="margin-top: 30px;">Importing Database Schema...</h2>';
            
            try {
                // Get database config
                $dbConfig = getDatabaseConfig();
                
                // Test connection
                echo '<div class="status info">Testing database connection...</div>';
                
                $pdo = new PDO(
                    "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
                    $dbConfig['username'],
                    $dbConfig['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                
                echo '<div class="status success">✓ Database connection successful</div>';
                
                // Check if tables already exist
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($tables)) {
                    echo '<div class="status warning">⚠ Database already contains ' . count($tables) . ' table(s). Importing anyway...</div>';
                }
                
                // Read SQL file
                $sqlFile = __DIR__ . '/db/bantay_bayanihan.sql';
                if (!file_exists($sqlFile)) {
                    throw new Exception('SQL file not found: ' . $sqlFile);
                }
                
                $sql = file_get_contents($sqlFile);
                
                // Execute SQL statements
                echo '<div class="status info">Importing schema from db/bantay_bayanihan.sql...</div>';
                
                $pdo->exec($sql);
                
                echo '<div class="status success">✓ Database schema imported successfully!</div>';
                
                // Verify tables
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                echo '<div class="status success">✓ Created ' . count($tables) . ' table(s): <code>' . implode(', ', $tables) . '</code></div>';
                
                echo '<div class="status info">';
                echo '<strong>⚠ IMPORTANT:</strong> Delete this file (setup-database.php) after setup for security reasons.<br><br>';
                echo '<button class="danger" onclick="if(confirm(\'Are you sure you want to delete this setup file?\')) { window.location.href=\'?delete=1\'; }">Delete Setup File</button>';
                echo '</div>';
                
            } catch (PDOException $e) {
                echo '<div class="status error">✗ Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<div class="status error">';
                echo '<strong>Troubleshooting:</strong><br>';
                echo '1. Check that environment variables are set correctly in Render<br>';
                echo '2. Verify the database service is running<br>';
                echo '3. Check Render logs for more details';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="status error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } elseif (isset($_GET['delete']) && $_GET['delete'] === '1') {
            try {
                $setupFile = __DIR__ . '/setup-database.php';
                if (file_exists($setupFile)) {
                    unlink($setupFile);
                    echo '<div class="status success">✓ Setup file deleted successfully. You can now access your application.</div>';
                    echo '<p><a href="/">Go to homepage</a></p>';
                }
            } catch (Exception $e) {
                echo '<div class="status error">Failed to delete file: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } else {
            // Show setup form
            $dbConfig = getDatabaseConfig();
            
            echo '<h2 style="margin-top: 30px;">Database Configuration</h2>';
            
            echo '<div class="status info">';
            echo '<strong>Current Database Settings:</strong><br>';
            echo 'Host: <span class="code">' . htmlspecialchars($dbConfig['host']) . '</span><br>';
            echo 'Database: <span class="code">' . htmlspecialchars($dbConfig['dbname']) . '</span><br>';
            echo 'Username: <span class="code">' . htmlspecialchars($dbConfig['username']) . '</span><br>';
            echo 'Password: <span class="code">' . (empty($dbConfig['password']) ? '(not set)' : '********') . '</span>';
            echo '</div>';
            
            echo '<form method="POST">';
            echo '<p>This will import the database schema from <span class="code">db/bantay_bayanihan.sql</span></p>';
            echo '<button type="submit" name="import" value="1">Import Database Schema</button>';
            echo '</form>';
            
            echo '<h2 style="margin-top: 30px;">Manual Setup (Alternative)</h2>';
            echo '<p>If the automatic import fails, you can manually import the schema:</p>';
            echo '<ol style="margin-left: 20px; line-height: 1.8;">';
            echo '<li>Download the SQL file from your Render dashboard</li>';
            echo '<li>Connect to your database using MySQL client or workbench</li>';
            echo '<li>Run the SQL file: <span class="code">mysql -h host -u user -p database < db/bantay_bayanihan.sql</span></li>';
            echo '</ol>';
            
            echo '<h2 style="margin-top: 30px;">Environment Variables</h2>';
            echo '<p>Make sure these are set in your Render dashboard:</p>';
            echo '<pre>';
            echo 'DB_HOST=' . htmlspecialchars($dbConfig['host']) . '<br>';
            echo 'DB_NAME=' . htmlspecialchars($dbConfig['dbname']) . '<br>';
            echo 'DB_USERNAME=' . htmlspecialchars($dbConfig['username']) . '<br>';
            echo 'DB_PASSWORD=********<br>';
            echo 'APP_ENV=' . htmlspecialchars($appEnv) . '<br>';
            echo 'APP_DEBUG=false';
            echo '</pre>';
        }
        ?>
        
        <hr style="margin: 30px 0;">
        <p style="font-size: 12px; color: #666;">
            <strong>Note:</strong> Delete this file after successful setup for security reasons.
        </p>
    </div>
</body>
</html>
