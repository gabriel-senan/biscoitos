<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Esta é uma implementação simplificada
// Em produção, você precisaria configurar OAuth com Google e Apple

$provider = $_GET['provider'] ?? '';
$error = '';

if ($provider === 'google') {
    // Aqui você implementaria o fluxo OAuth do Google
    // Por enquanto, vamos simular
    $error = 'Login com Google ainda não configurado. Configure as credenciais OAuth no .env';
    
} elseif ($provider === 'apple') {
    // Aqui você implementaria o fluxo OAuth da Apple
    $error = 'Login com Apple ainda não configurado. Configure as credenciais OAuth no .env';
}

// Redirecionar de volta para login com mensagem
header('Location: auth.php?error=' . urlencode($error));
exit();
