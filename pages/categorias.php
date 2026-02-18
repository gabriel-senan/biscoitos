<?php
require_once '../config/database.php';

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

// Buscar nome do usuário
$stmt = $pdo->prepare("SELECT nome_completo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();
$primeiro_nome = explode(' ', $usuario['nome_completo'])[0];
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
        <div style="text-align: center; margin-bottom: 24px;">
            <h1 style="font-size: 32px; margin-bottom: 8px; font-weight: 600; letter-spacing: -0.02em;">Olá, <?= htmlspecialchars($primeiro_nome) ?>! 👋</h1>
            <p style="color: var(--text-light); font-size: 14px; font-weight: 400;">
                Escolha uma categoria para sua sorte.
            </p>
        </div>

        <form action="pagamento.php" method="POST" id="categoryForm">
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

            <div style="text-align: center; margin: 20px 0;">
                <div style="font-size: 60px; opacity: 0.8;">🥠</div>
            </div>

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
