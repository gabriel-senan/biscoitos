<?php
require_once '../config/database.php';
require_once '../includes/security.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $nome = sanitize_input($_POST['nome'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    $errors = [];
    
    // Validar nome
    if (empty($nome)) {
        $errors[] = 'Nome é obrigatório';
    }
    
    // Validar email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    
    // Verificar se email já existe (exceto o próprio usuário)
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt->execute([$email, $usuario_id]);
    if ($stmt->fetch()) {
        $errors[] = 'Este email já está em uso';
    }
    
    // Validar senha se fornecida
    if (!empty($nova_senha)) {
        if (strlen($nova_senha) < 6) {
            $errors[] = 'A senha deve ter no mínimo 6 caracteres';
        }
        if ($nova_senha !== $confirmar_senha) {
            $errors[] = 'As senhas não coincidem';
        }
    }
    
    if (empty($errors)) {
        // Atualizar dados
        if (!empty($nova_senha)) {
            // Atualizar com nova senha
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET nome_completo = ?, email = ?, senha = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $senha_hash, $usuario_id]);
        } else {
            // Atualizar sem mudar senha
            $stmt = $pdo->prepare("UPDATE usuarios SET nome_completo = ?, email = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $usuario_id]);
        }
        
        // Atualizar sessão
        $_SESSION['usuario_nome'] = $nome;
        
        log_activity("Perfil atualizado: $email");
        
        // Redirecionar com sucesso
        header('Location: perfil.php?success=1');
        exit();
    } else {
        // Redirecionar com erro
        $error_msg = implode(', ', $errors);
        header('Location: perfil.php?error=' . urlencode($error_msg));
        exit();
    }
} else {
    header('Location: perfil.php');
    exit();
}
