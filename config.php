<?php
// Railway-ready production configuration. UI files are intentionally untouched.
function envv(string $key, ?string $fallback = null): ?string {
    $v = getenv($key);
    return ($v === false || $v === '') ? $fallback : $v;
}

return [
    'app' => [
        'name' => 'Mr Rashidi',
        'url' => envv('APP_URL', ''),
        'session_name' => envv('SESSION_NAME', 'mr_rashidi_session'),
    ],
    'db' => [
        // Railway MySQL automatically provides these variables.
        'host' => envv('MYSQLHOST', envv('MR_DB_HOST', '127.0.0.1')),
        'port' => (int)envv('MYSQLPORT', envv('MR_DB_PORT', '3306')),
        'name' => envv('MYSQLDATABASE', envv('MR_DB_NAME', 'mr_rashidi')),
        'user' => envv('MYSQLUSER', envv('MR_DB_USER', 'root')),
        'pass' => envv('MYSQLPASSWORD', envv('MR_DB_PASS', '')),
        'charset' => 'utf8mb4',
    ],
    'tmdb' => [
        'token' => envv('TMDB_ACCESS_TOKEN', ''),
        'language' => envv('TMDB_LANGUAGE', 'fa-IR'),
        'fallback_language' => envv('TMDB_FALLBACK_LANGUAGE', 'en-US'),
        'region' => envv('TMDB_REGION', 'US'),
        'image_base' => 'https://image.tmdb.org/t/p/',
    ],
    'security' => [
        'secure_cookies' => envv('FORCE_SECURE_COOKIES', '') === '1' || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'remember_days' => 30,
    ],
];
