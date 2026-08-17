<?php
// DO NOT ALTER WITHOUT APPROVAL — Application Entry Point
// Last modified: 2026-05-04
// Part of: SPED LMS — Front Controller

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_exception_handler(function($e) {
    echo "<div style='font-family:sans-serif; padding:20px; background:#fff3f3; border:1px solid #ffcdd2; border-radius:8px; margin:20px;'>";
    echo "<h2 style='color:#d32f2f; margin-top:0;'>Application Error</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Line " . $e->getLine() . ")</p>";
    echo "<pre style='background:#f5f5f5; padding:10px; border-radius:4px; overflow-x:auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
});

// Determine application root (supports both /project/public and /htdocs deployments)

$candidates = [realpath(__DIR__ . '/../'), realpath(__DIR__ . '/')] ;
$appRoot = null;
foreach ($candidates as $cand) {
    if ($cand === false) continue;
    // Heuristic: project app/ folder must exist
    if (is_dir($cand . '/app')) {
        $appRoot = rtrim($cand, '/\\') . '/';
        break;
    }
}
if ($appRoot === null) {
    // Fallback to parent directory
    $appRoot = rtrim(realpath(__DIR__ . '/../') ?: (__DIR__ . '/../'), '/\\') . '/';
}

// Load environment variables from .env file (try app root)
$envFile = $appRoot . '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (empty($line) || strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Composer autoloader (for PHPMailer and other dependencies)
$autoload1 = $appRoot . 'vendor/autoload.php';
if (file_exists($autoload1)) {
    require_once $autoload1;
}

// Detect base path automatically (MUST be before session start)
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptName === '/' ? '' : $scriptName;
define('BASE_PATH', $basePath);

// Start session
require_once $appRoot . 'app/Middleware/SessionMiddleware.php';
SessionMiddleware::start();

// Load configuration
require_once $appRoot . 'config/db.php';
require_once $appRoot . 'config/SchemaManager.php';

// Load middleware
require_once $appRoot . 'app/Middleware/RoleMiddleware.php';

// Load helpers
if (file_exists($appRoot . 'app/Helpers/MailHelper.php')) {
    require_once $appRoot . 'app/Helpers/MailHelper.php';
}
if (file_exists($appRoot . 'app/Helpers/StudentDisplayHelper.php')) {
    require_once $appRoot . 'app/Helpers/StudentDisplayHelper.php';
}

// Apply schema migrations on first run
$schemaManager = new SchemaManager();
$schemaManager->applyMigrations();

// Simple router
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove base path from request path
if ($basePath !== '' && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
$path = '/' . trim($path, '/');

// Load routes
require_once $appRoot . 'routes/web.php';
