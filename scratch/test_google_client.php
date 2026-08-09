<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../vendor/autoload.php';

$client = new Google_Client();
$client->setClientId(env('GOOGLE_CLIENT_ID'));
$client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));

// Configure Guzzle to bypass SSL verification locally if cacert.pem is missing
$guzzleClient = new \GuzzleHttp\Client(['verify' => false]);
$client->setHttpClient($guzzleClient);

echo "Configured Google_Client with Guzzle verify=false successfully!\n";
