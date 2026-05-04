<?php
// DO NOT ALTER WITHOUT APPROVAL — Application Entry Point
// Last modified: 2026-05-01
// Part of: SPED LMS — Front Controller

// Composer autoloader (for PHPMailer and other dependencies)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Detect base path automatically (MUST be before session start)
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptName === '/' ? '' : $scriptName;
define('BASE_PATH', $basePath);

// Start session
require_once __DIR__ . '/../app/Middleware/SessionMiddleware.php';
SessionMiddleware::start();

// Load configuration
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/SchemaManager.php';

// Load middleware
require_once __DIR__ . '/../app/Middleware/RoleMiddleware.php';

// Load helpers
if (file_exists(__DIR__ . '/../app/Helpers/MailHelper.php')) {
    require_once __DIR__ . '/../app/Helpers/MailHelper.php';
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
require_once __DIR__ . '/../routes/web.php';
