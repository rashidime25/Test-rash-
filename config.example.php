<?php
// Copy to config.php and fill in your MySQL + TMDB credentials.
return [
    'app' => [
        'name' => 'Mr Rashidi',
        'url' => 'https://example.com',
        'session_name' => 'mr_rashidi_session',
    ],
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'mr_rashidi',
        'user' => 'YOUR_DB_USER',
        'pass' => 'YOUR_DB_PASSWORD',
        'charset' => 'utf8mb4',
    ],
    'tmdb' => [
        // TMDB API Read Access Token (Bearer). Keep this server-side.
        'token' => 'YOUR_TMDB_API_READ_ACCESS_TOKEN',
        'language' => 'fa-IR',
        'fallback_language' => 'en-US',
        'region' => 'US',
        'image_base' => 'https://image.tmdb.org/t/p/',
    ],
    'security' => [
        'secure_cookies' => false, // Set true after enabling HTTPS.
        'remember_days' => 30,
    ],
];
