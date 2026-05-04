<?php
// Configurações do ambiente
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development'); // production ou development

// Configurações do banco de dados
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'biscoitos_sorte');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// URL base do site
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost');

// Configurações de segurança
define('SESSION_LIFETIME', 7200); // 2 horas
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@biscoitosdasorte.com');

// Configurações de pagamento
define('MERCADOPAGO_PUBLIC_KEY', getenv('MP_PUBLIC_KEY') ?: '');
define('MERCADOPAGO_ACCESS_TOKEN', getenv('MP_ACCESS_TOKEN') ?: '');

// Configurações de email
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_FROM', getenv('SMTP_FROM') ?: 'noreply@biscoitosdasorte.com');

// Configurações de erro
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Sessão segura (configurar ANTES de session_start)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Temporário: desabilitado para debug
    ini_set('session.cookie_samesite', 'Lax');
}
