<?php
require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já estiver logado, redirecionar para o perfil
if (isset($_SESSION['usuario_id'])) {
    header('Location: pages/perfil.php');
    exit();
}

// Redireciona para landing page
header('Location: pages/landing.php');
exit();
