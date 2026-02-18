<?php
// Funções de segurança

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function check_auth() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . '/pages/auth.php');
        exit();
    }
}

function check_admin() {
    check_auth();
    // Adicionar verificação de role admin quando implementado
}

function rate_limit($key, $max_attempts = 5, $time_window = 300) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    $now = time();
    $key_data = $_SESSION['rate_limit'][$key] ?? ['count' => 0, 'start' => $now];
    
    if ($now - $key_data['start'] > $time_window) {
        $key_data = ['count' => 1, 'start' => $now];
    } else {
        $key_data['count']++;
    }
    
    $_SESSION['rate_limit'][$key] = $key_data;
    
    return $key_data['count'] <= $max_attempts;
}

function secure_redirect($url) {
    header('Location: ' . $url);
    exit();
}

function log_activity($message, $level = 'info') {
    $log_file = __DIR__ . '/../logs/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_id = $_SESSION['usuario_id'] ?? 'guest';
    
    $log_message = "[$timestamp] [$level] [User: $user_id] [IP: $ip] $message" . PHP_EOL;
    error_log($log_message, 3, $log_file);
}
