<?php
// Health check endpoint para monitoramento

header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

// Verificar conexão com banco
try {
    require_once 'config/database.php';
    $pdo->query('SELECT 1');
    $health['checks']['database'] = 'ok';
} catch (Exception $e) {
    $health['status'] = 'error';
    $health['checks']['database'] = 'error';
}

// Verificar permissões de escrita
if (is_writable(__DIR__ . '/logs')) {
    $health['checks']['logs_writable'] = 'ok';
} else {
    $health['status'] = 'warning';
    $health['checks']['logs_writable'] = 'error';
}

// Verificar espaço em disco
$disk_free = disk_free_space('/');
$disk_total = disk_total_space('/');
$disk_usage = (1 - ($disk_free / $disk_total)) * 100;

if ($disk_usage > 90) {
    $health['status'] = 'warning';
    $health['checks']['disk_space'] = 'warning';
} else {
    $health['checks']['disk_space'] = 'ok';
}

$health['checks']['disk_usage_percent'] = round($disk_usage, 2);

// Verificar versão do PHP
$health['checks']['php_version'] = PHP_VERSION;

http_response_code($health['status'] === 'ok' ? 200 : 503);
echo json_encode($health, JSON_PRETTY_PRINT);
