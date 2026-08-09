<?php
require_once __DIR__ . '/../config/env.php';
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

echo "Google_Client class exists: " . (class_exists('Google_Client') ? "YES" : "NO") . "\n";
echo "GOOGLE_CLIENT_ID: " . env('GOOGLE_CLIENT_ID') . "\n";
echo "GOOGLE_CLIENT_SECRET: " . (empty(env('GOOGLE_CLIENT_SECRET')) ? 'EMPTY' : 'SET') . "\n";
echo "GOOGLE_REDIRECT_URI: " . env('GOOGLE_REDIRECT_URI') . "\n";
