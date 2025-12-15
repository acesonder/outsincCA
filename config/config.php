<?php
/**
 * OUTSINC - Main Configuration
 * Outreach Someone In Need of Change
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application settings
define('APP_NAME', 'OUTSINC');
define('APP_TAGLINE', 'Outreach Someone In Need of Change');
define('APP_VERSION', '1.0.0');

// Base URL - Auto-detect if running in subdirectory
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = $protocol . $host . (($scriptDir !== '/' && $scriptDir !== '\\') ? $scriptDir : '');
define('BASE_URL', $baseUrl);

// Timezone
date_default_timezone_set('America/Toronto');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes
$envPasscode = getenv('DEPLOYMENT_PASSCODE');
$appEnv = getenv('APP_ENV') ?: 'local';
$allowDefaultSetting = getenv('ALLOW_DEFAULT_DEPLOYMENT_PASSCODE');
$allowDefaultPasscode = ($allowDefaultSetting !== false)
    ? ($allowDefaultSetting === 'true')
    : ($appEnv === 'local');

if ($envPasscode !== false) {
    define('DEPLOYMENT_PASSCODE', $envPasscode);
} elseif ($appEnv === 'local' && $allowDefaultPasscode) {
    define('DEPLOYMENT_PASSCODE', '079777');
} else {
    throw new RuntimeException('DEPLOYMENT_PASSCODE must be set in non-local environments.');
}

// User roles
define('ROLE_CLIENT', 'client');
define('ROLE_WORKER', 'worker');
define('ROLE_PROVIDER', 'provider');
define('ROLE_ADMIN', 'admin');
define('ROLE_PUBLIC', 'public');

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Pagination
define('ITEMS_PER_PAGE', 20);

// Include database configuration
require_once __DIR__ . '/database.php';

// Autoload function for classes
spl_autoload_register(function ($class_name) {
    $paths = [
        __DIR__ . '/../includes/' . $class_name . '.php',
        __DIR__ . '/../modules/' . $class_name . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

/**
 * Get the base URL for assets
 * @param string $path Path relative to root (e.g., 'assets/css/styles.css')
 * @return string Full URL to the asset
 */
function asset_url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}
?>
