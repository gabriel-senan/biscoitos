<?php
require_once '../config/database.php';
require_once '../includes/creditos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth.php');
    exit();
}

// Verificar timeout de sessão
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    session_unset();
    session_destroy();
    header('Location: auth.php?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();

$stmt = $pdo->query("SELECT * FROM categorias WHERE ativo = 1");
$categorias = $stmt->fetchAll();

// Buscar nome do usuário e saldo de créditos
$stmt = $pdo->prepare("SELECT nome_completo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();
$primeiro_nome = explode(' ', $usuario['nome_completo'])[0];
$saldo_creditos = obterSaldoCreditos($pdo, $_SESSION['usuario_id']);

// Mensagens de sucesso/erro
$mensagem_sucesso = $_SESSION['mensagem_sucesso'] ?? null;
$mensagem_erro = $_SESSION['mensagem_erro'] ?? null;
unset($_SESSION['mensagem_sucesso'], $_SESSION['mensagem_erro']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escolha seu Destino - Biscoitos da Sorte</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <a href="categorias.php" style="font-size: 24px; text-decoration: none; color: var(--text-dark);">←</a>
        <div class="logo">Escolha seu Destino</div>
        <div style="display: flex; align-items: center; gap: 16px;">
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
            <a href="../admin/index.php" style="text-decoration: none; color: var(--text-dark); font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--secondary); border-radius: 8px; transition: all 0.3s;">
                📊 Dashboard
            </a>
            <?php endif; ?>
            <a href="perfil.php" style="font-size: 24px; text-decoration: none; color: var(--text-dark);">
                👤
            </a>
        </div>
    </div>

    <div class="container" style="padding-top: 40px;">
        <?php if ($mensagem_sucesso): ?>
            <div style="background: #E8F5E9; color: #2E7D32; padding: 16px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-weight: 500;">
                ✅ <?= htmlspecialchars($mensagem_sucesso) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($mensagem_erro): ?>
            <div style="background: #FFEBEE; color: #C62828; padding: 16px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-weight: 500;">
                ❌ <?= htmlspecialchars($mensagem_erro) ?>
            </div>
        <?php endif; ?>

        <!-- Saldo de Créditos -->
        <div class="card" style="background: linear-gradient(135deg, #FF8C42 0%, #FFB366 100%); color: white; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 4px;">Seus Créditos</div>
                    <div style="font-size: 32px; font-weight: 700;"><?= $saldo_creditos ?></div>
                    <div style="font-size: 12px; opacity: 0.8;">1 crédito = 1 biscoito</div>
                </div>
                <a href="comprar_creditos.php" style="background: white; color: var(--primary); padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    + Comprar
                </a>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 24px;">
            <h1 style="font-size: 32px; margin-bottom: 8px; font-weight: 600; letter-spacing: -0.02em;">Olá, <?= htmlspecialchars($primeiro_nome) ?>! 👋</h1>
            <p style="color: var(--text-light); font-size: 14px; font-weight: 400;">
                Escolha uma categoria para sua sorte.
            </p>
        </div>

        <form action="abrir_sorte.php" method="POST" id="categoryForm">
            <?php foreach ($categorias as $categoria): ?>
                <label class="category-card" onclick="selectCategory(this, <?= $categoria['id'] ?>)">
                    <div class="category-icon" style="background: <?= htmlspecialchars($categoria['cor']) ?>20;">
                        <?= htmlspecialchars($categoria['icone']) ?>
                    </div>
                    <div class="category-info" style="flex: 1;">
                        <h3><?= htmlspecialchars($categoria['nome']) ?></h3>
                        <p><?= htmlspecialchars($categoria['descricao']) ?></p>
                    </div>
                    <input type="radio" name="categoria_id" value="<?= $categoria['id'] ?>" style="display: none;">
                </label>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary" id="continueBtn" disabled>
                Continuar →
            </button>
        </form>
    </div>

    <!-- Navegação Mobile -->
    <nav class="mobile-nav">
        <a href="categorias.php" class="mobile-nav-item active">
            <span>🥠</span>
            Sortes
        </a>
        <a href="comprar_creditos.php" class="mobile-nav-item">
            <span>💳</span>
            Créditos
        </a>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <a href="../admin/index.php" class="mobile-nav-item">
            <span>📊</span>
            Admin
        </a>
        <?php endif; ?>
        <a href="perfil.php" class="mobile-nav-item">
            <span>👤</span>
            Perfil
        </a>
    </nav>

    <script>
        function selectCategory(element, id) {
            document.querySelectorAll('.category-card').forEach(card => {
                card.classList.remove('selected');
            });
            element.classList.add('selected');
            element.querySelector('input[type="radio"]').checked = true;
            document.getElementById('continueBtn').disabled = false;
        }
    </script>
</body>
</html>
