<?php
/**
 * Database Setup Script for Render PostgreSQL
 * This script will help you import the PostgreSQL schema
 * 
 * IMPORTANT: DELETE THIS FILE after setup for security!
 * 
 * Usage: Visit https://your-app.onrender.com/setup-postgres-db.php?setup=true&secret=bantay2026
 */

// Security check
$secret = isset($_GET['secret']) ? $_GET['secret'] : '';
$setupRequested = isset($_GET['setup']) && $_GET['setup'] === 'true';

if ($secret !== 'bantay2026') {
    http_response_code(403);
    die('<h1>Access Denied</h1><p>Add <code>?secret=bantay2026</code> to access this setup page.</p>');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Bantay Bayanihan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .status {
            padding: 20px;
            margin: 20px 0;
            border-radius: 12px;
            border-left: 5px solid;
        }
        .status.success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .status.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .status.info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            margin: 10px 5px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        pre {
            background: #2d3748;
            color: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
            margin: 15px 0;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
            font-family: 'Courier New', monospace;
        }
        .table-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }
        .table-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Database Setup for PostgreSQL</h1>
        <p class="subtitle">Bantay Bayanihan - Render Deployment</p>

        <?php
        // Get database configuration
        $dbConfig = getDatabaseConfig();
        
        echo '<div class="status info">';
        echo '<strong>Database Configuration Detected:</strong><br>';
        echo 'Type: <code>' . htmlspecialchars($dbConfig['type']) . '</code><br>';
        echo 'Host: <code>' . htmlspecialchars($dbConfig['host']) . '</code><br>';
        echo 'Database: <code>' . htmlspecialchars($dbConfig['dbname']) . '</code><br>';
        echo 'Port: <code>' . htmlspecialchars($dbConfig['port']) . '</code>';
        echo '</div>';

        if ($setupRequested) {
            echo '<h2 style="margin-top: 30px;">📦 Importing Database Schema...</h2>';
            
            $schemaFile = __DIR__ . '/db/bantay_bayanihan_postgresql.sql';
            
            if (!file_exists($schemaFile)) {
                echo '<div class="status error">';
                echo '<strong>❌ Error:</strong> Schema file not found at <code>' . htmlspecialchars($schemaFile) . '</code>';
                echo '</div>';
            } else {
                try {
                    // Read the SQL file
                    $sql = file_get_contents($schemaFile);
                    
                    if (empty($sql)) {
                        throw new Exception('SQL file is empty');
                    }
                    
                    // Execute the SQL
                    $pdo->exec($sql);
                    
                    echo '<div class="status success">';
                    echo '<strong>✅ Schema imported successfully!</strong><br>';
                    echo 'The PostgreSQL database structure has been created.';
                    echo '</div>';
                    
                    // Verify tables
                    $tables = $pdo->query("
                        SELECT table_name 
                        FROM information_schema.tables 
                        WHERE table_schema = 'public' 
                        ORDER BY table_name
                    ")->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (count($tables) > 0) {
                        echo '<div class="status info">';
                        echo '<strong>Tables created (' . count($tables) . '):</strong>';
                        echo '<div class="table-list">';
                        foreach ($tables as $table) {
                            echo '<div class="table-item">' . htmlspecialchars($table) . '</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                        
                        // Count some key tables
                        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                        $drillCount = $pdo->query("SELECT COUNT(*) FROM drills")->fetchColumn();
                        $announcementCount = $pdo->query("SELECT COUNT(*) FROM announcements")->fetchColumn();
                        
                        echo '<div class="status success">';
                        echo '<strong>Sample data imported:</strong><br>';
                        echo '• Users: ' . $userCount . '<br>';
                        echo '• Drills: ' . $drillCount . '<br>';
                        echo '• Announcements: ' . $announcementCount;
                        echo '</div>';
                    }
                    
                } catch (PDOException $e) {
                    echo '<div class="status error">';
                    echo '<strong>❌ Import Failed:</strong><br>';
                    echo 'Error: ' . htmlspecialchars($e->getMessage());
                    echo '<br><br>';
                    echo '<strong>Possible causes:</strong><br>';
                    echo '• Tables already exist (you can ignore this error if import was done before)<br>';
                    echo '• Permission issues with database user<br>';
                    echo '• Invalid SQL syntax in schema file';
                    echo '</div>';
                }
            }
        } else {
            echo '<div class="status warning">';
            echo '<strong>⚠️ Schema not imported yet</strong><br>';
            echo 'Click the button below to import the database schema.';
            echo '</div>';
            
            echo '<a href="?setup=true&secret=bantay2026" class="btn">📦 Import Database Schema</a>';
        }
        ?>

        <div class="warning-box">
            <h3>🔒 Security Warning</h3>
            <p><strong>DELETE THIS FILE after successful setup!</strong></p>
            <p>This script can be used to re-import the schema and should not be accessible in production.</p>
            <p><strong>How to delete:</strong></p>
            <ol style="margin-left: 20px; margin-top: 10px;">
                <li>Delete <code>setup-postgres-db.php</code> from your repository</li>
                <li>Or delete via Render Shell: <code>rm /var/www/html/setup-postgres-db.php</code></li>
            </ol>
        </div>

        <h3 style="margin-top: 30px;">📋 Next Steps</h3>
        <ol style="margin-left: 20px; line-height: 2;">
            <li>Import the database schema (click button above)</li>
            <li>Verify all tables were created</li>
            <li>Test your application at <a href="/"><?php echo $_SERVER['HTTP_HOST']; ?></a></li>
            <li><strong>DELETE this file for security</strong></li>
        </ol>

        <h3 style="margin-top: 30px;">🔍 Manual Import Alternative</h3>
        <p>If the automatic import fails, you can import manually:</p>
        <pre># Using Render Shell:
psql $DATABASE_URL -f /var/www/html/db/bantay_bayanihan_postgresql.sql

# Or from your local machine:
psql "your-database-url" -f db/bantay_bayanihan_postgresql.sql</pre>

        <div style="margin-top: 30px; text-align: center;">
            <a href="/" class="btn">🏠 Go to Homepage</a>
            <a href="/render-health.php?check=bantay2026" class="btn">🔍 Health Check</a>
        </div>
    </div>
</body>
</html>
