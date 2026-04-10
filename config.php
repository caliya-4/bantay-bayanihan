<?php
/**
 * Configuration loader for Bantay Bayanihan
 * Loads environment variables from .env file
 * 
 * Usage: require_once __DIR__ . '/config.php';
 */

// Prevent direct access
if (!defined('APP_CONFIG_LOADED')) {
    define('APP_CONFIG_LOADED', true);
}

/**
 * Load environment variables from .env file
 */
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key=value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Only set if not already defined in $_ENV
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
    
    return true;
}

// Load .env file
$envPath = __DIR__ . '/.env';
$envLoaded = loadEnv($envPath);

if (!$envLoaded) {
    // Log warning in production
    error_log('WARNING: .env file not found. Using system environment variables or defaults.');
}

/**
 * Get environment variable with optional default
 */
function env($key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key) ?? $_SERVER[$key] ?? $default;
    
    if ($value === 'false' || $value === 'true') {
        return $value === 'true';
    }
    
    return $value;
}

/**
 * Get database configuration
 */
function getDatabaseConfig() {
    return [
        'host' => env('DB_HOST', 'localhost'),
        'dbname' => env('DB_NAME', 'bantay_bayanihan'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4'
    ];
}

/**
 * Get SMTP configuration
 */
function getSMTPConfig() {
    return [
        'enabled' => env('SMTP_ENABLED', false),
        'host' => env('SMTP_HOST', 'smtp.gmail.com'),
        'port' => env('SMTP_PORT', 587),
        'username' => env('SMTP_USERNAME', ''),
        'password' => env('SMTP_PASSWORD', ''),
        'from_email' => env('SMTP_FROM_EMAIL', 'noreply@bantaybayanihan.com'),
        'from_name' => env('SMTP_FROM_NAME', 'Bantay Bayanihan')
    ];
}

/**
 * Get application settings
 */
function getAppSettings() {
    return [
        'env' => env('APP_ENV', 'production'),
        'debug' => env('APP_DEBUG', false),
        'url' => env('APP_URL', 'http://localhost'),
        'secret' => env('APP_SECRET', 'change-this-secret-key'),
        'upload_dir' => env('UPLOAD_DIR', __DIR__ . '/uploads'),
        'max_upload_size' => env('MAX_UPLOAD_SIZE', 5242880) // 5MB
    ];
}

/**
 * Get Gemini API configuration
 */
function getGeminiConfig() {
    return [
        'api_key' => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash')
    ];
}

/**
 * Check if application is in development mode
 */
function isDevelopment() {
    return env('APP_ENV', 'production') === 'development';
}

/**
 * Check if application is in production mode
 */
function isProduction() {
    return env('APP_ENV', 'production') === 'production';
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerate session ID securely
 */
function regenerateSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    session_regenerate_id(true);
}
