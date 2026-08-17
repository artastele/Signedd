<?php
// DO NOT ALTER WITHOUT APPROVAL — Environment Configuration
// Last modified: 2026-05-12
// Part of: SPED LMS — Environment Setup

/**
 * Environment Configuration Loader
 * Loads .env file variables into $_ENV and getenv()
 */

function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception('.env file not found at: ' . $path);
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
            
            // Remove quotes if present
            if (preg_match('/^"(.*)"$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                $value = $matches[1];
            }
            
            // Set environment variable
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Load environment variables
$envPath = __DIR__ . '/../.env';
loadEnv($envPath);

/**
 * Get environment variable with optional default
 */
function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

/**
 * Resolves absolute filesystem path for public assets & uploads
 * Automatically adapts between local development (/public/) and flat production hosting (/htdocs/)
 */
if (!function_exists('public_path')) {
    function public_path($path = '') {
        static $base = null;
        if ($base === null) {
            $root = dirname(__DIR__);
            $root = rtrim(str_replace('\\', '/', $root), '/') . '/';
            $scriptFile = isset($_SERVER['SCRIPT_FILENAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']) : '';
            
            if (strpos($scriptFile, '/public/') !== false) {
                $base = $root . 'public/';
            } else {
                $base = $root;
            }
        }
        $cleanPath = ltrim(str_replace('\\', '/', $path), '/');
        if (strpos($cleanPath, 'public/') === 0 && rtrim($base, '/') !== rtrim(dirname(__DIR__) . '/public', '/')) {
            $cleanPath = substr($cleanPath, 7);
        }
        return $cleanPath !== '' ? $base . $cleanPath : $base;
    }
}
