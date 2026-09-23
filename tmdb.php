<?php
require_once __DIR__ . '/db.php';

function tmdb_configured(): bool {
    global $config;
    return !empty($config['tmdb']['token']) && $config['tmdb']['token'] !== 'YOUR_TMDB_API_READ_ACCESS_TOKEN';
}
function tmdb_request(string $path, array $params = []): array {
    global $config;
    if (!tmdb_configured()) throw new RuntimeException('TMDB_ACCESS_TOKEN is not configured.');
    $params += ['language'=>$config['tmdb']['language'], 'include_adult'=>'false'];
    $url = 'https://api.themoviedb.org/3/' . ltrim($path,'/') . '?' . http_build_query($params);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer '.$config['tmdb']['token'], 'Accept: application/json'],
        CURLOPT_USERAGENT => 'Mr-Rashidi-CineVerse/1.0',
    ]);
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($body === false || $error) throw new RuntimeException('TMDB connection failed.');
    $data = json_decode($body, true);
    if ($status < 200 || $status >= 300 || !is_array($data)) throw new RuntimeException('TMDB returned an error.');
    return $data;
}
function tmdb_image(?string $path, string $size='w500'): ?string {
    global $config;
    return $path ? $config['tmdb']['image_base'].$size.$path : null;
}
function normalize_media(array $item, string $type): array {
    $isTv = $type === 'tv';
    return [
        'id' => (int)$item['id'],
        'type' => $type,
        'title' => $item['title'] ?? $item['name'] ?? '',
        'original_title' => $item['original_title'] ?? $item['original_name'] ?? '',
        'year' => substr($item['release_date'] ?? $item['first_air_date'] ?? '', 0, 4),
        'poster' => tmdb_image($item['poster_path'] ?? null, 'w500'),
        'backdrop' => tmdb_image($item['backdrop_path'] ?? null, 'w1280'),
        'overview' => $item['overview'] ?? '',
        'rating' => isset($item['vote_average']) ? round((float)$item['vote_average'], 1) : null,
        'votes' => (int)($item['vote_count'] ?? 0),
        'genres' => $item['genre_ids'] ?? [],
    ];
}

function tmdb_cached(string $key, callable $loader, int $ttl=900): array {
    try {
        $stmt = db()->prepare('SELECT payload FROM api_cache WHERE cache_key=? AND expires_at>NOW()');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        if ($row) return json_decode($row['payload'], true) ?: [];
    } catch (Throwable $e) { /* cache is optional */ }
    $data = $loader();
    try {
        $stmt = db()->prepare('INSERT INTO api_cache(cache_key,payload,expires_at) VALUES(?,?,DATE_ADD(NOW(), INTERVAL ? SECOND)) ON DUPLICATE KEY UPDATE payload=VALUES(payload),expires_at=VALUES(expires_at)');
        $stmt->execute([$key, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), $ttl]);
    } catch (Throwable $e) { /* cache is optional */ }
    return $data;
}
