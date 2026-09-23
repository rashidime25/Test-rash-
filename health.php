<?php
header('Content-Type: application/json; charset=utf-8');
try {
    require_once __DIR__ . '/db.php';
    db()->query('SELECT 1');
    http_response_code(200);
    echo json_encode(['ok'=>true,'service'=>'mr-rashidi'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['ok'=>false,'service'=>'mr-rashidi'], JSON_UNESCAPED_UNICODE);
}
