<?php
require_once '../config/database.php';
require_once '../includes/security.php';

// Iniciar sessão após carregar configurações
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? 'login';
$error = '';
$success = '';

// Rate limiting desabilitado para desenvolvimento local
// if (!rate_limit('auth_attempt', 10, 600)) {
//     $error = 'Muitas tentativas. Aguarde alguns minutos.';
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    if ($action === 'register') {
        $nome = sanitize_input($_POST['nome'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        
        if (empty($nome) || empty($email) || empty($senha)) {
            $error = 'Todos os campos são obrigatórios';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email inválido';
        } elseif (strlen($senha) < 6) {
            $error = 'A senha deve ter no mínimo 6 caracteres';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Este email já está cadastrado';
            } else {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome_completo, email, senha) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$nome, $email, $senha_hash])) {
                    $_SESSION['usuario_id'] = $pdo->lastInsertId();
                    $_SESSION['usuario_nome'] = $nome;
                    session_regenerate_id(true);
                    log_activity("Novo usuário registrado: $email");
                    header('Location: categorias.php');
                    exit();
                } else {
                    $error = 'Erro ao criar conta';
                }
            }
        }
    } elseif ($action === 'login') {
        $email = sanitize_input($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $manter_conectado = isset($_POST['manter_conectado']);
        
        if (empty($email) || empty($senha)) {
            $error = 'Email e senha são obrigatórios';
        } else {
            try {
                // Tentar com is_admin primeiro
                $stmt = $pdo->prepare("SELECT id, nome_completo, senha, is_admin FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                $usuario = $stmt->fetch();
            } catch (PDOException $e) {
                // Se falhar, tentar sem is_admin (compatibilidade)
                try {
                    $stmt = $pdo->prepare("SELECT id, nome_completo, senha FROM usuarios WHERE email = ?");
                    $stmt->execute([$email]);
                    $usuario = $stmt->fetch();
                    if ($usuario) {
                        $usuario['is_admin'] = 0; // Definir como não-admin por padrão
                    }
                } catch (PDOException $e2) {
                    $error = 'Erro ao processar login';
                    log_activity("Erro no login: " . $e2->getMessage(), 'error');
                    $usuario = false;
                }
            }
            
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome_completo'];
                $_SESSION['is_admin'] = isset($usuario['is_admin']) ? (int)$usuario['is_admin'] : 0;
                
                // Se manter conectado, estender tempo da sessão
                if ($manter_conectado) {
                    ini_set('session.gc_maxlifetime', 2592000); // 30 dias
                    session_set_cookie_params(2592000); // 30 dias
                }
                
                session_regenerate_id(true);
                log_activity("Login realizado: $email");
                header('Location: categorias.php');
                exit();
            } else {
                $error = 'Email ou senha incorretos';
                log_activity("Tentativa de login falhou: $email", 'warning');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action === 'register' ? 'Criar Conta' : 'Entrar' ?> - Biscoitos da Sorte</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="padding-top: 80px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="font-size: 80px; margin-bottom: 24px;">🥠</div>
            <h1 style="font-size: 28px; margin-bottom: 8px;">Sua sorte espera</h1>
            <p style="color: var(--text-light);">Descubra o que o destino reservou</p>
        </div>

        <div class="card">
            <div style="display: flex; border-bottom: 2px solid #F0F0F0; margin-bottom: 24px;">
                <a href="?action=login" 
                   style="flex: 1; text-align: center; padding: 12px; text-decoration: none; color: <?= $action === 'login' ? 'var(--primary)' : 'var(--text-light)' ?>; border-bottom: 2px solid <?= $action === 'login' ? 'var(--primary)' : 'transparent' ?>; margin-bottom: -2px; font-weight: 600;">
                    Entrar
                </a>
                <a href="?action=register" 
                   style="flex: 1; text-align: center; padding: 12px; text-decoration: none; color: <?= $action === 'register' ? 'var(--primary)' : 'var(--text-light)' ?>; border-bottom: 2px solid <?= $action === 'register' ? 'var(--primary)' : 'transparent' ?>; margin-bottom: -2px; font-weight: 600;">
                    Criar Conta
                </a>
            </div>

            <?php if ($error): ?>
                <div style="background: #FFE5E5; color: #D32F2F; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?php if ($action === 'register'): ?>
                    <div class="form-group">
                        <label>Nome Completo</label>
                        <input type="text" name="nome" class="form-control" placeholder="Como devemos te chamar?" required>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="seu@email.com" required>
                </div>

                <div class="form-group">
                    <label>Senha</label>
                    <input type="password" name="senha" class="form-control" placeholder="Sua senha secreta" required>
                </div>

                <?php if ($action === 'login'): ?>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; cursor: pointer; font-weight: 400;">
                            <input type="checkbox" name="manter_conectado" value="1" style="margin-right: 8px; width: 18px; height: 18px; cursor: pointer;">
                            Manter conectado
                        </label>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary">
                    ✨ <?= $action === 'register' ? 'Começar Jornada' : 'Entrar' ?>
                </button>
            </form>

            <div style="text-align: center; margin-top: 24px; padding-top: 24px; border-top: 1px solid #F0F0F0;">
                <p style="font-size: 14px; color: var(--text-light); margin-bottom: 16px;">OU CONTINUE COM</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <a href="oauth_callback.php?provider=google" class="oauth-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Google
                    </a>
                    <a href="oauth_callback.php?provider=apple" class="oauth-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/></svg>
                        Apple
                    </a>
                </div>
            </div>

            <p style="font-size: 12px; color: var(--text-light); text-align: center; margin-top: 20px;">
                Ao continuar, você concorda com nossos 
                <a href="termos.php" style="color: var(--primary);">Termos de Serviço</a> e 
                <a href="privacidade.php" style="color: var(--primary);">Política de Privacidade</a>
            </p>
        </div>
    </div>
</body>
</html>
