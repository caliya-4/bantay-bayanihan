<?php
/**
 * Render Health Check & Diagnostic Tool
 * Access at: https://your-app.onrender.com/render-health.php
 * DELETE THIS FILE after deployment for security!
 */

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Security: Only allow access with a secret parameter
$secret = isset($_GET['check']) ? $_GET['check'] : '';
if ($secret !== 'bantay2026') {
    http_response_code(403);
    die('Access denied. Add ?check=bantay2026 to access this diagnostic page.');
}

// Start output buffering to catch any errors
ob_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Render Health Check - Bantay Bayanihan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
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
            margin-bottom: 30px;
            font-size: 32px;
        }
        .check-item {
            padding: 20px;
            margin: 15px 0;
            border-radius: 12px;
            border-left: 5px solid;
        }
        .check-item.pass {
            background: #d4edda;
            border-color: #28a745;
        }
        .check-item.fail {
            background: #f8d7da;
            border-color: #dc3545;
        }
        .check-item.warn {
            background: #fff3cd;
            border-color: #ffc107;
        }
        .check-item.info {
            background: #d1ecf1;
            border-color: #17a2b8;
        }
        .check-title {
            font-weight: 800;
            font-size: 18px;
            margin-bottom: 8px;
        }
        .check-detail {
            font-size: 14px;
            line-height: 1.6;
            color: #495057;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 13px;
            font-family: 'Courier New', monospace;
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
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            margin: 10px 5px 10px 0;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Render Health Check</h1>
        
        <?php
        $allPassed = true;
        
        // Check 1: PHP Version
        echo '<div class="check-item pass">';
        echo '<div class="check-title">✓ PHP Version</div>';
        echo '<div class="check-detail">PHP ' . phpversion() . ' is running</div>';
        echo '</div>';
        
        // Check 2: Apache Modules
        echo '<div class="check-item pass">';
        echo '<div class="check-title">✓ Apache Modules</div>';
        echo '<div class="check-detail">';
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            $required = ['mod_rewrite', 'mod_headers', 'mod_ssl'];
            foreach ($required as $mod) {
                $status = in_array($mod, $modules) ? '✓' : '✗';
                echo "$status $mod<br>";
                if (!in_array($mod, $modules)) $allPassed = false;
            }
        } else {
            echo "Cannot retrieve Apache modules (not running on Apache)";
        }
        echo '</div></div>';
        
        // Check 3: PDO MySQL Extension
        echo '<div class="check-item ' . (extension_loaded('pdo_mysql') ? 'pass' : 'fail') . '">';
        echo '<div class="check-title">' . (extension_loaded('pdo_mysql') ? '✓' : '✗') . ' PDO MySQL Extension</div>';
        echo '<div class="check-detail">';
        if (extension_loaded('pdo_mysql')) {
            echo 'Extension is loaded and available';
        } else {
            echo 'ERROR: PDO MySQL extension is not loaded!';
            $allPassed = false;
        }
        echo '</div></div>';
        
        // Check 4: Environment Variables
        echo '<div class="check-item info">';
        echo '<div class="check-title">ℹ Environment Variables</div>';
        echo '<div class="check-detail">';
        require_once __DIR__ . '/config.php';
        
        $dbHost = env('DB_HOST', 'not set');
        $dbName = env('DB_NAME', 'not set');
        $dbUser = env('DB_USERNAME', 'not set');
        $dbPass = env('DB_PASSWORD', 'not set');
        $appEnv = env('APP_ENV', 'not set');
        $appDebug = env('APP_DEBUG', 'not set');
        
        echo '<pre>';
        echo "DB_HOST=" . (empty($dbHost) ? '<span style="color:#dc3545">not set</span>' : '<span style="color:#28a745">' . htmlspecialchars($dbHost) . '</span>') . "\n";
        echo "DB_NAME=" . (empty($dbName) ? '<span style="color:#dc3545">not set</span>' : '<span style="color:#28a745">' . htmlspecialchars($dbName) . '</span>') . "\n";
        echo "DB_USERNAME=" . (empty($dbUser) ? '<span style="color:#dc3545">not set</span>' : '<span style="color:#28a745">' . htmlspecialchars($dbUser) . '</span>') . "\n";
        echo "DB_PASSWORD=" . (empty($dbPass) ? '<span style="color:#dc3545">not set</span>' : '<span style="color:#28a745">********</span>') . "\n";
        echo "APP_ENV=" . htmlspecialchars($appEnv) . "\n";
        echo "APP_DEBUG=" . ($appDebug ? 'true' : 'false') . "\n";
        echo '</pre>';
        
        if (empty($dbHost) || empty($dbName) || empty($dbUser) || empty($dbPass)) {
            echo '<strong style="color:#dc3545">⚠ WARNING: Database credentials are not set!</strong>';
            $allPassed = false;
        }
        echo '</div></div>';
        
        // Check 5: Database Connection
        if (!empty($dbHost) && !empty($dbName) && !empty($dbUser)) {
            try {
                $pdo = new PDO(
                    "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
                    $dbUser,
                    $dbPass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                
                echo '<div class="check-item pass">';
                echo '<div class="check-title">✓ Database Connection</div>';
                echo '<div class="check-detail">Successfully connected to database</div>';
                echo '</div>';
                
                // Check 6: Database Tables
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($tables)) {
                    echo '<div class="check-item pass">';
                    echo '<div class="check-title">✓ Database Tables (' . count($tables) . ')</div>';
                    echo '<div class="check-detail">' . implode(', ', array_map('htmlspecialchars', $tables)) . '</div>';
                    echo '</div>';
                } else {
                    echo '<div class="check-item warn">';
                    echo '<div class="check-title">⚠ No Database Tables</div>';
                    echo '<div class="check-detail">Database is connected but has no tables. Import the schema from <code>db/bantay_bayanihan.sql</code></div>';
                    echo '</div>';
                }
                
            } catch (PDOException $e) {
                echo '<div class="check-item fail">';
                echo '<div class="check-title">✗ Database Connection Failed</div>';
                echo '<div class="check-detail">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '</div>';
                $allPassed = false;
            }
        }
        
        // Check 7: File Permissions
        echo '<div class="check-item ' . (is_writable(__DIR__ . '/uploads') ? 'pass' : 'warn') . '">';
        echo '<div class="check-title">' . (is_writable(__DIR__ . '/uploads') ? '✓' : '⚠') . ' Uploads Directory</div>';
        echo '<div class="check-detail">';
        if (is_dir(__DIR__ . '/uploads')) {
            if (is_writable(__DIR__ . '/uploads')) {
                echo 'Uploads directory exists and is writable';
            } else {
                echo 'Uploads directory exists but is NOT writable - file uploads will fail!';
            }
        } else {
            echo 'Uploads directory does not exist - create it for file uploads to work';
        }
        echo '</div></div>';
        
        // Check 8: PHP Extensions
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'gd', 'json', 'curl', 'xml'];
        echo '<div class="check-item info">';
        echo '<div class="check-title">ℹ PHP Extensions</div>';
        echo '<div class="check-detail">';
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            echo ($loaded ? '✓' : '✗') . ' ' . $ext . '<br>';
            if (!$loaded) $allPassed = false;
        }
        echo '</div></div>';
        
        // Check 9: Server Info
        echo '<div class="check-item info">';
        echo '<div class="check-title">ℹ Server Information</div>';
        echo '<div class="check-detail">';
        echo '<pre>';
        echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
        echo "Server Name: " . $_SERVER['SERVER_NAME'] . "\n";
        echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
        echo "Script Filename: " . __FILE__ . "\n";
        echo "PHP SAPI: " . php_sapi_name() . "\n";
        echo "Max Upload Size: " . ini_get('upload_max_filesize') . "\n";
        echo "Post Max Size: " . ini_get('post_max_size') . "\n";
        echo "Memory Limit: " . ini_get('memory_limit') . "\n";
        echo "Display Errors: " . ini_get('display_errors') . "\n";
        echo "Error Reporting: " . error_reporting() . "\n";
        echo '</pre>';
        echo '</div></div>';
        
        // Final Status
        if ($allPassed) {
            echo '<div class="check-item pass">';
            echo '<div class="check-title">✓ Overall Status: ALL CHECKS PASSED</div>';
            echo '<div class="check-detail">Your application should be working correctly!</div>';
            echo '</div>';
        } else {
            echo '<div class="check-item fail">';
            echo '<div class="check-title">✗ Overall Status: ISSUES DETECTED</div>';
            echo '<div class="check-detail">Please fix the issues above before proceeding</div>';
            echo '</div>';
        }
        
        // Actions
        echo '<hr style="margin: 30px 0;">';
        echo '<h2 style="margin-bottom: 20px;">Actions</h2>';
        echo '<a href="/" class="btn">Go to Homepage</a>';
        echo '<a href="/setup-database.php?setup=true" class="btn">Setup Database</a>';
        echo '<a href="?check=bantay2026&refresh=1" class="btn">Refresh Check</a>';
        echo '<a href="?delete=1" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this diagnostic file?\');">Delete This File</a>';
        
        // Delete self
        if (isset($_GET['delete']) && $_GET['delete'] === '1') {
            try {
                unlink(__FILE__);
                echo '<div class="check-item pass" style="margin-top: 20px;">';
                echo '<div class="check-title">✓ File Deleted</div>';
                echo '<div class="check-detail">Diagnostic file has been removed successfully</div>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="check-item fail" style="margin-top: 20px;">';
                echo '<div class="check-title">✗ Delete Failed</div>';
                echo '<div class="check-detail">Could not delete file: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '</div>';
            }
        }
        ?>
        
    </div>
</body>
</html>
<?php
// Flush output buffer
ob_end_flush();
?>
