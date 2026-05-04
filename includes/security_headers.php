<?php
/**
 * Headers de Segurança
 * Incluir no início de todas as páginas
 */

// Prevenir clickjacking
header('X-Frame-Options: SAMEORIGIN');

// Prevenir MIME type sniffing
header('X-Content-Type-Options: nosniff');

// Ativar proteção XSS do navegador
header('X-XSS-Protection: 1; mode=block');

// Política de referrer
header('Referrer-Policy: strict-origin-when-cross-origin');

// Permissões de recursos
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Content Security Policy
$csp = "default-src 'self'; ";
$csp .= "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; ";
$csp .= "style-src 'self' 'unsafe-inline'; ";
$csp .= "img-src 'self' data: https:; ";
$csp .= "font-src 'self' data:; ";
$csp .= "connect-src 'self'; ";
header("Content-Security-Policy: $csp");
